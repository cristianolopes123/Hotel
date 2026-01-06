<?php
/**
 * Minha Conta / Perfil do Hóspede
 * Hotel Mucinga Nzambi
 */

define('SYSTEM_ACCESS', true);
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/rbac.php';
require_once 'includes/helpers.php';

// Verificar login
if (!Auth::isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$usuario = Auth::getUser();

// Apenas hóspedes podem acessar esta página
if ($usuario['role'] !== 'HOSPEDE') {
    if (in_array($usuario['role'], ['ADMIN', 'RECEPCAO', 'FINANCEIRO'])) {
        header('Location: admin/index.php');
    } else {
        header('Location: index.php');
    }
    exit;
}

$db = getDB();
$mensagem = '';
$sucesso = false;

// Processar atualização de perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['atualizar_perfil'])) {
    $nome = sanitizeInput($_POST['nome'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $telefone = sanitizeInput($_POST['telefone'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirmarSenha = $_POST['confirmar_senha'] ?? '';
    
    if ($nome && $email) {
        try {
            // Verificar se email já existe em outro usuário
            $stmt = $db->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
            $stmt->execute([$email, $usuario['id']]);
            if ($stmt->fetch()) {
                $mensagem = 'Email já cadastrado por outro usuário';
            } else {
                // Atualizar dados básicos
                $stmt = $db->prepare("UPDATE usuarios SET nome = ?, email = ?, telefone = ? WHERE id = ?");
                $stmt->execute([$nome, $email, $telefone, $usuario['id']]);
                
                // Atualizar senha se fornecida
                if ($senha && $senha === $confirmarSenha && strlen($senha) >= PASSWORD_MIN_LENGTH) {
                    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                    $stmt = $db->prepare("UPDATE usuarios SET senha_hash = ? WHERE id = ?");
                    $stmt->execute([$senha_hash, $usuario['id']]);
                } elseif ($senha && $senha !== $confirmarSenha) {
                    $mensagem = 'As senhas não coincidem';
                }
                
                // Atualizar sessão
                $_SESSION['user_nome'] = $nome;
                $_SESSION['user_email'] = $email;
                
                // Recarregar dados do usuário
                $stmt = $db->prepare("SELECT * FROM usuarios WHERE id = ?");
                $stmt->execute([$usuario['id']]);
                $usuario = $stmt->fetch();
                
                $sucesso = true;
                $mensagem = 'Perfil atualizado com sucesso!';
            }
        } catch (PDOException $e) {
            $mensagem = 'Erro ao atualizar perfil: ' . $e->getMessage();
        }
    }
}

// Buscar reservas do usuário
$stmt = $db->prepare("
    SELECT COUNT(*) as total,
           SUM(CASE WHEN status = 'CONFIRMADA' THEN 1 ELSE 0 END) as confirmadas,
           SUM(CASE WHEN status = 'EM_ANALISE' THEN 1 ELSE 0 END) as em_analise,
           SUM(CASE WHEN status IN ('CONFIRMADA', 'CHECKIN_REALIZADO', 'CHECKOUT_REALIZADO') THEN total_liquido ELSE 0 END) as total_gasto
    FROM reservas
    WHERE usuario_id = ?
");
$stmt->execute([$usuario['id']]);
$estatisticas = $stmt->fetch();

?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minha Conta | Hotel Mucinga Nzambi</title>
    
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
        
        .conta-section {
            padding: 60px 0;
            min-height: calc(100vh - 200px);
        }
        
        .conta-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .page-title {
            color: #005051;
            font-weight: 700;
            margin-bottom: 30px;
        }
        
        .profile-card, .stats-card {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 25px;
        }
        
        .profile-card h3, .stats-card h3 {
            color: #005051;
            font-weight: 700;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #eee;
        }
        
        .stat-box {
            text-align: center;
            padding: 20px;
            background: linear-gradient(135deg, rgba(0, 80, 81, 0.05) 0%, rgba(242, 141, 0, 0.05) 100%);
            border-radius: 10px;
            border: 2px solid #005051;
        }
        
        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: #005051;
            margin-bottom: 10px;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #005051 0%, #006b6d 100%);
            border: none;
            padding: 12px 30px;
            font-weight: 600;
            border-radius: 8px;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #006b6d 0%, #005051 100%);
            transform: translateY(-2px);
        }
        
        .quick-actions {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .quick-action-btn {
            flex: 1;
            min-width: 150px;
            padding: 15px;
            background: #f8f9fa;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            text-decoration: none;
            color: #005051;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .quick-action-btn:hover {
            background: #005051;
            color: #fff;
            border-color: #005051;
            transform: translateY(-3px);
        }
        
        .quick-action-btn i {
            font-size: 2rem;
            display: block;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <section class="conta-section">
        <div class="conta-container">
            <h1 class="page-title">
                <i class="bi bi-person-circle"></i> Minha Conta
            </h1>
            
            <?php if ($mensagem): ?>
                <div class="alert <?= $sucesso ? 'alert-success' : 'alert-danger' ?>" role="alert">
                    <i class="bi bi-<?= $sucesso ? 'check-circle' : 'exclamation-triangle' ?>"></i> 
                    <?= htmlspecialchars($mensagem) ?>
                </div>
            <?php endif; ?>
            
            <div class="row">
                <!-- Estatísticas -->
                <div class="col-md-4 mb-4">
                    <div class="stats-card">
                        <h3><i class="bi bi-bar-chart"></i> Estatísticas</h3>
                        
                        <div class="stat-box mb-3">
                            <div class="stat-value"><?= $estatisticas['total'] ?></div>
                            <div class="stat-label">Total de Reservas</div>
                        </div>
                        
                        <div class="stat-box mb-3">
                            <div class="stat-value"><?= $estatisticas['confirmadas'] ?></div>
                            <div class="stat-label">Reservas Confirmadas</div>
                        </div>
                        
                        <div class="stat-box mb-3">
                            <div class="stat-value"><?= $estatisticas['em_analise'] ?></div>
                            <div class="stat-label">Em Análise</div>
                        </div>
                        
                        <div class="stat-box">
                            <div class="stat-value" style="font-size: 1.8rem;"><?= formatarMoeda($estatisticas['total_gasto'] ?? 0) ?></div>
                            <div class="stat-label">Total Gasto</div>
                        </div>
                    </div>
                    
                    <!-- Ações Rápidas -->
                    <div class="profile-card">
                        <h3><i class="bi bi-lightning"></i> Ações Rápidas</h3>
                        <div class="quick-actions">
                            <a href="minhas-reservas.php" class="quick-action-btn">
                                <i class="bi bi-calendar-check"></i>
                                <strong>Minhas Reservas</strong>
                            </a>
                            <a href="disponibilidade.php" class="quick-action-btn">
                                <i class="bi bi-calendar-plus"></i>
                                <strong>Nova Reserva</strong>
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Formulário de Perfil -->
                <div class="col-md-8">
                    <div class="profile-card">
                        <h3><i class="bi bi-person-gear"></i> Dados do Perfil</h3>
                        
                        <form method="POST" action="">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Nome Completo <span class="text-danger">*</span></label>
                                    <input type="text" name="nome" class="form-control" 
                                           value="<?= htmlspecialchars($usuario['nome']) ?>" required>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control" 
                                           value="<?= htmlspecialchars($usuario['email']) ?>" required>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Telefone/WhatsApp</label>
                                    <input type="tel" name="telefone" class="form-control" 
                                           value="<?= htmlspecialchars($usuario['telefone'] ?? '') ?>" 
                                           placeholder="+244 923 456 789">
                                </div>
                                
                                <div class="col-md-12">
                                    <hr>
                                    <h5 class="mb-3" style="color: #005051;">Alterar Senha</h5>
                                    <p class="text-muted small">Deixe em branco se não quiser alterar</p>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Nova Senha</label>
                                    <input type="password" name="senha" class="form-control" 
                                           minlength="<?= PASSWORD_MIN_LENGTH ?>">
                                    <small class="text-muted">Mínimo de <?= PASSWORD_MIN_LENGTH ?> caracteres</small>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Confirmar Nova Senha</label>
                                    <input type="password" name="confirmar_senha" class="form-control" 
                                           minlength="<?= PASSWORD_MIN_LENGTH ?>">
                                </div>
                                
                                <div class="col-12 mt-4">
                                    <button type="submit" name="atualizar_perfil" class="btn btn-primary">
                                        <i class="bi bi-check-circle"></i> Atualizar Perfil
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Informações da Conta -->
                    <div class="profile-card">
                        <h3><i class="bi bi-info-circle"></i> Informações da Conta</h3>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <strong style="color: #005051;">ID da Conta:</strong><br>
                                <span class="text-muted">#<?= $usuario['id'] ?></span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong style="color: #005051;">Tipo de Conta:</strong><br>
                                <span class="badge bg-info">Hóspede</span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong style="color: #005051;">Conta criada em:</strong><br>
                                <span class="text-muted"><?= formatarData($usuario['criado_em'] ?? date('Y-m-d'), 'd/m/Y') ?></span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong style="color: #005051;">Status:</strong><br>
                                <?php if ($usuario['ativo']): ?>
                                    <span class="badge bg-success">Ativa</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Inativa</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validar senha ao submeter
        document.querySelector('form').addEventListener('submit', function(e) {
            const senha = document.querySelector('[name="senha"]').value;
            const confirmar = document.querySelector('[name="confirmar_senha"]').value;
            
            if (senha && senha !== confirmar) {
                e.preventDefault();
                alert('As senhas não coincidem!');
            }
        });
    </script>
</body>
</html>

