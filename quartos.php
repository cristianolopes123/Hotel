<?php
/**
 * Quartos & Suítes (Público)
 * Hotel Mucinga Nzambi
 */

define('SYSTEM_ACCESS', true);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/rbac.php';
require_once __DIR__ . '/includes/helpers.php';

$db = getDB();

/**
 * Helpers locais (não conflitam com os teus includes)
 */
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

function calcularNoitesSafe($checkin, $checkout) {
    if (!$checkin || !$checkout) return 0;
    try {
        $inicio = new DateTime($checkin);
        $fim = new DateTime($checkout);
        $dias = $inicio->diff($fim)->days;
        return max(0, (int)$dias);
    } catch (Exception $e) {
        return 0;
    }
}

function formatKz($valor) {
    return number_format((float)$valor, 0, ',', '.') . ' Kz';
}

/**
 * GET Filters
 */
$checkin  = $_GET['checkin']  ?? '';
$checkout = $_GET['checkout'] ?? '';
$adultos  = max(1, (int)($_GET['adultos'] ?? 1));
$criancas = max(0, (int)($_GET['criancas'] ?? 0));

$tipoId   = (int)($_GET['tipo_quarto_id'] ?? 0);
$precoMin = (int)($_GET['preco_min'] ?? 0);
$precoMax = (int)($_GET['preco_max'] ?? 0);

$ordem    = $_GET['ordem'] ?? 'recomendado'; // recomendado | menor_preco | maior_preco | capacidade

$datasValidas = false;
if (!empty($checkin) && !empty($checkout) && $checkout > $checkin) {
    $datasValidas = true;
}

$dataTarifa = $datasValidas ? $checkin : date('Y-m-d');
$noites = $datasValidas ? calcularNoitesSafe($checkin, $checkout) : 0;

/**
 * Tipos para dropdown
 */
