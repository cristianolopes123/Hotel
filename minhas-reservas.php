<?php
/**
 * Minhas Reservas
 * Hotel Mucinga Nzambi
 */

define('SYSTEM_ACCESS', true);
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/rbac.php';
require_once 'includes/helpers.php';
require_once 'includes/upload.php';

// Verificar login
if (!Auth::isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$usuario = Auth::getUser();
$db = getDB();

// Buscar reservas do usuário
$stmt = $db->prepare("
    SELECT r.*, tq.nome as tipo_quarto_nome, q.numero as quarto_numero,
           b.nome_banco as banco_nome
    FROM reservas r
    LEFT JOIN tipos_quarto tq ON r.tipo_quarto_id = tq.id
    LEFT JOIN quartos q ON r.quarto_id = q.id
    LEFT JOIN bancos b ON r.banco_escolhido_id = b.id
    WHERE r.usuario_id = ?
    ORDER BY r.criado_em DESC
");
$stmt->execute([$usuario['id']]);
$reservas = $stmt->fetchAll();

// Buscar comprovantes
$comprovantes = [];
foreach ($reservas as $reserva) {
    $stmt = $db->prepare("SELECT * FROM comprovantes WHERE reserva_id = ? ORDER BY criado_em DESC LIMIT 1");
    $stmt->execute([$reserva['id']]);
    $comprovantes[$reserva['id']] = $stmt->fetch();
}

?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Reservas | Hotel Mucinga Nzambi</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    
    <?php include 'includes/navbar-styles.php'; ?>
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #FDF7E6;
            padding-top: 100px;
        }
        
        .reservas-section {
            padding: 60px 0;
            min-height: calc(100vh - 200px);
        }
        
        .reservas-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .page-title {
            color: #005051;
            font-weight: 700;
            margin-bottom: 30px;
        }
        
        .reserva-card {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 25px;
            margin-bottom: 25px;
            transition: all 0.3s ease;
        }
        
        .reserva-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        
        .reserva-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #eee;
        }
        
        .reserva-codigo {
            font-size: 1.2rem;
            font-weight: 700;
            color: #005051;
        }
        
        .reserva-status {
            display: inline-block;
        }
        
        .reserva-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
        }
        
        .info-label {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 5px;
        }
        
        .info-value {
            font-size: 1rem;
            font-weight: 600;
            color: #333;
        }
        
        .reserva-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .btn-action {
            padding: 8px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
        }
        
        .btn-pdf {
            background: #dc3545;
            color: #fff;
        }
        
        .btn-pdf:hover {
            background: #bb2d3b;
            color: #fff;
            transform: translateY(-2px);
        }
        
        .btn-upload {
            background: #005051;
            color: #fff;
        }
        
        .btn-upload:hover {
            background: #006b6d;
            color: #fff;
            transform: translateY(-2px);
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .empty-icon {
            font-size: 4rem;
            color: #ccc;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <section class="reservas-section">
        <div class="reservas-container">
            <h1 class="page-title">
                <i class="bi bi-calendar-check"></i> Minhas Reservas
            </h1>
            
            <?php if (empty($reservas)): ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="bi bi-inbox"></i>
                    </div>
                    <h3 style="color: #005051; margin-bottom: 15px;">Nenhuma reserva encontrada</h3>
                    <p class="text-muted mb-4">Você ainda não fez nenhuma reserva.</p>
                    <a href="disponibilidade.php" class="btn btn-primary">
                        <i class="bi bi-calendar-plus"></i> Fazer uma Reserva
                    </a>
                </div>
            <?php else: ?>
                <?php foreach ($reservas as $reserva): ?>
                    <div class="reserva-card">
                        <div class="reserva-header">
                            <div>
                                <div class="reserva-codigo">
                                    <i class="bi bi-hash"></i> <?= htmlspecialchars($reserva['codigo']) ?>
                                </div>
                                <small class="text-muted">
                                    Criada em <?= formatarData($reserva['criado_em'], 'd/m/Y H:i') ?>
                                </small>
                            </div>
                            <div class="reserva-status">
                                <?= statusBadge($reserva['status']) ?>
                            </div>
                        </div>
                        
                        <div class="reserva-info">
                            <div class="info-item">
                                <span class="info-label">Quarto</span>
                                <span class="info-value"><?= htmlspecialchars($reserva['tipo_quarto_nome']) ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Check-in</span>
                                <span class="info-value"><?= formatarData($reserva['checkin']) ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Check-out</span>
                                <span class="info-value"><?= formatarData($reserva['checkout']) ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Noites</span>
                                <span class="info-value"><?= calcularNoites($reserva['checkin'], $reserva['checkout']) ?> noite(s)</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Total</span>
                                <span class="info-value" style="color: #005051; font-size: 1.1rem;">
                                    <?= formatarMoeda($reserva['total_liquido']) ?>
                                </span>
                            </div>
                            <?php if ($reserva['banco_nome']): ?>
                                <div class="info-item">
                                    <span class="info-label">Banco</span>
                                    <span class="info-value"><?= htmlspecialchars($reserva['banco_nome']) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="reserva-actions">
                            <a href="pdf/comprovante_reserva.php?codigo=<?= urlencode($reserva['codigo']) ?>" 
                               target="_blank" class="btn-action btn-pdf">
                                <i class="bi bi-file-earmark-pdf"></i> Baixar PDF
                            </a>
                            
                            <?php if ($reserva['status'] === 'PENDENTE_COMPROVANTE' || $reserva['status'] === 'RECUSADA'): ?>
                                <a href="upload_comprovante.php?reserva_id=<?= $reserva['id'] ?>" 
                                   class="btn-action btn-upload">
                                    <i class="bi bi-cloud-upload"></i> Enviar Comprovante
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($comprovantes[$reserva['id']] ?? false): ?>
                                <a href="<?= $comprovantes[$reserva['id']]['arquivo_path'] ?>" 
                                   target="_blank" class="btn-action btn-secondary">
                                    <i class="bi bi-eye"></i> Ver Comprovante
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>

