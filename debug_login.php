<?php
define('SYSTEM_ACCESS', true);
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Testar login diretamente
$email = 'admin@hotelmucinga.ao';
$senha = 'admin123';

echo "<h2>Debug Login</h2>";
echo "Email: " . $email . "<br>";
echo "Senha: " . $senha . "<br><hr>";

$resultado = Auth::login($email, $senha);

echo "<h3>Resultado do Auth::login():</h3>";
echo "<pre>";
print_r($resultado);
echo "</pre>";

if ($resultado['success']) {
    echo "<h3 style='color:green;'>✅ LOGIN BEM-SUCEDIDO!</h3>";
    echo "Role do usuário: " . $resultado['user']['role'] . "<br>";
    echo "Role em maiúsculas: " . strtoupper($resultado['user']['role']) . "<br>";
    
    // Testar a condição do redirecionamento
    $roles_validos = ['ADMIN', 'RECEPCAO', 'FINANCEIRO'];
    $role_upper = strtoupper($resultado['user']['role']);
    
    echo "in_array('$role_upper', " . json_encode($roles_validos) . ") = ";
    echo in_array($role_upper, $roles_validos) ? 'true' : 'false';
} else {
    echo "<h3 style='color:red;'>❌ LOGIN FALHOU</h3>";
    echo "Erro: " . $resultado['message'];
}
?>