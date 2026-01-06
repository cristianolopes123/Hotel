<?php
/**
 * Detalhes da Reserva
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

$reservaId = intval($_GET['id'] ?? 0);
if (!$reservaId) {
    header('Location: solicitacoes.php');
    exit;
}

$db = getDB();

// Buscar reserva
$stmt = $db->prepare("
    SELECT r.*, tq.nome as tipo_quarto_nome, tq.descricao as tipo_quarto_descricao,
           q.numero as quarto_numero, q.id as quarto_id,
           b.nome_banco, b.titular as banco_titular, b.iban as banco_iban,
           u.nome as usuario_nome, u.email as usuario_email
    FROM reservas r
    LEFT JOIN tipos_quarto tq ON r.tipo_quarto_id = tq.id
    LEFT JOIN quartos q ON r.quarto_id = q.id
    LEFT JOIN bancos b ON r.banco_escolhido_id = b.id
    LEFT JOIN usuarios u ON r.usuario_id = u.id
    WHERE r.id = ?
");
$stmt->execute([$reservaId]);
$reserva = $stmt->fetch();

if (!$reserva) {
    header('Location: solicitacoes.php');
    exit;
}

// Buscar serviços
$stmt = $db->prepare("
    SELECT s.nome, rs.quantidade, rs.valor_unit, rs.subtotal
    FROM reservas_servicos rs
    INNER JOIN servicos s ON rs.servico_id = s.id
    WHERE rs.reserva_id = ?
");
$stmt->execute([$reservaId]);
$servicos = $stmt->fetchAll();

// Buscar comprovantes
$stmt = $db->prepare("
    SELECT c.*, u.nome as enviado_por_nome
    FROM comprovantes c
    LEFT JOIN usuarios u ON c.enviado_por = u.id
    WHERE c.reserva_id = ?
    ORDER BY c.criado_em DESC
");
$stmt->execute([$reservaId]);
$comprovantes = $stmt->fetchAll();

// Buscar quartos disponíveis para atribuir
$stmt = $db->prepare("
    SELECT q.id, q.numero
    FROM quartos q
    WHERE q.tipo_quarto_id = ?
    AND q.status = 'ATIVO'
    AND q.id NOT IN (
        SELECT r2.quarto_id FROM reservas r2
        WHERE r2.quarto_id IS NOT NULL
        AND r2.status IN ('EM_ANALISE', 'CONFIRMADA', 'CHECKIN_REALIZADO')
        AND NOT (r2.checkout <= ? OR r2.checkin >= ?)
        AND r2.id != ?
    )
    ORDER BY q.numero ASC
");
$stmt->execute([
    $reserva['tipo_quarto_id'],
    $reserva['checkin'],
    $reserva['checkout'],
    $reservaId
]);
$quartosDisponiveis = $stmt->fetchAll();

// Buscar auditoria
$stmt = $db->prepare("
    SELECT a.*, u.nome as usuario_nome
    FROM auditoria a
    LEFT JOIN usuarios u ON a.usuario_id = u.id
    WHERE a.entidade = 'reserva' AND a.entidade_id = ?
    ORDER BY a.criado_em DESC
    LIMIT 20
");
$stmt->execute([$reservaId]);
$auditoria = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes da Reserva | Hotel Mucinga Nzambi</title>
    
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
        
        .main-content {
            margin-left: 250px;
            padding: 30px;
        }
        
        .detail-card {
            background: #fff;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        
        .detail-card h3 {
            color: #005051;
            font-weight: 700;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
        }
        
        .info-row {
            display: grid;
            grid-template-columns: 150px 1fr;
            gap: 15px;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 700;
            color: #005051;
        }
        
        .comprovante-img {
            max-width: 300px;
            max-height: 400px;
            border-radius: 8px;
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        
        .comprovante-img:hover {
            transform: scale(1.05);
        }
        
        .actions-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .btn-success {
            background: #28a745;
            border: none;
        }
        
        .btn-danger {
            background: #dc3545;
            border: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #005051 0%, #006b6d 100%);
            border: none;
        }
        
        .timeline {
            position: relative;
            padding-left: 30px;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #005051;
        }
        
        .timeline-item {
            position: relative;
            padding-bottom: 20px;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -36px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #005051;
        }
        
        .timeline-content {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="detail-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 style="color: #005051; font-weight: 700; margin: 0;">
                    <i class="bi bi-calendar-check"></i> Reserva <?= htmlspecialchars($reserva['codigo']) ?>
                </h2>
                <div>
                    <?= statusBadge($reserva['status']) ?>
                </div>
            </div>
            
            <a href="solicitacoes.php" class="btn btn-secondary mb-4">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>
        
        <!-- Dados do Hóspede -->
        <div class="detail-card">
            <h3><i class="bi bi-person"></i> Dados do Hóspede</h3>
            
            <div class="info-row">
                <span class="info-label">Nome:</span>
                <span><?= htmlspecialchars($reserva['nome_cliente']) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Email:</span>
                <span><?= htmlspecialchars($reserva['email']) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Telefone:</span>
                <span><?= htmlspecialchars($reserva['telefone']) ?></span>
            </div>
            <?php if ($reserva['documento']): ?>
                <div class="info-row">
                    <span class="info-label">Documento:</span>
                    <span><?= htmlspecialchars($reserva['documento']) ?></span>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Dados da Reserva -->
        <div class="detail-card">
            <h3><i class="bi bi-calendar-event"></i> Dados da Reserva</h3>
            
            <div class="info-row">
                <span class="info-label">Código:</span>
                <span><strong><?= htmlspecialchars($reserva['codigo']) ?></strong></span>
            </div>
            <div class="info-row">
                <span class="info-label">Tipo de Quarto:</span>
                <span><?= htmlspecialchars($reserva['tipo_quarto_nome']) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Quarto:</span>
                <span>
                    <?php if ($reserva['quarto_numero']): ?>
                        <strong class="text-success">Quarto <?= htmlspecialchars($reserva['quarto_numero']) ?></strong>
                    <?php else: ?>
                        <span class="text-muted">Não atribuído</span>
                    <?php endif; ?>
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Check-in:</span>
                <span><?= formatarData($reserva['checkin']) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Check-out:</span>
                <span><?= formatarData($reserva['checkout']) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Noites:</span>
                <span><?= calcularNoites($reserva['checkin'], $reserva['checkout']) ?> noite(s)</span>
            </div>
            <div class="info-row">
                <span class="info-label">Hóspedes:</span>
                <span><?= $reserva['adultos'] ?> adulto(s), <?= $reserva['criancas'] ?> criança(s)</span>
            </div>
            <?php if ($reserva['nome_banco']): ?>
                <div class="info-row">
                    <span class="info-label">Banco:</span>
                    <span><?= htmlspecialchars($reserva['nome_banco']) ?></span>
                </div>
                <?php if ($reserva['banco_iban']): ?>
                    <div class="info-row">
                        <span class="info-label">IBAN:</span>
                        <span><?= htmlspecialchars($reserva['banco_iban']) ?></span>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            <?php if ($reserva['observacoes']): ?>
                <div class="info-row">
                    <span class="info-label">Observações:</span>
                    <span><?= nl2br(htmlspecialchars($reserva['observacoes'])) ?></span>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Comprovantes -->
        <?php if (!empty($comprovantes)): ?>
            <div class="detail-card">
                <h3><i class="bi bi-file-earmark-image"></i> Comprovantes de Pagamento</h3>
                
                <div class="row g-3">
                    <?php foreach ($comprovantes as $comprovante): ?>
                        <div class="col-md-4">
                            <div class="text-center">
                                <img src="<?= htmlspecialchars($comprovante['arquivo_path']) ?>" 
                                     class="comprovante-img img-thumbnail" 
                                     onclick="window.open('<?= htmlspecialchars($comprovante['arquivo_path']) ?>', '_blank')">
                                <p class="mt-2 mb-0">
                                    <small class="text-muted">
                                        Enviado em <?= formatarData($comprovante['criado_em'], 'd/m/Y H:i') ?><br>
                                        <?= $comprovante['enviado_por_nome'] ? 'Por: ' . htmlspecialchars($comprovante['enviado_por_nome']) : 'Por visitante' ?>
                                    </small>
                                </p>
                                <a href="<?= htmlspecialchars($comprovante['arquivo_path']) ?>" 
                                   target="_blank" class="btn btn-sm btn-primary mt-2">
                                    <i class="bi bi-download"></i> Baixar
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Ações -->
        <?php if (in_array($reserva['status'], ['EM_ANALISE', 'PENDENTE_COMPROVANTE', 'RECUSADA'])): ?>
            <div class="detail-card">
                <h3><i class="bi bi-gear"></i> Ações</h3>
                
                <div class="actions-buttons">
                    <?php if ($reserva['status'] === 'EM_ANALISE'): ?>
                        <form method="POST" action="reserva_confirmar.php" style="display: inline;">
                            <input type="hidden" name="reserva_id" value="<?= $reserva['id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= gerarCSRFToken() ?>">
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Atribuir Quarto:</label>
                                <select name="quarto_id" class="form-select" required>
                                    <option value="">Selecione um quarto</option>
                                    <?php foreach ($quartosDisponiveis as $quarto): ?>
                                        <option value="<?= $quarto['id'] ?>" 
                                                <?= $reserva['quarto_id'] == $quarto['id'] ? 'selected' : '' ?>>
                                            Quarto <?= htmlspecialchars($quarto['numero']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle"></i> Confirmar Reserva
                            </button>
                        </form>
                        
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#recusarModal">
                            <i class="bi bi-x-circle"></i> Recusar Reserva
                        </button>
                    <?php endif; ?>
                    
                    <a href="../pdf/comprovante_reserva.php?codigo=<?= urlencode($reserva['codigo']) ?>" 
                       target="_blank" class="btn btn-primary">
                        <i class="bi bi-file-earmark-pdf"></i> Gerar PDF Reserva
                    </a>
                    
                    <?php if ($reserva['status'] === 'CHECKIN_REALIZADO'): ?>
                        <a href="../pdf/comprovante_checkin.php?id=<?= $reserva['id'] ?>" 
                           target="_blank" class="btn btn-success">
                            <i class="bi bi-file-earmark-pdf"></i> PDF Check-in
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($reserva['status'] === 'CHECKOUT_REALIZADO'): ?>
                        <a href="../pdf/comprovante_checkout.php?id=<?= $reserva['id'] ?>" 
                           target="_blank" class="btn btn-primary">
                            <i class="bi bi-file-earmark-pdf"></i> PDF Check-out
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if ($reserva['status'] === 'CONFIRMADA'): ?>
            <div class="detail-card">
                <h3><i class="bi bi-door-open"></i> Check-in / Check-out</h3>
                
                <form method="POST" action="checkin.php" class="mb-3">
                    <input type="hidden" name="reserva_id" value="<?= $reserva['id'] ?>">
                    <input type="hidden" name="csrf_token" value="<?= gerarCSRFToken() ?>">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-door-open"></i> Realizar Check-in
                    </button>
                </form>
            </div>
        <?php endif; ?>
        
        <?php if ($reserva['status'] === 'CHECKIN_REALIZADO'): ?>
            <div class="detail-card">
                <h3><i class="bi bi-door-closed"></i> Check-out</h3>
                
                <form method="POST" action="checkout.php">
                    <input type="hidden" name="reserva_id" value="<?= $reserva['id'] ?>">
                    <input type="hidden" name="csrf_token" value="<?= gerarCSRFToken() ?>">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-door-closed"></i> Realizar Check-out
                    </button>
                </form>
            </div>
        <?php endif; ?>
        
        <!-- Histórico de Auditoria -->
        <?php if (!empty($auditoria)): ?>
            <div class="detail-card">
                <h3><i class="bi bi-clock-history"></i> Histórico</h3>
                
                <div class="timeline">
                    <?php foreach ($auditoria as $item): ?>
                        <div class="timeline-item">
                            <div class="timeline-content">
                                <strong><?= htmlspecialchars($item['acao']) ?></strong>
                                <p class="mb-1">
                                    <?= $item['usuario_nome'] ? 'Por: ' . htmlspecialchars($item['usuario_nome']) : 'Por sistema' ?>
                                    em <?= formatarData($item['criado_em'], 'd/m/Y H:i') ?>
                                </p>
                                <?php if ($item['depois']): ?>
                                    <?php
                                    $depois = json_decode($item['depois'], true);
                                    if (isset($depois['status'])) {
                                        echo '<small class="text-muted">Status: ' . htmlspecialchars($depois['status']) . '</small>';
                                    }
                                    ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Modal Recusar -->
    <div class="modal fade" id="recusarModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Recusar Reserva</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="reserva_recusar.php">
                    <div class="modal-body">
                        <input type="hidden" name="reserva_id" value="<?= $reserva['id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= gerarCSRFToken() ?>">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Motivo da Recusa <span class="text-danger">*</span></label>
                            <textarea name="motivo" class="form-control" rows="4" required 
                                      placeholder="Descreva o motivo da recusa da reserva..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Recusar Reserva</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>

