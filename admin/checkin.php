<?php
/**
 * Check-in
 * Hotel Mucinga Nzambi
 */

define('ADMIN_ACCESS', true);
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/rbac.php';
require_once __DIR__ . '/../includes/helpers.php';

// 🔒 Proteção do Painel Administrativo
if (!Auth::isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

if (!RBAC::hasPermission('VIEW_DASHBOARD')) {
    header('Location: ../includes/index.php');
    exit;
}


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticação e permissões
if (!Auth::isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}


if (!RBAC::isRecepcao()) {
    die('Acesso negado.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: solicitacoes.php');
    exit;
}

// Validar CSRF
if (!validarCSRFToken($_POST['csrf_token'] ?? '')) {
    die('Token CSRF inválido');
}

$reservaId = intval($_POST['reserva_id'] ?? 0);

if (!$reservaId) {
    $_SESSION['erro'] = 'Reserva não encontrada';
    header('Location: solicitacoes.php');
    exit;
}

$db = getDB();
$usuario = Auth::getUser();

// Buscar reserva atual
$stmt = $db->prepare("SELECT * FROM reservas WHERE id = ?");
$stmt->execute([$reservaId]);
$reservaAntes = $stmt->fetch();

if (!$reservaAntes || $reservaAntes['status'] !== 'CONFIRMADA') {
    $_SESSION['erro'] = 'Reserva não pode ser check-in';
    header('Location: reserva_detalhes.php?id=' . $reservaId);
    exit;
}

// ============================================
// MODIFICAÇÃO: ATUALIZAR STATUS DO QUARTO PARA "OCUPADO"
// Adicionado em 2025-12-30 para sincronização automática
// ============================================
// Quando o check-in é realizado, o quarto deve ficar como OCUPADO
atualizarStatusQuarto($reservaAntes['quarto_id'], 'OCUPADO');
// ============================================

// Atualizar reserva
$stmt = $db->prepare("UPDATE reservas SET status = 'CHECKIN_REALIZADO' WHERE id = ?");
$stmt->execute([$reservaId]);

// Log de auditoria
logAuditoria(
    'reserva',
    $reservaId,
    'CHECKIN',
    ['status' => $reservaAntes['status']],
    ['status' => 'CHECKIN_REALIZADO'],
    $usuario['id'],
    $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
);

// Gerar PDF de check-in (opcional - pode ser gerado manualmente depois)
// Link disponível em reserva_detalhes.php

$_SESSION['sucesso'] = 'Check-in realizado com sucesso!';
header('Location: reserva_detalhes.php?id=' . $reservaId);
exit;