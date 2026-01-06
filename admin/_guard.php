<?php
/**
 * Arquivo de proteção para páginas administrativas
 * Deve ser incluído no topo de todas as páginas do painel administrativo
 */

define('ADMIN_ACCESS', true);

// Carrega as dependências necessárias
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/rbac.php';
require_once __DIR__ . '/../includes/helpers.php';

// 1. Verifica se o usuário está logado
if (!Auth::isLoggedIn()) {
    $_SESSION['error_message'] = 'Por favor, faça login para acessar esta área.';
    header('Location: ../login.php');
    exit;
}

// 2. Obtém os dados do usuário
$user = Auth::getUser();

// 3. Verifica se o usuário tem uma função válida para acessar o painel
$allowedRoles = ['ADMIN', 'RECEPCAO', 'FINANCEIRO'];
if (!in_array($user['role'], $allowedRoles, true)) {
    $_SESSION['error_message'] = 'Você não tem permissão para acessar esta área.';
    header('Location: ../includes/index.php');
    exit;
}

// 4. Verifica se o usuário tem permissão para acessar o painel administrativo
if (!RBAC::hasPermission('ACESSAR_PAINEL')) {
    $_SESSION['error_message'] = 'Você não tem permissão para acessar o painel administrativo.';
    header('Location: ../includes/index.php');
    exit;
}

// 5. Se chegou até aqui, o usuário está autenticado e autorizado
// As permissões específicas de cada página devem ser verificadas individualmente
