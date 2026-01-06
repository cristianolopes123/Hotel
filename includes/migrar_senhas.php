<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

$db = getDB();

$stmt = $db->query("SELECT id, senha_hash, email FROM usuarios");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($usuarios as $u) {
    $senhaAtual = (string)$u['senha_hash'];

    // se NÃO começa com $2y$ (bcrypt), consideramos texto e convertemos
    if (strpos($senhaAtual, '$2y$') !== 0) {
        $novoHash = password_hash($senhaAtual, PASSWORD_DEFAULT);
        $up = $db->prepare("UPDATE usuarios SET senha_hash = ?, atualizado_em = NOW() WHERE id = ?");
        $up->execute([$novoHash, $u['id']]);
        echo "Convertido: " . htmlspecialchars($u['email']) . "<br>";
    }
}

echo "<hr>Concluído. Apague este arquivo.";
