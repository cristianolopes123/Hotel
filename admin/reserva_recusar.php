<?php
/**
 * Recusar Reserva
 * Hotel Mucinga Nzambi
 */

define('ADMIN_ACCESS', true);
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/rbac.php';
require_once __DIR__ . '/../includes/helpers.php';

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
$motivo = sanitizeInput($_POST['motivo'] ?? '');

if (!$reservaId || !$motivo) {
    $_SESSION['erro'] = 'Motivo é obrigatório';
    header('Location: reserva_detalhes.php?id=' . $reservaId);
    exit;
}

$db = getDB();
$usuario = Auth::getUser();

// Buscar reserva atual
$stmt = $db->prepare("SELECT * FROM reservas WHERE id = ?");
$stmt->execute([$reservaId]);
$reservaAntes = $stmt->fetch();

if (!$reservaAntes) {
    $_SESSION['erro'] = 'Reserva não encontrada';
    header('Location: solicitacoes.php');
    exit;
}

// ============================================
// MODIFICAÇÃO: ATUALIZAR STATUS DO QUARTO SE ELE ESTAVA RESERVADO
// Adicionado em 2025-12-30 para sincronização automática
// ============================================
// Se a reserva estava CONFIRMADA, o quarto estava RESERVADO
// Ao recusar/cancelar, devemos liberar o quarto
if ($reservaAntes['status'] === 'CONFIRMADA' && !empty($reservaAntes['quarto_id'])) {
    atualizarStatusQuarto($reservaAntes['quarto_id'], 'DISPONIVEL');
}
// ============================================

// Atualizar reserva
$stmt = $db->prepare("UPDATE reservas SET status = 'RECUSADA', motivo_recusa = ? WHERE id = ?");
$stmt->execute([$motivo, $reservaId]);

// Log de auditoria
logAuditoria(
    'reserva',
    $reservaId,
    'RECUSAR',
    ['status' => $reservaAntes['status']],
    ['status' => 'RECUSADA', 'motivo' => $motivo],
    $usuario['id'],
    $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
);

// Enviar notificação
require_once '../includes/notifications.php';
Notifications::notificarRecusaReserva($reservaId);

$_SESSION['sucesso'] = 'Reserva recusada.';
header('Location: reserva_detalhes.php?id=' . $reservaId);
exit;