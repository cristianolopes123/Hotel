<?php
/**
 * Confirmar Reserva
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
$quartoId = intval($_POST['quarto_id'] ?? 0);

if (!$reservaId || !$quartoId) {
    $_SESSION['erro'] = 'Dados inválidos';
    header('Location: solicitacoes.php');
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

// Verificar se o quarto está disponível
$disponivel = verificarDisponibilidade(
    $reservaAntes['tipo_quarto_id'],
    $reservaAntes['checkin'],
    $reservaAntes['checkout'],
    $quartoId
);

if ($disponivel !== $quartoId) {
    $_SESSION['erro'] = 'Quarto não disponível para o período selecionado';
    header('Location: reserva_detalhes.php?id=' . $reservaId);
    exit;
}

// ============================================
// MODIFICAÇÃO: ATUALIZAR STATUS DO QUARTO PARA "RESERVADO"
// Adicionado em 2025-12-30 para sincronização automática
// ============================================
// Quando uma reserva é confirmada, o quarto deve ficar como RESERVADO
atualizarStatusQuarto($quartoId, 'RESERVADO');
// ============================================

// Atualizar reserva
$stmt = $db->prepare("UPDATE reservas SET status = 'CONFIRMADA', quarto_id = ? WHERE id = ?");
$stmt->execute([$quartoId, $reservaId]);

// Log de auditoria
logAuditoria(
    'reserva',
    $reservaId,
    'CONFIRMAR',
    ['status' => $reservaAntes['status']],
    ['status' => 'CONFIRMADA', 'quarto_id' => $quartoId],
    $usuario['id'],
    $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
);

// Enviar notificação
require_once '../includes/notifications.php';
Notifications::notificarConfirmacaoReserva($reservaId);

$_SESSION['sucesso'] = 'Reserva confirmada com sucesso!';
header('Location: reserva_detalhes.php?id=' . $reservaId);
exit;