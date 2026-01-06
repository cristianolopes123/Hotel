<?php
/**
 * Relatórios
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


if (!RBAC::isRecepcao()) {
    die('Acesso negado.');
}

$db = getDB();

// Filtros
$dataInicio = $_GET['data_inicio'] ?? date('Y-m-01'); // Primeiro dia do mês
$dataFim = $_GET['data_fim'] ?? date('Y-m-d');
$tipoRelatorio = $_GET['tipo'] ?? 'ocupacao';
$bancoId = $_GET['banco'] ?? '';
$statusFiltro = $_GET['status'] ?? '';

// Relatório de Ocupação
$stmt = $db->prepare("
    SELECT DATE(checkin) as data, COUNT(*) as total_reservas,
           SUM(CASE WHEN status IN ('CONFIRMADA', 'CHECKIN_REALIZADO') THEN 1 ELSE 0 END) as ocupadas
    FROM reservas
    WHERE DATE(checkin) BETWEEN ? AND ?
    GROUP BY DATE(checkin)
    ORDER BY data ASC
");
$stmt->execute([$dataInicio, $dataFim]);
$ocupacaoDiaria = $stmt->fetchAll();

// Receita por Banco
$sqlReceita = "
    SELECT b.nome_banco, 
           COUNT(r.id) as total_reservas,
           SUM(CASE WHEN r.status IN ('CONFIRMADA', 'CHECKIN_REALIZADO', 'CHECKOUT_REALIZADO') THEN r.total_liquido ELSE 0 END) as receita
    FROM bancos b
    LEFT JOIN reservas r ON r.banco_escolhido_id = b.id 
        AND DATE(r.criado_em) BETWEEN ? AND ?
    WHERE b.ativo = 1
    GROUP BY b.id, b.nome_banco
    ORDER BY receita DESC
";
$stmt = $db->prepare($sqlReceita);
$stmt->execute([$dataInicio, $dataFim]);
$receitaPorBanco = $stmt->fetchAll();

// Reservas por Status
$sqlStatus = "
    SELECT status, COUNT(*) as total, SUM(total_liquido) as valor_total
    FROM reservas
    WHERE DATE(criado_em) BETWEEN ? AND ?
    GROUP BY status
    ORDER BY total DESC
";
$stmt = $db->prepare($sqlStatus);
$stmt->execute([$dataInicio, $dataFim]);
$reservasPorStatus = $stmt->fetchAll();

// Origem (logado vs visitante)
$stmt = $db->prepare("
    SELECT 
        CASE WHEN usuario_id IS NULL THEN 'Visitante' ELSE 'Hóspede Logado' END as origem,
        COUNT(*) as total,
        SUM(total_liquido) as valor_total
    FROM reservas
    WHERE DATE(criado_em) BETWEEN ? AND ?
    GROUP BY origem
");
$stmt->execute([$dataInicio, $dataFim]);
$origemClientes = $stmt->fetchAll();

// Tempo médio de análise
$stmt = $db->prepare("
    SELECT AVG(TIMESTAMPDIFF(HOUR, 
        (SELECT MIN(criado_em) FROM comprovantes WHERE reserva_id = r.id),
        r.atualizado_em
    )) as tempo_medio_horas
    FROM reservas r
    WHERE r.status IN ('CONFIRMADA', 'RECUSADA')
    AND DATE(r.criado_em) BETWEEN ? AND ?
");
$stmt->execute([$dataInicio, $dataFim]);
$tempoMedio = $stmt->fetch();
$tempoMedioHoras = round($tempoMedio['tempo_medio_horas'] ?? 0, 1);

// Buscar bancos para filtro
$stmt = $db->query("SELECT id, nome_banco FROM bancos WHERE ativo = 1 ORDER BY nome_banco");
$bancos = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios | Hotel Mucinga Nzambi</title>
    
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
        .stat-box {
            background: linear-gradient(135deg, rgba(0, 80, 81, 0.1) 0%, rgba(242, 141, 0, 0.1) 100%);
            border: 2px solid #005051;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #005051;
        }
        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="content-card">
            <h2 style="color: #005051; font-weight: 700; margin-bottom: 25px;">
                <i class="bi bi-graph-up"></i> Relatórios
            </h2>
            
            <!-- Filtros -->
            <div class="filters-card">
                <form method="GET" action="">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Data Início</label>
                            <input type="date" name="data_inicio" class="form-control" 
                                   value="<?= htmlspecialchars($dataInicio) ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Data Fim</label>
                            <input type="date" name="data_fim" class="form-control" 
                                   value="<?= htmlspecialchars($dataFim) ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Tipo de Relatório</label>
                            <select name="tipo" class="form-select">
                                <option value="ocupacao" <?= $tipoRelatorio === 'ocupacao' ? 'selected' : '' ?>>Ocupação</option>
                                <option value="receita" <?= $tipoRelatorio === 'receita' ? 'selected' : '' ?>>Receita</option>
                                <option value="status" <?= $tipoRelatorio === 'status' ? 'selected' : '' ?>>Por Status</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-search"></i> Gerar Relatório
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Estatísticas Gerais -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="stat-box">
                        <div class="stat-value"><?= count($ocupacaoDiaria) ?></div>
                        <div class="stat-label">Dias Analisados</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-box">
                        <div class="stat-value"><?= array_sum(array_column($reservasPorStatus, 'total')) ?></div>
                        <div class="stat-label">Total de Reservas</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-box">
                        <div class="stat-value"><?= formatarMoeda(array_sum(array_column($receitaPorBanco, 'receita'))) ?></div>
                        <div class="stat-label">Receita Total</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-box">
                        <div class="stat-value"><?= $tempoMedioHoras ?>h</div>
                        <div class="stat-label">Tempo Médio de Análise</div>
                    </div>
                </div>
            </div>
            
            <!-- Receita por Banco -->
            <div class="content-card">
                <h3 style="color: #005051; margin-bottom: 20px;">Receita por Banco</h3>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Banco</th>
                                <th>Total de Reservas</th>
                                <th>Receita Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($receitaPorBanco as $item): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($item['nome_banco']) ?></strong></td>
                                    <td><?= $item['total_reservas'] ?></td>
                                    <td><strong style="color: #005051;"><?= formatarMoeda($item['receita'] ?? 0) ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Reservas por Status -->
            <div class="content-card">
                <h3 style="color: #005051; margin-bottom: 20px;">Reservas por Status</h3>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Quantidade</th>
                                <th>Valor Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservasPorStatus as $item): ?>
                                <tr>
                                    <td><?= statusBadge($item['status']) ?></td>
                                    <td><strong><?= $item['total'] ?></strong></td>
                                    <td><strong style="color: #005051;"><?= formatarMoeda($item['valor_total'] ?? 0) ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Origem dos Clientes -->
            <div class="content-card">
                <h3 style="color: #005051; margin-bottom: 20px;">Origem dos Clientes</h3>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Origem</th>
                                <th>Quantidade</th>
                                <th>Valor Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($origemClientes as $item): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($item['origem']) ?></strong></td>
                                    <td><?= $item['total'] ?></td>
                                    <td><strong style="color: #005051;"><?= formatarMoeda($item['valor_total'] ?? 0) ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Botão Exportar PDF -->
            <div class="text-center mt-4">
                <a href="../pdf/relatorio_reservas.php?inicio=<?= urlencode($dataInicio) ?>&fim=<?= urlencode($dataFim) ?>" 
                   target="_blank" class="btn btn-primary">
                    <i class="bi bi-file-earmark-pdf"></i> Exportar Relatório em PDF
                </a>
            </div>
        </div>
    </div>
    
    <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>

