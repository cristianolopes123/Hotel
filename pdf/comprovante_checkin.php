<?php
/**
 * Comprovante de Check-in (PDF)
 * Hotel Mucinga Nzambi - TCPDF
 */

define('SYSTEM_ACCESS', true);
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/rbac.php';
require_once '../includes/helpers.php';
require_once '../tcpdf/tcpdf.php';

// Verificar permissões
if (!Auth::isLoggedIn() || !RBAC::isRecepcao()) {
    die('Acesso negado');
}

// Buscar ID da reserva
$reservaId = intval($_GET['id'] ?? 0);
if (!$reservaId) {
    die('ID da reserva não fornecido');
}

$db = getDB();

// Buscar reserva
$stmt = $db->prepare("
    SELECT r.*, tq.nome as tipo_quarto_nome, q.numero as quarto_numero,
           u.nome as usuario_nome
    FROM reservas r
    LEFT JOIN tipos_quarto tq ON r.tipo_quarto_id = tq.id
    LEFT JOIN quartos q ON r.quarto_id = q.id
    LEFT JOIN usuarios u ON r.usuario_id = u.id
    WHERE r.id = ?
");
$stmt->execute([$reservaId]);
$reserva = $stmt->fetch();

if (!$reserva || $reserva['status'] !== 'CHECKIN_REALIZADO') {
    die('Reserva não encontrada ou check-in não realizado');
}

// Criar PDF customizado
class PDFCheckin extends TCPDF {
    public function Header() {
        if (file_exists('../imagens/logo.png')) {
            $this->Image('../imagens/logo.png', 15, 10, 25, 0, 'PNG');
        }
        
        $this->SetFont('helvetica', 'B', 18);
        $this->SetTextColor(0, 80, 81);
        $this->SetY(10);
        $this->Cell(0, 10, 'Hotel Mucinga Nzambi', 0, false, 'C');
        $this->Ln(5);
        
        $this->SetFont('helvetica', '', 12);
        $this->SetTextColor(242, 141, 0);
        $this->Cell(0, 10, 'Comprovante de Check-in', 0, false, 'C');
        $this->Ln(5);
        
        $this->SetFont('helvetica', '', 9);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(0, 5, SYSTEM_ADDRESS, 0, false, 'C');
        $this->Ln(5);
        
        $this->SetLineWidth(0.5);
        $this->SetDrawColor(0, 80, 81);
        $this->Line(15, 35, 195, 35);
        
        $this->SetY(40);
    }
    
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->SetTextColor(128, 128, 128);
        $this->Cell(0, 10, 'Sistema de Reservas © ' . date('Y') . ' - Hotel Mucinga Nzambi', 0, false, 'C');
    }
}

$pdf = new PDFCheckin(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->SetCreator('Hotel Mucinga Nzambi');
$pdf->SetAuthor('Hotel Mucinga Nzambi');
$pdf->SetTitle('Comprovante de Check-in - ' . $reserva['codigo']);

$pdf->setPrintHeader(true);
$pdf->setPrintFooter(true);

$pdf->SetMargins(15, 40, 15);
$pdf->SetAutoPageBreak(TRUE, 25);

$pdf->AddPage();
$pdf->SetFont('helvetica', '', 11);

// Título
$pdf->SetFont('helvetica', 'B', 16);
$pdf->SetTextColor(0, 80, 81);
$pdf->Cell(0, 10, 'COMPROVANTE DE CHECK-IN', 0, 1, 'C');
$pdf->Ln(5);

// Informações
$pdf->SetFont('helvetica', '', 10);
$pdf->SetFillColor(245, 247, 250);

$dados = [
    'Código da Reserva' => $reserva['codigo'],
    'Hóspede' => $reserva['nome_cliente'],
    'Quarto' => $reserva['quarto_numero'] ? 'Quarto ' . $reserva['quarto_numero'] : 'A definir',
    'Tipo de Quarto' => $reserva['tipo_quarto_nome'],
    'Check-in' => formatarData($reserva['checkin'], 'd/m/Y'),
    'Check-out' => formatarData($reserva['checkout'], 'd/m/Y'),
    'Data/Hora do Check-in' => date('d/m/Y H:i'),
    'Total Pago' => formatarMoeda($reserva['total_liquido']),
];

foreach ($dados as $label => $valor) {
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(80, 7, $label . ':', 1, 0, 'L', true);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 7, $valor, 1, 1, 'L', true);
}

$pdf->Ln(10);

// QR Code
$style = array(
    'border' => true,
    'vpadding' => 'auto',
    'hpadding' => 'auto',
    'fgcolor' => array(0, 80, 81),
    'bgcolor' => false,
    'module_width' => 1,
    'module_height' => 1
);
$pdf->write2DBarcode($reserva['codigo'], 'QRCODE,L', 150, $pdf->GetY(), 40, 40, $style, 'N');

// Instruções
$pdf->SetFont('helvetica', 'I', 9);
$pdf->SetTextColor(128, 128, 128);
$pdf->SetY(150);
$pdf->MultiCell(0, 6, 'Apresente este comprovante ao sair e retornar ao hotel.', 0, 'L');

// Salvar
$fileName = 'checkin_' . $reserva['codigo'] . '.pdf';
$filePath = STORAGE_PATH . $fileName;

if (!file_exists(STORAGE_PATH)) {
    mkdir(STORAGE_PATH, 0755, true);
}

$pdf->Output($filePath, 'F');
$pdf->Output($fileName, 'I');