$tiposDropdown = $db->query("SELECT id, nome FROM tipos_quarto WHERE ativo = 1 ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);

/**
 * Buscar tipos com contagem de quartos disponíveis (status_ocupacao = DISPONIVEL)
 * e contagem total ativos (ATIVO)
 */
$sql = "
    SELECT 
        tq.*,
        (SELECT COUNT(*) FROM quartos q 
            WHERE q.tipo_quarto_id = tq.id 
            AND q.status='ATIVO'
        ) AS total_ativos,
        (SELECT COUNT(*) FROM quartos q 
            WHERE q.tipo_quarto_id = tq.id 
            AND q.status='ATIVO'
            AND q.status_ocupacao='DISPONIVEL'
        ) AS total_disponiveis
    FROM tipos_quarto tq
    WHERE tq.ativo = 1
";

$params = [];

/** filtro por tipo */
if ($tipoId > 0) {
    $sql .= " AND tq.id = ? ";
    $params[] = $tipoId;
}

/** filtro por capacidade */
$sql .= " AND tq.capacidade_adultos >= ? ";
$params[] = $adultos;

$sql .= " AND (tq.capacidade_adultos + tq.capacidade_criancas) >= ? ";
$params[] = ($adultos + $criancas);

$sql .= " ORDER BY tq.capacidade_adultos ASC ";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$tipos = $stmt->fetchAll(PDO::FETCH_ASSOC);

/**
 * Montar cards com preço/noite + total + disponibilidade
 */
$cards = [];

foreach ($tipos as $tq) {
    $amenidades = json_decode($tq['amenidades'] ?? '[]', true);
    if (!is_array($amenidades)) $amenidades = [];

    $precoNoite = (float) buscarTarifa((int)$tq['id'], $dataTarifa);

    // filtro por preço/noite
    if ($precoMin > 0 && $precoNoite < $precoMin) continue;
    if ($precoMax > 0 && $precoNoite > $precoMax) continue;

    $precoTotal = $datasValidas ? ($precoNoite * $noites) : 0;

    // disponibilidade real (usa tua função)
    $disponivel = true;
    $quartoLivreId = null;
    if ($datasValidas) {
        $quartoLivreId = verificarDisponibilidade((int)$tq['id'], $checkin, $checkout);
        $disponivel = (bool)$quartoLivreId;
    }

    // foto fallback
    $foto = !empty($tq['foto_capa']) ? $tq['foto_capa'] : 'imagens/pic3.jpg';

    $cards[] = [
        'id' => (int)$tq['id'],
        'nome' => $tq['nome'] ?? '',
        'descricao' => $tq['descricao'] ?? '',
        'cap_adultos' => (int)$tq['capacidade_adultos'],
        'cap_criancas' => (int)$tq['capacidade_criancas'],
        'amenidades' => $amenidades,
        'foto' => $foto,
        'total_ativos' => (int)($tq['total_ativos'] ?? 0),
        'total_disponiveis' => (int)($tq['total_disponiveis'] ?? 0),
        'preco_noite' => $precoNoite,
        'preco_total' => $precoTotal,
        'noites' => $noites,
        'datas_validas' => $datasValidas,
        'disponivel' => $disponivel,
        'quarto_livre_id' => $quartoLivreId
    ];
}

/**
 * Ordenação
 */
usort($cards, function($a, $b) use ($ordem) {
    if ($ordem === 'menor_preco') return $a['preco_noite'] <=> $b['preco_noite'];
    if ($ordem === 'maior_preco') return $b['preco_noite'] <=> $a['preco_noite'];
    if ($ordem === 'capacidade') {
        $capA = $a['cap_adultos'] + $a['cap_criancas'];
        $capB = $b['cap_adultos'] + $b['cap_criancas'];
        return $capB <=> $capA;
    }
    // recomendado: disponíveis primeiro + preço menor
    if ($a['disponivel'] !== $b['disponivel']) return $a['disponivel'] ? -1 : 1;
    return $a['preco_noite'] <=> $b['preco_noite'];
});

/**
 * Mensagens
 */
$mensagem = '';
if ($datasValidas && empty($cards)) {
    $mensagem = 'Nenhum tipo de quarto disponível com os filtros e datas selecionadas.';
} elseif (!$datasValidas && empty($cards)) {
    $mensagem = 'Nenhum tipo de quarto encontrado com os filtros selecionados.';
}

?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Quartos & Suítes | Hotel Mucinga Nzambi</title>

    <!-- Bootstrap Local -->
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">

    <!-- Icons (tu já usa FontAwesome na navbar; aqui usamos Bootstrap Icons no layout) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Estilos da navbar -->
    <?php include __DIR__ . '/includes/navbar-styles.php'; ?>

    <!-- Fonts (mantém teu padrão Inter/Playfair) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body{
            font-family: 'Inter', sans-serif;
            background: linear-gradient(180deg, #FDF7E6 0%, #ffffff 65%);
        }

        .page-hero{
            padding: 65px 0 25px 0;
            position: relative;
            overflow: hidden;
        }
        .page-hero::before{
            content:'';
            position:absolute; inset:0;
            background:
                radial-gradient(circle at 10% 25%, rgba(0,80,81,.12) 0%, transparent 50%),
                radial-gradient(circle at 90% 15%, rgba(242,141,0,.14) 0%, transparent 50%),
                radial-gradient(circle at 50% 95%, rgba(0,80,81,.08) 0%, transparent 55%);
            pointer-events:none;
        }
        .page-hero h1{
            font-family:'Playfair Display', serif;
            font-weight:800;
            color:#005051;
            letter-spacing:-0.8px;
            font-size: clamp(2.1rem, 3.1vw, 3.2rem);
            margin-bottom: 10px;
        }
        .page-hero p{
            color:#4b5563;
            max-width: 880px;
            margin: 0;
        }

        .hero-actions{
            display:flex;
            gap:10px;
            flex-wrap: wrap;
            justify-content:flex-end;
        }

        .btn-main{
            background: linear-gradient(135deg, #005051 0%, #006b6d 100%);
            border:none;
            color:#fff;
            font-weight:700;
            padding: 11px 18px;
            border-radius: 999px;
            box-shadow: 0 10px 28px rgba(0,80,81,.22);
            transition: all .25s ease;
            text-decoration:none;
            display:inline-flex;
            align-items:center;
            gap:8px;
        }
        .btn-main:hover{ transform: translateY(-2px); color:#fff; }

        .btn-soft{
            background: rgba(255,255,255,.9);
            border: 1px solid rgba(0,80,81,.18);
            color:#005051;
            font-weight:700;
            padding: 11px 18px;
            border-radius: 999px;
            box-shadow: 0 10px 28px rgba(0,0,0,.06);
            transition: all .25s ease;
            text-decoration:none;
            display:inline-flex;
            align-items:center;
            gap:8px;
        }
        .btn-soft:hover{ transform: translateY(-2px); color:#005051; }

        .wrap{
            padding-bottom: 60px;
        }

        .filter-card{
            background: rgba(255,255,255,.92);
            border: 1px solid rgba(0,80,81,.12);
            border-radius: 18px;
            padding: 18px;
            box-shadow: 0 12px 45px rgba(0,0,0,.07);
            position: sticky;
            top: 110px;
        }
        .filter-card h5{
            color:#005051;
            font-weight:900;
            margin: 0;
        }
        .filter-card .form-label{
            font-weight: 700;
            color:#0f172a;
            font-size: .9rem;
        }

        .room-card{
            border-radius: 18px;
            overflow: hidden;
            border: 1px solid rgba(0,0,0,.06);
            box-shadow: 0 14px 55px rgba(0,0,0,.08);
            background: #fff;
            transition: transform .25s ease, box-shadow .25s ease;
            height: 100%;
        }
        .room-card:hover{
            transform: translateY(-5px);
            box-shadow: 0 20px 70px rgba(0,0,0,.12);
        }

        .room-img{
            height: 220px;
            position: relative;
            overflow:hidden;
            background:#083b3c;
        }
        .room-img img{
            width:100%;
            height:100%;
            object-fit: cover;
            transform: scale(1.03);
            opacity:.95;
            transition: transform .45s ease;
        }
        .room-card:hover .room-img img{
            transform: scale(1.10);
        }

        .badge-price{
            position:absolute;
            top:14px; right:14px;
            background: linear-gradient(135deg, #F28D00 0%, #FFC107 100%);
            color:#111827;
            font-weight: 900;
            padding: 8px 12px;
            border-radius: 999px;
            box-shadow: 0 10px 25px rgba(242,141,0,.35);
            font-size: .92rem;
        }

        .badge-status{
            position:absolute;
            top:14px; left:14px;
            background: rgba(255,255,255,.88);
            border: 1px solid rgba(0,0,0,.08);
            backdrop-filter: blur(12px);
            padding: 8px 12px;
            border-radius: 999px;
            font-weight: 900;
            font-size: .9rem;
            display:flex;
            align-items:center;
            gap:8px;
        }
        .ok{ color:#065f46; }
        .no{ color:#b91c1c; }

        .room-body{ padding: 18px 18px 14px 18px; }
        .room-title{
            color:#005051;
            font-weight: 950;
            font-size: 1.25rem;
            margin:0;
        }
        .room-desc{
            color:#6b7280;
            margin-top: 8px;
            min-height: 44px;
        }

        .chips{
            margin-top: 12px;
            display:flex;
            gap:8px;
            flex-wrap: wrap;
        }
        .chip{
            background: #f8fafc;
            border: 1px solid rgba(0,0,0,.06);
            padding: 6px 10px;
            border-radius: 999px;
            font-size: .82rem;
            color:#0f172a;
            display:inline-flex;
            align-items:center;
            gap:7px;
        }

        .room-footer{
            border-top: 1px solid rgba(0,0,0,.06);
            padding: 14px 18px;
            display:flex;
            align-items:center;
            justify-content: space-between;
            gap: 12px;
        }
        .total{
            font-weight: 950;
            color:#0f172a;
        }
        .muted{ color:#6b7280; font-size: .88rem; }

        .btn-card{
            background: linear-gradient(135deg, #005051 0%, #006b6d 100%);
            border:none;
            color:#fff;
            font-weight:800;
            padding: 10px 14px;
            border-radius: 12px;
            text-decoration:none;
            display:inline-flex;
            align-items:center;
            gap:8px;
            transition: transform .2s ease;
            white-space: nowrap;
        }
        .btn-card:hover{ transform: translateY(-2px); color:#fff; }

        .btn-card-soft{
            background:#fff;
            border: 2px solid rgba(0,80,81,.22);
            color:#005051;
            font-weight:900;
            padding: 10px 14px;
            border-radius: 12px;
            text-decoration:none;
            display:inline-flex;
            align-items:center;
            gap:8px;
            transition: transform .2s ease;
            white-space: nowrap;
        }
        .btn-card-soft:hover{ transform: translateY(-2px); color:#005051; }

        .alert-custom{
            background: linear-gradient(135deg, rgba(0,80,81,0.10) 0%, rgba(242,141,0,0.10) 100%);
            border: 1px solid rgba(0,80,81,.25);
            border-radius: 14px;
            color:#005051;
        }

        @media(max-width: 991.98px){
            .filter-card{ position: relative; top: auto; }
            .hero-actions{ justify-content:flex-start; }
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/includes/navbar.php'; ?>

<section class="page-hero">
    <div class="container position-relative">
        <div class="d-flex flex-column flex-lg-row align-items-lg-end justify-content-between gap-3">
            <div>
                <h1>Quartos & Suítes</h1>
                <p>
                    Selecione o tipo ideal, aplique filtros e, se desejar, informe as datas para ver
                    a disponibilidade real para o seu período.
                </p>
            </div>
            <div class="hero-actions">
                <a class="btn-soft" href="disponibilidade.php"><i class="bi bi-search"></i> Ver disponibilidade</a>
                <a class="btn-main" href="disponibilidade.php"><i class="bi bi-calendar-check"></i> Reservar</a>
            </div>
        </div>
    </div>
</section>

<section class="wrap">
    <div class="container">
        <div class="row g-4">

            <!-- filtros -->
            <div class="col-lg-4">
                <form class="filter-card" method="GET" action="">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h5><i class="bi bi-funnel"></i> Filtros</h5>
                        <a class="btn btn-sm btn-outline-secondary" href="quartos.php">Limpar</a>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-12">
                            <label class="form-label">Tipo</label>
                            <select class="form-select" name="tipo_quarto_id">
                                <option value="0">Todos</option>
                                <?php foreach ($tiposDropdown as $t): ?>
                                    <option value="<?= (int)$t['id'] ?>" <?= ($tipoId==(int)$t['id']?'selected':'') ?>>
                                        <?= h($t['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-6">
                            <label class="form-label">Check-in</label>
                            <input type="date" class="form-control" name="checkin"
                                   value="<?= h($checkin) ?>" min="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Check-out</label>
                            <input type="date" class="form-control" name="checkout"
                                   value="<?= h($checkout) ?>" min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                        </div>

                        <div class="col-6">
                            <label class="form-label">Adultos</label>
                            <input type="number" class="form-control" name="adultos" min="1" value="<?= (int)$adultos ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Crianças</label>
                            <input type="number" class="form-control" name="criancas" min="0" value="<?= (int)$criancas ?>">
                        </div>

                        <div class="col-6">
                            <label class="form-label">Preço mín. (Kz)</label>
                            <input type="number" class="form-control" name="preco_min" min="0" value="<?= (int)$precoMin ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Preço máx. (Kz)</label>
                            <input type="number" class="form-control" name="preco_max" min="0" value="<?= (int)$precoMax ?>">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Ordenar</label>
                            <select class="form-select" name="ordem">
                                <option value="recomendado" <?= ($ordem==='recomendado'?'selected':'') ?>>Recomendado</option>
                                <option value="menor_preco" <?= ($ordem==='menor_preco'?'selected':'') ?>>Menor preço</option>
                                <option value="maior_preco" <?= ($ordem==='maior_preco'?'selected':'') ?>>Maior preço</option>
                                <option value="capacidade" <?= ($ordem==='capacidade'?'selected':'') ?>>Maior capacidade</option>
                            </select>
                        </div>

                        <div class="col-12 d-grid">
                            <button class="btn btn-main" type="submit" style="border-radius:14px;">
                                <i class="bi bi-search"></i> Aplicar
                            </button>
                        </div>

                        <div class="col-12">
                            <div class="alert alert-custom mb-0">
                                <div class="d-flex gap-2 align-items-start">
                                    <i class="bi bi-info-circle" style="font-size:1.05rem;"></i>
                                    <div class="small">
                                        Preços são calculados com base na tabela <strong>tarifas</strong>.
                                        Ao preencher datas, mostramos disponibilidade real do período.
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </form>
            </div>

            <!-- cards -->
            <div class="col-lg-8">
                <?php if (!empty($mensagem)): ?>
                    <div class="alert alert-custom">
                        <i class="bi bi-exclamation-triangle"></i> <?= h($mensagem) ?>
                    </div>
                <?php endif; ?>

                <div class="row g-4">
                    <?php foreach ($cards as $c): ?>
                        <?php
                            $capTotal = $c['cap_adultos'] + $c['cap_criancas'];

                            $statusTxt = $c['datas_validas']
                                ? ($c['disponivel'] ? 'Disponível' : 'Indisponível')
                                : 'Ver datas';

                            $statusIcon = $c['datas_validas']
                                ? ($c['disponivel'] ? 'bi-check-circle' : 'bi-x-circle')
                                : 'bi-calendar3';

                            $statusClass = $c['datas_validas']
                                ? ($c['disponivel'] ? 'ok' : 'no')
                                : '';

                            // Link para disponibilidade com filtro pré-selecionado
                            $link = "disponibilidade.php?"
                                . "tipo_quarto_id=" . (int)$c['id']
                                . "&checkin=" . urlencode($checkin)
                                . "&checkout=" . urlencode($checkout)
                                . "&adultos=" . (int)$adultos
                                . "&criancas=" . (int)$criancas;
                        ?>
                        <div class="col-md-6">
                            <div class="room-card">
                                <div class="room-img">
                                    <img src="<?= h($c['foto']) ?>" alt="<?= h($c['nome']) ?>">
                                    <div class="badge-price">
                                        <?= formatKz($c['preco_noite']) ?>/noite
                                    </div>
                                    <div class="badge-status <?= $statusClass ?>">
                                        <i class="bi <?= $statusIcon ?>"></i>
                                        <?= h($statusTxt) ?>
                                    </div>
                                </div>

                                <div class="room-body">
                                    <h3 class="room-title"><?= h($c['nome']) ?></h3>
                                    <div class="room-desc"><?= h($c['descricao']) ?></div>

                                    <div class="chips">
                                        <span class="chip"><i class="bi bi-people"></i> Até <?= (int)$capTotal ?> hóspedes</span>
                                        <span class="chip"><i class="bi bi-door-open"></i> <?= (int)$c['total_ativos'] ?> no total</span>
                                        <span class="chip"><i class="bi bi-check2-circle"></i> <?= (int)$c['total_disponiveis'] ?> livres</span>
                                    </div>

                                    <?php if (!empty($c['amenidades'])): ?>
                                        <div class="chips mt-2">
                                            <?php foreach (array_slice($c['amenidades'], 0, 6) as $am): ?>
                                                <span class="chip"><i class="bi bi-stars"></i> <?= h($am) ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="room-footer">
                                    <div>
                                        <?php if ($c['datas_validas']): ?>
                                            <div class="muted"><?= (int)$c['noites'] ?> noite(s)</div>
                                            <div class="total">Total: <?= formatKz($c['preco_total']) ?></div>
                                        <?php else: ?>
                                            <div class="muted">Informe datas para total</div>
                                            <div class="total">A partir de <?= formatKz($c['preco_noite']) ?></div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="d-flex gap-2">
                                        <a class="btn-card-soft" href="<?= h($link) ?>">
                                            <i class="bi bi-search"></i> Ver
                                        </a>
                                        <a class="btn-card <?= ($c['datas_validas'] && !$c['disponivel']) ? 'disabled' : '' ?>"
                                           href="<?= h($link) ?>">
                                            <i class="bi bi-calendar-check"></i> Reservar
                                        </a>
                                    </div>
                                </div>

                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if (empty($cards) && empty($mensagem)): ?>
                    <div class="alert alert-custom">
                        <i class="bi bi-info-circle"></i> Nenhum quarto para mostrar.
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>

<script src="bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>