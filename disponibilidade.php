<?php
/**
 * Verificação de Disponibilidade
 * Hotel Mucinga Nzambi
 */

define('SYSTEM_ACCESS', true);
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/rbac.php';
require_once 'includes/helpers.php';

$disponibilidade = [];
$mensagem = '';

// Processar busca
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $checkin = $_POST['checkin'] ?? '';
    $checkout = $_POST['checkout'] ?? '';
    $adultos = intval($_POST['adultos'] ?? 1);
    $criancas = intval($_POST['criancas'] ?? 0);
    $tipoQuartoId = intval($_POST['tipo_quarto_id'] ?? 0);
    
    if ($checkin && $checkout && $checkout > $checkin) {
        $db = getDB();
        
        // Buscar tipos de quarto disponíveis
        $stmt = $db->prepare("
            SELECT DISTINCT tq.* 
            FROM tipos_quarto tq
            INNER JOIN quartos q ON q.tipo_quarto_id = tq.id
            WHERE tq.ativo = 1 
            AND q.status = 'ATIVO'
            AND tq.capacidade_adultos >= ?
            AND (tq.capacidade_adultos + tq.capacidade_criancas) >= ?
            ORDER BY tq.capacidade_adultos ASC
        ");
        $stmt->execute([$adultos, $adultos + $criancas]);
        $tipos = $stmt->fetchAll();
        
        foreach ($tipos as &$tipo) {
            // Verificar disponibilidade de quartos deste tipo
            $disponivel = verificarDisponibilidade($tipo['id'], $checkin, $checkout);
            
            if ($disponivel) {
                // Buscar tarifa
                $tarifa = buscarTarifa($tipo['id'], $checkin);
                $noites = calcularNoites($checkin, $checkout);
                
                $tipo['disponivel'] = true;
                $tipo['preco_noite'] = $tarifa;
                $tipo['preco_total'] = $tarifa * $noites;
                $tipo['noites'] = $noites;
                
                // Parse amenities
                $tipo['amenidades_array'] = json_decode($tipo['amenidades'] ?? '[]', true);
            } else {
                $tipo['disponivel'] = false;
            }
        }
        
        $disponibilidade = array_filter($tipos, function($tipo) {
            return $tipo['disponivel'] === true;
        });
        
        if (empty($disponibilidade)) {
            $mensagem = 'Nenhum quarto disponível para as datas selecionadas.';
        }
    } else {
        $mensagem = 'Por favor, preencha corretamente as datas.';
    }
}

