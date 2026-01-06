<?php
/**
 * Logout
 * Hotel Mucinga Nzambi
 */

define('SYSTEM_ACCESS', true);
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Fazer logout
Auth::logout();

// Redirecionar para página inicial
header('Location: includes/index.php');
exit;

