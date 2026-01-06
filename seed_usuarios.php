<?php
define('SYSTEM_ACCESS', true);
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

$db = getDB();

/**
 * Defina aqui os usuários que quer criar
 * Senhas em texto (serão convertidas em hash automaticamente)
 */
$usuarios = [
    ['Administrador', 'admin@hotelmucinga.ao', '+244 923 456 789', 'admin123', 'ADMIN'],
    ['Recepção', 'recepcao@hotelmucinga.ao', '+244 923 456 790', 'recepc123', 'RECEPCAO'],
    ['Financeiro', 'financeiro@hotelmucinga.ao', '+244 923 456 791', 'fin123', 'FINANCEIRO'],
    ['João Silva', 'joao@email.com', '+244 923 111 111', 'joao123', 'HOSPEDE'],
];

$stmt = $db->prepare("
    INSERT INTO usuarios (nome, email, telefone, senha_hash, role, ativo)
    VALUES (?, ?, ?, ?, ?, 1)
");

foreach ($usuarios as $u) {
    [$nome, $email, $telefone, $senha, $role] = $u;

    $hash = password_hash($senha, PASSWORD_DEFAULT);
    $stmt->execute([$nome, $email, $telefone, $hash, $role]);
}

echo "✅ Usuários criados com sucesso. Agora apague o arquivo seed_usuarios.php por segurança.";
