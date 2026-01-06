<?php
/**
 * Recuperação de Senha
 * Hotel Mucinga Nzambi
 */

define('SYSTEM_ACCESS', true);
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/helpers.php';

$erro = '';
$sucesso = false;
$mostrarForm = true;

// Processar solicitação de recuperação
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $token = $_POST['token'] ?? '';
    $novaSenha = $_POST['nova_senha'] ?? '';
    
    if ($email && empty($token)) {
        // Solicitar recuperação
        $db = getDB();
        $stmt = $db->prepare("SELECT id, nome FROM usuarios WHERE email = ? AND ativo = 1");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();
        
        if ($usuario) {
            // Gerar token
            $token = bin2hex(random_bytes(32));
            $expira = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Salvar token (você pode criar uma tabela de tokens ou usar cache)
            $_SESSION['reset_token_' . $token] = [
                'user_id' => $usuario['id'],
                'email' => $email,
                'expira' => $expira
            ];
            
            // Enviar email com link de recuperação
            $resetLink = BASE_URL . "recuperar_senha.php?token=" . $token;
            $subject = "Recuperação de Senha - Hotel Mucinga Nzambi";
            $message = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #005051; color: #fff; padding: 20px; text-align: center; }
                    .content { background: #f9f9f9; padding: 20px; }
                    .btn { display: inline-block; background: #005051; color: #fff; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>Hotel Mucinga Nzambi</h2>
                    </div>
                    <div class='content'>
                        <p>Olá, <strong>{$usuario['nome']}</strong>!</p>
                        <p>Você solicitou a recuperação de senha.</p>
                        <p>Clique no link abaixo para redefinir sua senha:</p>
                        <a href='{$resetLink}' class='btn'>Redefinir Senha</a>
                        <p>Ou copie e cole no navegador:</p>
                        <p>{$resetLink}</p>
                        <p><small>Este link expira em 1 hora.</small></p>
                        <p>Se você não solicitou esta recuperação, ignore este email.</p>
                    </div>
                </div>
            </body>
            </html>";
            
            // TODO: Implementar envio real de email
            // Por enquanto, mostra o token para desenvolvimento
            $sucesso = true;
            $mostrarForm = false;
            $erro = "Email de recuperação enviado! (Desenvolvimento: Token = {$token})";
        } else {
            $erro = 'Email não encontrado';
        }
    } elseif ($token && $novaSenha) {
        // Processar redefinição de senha
        if (strlen($novaSenha) >= PASSWORD_MIN_LENGTH) {
            $tokenData = $_SESSION['reset_token_' . $token] ?? null;
            
            if ($tokenData && strtotime($tokenData['expira']) > time()) {
                $db = getDB();
                $senha_hash = password_hash($novaSenha, PASSWORD_DEFAULT);
                
                $stmt = $db->prepare("UPDATE usuarios SET senha_hash = ? WHERE id = ?");
                $stmt->execute([$senha_hash, $tokenData['user_id']]);
                
                unset($_SESSION['reset_token_' . $token]);
                
                $sucesso = true;
                $mostrarForm = false;
                $erro = 'Senha redefinida com sucesso! <a href="login.php">Fazer login</a>';
            } else {
                $erro = 'Token inválido ou expirado';
            }
        } else {
            $erro = 'A senha deve ter pelo menos ' . PASSWORD_MIN_LENGTH . ' caracteres';
        }
    }
}

// Verificar token na URL
$tokenUrl = $_GET['token'] ?? '';
if ($tokenUrl) {
    $tokenData = $_SESSION['reset_token_' . $tokenUrl] ?? null;
    if ($tokenData && strtotime($tokenData['expira']) > time()) {
        $mostrarForm = false;
    } else {
        $erro = 'Token inválido ou expirado';
        $mostrarForm = true;
    }
}

?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha | Hotel Mucinga Nzambi</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    
    <?php include 'includes/navbar-styles.php'; ?>
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, rgba(0, 80, 81, 0.05) 0%, rgba(242, 141, 0, 0.05) 100%);
            min-height: 100vh;
            padding-top: 100px;
        }
        
        .reset-container {
            max-width: 450px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        
        .reset-card {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 40px;
        }
        
        .reset-card h2 {
            color: #005051;
            font-weight: 700;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .form-control {
            border-radius: 8px;
            border: 2px solid #e0e0e0;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #005051;
            box-shadow: 0 0 0 0.2rem rgba(0, 80, 81, 0.25);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #005051 0%, #006b6d 100%);
            border: none;
            padding: 12px 40px;
            font-weight: 600;
            border-radius: 30px;
            width: 100%;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #006b6d 0%, #005051 100%);
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="reset-container">
        <div class="reset-card">
            <h2><i class="bi bi-key"></i> Recuperar Senha</h2>
            
            <?php if ($erro): ?>
                <div class="alert <?= $sucesso ? 'alert-success' : 'alert-danger' ?>" role="alert">
                    <?= $sucesso ? htmlspecialchars($erro) : '<i class="bi bi-exclamation-triangle"></i> ' . htmlspecialchars($erro) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($mostrarForm && empty($tokenUrl)): ?>
                <!-- Formulário de Solicitação -->
                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Email</label>
                        <input type="email" name="email" class="form-control" required>
                        <small class="text-muted">Digite o email cadastrado para receber o link de recuperação</small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-envelope"></i> Enviar Link de Recuperação
                    </button>
                    
                    <div class="text-center mt-4">
                        <a href="login.php" class="text-decoration-none" style="color: #005051;">
                            <i class="bi bi-arrow-left"></i> Voltar para Login
                        </a>
                    </div>
                </form>
            <?php elseif ($tokenUrl && !$sucesso): ?>
                <!-- Formulário de Redefinição -->
                <form method="POST" action="">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($tokenUrl) ?>">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nova Senha <span class="text-danger">*</span></label>
                        <input type="password" name="nova_senha" class="form-control" 
                               minlength="<?= PASSWORD_MIN_LENGTH ?>" required>
                        <small class="text-muted">Mínimo de <?= PASSWORD_MIN_LENGTH ?> caracteres</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Confirmar Nova Senha <span class="text-danger">*</span></label>
                        <input type="password" name="confirmar_senha" class="form-control" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Redefinir Senha
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validar confirmação de senha
        document.querySelector('form[method="POST"]')?.addEventListener('submit', function(e) {
            const senha = document.querySelector('[name="nova_senha"]');
            const confirmar = document.querySelector('[name="confirmar_senha"]');
            
            if (senha && confirmar && senha.value !== confirmar.value) {
                e.preventDefault();
                alert('As senhas não coincidem!');
            }
        });
    </script>
</body>
</html>

