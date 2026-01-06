<?php
/**
 * Página de Debug do Login
 * Hotel Mucinga Nzambi
 * 
 * Esta página ajuda a diagnosticar problemas de login
 * REMOVER EM PRODUÇÃO
 */

define('SYSTEM_ACCESS', true);
require_once 'includes/config.php';
require_once 'includes/db.php';

$resultado = '';
$email_teste = $_GET['email'] ?? '';

if ($email_teste && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $db = getDB();
    
    // Tentar busca normal
    $stmt = $db->prepare("SELECT id, nome, email, telefone, role, ativo, LENGTH(senha_hash) as hash_length FROM usuarios WHERE email = ?");
    $stmt->execute([$email_teste]);
    $usuario1 = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Tentar busca case-insensitive
    $stmt = $db->prepare("SELECT id, nome, email, telefone, role, ativo, LENGTH(senha_hash) as hash_length FROM usuarios WHERE LOWER(email) = LOWER(?)");
    $stmt->execute([$email_teste]);
    $usuario2 = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Buscar todos os emails (primeiros 10)
    $stmt = $db->query("SELECT id, nome, email, role, ativo FROM usuarios LIMIT 10");
    $todos_usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    ob_start();
    echo "<h3>Resultado da Busca para: " . htmlspecialchars($email_teste) . "</h3>";
    echo "<h4>Busca Normal (case-sensitive):</h4>";
    if ($usuario1) {
        echo "<pre>" . print_r($usuario1, true) . "</pre>";
    } else {
        echo "<p style='color: red;'>Usuário NÃO encontrado com busca case-sensitive</p>";
    }
    
    echo "<h4>Busca Case-Insensitive:</h4>";
    if ($usuario2) {
        echo "<pre>" . print_r($usuario2, true) . "</pre>";
    } else {
        echo "<p style='color: red;'>Usuário NÃO encontrado com busca case-insensitive</p>";
    }
    
    echo "<h4>Usuários no Banco (primeiros 10):</h4>";
    echo "<pre>" . print_r($todos_usuarios, true) . "</pre>";
    
    $resultado = ob_get_clean();
}

?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Login | Hotel Mucinga Nzambi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .form-group {
            margin: 20px 0;
        }
        input[type="text"] {
            padding: 10px;
            width: 300px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            padding: 10px 20px;
            background: #005051;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background: #006b6d;
        }
        pre {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
        .alert {
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }
        .alert-warning {
            background: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
        }
    </style>
</head>
<body>
    <h1>🔍 Debug do Sistema de Login</h1>
    
    <div class="alert alert-warning">
        <strong>⚠️ ATENÇÃO:</strong> Esta página é apenas para debug. REMOVER EM PRODUÇÃO!
    </div>
    
    <form method="GET">
        <div class="form-group">
            <label>Email para testar:</label><br>
            <input type="text" name="email" value="<?= htmlspecialchars($email_teste) ?>" placeholder="email@exemplo.com">
        </div>
        <button type="submit">Testar Busca</button>
    </form>
    
    <?php if ($resultado): ?>
        <hr>
        <?= $resultado ?>
    <?php endif; ?>
    
    <hr>
    <h3>Testar Login Direto</h3>
    <form method="POST" action="login.php">
        <div class="form-group">
            <label>Email:</label><br>
            <input type="email" name="email" required>
        </div>
        <div class="form-group">
            <label>Senha:</label><br>
            <input type="password" name="senha" required>
        </div>
        <button type="submit">Fazer Login</button>
    </form>
</body>
</html>

