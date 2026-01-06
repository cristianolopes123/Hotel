<?php
/**
 * Painel de Solicitações - Recepção
 * Hotel Mucinga Nzambi
 */

define('ADMIN_ACCESS', true);
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/rbac.php';
require_once __DIR__ . '/../includes/helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticação e permissões
if (!Auth::isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

if (!RBAC::isRecepcao()) {
    die('Acesso negado. Apenas recepção e admin podem acessar.');
}

$db = getDB();
$mensagem = '';

// Filtros
$statusFiltro = $_GET['status'] ?? 'EM_ANALISE';
$dataFiltro = $_GET['data'] ?? '';
$bancoFiltro = $_GET['banco'] ?? '';
$codigoFiltro = $_GET['codigo'] ?? '';
$busca = $_GET['busca'] ?? '';

// Buscar reservas em análise
$sql = "
    SELECT r.*, tq.nome as tipo_quarto_nome, q.numero as quarto_numero,
           b.nome_banco, u.nome as usuario_nome,
           (SELECT arquivo_path FROM comprovantes WHERE reserva_id = r.id ORDER BY criado_em DESC LIMIT 1) as comprovante_path,
           (SELECT id FROM comprovantes WHERE reserva_id = r.id ORDER BY criado_em DESC LIMIT 1) as comprovante_id
    FROM reservas r
    LEFT JOIN tipos_quarto tq ON r.tipo_quarto_id = tq.id
    LEFT JOIN quartos q ON r.quarto_id = q.id
    LEFT JOIN bancos b ON r.banco_escolhido_id = b.id
    LEFT JOIN usuarios u ON r.usuario_id = u.id
    WHERE 1=1
";

$params = [];

if ($statusFiltro) {
    $sql .= " AND r.status = ?";
    $params[] = $statusFiltro;
}

if ($dataFiltro) {
    $sql .= " AND DATE(r.criado_em) = ?";
    $params[] = $dataFiltro;
}

if ($bancoFiltro) {
    $sql .= " AND r.banco_escolhido_id = ?";
    $params[] = $bancoFiltro;
}

if ($codigoFiltro) {
    $sql .= " AND r.codigo LIKE ?";
    $params[] = '%' . $codigoFiltro . '%';
}

if ($busca) {
    $sql .= " AND (r.nome_cliente LIKE ? OR r.telefone LIKE ? OR r.email LIKE ?)";
    $params[] = '%' . $busca . '%';
    $params[] = '%' . $busca . '%';
    $params[] = '%' . $busca . '%';
}

$sql .= " ORDER BY r.criado_em DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$reservas = $stmt->fetchAll();

// Buscar bancos para filtro
$stmt = $db->query("SELECT id, nome_banco FROM bancos WHERE ativo = 1 ORDER BY nome_banco");
$bancos = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitações | Hotel Mucinga Nzambi</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #F5F5F5;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 250px;
            background: linear-gradient(135deg, #005051 0%, #006b6d 100%);
            color: #fff;
            padding: 20px 0;
            overflow-y: auto;
            z-index: 1000;
        }
        
        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar-menu li a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #fff;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .sidebar-menu li a:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-menu li a.active {
            background: rgba(242, 141, 0, 0.3);
            border-left: 3px solid #F28D00;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 30px;
        }
        
        .content-card {
            background: #fff;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .filters-card {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        
        .reserva-item {
            background: #fff;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        
        .reserva-item:hover {
            border-color: #005051;
            box-shadow: 0 4px 15px rgba(0, 80, 81, 0.1);
        }
        
        .comprovante-thumb {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            cursor: pointer;
        }
        
        .modal-lg .modal-dialog {
            max-width: 900px;
        }
        
        .comprovante-full {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <!-- Sidebar (mesma estrutura do index.php) -->
    <?php include 'sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="content-card">
            <h2 style="color: #005051; font-weight: 700; margin-bottom: 25px;">
                <i class="bi bi-inbox"></i> Solicitações de Reserva
            </h2>
            
            <!-- Filtros -->
            <div class="filters-card">
                <form method="GET" action="">
                    <div class="row g-3">
                        <div class="col-md-2">
                            <label class="form-label fw-bold">Status</label>
                            <select name="status" class="form-select">
                                <option value="">Todos</option>
                                <option value="EM_ANALISE" <?= $statusFiltro === 'EM_ANALISE' ? 'selected' : '' ?>>Em Análise</option>
                                <option value="PENDENTE_COMPROVANTE" <?= $statusFiltro === 'PENDENTE_COMPROVANTE' ? 'selected' : '' ?>>Pendente Comprovante</option>
                                <option value="CONFIRMADA" <?= $statusFiltro === 'CONFIRMADA' ? 'selected' : '' ?>>Confirmada</option>
                                <option value="RECUSADA" <?= $statusFiltro === 'RECUSADA' ? 'selected' : '' ?>>Recusada</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold">Data</label>
                            <input type="date" name="data" class="form-control" value="<?= htmlspecialchars($dataFiltro) ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold">Banco</label>
                            <select name="banco" class="form-select">
                                <option value="">Todos</option>
                                <?php foreach ($bancos as $banco): ?>
                                    <option value="<?= $banco['id'] ?>" <?= $bancoFiltro == $banco['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($banco['nome_banco']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold">Código</label>
                            <input type="text" name="codigo" class="form-control" 
                                   value="<?= htmlspecialchars($codigoFiltro) ?>" placeholder="HZ-2025-...">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Buscar (Nome/Tel/Email)</label>
                            <input type="text" name="busca" class="form-control" 
                                   value="<?= htmlspecialchars($busca) ?>" placeholder="Nome, telefone ou email">
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-search"></i> Buscar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Lista de Reservas -->
            <?php if (empty($reservas)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
                    <p class="text-muted mt-3">Nenhuma reserva encontrada com os filtros selecionados.</p>
                </div>
            <?php else: ?>
                <?php foreach ($reservas as $reserva): ?>
                    <div class="reserva-item">
                        <div class="row align-items-center">
                            <div class="col-md-1">
                                <?php if ($reserva['comprovante_path']): ?>
                                    <img src="<?= htmlspecialchars($reserva['comprovante_path']) ?>" 
                                         class="comprovante-thumb" 
                                         onclick="showComprovante('<?= htmlspecialchars($reserva['comprovante_path']) ?>')">
                                <?php else: ?>
                                    <div class="text-center text-muted">
                                        <i class="bi bi-file-earmark-image" style="font-size: 2rem;"></i>
                                        <small>Sem comprovante</small>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-9">
                                <div class="row">
                                    <div class="col-md-3">
                                        <strong style="color: #005051;"><?= htmlspecialchars($reserva['codigo']) ?></strong><br>
                                        <small class="text-muted"><?= formatarData($reserva['criado_em'], 'd/m/Y H:i') ?></small>
                                    </div>
                                    <div class="col-md-3">
                                        <strong><?= htmlspecialchars($reserva['nome_cliente']) ?></strong><br>
                                        <small class="text-muted">
                                            <i class="bi bi-telephone"></i> <?= htmlspecialchars($reserva['telefone']) ?><br>
                                            <i class="bi bi-envelope"></i> <?= htmlspecialchars($reserva['email']) ?>
                                        </small>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted">Período:</small><br>
                                        <strong><?= formatarData($reserva['checkin']) ?> - <?= formatarData($reserva['checkout']) ?></strong><br>
                                        <small class="text-muted"><?= calcularNoites($reserva['checkin'], $reserva['checkout']) ?> noite(s)</small>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted">Quarto:</small><br>
                                        <strong><?= htmlspecialchars($reserva['tipo_quarto_nome']) ?></strong><br>
                                        <?php if ($reserva['quarto_numero']): ?>
                                            <small class="text-success">Quarto <?= htmlspecialchars($reserva['quarto_numero']) ?></small>
                                        <?php endif; ?>
                                        <br>
                                        <small class="text-muted">Total: <strong><?= formatarMoeda($reserva['total_liquido']) ?></strong></small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2 text-end">
                                <div class="mb-2">
                                    <?= statusBadge($reserva['status']) ?>
                                </div>
                                <a href="reserva_detalhes.php?id=<?= $reserva['id'] ?>" 
                                   class="btn btn-sm btn-primary">
                                    <i class="bi bi-eye"></i> Ver Detalhes
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Modal Comprovante -->
    <div class="modal fade" id="comprovanteModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Comprovante de Pagamento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="comprovanteImg" src="" class="comprovante-full" alt="Comprovante">
                </div>
            </div>
        </div>
    </div>
    
    <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        function showComprovante(path) {
            document.getElementById('comprovanteImg').src = path;
            new bootstrap.Modal(document.getElementById('comprovanteModal')).show();
        }
    </script>
</body>
</html>

