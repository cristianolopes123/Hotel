<?php
/**
 * Listagem Completa de Reservas
 * Hotel Mucinga Nzambi - Admin
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


$db = getDB();

// Filtros
$statusFiltro = $_GET['status'] ?? '';
$dataInicio = $_GET['data_inicio'] ?? '';
$dataFim = $_GET['data_fim'] ?? '';
$codigoFiltro = $_GET['codigo'] ?? '';
$busca = $_GET['busca'] ?? '';

// Buscar reservas
$sql = "
    SELECT r.*, tq.nome as tipo_quarto_nome, q.numero as quarto_numero,
           b.nome_banco, u.nome as usuario_nome
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

if ($dataInicio) {
    $sql .= " AND DATE(r.criado_em) >= ?";
    $params[] = $dataInicio;
}

if ($dataFim) {
    $sql .= " AND DATE(r.criado_em) <= ?";
    $params[] = $dataFim;
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

$sql .= " ORDER BY r.criado_em DESC LIMIT 100";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$reservas = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservas | Hotel Mucinga Nzambi</title>
    
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
        .content-card {
            background: #fff;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .filters-card {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #005051 0%, #006b6d 100%);
            border: none;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="content-card">
            <h2 style="color: #005051; font-weight: 700; margin-bottom: 25px;">
                <i class="bi bi-calendar-check"></i> Todas as Reservas
            </h2>
            
            <!-- Filtros -->
            <div class="filters-card">
                <form method="GET" action="">
                    <div class="row g-3">
                        <div class="col-md-2">
                            <label class="form-label fw-bold">Status</label>
                            <select name="status" class="form-select">
                                <option value="">Todos</option>
                                <option value="PENDENTE_COMPROVANTE" <?= $statusFiltro === 'PENDENTE_COMPROVANTE' ? 'selected' : '' ?>>Pendente Comprovante</option>
                                <option value="EM_ANALISE" <?= $statusFiltro === 'EM_ANALISE' ? 'selected' : '' ?>>Em Análise</option>
                                <option value="CONFIRMADA" <?= $statusFiltro === 'CONFIRMADA' ? 'selected' : '' ?>>Confirmada</option>
                                <option value="RECUSADA" <?= $statusFiltro === 'RECUSADA' ? 'selected' : '' ?>>Recusada</option>
                                <option value="CHECKIN_REALIZADO" <?= $statusFiltro === 'CHECKIN_REALIZADO' ? 'selected' : '' ?>>Check-in Realizado</option>
                                <option value="CHECKOUT_REALIZADO" <?= $statusFiltro === 'CHECKOUT_REALIZADO' ? 'selected' : '' ?>>Check-out Realizado</option>
                                <option value="CANCELADA" <?= $statusFiltro === 'CANCELADA' ? 'selected' : '' ?>>Cancelada</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold">Data Início</label>
                            <input type="date" name="data_inicio" class="form-control" value="<?= htmlspecialchars($dataInicio) ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold">Data Fim</label>
                            <input type="date" name="data_fim" class="form-control" value="<?= htmlspecialchars($dataFim) ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold">Código</label>
                            <input type="text" name="codigo" class="form-control" 
                                   value="<?= htmlspecialchars($codigoFiltro) ?>" placeholder="HZ-2025-...">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Buscar</label>
                            <input type="text" name="busca" class="form-control" 
                                   value="<?= htmlspecialchars($busca) ?>" placeholder="Nome, telefone ou email">
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Tabela de Reservas -->
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Hóspede</th>
                            <th>Quarto</th>
                            <th>Período</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Criado em</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservas as $reserva): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($reserva['codigo']) ?></strong></td>
                                <td>
                                    <?= htmlspecialchars($reserva['nome_cliente']) ?><br>
                                    <small class="text-muted">
                                        <?= htmlspecialchars($reserva['telefone']) ?>
                                    </small>
                                </td>
                                <td>
                                    <?= htmlspecialchars($reserva['tipo_quarto_nome']) ?><br>
                                    <?php if ($reserva['quarto_numero']): ?>
                                        <small class="text-success">Quarto <?= htmlspecialchars($reserva['quarto_numero']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= formatarData($reserva['checkin']) ?> - <?= formatarData($reserva['checkout']) ?><br>
                                    <small class="text-muted"><?= calcularNoites($reserva['checkin'], $reserva['checkout']) ?> noite(s)</small>
                                </td>
                                <td><strong style="color: #005051;"><?= formatarMoeda($reserva['total_liquido']) ?></strong></td>
                                <td><?= statusBadge($reserva['status']) ?></td>
                                <td><?= formatarData($reserva['criado_em'], 'd/m/Y H:i') ?></td>
                                <td>
                                    <a href="reserva_detalhes.php?id=<?= $reserva['id'] ?>" 
                                       class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i> Ver
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if (empty($reservas)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
                    <p class="text-muted mt-3">Nenhuma reserva encontrada com os filtros selecionados.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>