// Buscar todos os tipos de quarto para o formulário
$db = getDB();
$stmt = $db->query("SELECT id, nome FROM tipos_quarto WHERE ativo = 1 ORDER BY capacidade_adultos ASC");
$tiposQuarto = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar Disponibilidade | Hotel Mucinga Nzambi</title>
    
    <!-- Google Fonts Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Bootstrap Local -->
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    
    <!-- Estilos -->
    <?php include 'includes/navbar-styles.php'; ?>
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #FDF7E6;
        }
        
        .disponibilidade-section {
            padding: 60px 0;
            min-height: calc(100vh - 200px);
        }
        
        .form-card {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 40px;
        }
        
        .form-card h2 {
            color: #005051;
            font-weight: 700;
            margin-bottom: 25px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #005051 0%, #006b6d 100%);
            border: none;
            padding: 12px 40px;
            font-weight: 600;
            border-radius: 30px;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #006b6d 0%, #005051 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 80, 81, 0.3);
        }
        
        .quarto-card {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            margin-bottom: 30px;
        }
        
        .quarto-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        
        .quarto-img-wrapper {
            position: relative;
            height: 250px;
            overflow: hidden;
        }
        
        .quarto-img-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .quarto-card:hover .quarto-img-wrapper img {
            transform: scale(1.1);
        }
        
        .price-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: linear-gradient(135deg, #F28D00 0%, #FFC107 100%);
            color: #000;
            padding: 8px 18px;
            border-radius: 25px;
            font-weight: 700;
            font-size: 0.95rem;
            box-shadow: 0 4px 10px rgba(242, 141, 0, 0.4);
        }
        
        .quarto-content {
            padding: 25px;
        }
        
        .quarto-title {
            color: #005051;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .quarto-description {
            color: #666;
            margin-bottom: 15px;
        }
        
        .amenidades-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 20px;
        }
        
        .amenidade-item {
            background: #f8f8f8;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            color: #333;
        }
        
        .preco-info {
            border-top: 2px solid #eee;
            padding-top: 15px;
            margin-top: 15px;
        }
        
        .preco-noite {
            font-size: 0.9rem;
            color: #666;
        }
        
        .preco-total {
            font-size: 1.3rem;
            font-weight: 700;
            color: #005051;
        }
        
        .btn-reservar {
            background: linear-gradient(135deg, #005051 0%, #006b6d 100%);
            color: #fff;
            border: none;
            padding: 12px 30px;
            font-weight: 600;
            border-radius: 8px;
            width: 100%;
            transition: all 0.3s ease;
        }
        
        .btn-reservar:hover {
            background: linear-gradient(135deg, #006b6d 0%, #005051 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 80, 81, 0.3);
            color: #fff;
        }
        
        .alert-info {
            background: linear-gradient(135deg, rgba(0, 80, 81, 0.1) 0%, rgba(242, 141, 0, 0.1) 100%);
            border: 2px solid #005051;
            border-radius: 12px;
            color: #005051;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <section class="disponibilidade-section">
        <div class="container">
            <!-- Formulário de Busca -->
            <div class="form-card">
                <h2><i class="bi bi-search"></i> Verificar Disponibilidade</h2>
                
                <form method="POST" action="">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Check-in <span class="text-danger">*</span></label>
                            <input type="date" name="checkin" class="form-control" 
                                   value="<?= $_POST['checkin'] ?? '' ?>" 
                                   min="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Check-out <span class="text-danger">*</span></label>
                            <input type="date" name="checkout" class="form-control" 
                                   value="<?= $_POST['checkout'] ?? '' ?>" 
                                   min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold">Adultos</label>
                            <input type="number" name="adultos" class="form-control" 
                                   value="<?= $_POST['adultos'] ?? 1 ?>" min="1" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold">Crianças</label>
                            <input type="number" name="criancas" class="form-control" 
                                   value="<?= $_POST['criancas'] ?? 0 ?>" min="0">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-search"></i> Buscar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Resultados -->
            <?php if (!empty($mensagem)): ?>
                <div class="alert alert-info" role="alert">
                    <i class="bi bi-info-circle"></i> <?= $mensagem ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($disponibilidade)): ?>
                <h3 class="mb-4" style="color: #005051;">Quartos Disponíveis</h3>
                <div class="row">
                    <?php foreach ($disponibilidade as $quarto): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="quarto-card">
                                <div class="quarto-img-wrapper">
                                    <?php if ($quarto['foto_capa']): ?>
                                        <img src="<?= $quarto['foto_capa'] ?>" alt="<?= htmlspecialchars($quarto['nome']) ?>">
                                    <?php else: ?>
                                        <img src="imagens/pic3.jpg" alt="<?= htmlspecialchars($quarto['nome']) ?>">
                                    <?php endif; ?>
                                    <div class="price-badge">
                                        <?= formatarMoeda($quarto['preco_noite']) ?>/noite
                                    </div>
                                </div>
                                <div class="quarto-content">
                                    <h3 class="quarto-title"><?= htmlspecialchars($quarto['nome']) ?></h3>
                                    <p class="quarto-description"><?= htmlspecialchars($quarto['descricao'] ?? '') ?></p>
                                    
                                    <?php if (!empty($quarto['amenidades_array'])): ?>
                                        <div class="amenidades-list">
                                            <?php foreach ($quarto['amenidades_array'] as $amenidade): ?>
                                                <span class="amenidade-item">
                                                    <i class="bi bi-check-circle"></i> <?= htmlspecialchars($amenidade) ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="preco-info">
                                        <div class="preco-noite">
                                            <i class="bi bi-calendar"></i> <?= $quarto['noites'] ?> noite(s)
                                        </div>
                                        <div class="preco-total">
                                            Total: <?= formatarMoeda($quarto['preco_total']) ?>
                                        </div>
                                    </div>
                                    
                                    <form method="POST" action="reserva.php" style="margin-top: 20px;">
                                        <input type="hidden" name="tipo_quarto_id" value="<?= $quarto['id'] ?>">
                                        <input type="hidden" name="checkin" value="<?= $_POST['checkin'] ?? '' ?>">
                                        <input type="hidden" name="checkout" value="<?= $_POST['checkout'] ?? '' ?>">
                                        <input type="hidden" name="adultos" value="<?= $_POST['adultos'] ?? 1 ?>">
                                        <input type="hidden" name="criancas" value="<?= $_POST['criancas'] ?? 0 ?>">
                                        <button type="submit" class="btn btn-reservar">
                                            <i class="bi bi-calendar-check"></i> Reservar Agora
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>

