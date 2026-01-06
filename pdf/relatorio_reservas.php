<?php
/**
 * Relatório de Reservas (PDF)
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

$db = getDB();
$usuario = Auth::getUser();

// Filtros
$dataInicio = $_GET['inicio'] ?? date('Y-m-01');
$dataFim = $_GET['fim'] ?? date('Y-m-d');
$statusFiltro = $_GET['status'] ?? '';
$bancoId = $_GET['banco'] ?? '';

// Buscar reservas
$sql = "
    SELECT r.*, tq.nome as tipo_quarto_nome, q.numero as quarto_numero,
           b.nome_banco, u.nome as usuario_nome
    FROM reservas r
    LEFT JOIN tipos_quarto tq ON r.tipo_quarto_id = tq.id
    LEFT JOIN quartos q ON r.quarto_id = q.id
    LEFT JOIN bancos b ON r.banco_escolhido_id = b.id
    LEFT JOIN usuarios u ON r.usuario_id = u.id
    WHERE DATE(r.criado_em) BETWEEN ? AND ?
";

$params = [$dataInicio, $dataFim];

if ($statusFiltro) {
    $sql .= " AND r.status = ?";
    $params[] = $statusFiltro;
}

if ($bancoId) {
    $sql .= " AND r.banco_escolhido_id = ?";
    $params[] = $bancoId;
}

$sql .= " ORDER BY r.criado_em DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$reservas = $stmt->fetchAll();

// Criar PDF customizado
class PDFRelatorio extends TCPDF {
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
        $this->Cell(0, 10, 'Relatório de Reservas', 0, false, 'C');
        $this->Ln(5);
        
        $this->SetFont('helvetica', '', 9);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(0, 5, 'Período: ' . formatarData($GLOBALS['dataInicio']) . ' a ' . formatarData($GLOBALS['dataFim']), 0, false, 'C');
        $this->Ln(5);
        $this->Cell(0, 5, 'Emitido em: ' . date('d/m/Y H:i'), 0, false, 'C');
        
        $this->SetLineWidth(0.5);
        $this->SetDrawColor(0, 80, 81);
        $this->Line(15, 40, 195, 40);
        
        $this->SetY(45);
    }
    
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->SetTextColor(128, 128, 128);
        $this->Cell(0, 10, 'Sistema de Reservas © ' . date('Y') . ' - Hotel Mucinga Nzambi', 0, false, 'C');
    }
}

$pdf = new PDFRelatorio(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->SetCreator('Hotel Mucinga Nzambi');
$pdf->SetAuthor('Hotel Mucinga Nzambi');
$pdf->SetTitle('Relatório de Reservas - ' . formatarData($dataInicio) . ' a ' . formatarData($dataFim));

$pdf->setPrintHeader(true);
$pdf->setPrintFooter(true);

$pdf->SetMargins(15, 45, 15);
$pdf->SetAutoPageBreak(TRUE, 25);

$pdf->AddPage();
$pdf->SetFont('helvetica', '', 9);

// Cabeçalho da tabela
$pdf->SetFillColor(0, 80, 81);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('helvetica', 'B', 9);

$pdf->Cell(25, 8, 'Código', 1, 0, 'L', true);
$pdf->Cell(50, 8, 'Hóspede', 1, 0, 'L', true);
$pdf->Cell(35, 8, 'Quarto', 1, 0, 'L', true);
$pdf->Cell(30, 8, 'Check-in', 1, 0, 'C', true);
$pdf->Cell(25, 8, 'Total', 1, 0, 'R', true);
$pdf->Cell(30, 8, 'Status', 1, 1, 'C', true);

$pdf->SetFillColor(245, 247, 250);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('helvetica', '', 8);

$totalGeral = 0;

foreach ($reservas as $reserva) {
    if ($pdf->GetY() > 260) {
        $pdf->AddPage();
        // Recriar cabeçalho
        $pdf->SetFillColor(0, 80, 81);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(25, 8, 'Código', 1, 0, 'L', true);
        $pdf->Cell(50, 8, 'Hóspede', 1, 0, 'L', true);
        $pdf->Cell(35, 8, 'Quarto', 1, 0, 'L', true);
        $pdf->Cell(30, 8, 'Check-in', 1, 0, 'C', true);
        $pdf->Cell(25, 8, 'Total', 1, 0, 'R', true);
        $pdf->Cell(30, 8, 'Status', 1, 1, 'C', true);
        $pdf->SetFillColor(245, 247, 250);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', '', 8);
    }
    
    $codigo = substr($reserva['codigo'], 0, 12);
    $hospede = substr($reserva['nome_cliente'], 0, 25);
    $quarto = substr($reserva['quarto_numero'] ?? $reserva['tipo_quarto_nome'], 0, 15);
    $checkin = formatarData($reserva['checkin'], 'd/m/Y');
    $total = formatarMoeda($reserva['total_liquido']);
    $status = $reserva['status'];
    
    $totalGeral += $reserva['total_liquido'];
    
    $pdf->Cell(25, 7, $codigo, 1, 0, 'L', true);
    $pdf->Cell(50, 7, $hospede, 1, 0, 'L', true);
    $pdf->Cell(35, 7, $quarto, 1, 0, 'L', true);
    $pdf->Cell(30, 7, $checkin, 1, 0, 'C', true);
    $pdf->Cell(25, 7, $total, 1, 0, 'R', true);
    $pdf->Cell(30, 7, $status, 1, 1, 'C', true);
}

// Total
$pdf->Ln(5);
$pdf->SetFillColor(242, 141, 0);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(165, 10, 'TOTAL GERAL:', 1, 0, 'R', true);
$pdf->Cell(25, 10, formatarMoeda($totalGeral), 1, 1, 'R', true);

// Estatísticas
$pdf->Ln(5);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetTextColor(0, 80, 81);
$pdf->Cell(0, 8, 'Resumo:', 0, 1, 'L');

$pdf->SetFont('helvetica', '', 9);
$pdf->SetTextColor(0, 0, 0);

$totalReservas = count($reservas);
$pdf->Cell(0, 6, 'Total de Reservas: ' . $totalReservas, 0, 1, 'L');
$pdf->Cell(0, 6, 'Receita Total: ' . formatarMoeda($totalGeral), 0, 1, 'L');
$pdf->Cell(0, 6, 'Emitido por: ' . htmlspecialchars($usuario['nome']), 0, 1, 'L');

// Salvar
$fileName = 'relatorio_reservas_' . date('Y-m-d') . '.pdf';
$filePath = STORAGE_PATH . $fileName;

if (!file_exists(STORAGE_PATH)) {
    mkdir(STORAGE_PATH, 0755, true);
}

$pdf->Output($filePath, 'F');
$pdf->Output($fileName, 'I');

