<?php
/**
 * Página de Login
 * Hotel Mucinga Nzambi
 */

define('SYSTEM_ACCESS', true);
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/rbac.php';
require_once 'includes/helpers.php';

$erro = '';

// Se já está logado, redirecionar (não pode acessar página de login)
if (Auth::isLoggedIn()) {
    $user = Auth::getUser();
    if (in_array($user['role'], ['ADMIN', 'RECEPCAO', 'FINANCEIRO'])) {
        header('Location: admin/index.php');
    } else {
        header('Location: minha-conta.php');
    }
    exit;
}

// Processar login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? ''; // NÃO fazer trim na senha
    
    if ($email && !empty($senha)) {
        try {
            $resultado = Auth::login($email, $senha);
            
            if ($resultado['success']) {
                $user = $resultado['user'];
                
                // Redirecionar conforme perfil
                if (in_array($user['role'], ['ADMIN', 'RECEPCAO', 'FINANCEIRO'])) {
                    header('Location: admin/index.php');
                    exit;
                } else {
                    header('Location: minha-conta.php');
                    exit;
                }
            } else {
                $erro = $resultado['message'] ?? 'Email ou senha incorretos';
            }
        } catch (Exception $e) {
            $erro = 'Erro ao fazer login. Tente novamente.';
            error_log('Login Error: ' . $e->getMessage());
        }
    } else {
        $erro = 'Por favor, preencha todos os campos';
    }
}

?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Hotel Mucinga Nzambi</title>
    
    <!-- Google Fonts Elegantes -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    
    <?php include 'includes/navbar-styles.php'; ?>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%);
            min-height: 100vh;
            padding-top: 120px;
            position: relative;
            overflow-x: hidden;
        }

        /* Background decorativo */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 50%, rgba(0, 80, 81, 0.03) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(242, 141, 0, 0.03) 0%, transparent 50%);
            z-index: 0;
            pointer-events: none;
        }

        .login-wrapper {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: calc(100vh - 120px);
            padding: 40px 20px;
        }

        .login-container {
            max-width: 480px;
            width: 100%;
            animation: fadeInUp 0.8s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow: 
                0 20px 60px rgba(0, 0, 0, 0.1),
                0 0 0 1px rgba(255, 255, 255, 0.5);
            padding: 50px 45px;
            border: 1px solid rgba(255, 255, 255, 0.8);
        }

        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .login-header .icon-wrapper {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, #005051 0%, #006b6d 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 30px rgba(0, 80, 81, 0.25);
        }

        .login-header .icon-wrapper i {
            font-size: 2.5rem;
            color: #fff;
        }

        .login-card h2 {
            font-family: 'Playfair Display', serif;
            color: #005051;
            font-weight: 700;
            font-size: 2rem;
            margin-bottom: 10px;
            letter-spacing: -0.5px;
        }

        .login-card .subtitle {
            color: #6c757d;
            font-size: 0.95rem;
            font-weight: 400;
            letter-spacing: 0.3px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-label {
            font-weight: 600;
            color: #2c3e50;
            font-size: 0.9rem;
            margin-bottom: 10px;
            display: block;
            letter-spacing: 0.2px;
        }

        .form-control {
            font-family: 'Inter', sans-serif;
            border-radius: 12px;
            border: 2px solid #e9ecef;
            padding: 14px 18px;
            font-size: 0.95rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: #fff;
            color: #2c3e50;
        }

        .form-control:focus {
            border-color: #005051;
            box-shadow: 0 0 0 4px rgba(0, 80, 81, 0.1);
            outline: none;
            background: #fff;
        }

        .form-control::placeholder {
            color: #adb5bd;
            font-weight: 400;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            font-size: 1.1rem;
        }

        .input-wrapper .form-control {
            padding-left: 50px;
        }

        .btn-primary {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #005051 0%, #006b6d 100%);
            border: none;
            padding: 16px 40px;
            font-weight: 600;
            font-size: 1rem;
            border-radius: 12px;
            width: 100%;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            color: #fff;
            letter-spacing: 0.3px;
            box-shadow: 0 8px 25px rgba(0, 80, 81, 0.25);
            position: relative;
            overflow: hidden;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.6s ease;
        }

        .btn-primary:hover::before {
            left: 100%;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #006b6d 0%, #005051 100%);
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(0, 80, 81, 0.35);
            color: #fff;
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .alert {
            border-radius: 12px;
            padding: 14px 18px;
            margin-bottom: 25px;
            border: none;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .alert-danger {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border-left: 4px solid #dc3545;
        }

        .btn-link {
            color: #005051;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .btn-link:hover {
            color: #006b6d;
            text-decoration: underline;
        }

        .divider {
            text-align: center;
            margin: 30px 0;
            position: relative;
            color: #adb5bd;
            font-size: 0.85rem;
        }

        .divider::before,
        .divider::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 40%;
            height: 1px;
            background: linear-gradient(to right, transparent, #dee2e6, transparent);
        }

        .divider::before {
            left: 0;
        }

        .divider::after {
            right: 0;
        }

        .register-link {
            text-align: center;
            margin-top: 30px;
            padding-top: 25px;
            border-top: 1px solid #e9ecef;
            color: #6c757d;
            font-size: 0.9rem;
        }

        .register-link a {
            color: #005051;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .register-link a:hover {
            color: #006b6d;
            text-decoration: underline;
        }

        @media (max-width: 576px) {
            .login-card {
                padding: 40px 30px;
                border-radius: 20px;
            }

            .login-card h2 {
                font-size: 1.75rem;
            }

            body {
                padding-top: 100px;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="login-wrapper">
        <div class="login-container">
            <div class="login-card">
                <div class="login-header">
                    <div class="icon-wrapper">
                        <i class="bi bi-person-circle"></i>
                    </div>
                    <h2>Bem-vindo de volta</h2>
                    <p class="subtitle">Entre na sua conta para continuar</p>
                </div>
                
                <?php if ($erro): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($erro) ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <div class="input-wrapper">
                            <i class="bi bi-envelope"></i>
                            <input type="email" name="email" class="form-control" placeholder="seu@email.com" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Senha</label>
                        <div class="input-wrapper">
                            <i class="bi bi-lock"></i>
                            <input type="password" name="senha" class="form-control" placeholder="••••••••" required>
                        </div>
                    </div>
                    
                    <div class="mb-4 text-end">
                        <a href="recuperar_senha.php" class="btn-link">Esqueceu a senha?</a>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-box-arrow-in-right"></i> Entrar
                    </button>
                    
                    <div class="register-link">
                        Não tem conta? <a href="register.php">Cadastre-se agora</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
