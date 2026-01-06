<?php
/**
 * Painel Administrativo
 * Hotel Mucinga Nzambi
 */

// Carrega o sistema de proteção
require_once __DIR__ . '/_guard.php';

// Verifica permissão específica para visualizar o dashboard
if (!RBAC::hasPermission('VIEW_DASHBOARD')) {
    $_SESSION['error_message'] = 'Você não tem permissão para acessar o dashboard.';
    header('Location: ../includes/index.php');
    exit;
}

$db = getDB();
$user = Auth::getUser(); // Já verificado no _guard.php

// ============================================
// ESTATÍSTICAS DO SISTEMA
// ============================================

// 1. CONTAGEM DE RESERVAS POR STATUS
$statusCounts = [
    'total' => 0,
    'pendentes' => 0,
    'confirmadas' => 0,
    'checkin' => 0,
    'checkout' => 0
];

try {
    // Total de reservas
    $stmt = $db->query("SELECT COUNT(*) as total FROM reservas");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $statusCounts['total'] = $result['total'] ?? 0;
    
    // Reservas pendentes
    $stmt = $db->query("SELECT COUNT(*) as count FROM reservas WHERE status IN ('PENDENTE_COMPROVANTE', 'EM_ANALISE')");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $statusCounts['pendentes'] = $result['count'] ?? 0;
    
    // Reservas confirmadas
    $stmt = $db->query("SELECT COUNT(*) as count FROM reservas WHERE status = 'CONFIRMADA'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $statusCounts['confirmadas'] = $result['count'] ?? 0;
    
    // Check-in realizados
    $stmt = $db->query("SELECT COUNT(*) as count FROM reservas WHERE status = 'CHECKIN_REALIZADO'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $statusCounts['checkin'] = $result['count'] ?? 0;
    
    // Check-out realizados
    $stmt = $db->query("SELECT COUNT(*) as count FROM reservas WHERE status = 'CHECKOUT_REALIZADO'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $statusCounts['checkout'] = $result['count'] ?? 0;
    
} catch (PDOException $e) {
    error_log("Erro ao buscar estatísticas: " . $e->getMessage());
}

// 2. STATUS DOS QUARTOS (usando a VIEW que criamos)
$quartosStatus = [
    'total' => 35, // Total fixo baseado na sua estrutura
    'disponiveis' => 0,
    'ocupados' => 0,
    'reservados' => 0,
    'manutencao' => 0
];

try {
    // Usando a view vw_dashboard_status_quartos que já existe no seu banco
    $stmt = $db->query("SELECT * FROM vw_dashboard_status_quartos");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        $quartosStatus['disponiveis'] = $result['disponiveis'] ?? 0;
        $quartosStatus['reservados'] = $result['reservados'] ?? 0;
        $quartosStatus['ocupados'] = $result['ocupados'] ?? 0;
        $quartosStatus['manutencao'] = $result['manutencao'] ?? 0;
    }
} catch (PDOException $e) {
    // Se a view não existir, usar consulta direta
    error_log("View não encontrada, usando consulta direta: " . $e->getMessage());
    
    $stmt = $db->query("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status_ocupacao = 'DISPONIVEL' THEN 1 ELSE 0 END) as disponiveis,
        SUM(CASE WHEN status_ocupacao = 'RESERVADO' THEN 1 ELSE 0 END) as reservados,
        SUM(CASE WHEN status_ocupacao = 'OCUPADO' THEN 1 ELSE 0 END) as ocupados,
        SUM(CASE WHEN status_ocupacao = 'MANUTENCAO' THEN 1 ELSE 0 END) as manutencao
        FROM quartos WHERE status = 'ATIVO'");
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        $quartosStatus['total'] = $result['total'] ?? 35;
        $quartosStatus['disponiveis'] = $result['disponiveis'] ?? 0;
        $quartosStatus['reservados'] = $result['reservados'] ?? 0;
        $quartosStatus['ocupados'] = $result['ocupados'] ?? 0;
        $quartosStatus['manutencao'] = $result['manutencao'] ?? 0;
    }
}

// 3. RECEITA DO MÊS
$receitaMes = 0;
try {
    $mesAtual = date('Y-m');
    $stmt = $db->prepare("SELECT SUM(total_liquido) as total FROM reservas 
                         WHERE status IN ('CONFIRMADA', 'CHECKIN_REALIZADO', 'CHECKOUT_REALIZADO')
                         AND DATE_FORMAT(criado_em, '%Y-%m') = ?");
    $stmt->execute([$mesAtual]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $receitaMes = $result['total'] ?? 0;
} catch (PDOException $e) {
    error_log("Erro ao buscar receita: " . $e->getMessage());
}

// 4. MÉDIA DE AVALIAÇÕES (CORRIGIDO - usar 'classificacao' em vez de 'avaliacao')
$mediaAvaliacoes = 0;
try {
    // ✅ CORREÇÃO AQUI: usar 'classificacao' em vez de 'avaliacao'
    $stmt = $db->query("SELECT AVG(classificacao) as media FROM avaliacoes WHERE status = 'APROVADA'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $mediaAvaliacoes = round($result['media'] ?? 0, 1);
} catch (PDOException $e) {
    error_log("Erro ao buscar avaliações: " . $e->getMessage());
}

// 5. PRÓXIMOS CHECK-INS (hoje e amanhã)
$proximosCheckins = [];
try {
    $hoje = date('Y-m-d');
    $amanha = date('Y-m-d', strtotime('+1 day'));
    
    $stmt = $db->prepare("SELECT r.*, q.numero as numero_quarto 
                         FROM reservas r
                         LEFT JOIN quartos q ON r.quarto_id = q.id
                         WHERE r.status = 'CONFIRMADA'
                         AND DATE(r.checkin) BETWEEN ? AND ?
                         ORDER BY r.checkin ASC
                         LIMIT 10");
    $stmt->execute([$hoje, $amanha]);
    $proximosCheckins = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Erro ao buscar checkins: " . $e->getMessage());
}

// 6. RESERVAS RECENTES
$reservasRecentes = [];
try {
    $stmt = $db->query("SELECT r.*, q.numero as numero_quarto 
                       FROM reservas r
                       LEFT JOIN quartos q ON r.quarto_id = q.id
                       ORDER BY r.criado_em DESC
                       LIMIT 5");
    $reservasRecentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Erro ao buscar reservas recentes: " . $e->getMessage());
}

// ============================================
// HTML DO PAINEL
// ============================================
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo | Hotel Mucinga Nzambi</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }

        .sidebar {
            background: linear-gradient(180deg, #005051 0%, #003738 100%);
            color: white;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            width: 250px;
            padding: 20px 0;
            box-shadow: 3px 0 15px rgba(0, 0, 0, 0.1);
        }

        .sidebar-brand {
            text-align: center;
            padding: 20px 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }

        .sidebar-brand h3 {
            font-weight: 700;
            font-size: 1.5rem;
            color: white;
            margin: 0;
        }

        .sidebar-brand .subtitle {
            font-size: 0.8rem;
            opacity: 0.8;
            margin-top: 5px;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 20px;
            margin: 5px 15px;
            border-radius: 8px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-link:hover, .nav-link.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            text-decoration: none;
        }

        .nav-link i {
            font-size: 1.2rem;
            width: 24px;
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
        }

        .navbar-top {
            background: white;
            padding: 15px 25px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #005051 0%, #006b6d 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .welcome-message h1 {
            font-size: 1.8rem;
            color: #005051;
            margin-bottom: 5px;
            font-weight: 700;
        }

        .welcome-message p {
            color: #6c757d;
            margin: 0;
        }

        /* Cards de Estatísticas */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            border-left: 5px solid #005051;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            font-size: 1.8rem;
            color: white;
        }

        .stat-card.reservas { border-left-color: #005051; }
        .stat-card.reservas .stat-icon { background: linear-gradient(135deg, #005051, #006b6d); }

        .stat-card.quartos { border-left-color: #28a745; }
        .stat-card.quartos .stat-icon { background: linear-gradient(135deg, #28a745, #20c997); }

        .stat-card.receita { border-left-color: #ffc107; }
        .stat-card.receita .stat-icon { background: linear-gradient(135deg, #ffc107, #fd7e14); }

        .stat-card.avaliacoes { border-left-color: #17a2b8; }
        .stat-card.avaliacoes .stat-icon { background: linear-gradient(135deg, #17a2b8, #0dcaf0); }

        .stat-value {
            font-size: 2.2rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 1rem;
            color: #6c757d;
            font-weight: 500;
        }

        /* Tabelas */
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            margin-bottom: 25px;
        }

        .card-header {
            background: white;
            border-bottom: 1px solid #e9ecef;
            padding: 20px 25px;
            border-radius: 15px 15px 0 0;
        }

        .card-header h5 {
            color: #005051;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .table {
            margin-bottom: 0;
        }

        .table th {
            border-top: none;
            color: #005051;
            font-weight: 600;
            padding: 15px;
        }

        .table td {
            padding: 15px;
            vertical-align: middle;
        }

        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.85rem;
        }

        .badge-pendente { background-color: #fff3cd; color: #856404; }
        .badge-confirmada { background-color: #d4edda; color: #155724; }
        .badge-checkin { background-color: #cce5ff; color: #004085; }
        .badge-checkout { background-color: #d1ecf1; color: #0c5460; }
        .badge-cancelada { background-color: #f8d7da; color: #721c24; }

        /* Responsividade */
        @media (max-width: 992px) {
            .sidebar {
                width: 70px;
                overflow: hidden;
            }
            
            .sidebar-brand h3,
            .sidebar-brand .subtitle,
            .nav-link span {
                display: none;
            }
            
            .main-content {
                margin-left: 70px;
            }
            
            .nav-link {
                justify-content: center;
                padding: 15px;
            }
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .welcome-message h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <h3>Mucinga</h3>
            <div class="subtitle">Painel Administrativo</div>
        </div>
        
        <nav class="nav flex-column">
            <a href="index.php" class="nav-link active">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
            
            <a href="reservas.php" class="nav-link">
                <i class="bi bi-calendar-check"></i>
                <span>Reservas</span>
            </a>
            
            <a href="quartos.php" class="nav-link">
                <i class="bi bi-door-closed"></i>
                <span>Quartos</span>
            </a>
            
            <a href="checkin.php" class="nav-link">
                <i class="bi bi-box-arrow-in-right"></i>
                <span>Check-in</span>
            </a>
            
            <a href="checkout.php" class="nav-link">
                <i class="bi bi-box-arrow-right"></i>
                <span>Check-out</span>
            </a>
            
            <a href="clientes.php" class="nav-link">
                <i class="bi bi-people"></i>
                <span>Clientes</span>
            </a>
            
            <?php if ($user['role'] === 'ADMIN'): ?>
            <a href="usuarios.php" class="nav-link">
                <i class="bi bi-person-badge"></i>
                <span>Usuários</span>
            </a>
            
            <a href="relatorios.php" class="nav-link">
                <i class="bi bi-graph-up"></i>
                <span>Relatórios</span>
            </a>
            
            <a href="configuracoes.php" class="nav-link">
                <i class="bi bi-gear"></i>
                <span>Configurações</span>
            </a>
            <?php endif; ?>
            
            <div style="margin-top: auto; padding: 20px;">
                <a href="../logout.php" class="nav-link text-danger">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Sair</span>
                </a>
            </div>
        </nav>
    </div>
    
    <!-- Conteúdo Principal -->
    <div class="main-content">
        <!-- Barra Superior -->
        <div class="navbar-top">
            <div class="welcome-message">
                <h1>Bem-vindo, <?= htmlspecialchars($user['nome']) ?>!</h1>
                <p><?= date('d/m/Y H:i') ?> • Painel Administrativo</p>
            </div>
            
            <div class="user-info">
                <div class="user-avatar">
                    <?= strtoupper(substr($user['nome'], 0, 2)) ?>
                </div>
                <div>
                    <strong><?= htmlspecialchars($user['nome']) ?></strong>
                    <div class="text-muted" style="font-size: 0.9rem;">
                        <?= $user['role'] === 'ADMIN' ? 'Administrador' : 
                           ($user['role'] === 'RECEPCAO' ? 'Recepcionista' : 'Financeiro') ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Estatísticas -->
        <div class="stats-grid">
            <div class="stat-card reservas">
                <div class="stat-icon">
                    <i class="bi bi-calendar-check"></i>
                </div>
                <div class="stat-value"><?= $statusCounts['total'] ?></div>
                <div class="stat-label">Total de Reservas</div>
                <div class="text-muted mt-2" style="font-size: 0.9rem;">
                    <span class="text-success"><?= $statusCounts['confirmadas'] ?> confirmadas</span> • 
                    <span class="text-warning"><?= $statusCounts['pendentes'] ?> pendentes</span>
                </div>
            </div>
            
            <div class="stat-card quartos">
                <div class="stat-icon">
                    <i class="bi bi-door-closed"></i>
                </div>
                <div class="stat-value"><?= $quartosStatus['disponiveis'] ?>/<?= $quartosStatus['total'] ?></div>
                <div class="stat-label">Quartos Disponíveis</div>
                <div class="text-muted mt-2" style="font-size: 0.9rem;">
                    <span class="text-danger"><?= $quartosStatus['ocupados'] ?> ocupados</span> • 
                    <span class="text-info"><?= $quartosStatus['reservados'] ?> reservados</span>
                </div>
            </div>
            
            <div class="stat-card receita">
                <div class="stat-icon">
                    <i class="bi bi-currency-dollar"></i>
                </div>
                <div class="stat-value"><?= number_format($receitaMes, 0, ',', '.') ?> Kz</div>
                <div class="stat-label">Receita do Mês</div>
                <div class="text-muted mt-2" style="font-size: 0.9rem;">
                    <?= date('F Y') ?>
                </div>
            </div>
            
            <div class="stat-card avaliacoes">
                <div class="stat-icon">
                    <i class="bi bi-star-fill"></i>
                </div>
                <div class="stat-value"><?= $mediaAvaliacoes ?>/5.0</div>
                <div class="stat-label">Avaliação Média</div>
                <div class="text-muted mt-2" style="font-size: 0.9rem;">
                    Baseado em avaliações aprovadas
                </div>
            </div>
        </div>
        
        <!-- Próximos Check-ins -->
        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-clock-history"></i> Próximos Check-ins (Hoje e Amanhã)</h5>
            </div>
            <div class="card-body">
                <?php if (empty($proximosCheckins)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-calendar-x" style="font-size: 3rem;"></i>
                        <p class="mt-3">Nenhum check-in agendado para hoje ou amanhã</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Cliente</th>
                                    <th>Quarto</th>
                                    <th>Check-in</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($proximosCheckins as $reserva): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($reserva['codigo']) ?></strong>
                                    </td>
                                    <td>
                                        <div><?= htmlspecialchars($reserva['nome_cliente']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($reserva['telefone']) ?></small>
                                    </td>
                                    <td>
                                        <?php if ($reserva['numero_quarto']): ?>
                                            <span class="badge badge-secondary">#<?= $reserva['numero_quarto'] ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">Não atribuído</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= date('d/m/Y H:i', strtotime($reserva['checkin'])) ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-confirmada">CONFIRMADA</span>
                                    </td>
                                    <td>
                                        <a href="checkin.php?id=<?= $reserva['id'] ?>" class="btn btn-sm btn-primary">
                                            <i class="bi bi-box-arrow-in-right"></i> Check-in
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Reservas Recentes -->
        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-list-ul"></i> Reservas Recentes</h5>
            </div>
            <div class="card-body">
                <?php if (empty($reservasRecentes)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-calendar" style="font-size: 3rem;"></i>
                        <p class="mt-3">Nenhuma reserva encontrada</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Código</th>
                                    <th>Cliente</th>
                                    <th>Período</th>
                                    <th>Valor</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reservasRecentes as $reserva): 
                                    $statusClass = 'badge-pendente';
                                    if ($reserva['status'] === 'CONFIRMADA') $statusClass = 'badge-confirmada';
                                    if ($reserva['status'] === 'CHECKIN_REALIZADO') $statusClass = 'badge-checkin';
                                    if ($reserva['status'] === 'CHECKOUT_REALIZADO') $statusClass = 'badge-checkout';
                                    if (in_array($reserva['status'], ['CANCELADA', 'RECUSADA'])) $statusClass = 'badge-cancelada';
                                ?>
                                <tr>
                                    <td>
                                        <?= date('d/m/Y', strtotime($reserva['criado_em'])) ?>
                                        <br>
                                        <small class="text-muted"><?= date('H:i', strtotime($reserva['criado_em'])) ?></small>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($reserva['codigo']) ?></strong>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($reserva['nome_cliente']) ?>
                                        <br>
                                        <small class="text-muted"><?= htmlspecialchars($reserva['email']) ?></small>
                                    </td>
                                    <td>
                                        <?= date('d/m', strtotime($reserva['checkin'])) ?> - 
                                        <?= date('d/m', strtotime($reserva['checkout'])) ?>
                                    </td>
                                    <td>
                                        <strong><?= number_format($reserva['total_liquido'], 0, ',', '.') ?> Kz</strong>
                                    </td>
                                    <td>
                                        <span class="badge <?= $statusClass ?>">
                                            <?= $reserva['status'] ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Atualizar data/hora a cada minuto
        function atualizarHora() {
            const agora = new Date();
            const dataFormatada = agora.toLocaleDateString('pt-BR');
            const horaFormatada = agora.toLocaleTimeString('pt-BR', {hour: '2-digit', minute:'2-digit'});
            
            const elemento = document.querySelector('.welcome-message p');
            if (elemento) {
                elemento.innerHTML = `${dataFormatada} ${horaFormatada} • Painel Administrativo`;
            }
        }
        
        setInterval(atualizarHora, 60000);
        
        // Tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    </script>
</body>
</html>