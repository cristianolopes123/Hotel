<?php
/**
 * Comprovante de Reserva (PDF)
 * Hotel Mucinga Nzambi - TCPDF
 */

define('SYSTEM_ACCESS', true);
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';
require_once '../tcpdf/tcpdf.php';

// Buscar código da reserva
$codigo = $_GET['codigo'] ?? '';
if (!$codigo) {
    die('Código de reserva não fornecido');
}

$db = getDB();

// Buscar reserva
$stmt = $db->prepare("
    SELECT r.*, tq.nome as tipo_quarto_nome, q.numero as quarto_numero,
           b.nome_banco, u.nome as usuario_nome
    FROM reservas r
    LEFT JOIN tipos_quarto tq ON r.tipo_quarto_id = tq.id
    LEFT JOIN quartos q ON r.quarto_id = q.id
    LEFT JOIN bancos b ON r.banco_escolhido_id = b.id
    LEFT JOIN usuarios u ON r.usuario_id = u.id
    WHERE r.codigo = ?
");
$stmt->execute([$codigo]);
$reserva = $stmt->fetch();

if (!$reserva) {
    die('Reserva não encontrada');
}

// Buscar serviços
$stmt = $db->prepare("
    SELECT s.nome, rs.quantidade, rs.valor_unit, rs.subtotal
    FROM reservas_servicos rs
    INNER JOIN servicos s ON rs.servico_id = s.id
    WHERE rs.reserva_id = ?
");
$stmt->execute([$reserva['id']]);
$servicos = $stmt->fetchAll();

// Criar PDF customizado
class PDFReserva extends TCPDF {
    public function Header() {
        // Logo (se existir)
        if (file_exists('../imagens/logo.png')) {
            $this->Image('../imagens/logo.png', 15, 10, 25, 0, 'PNG');
        }
        
        // Cabeçalho
        $this->SetFont('helvetica', 'B', 18);
        $this->SetTextColor(0, 80, 81); // #005051
        $this->SetY(10);
        $this->Cell(0, 10, 'Hotel Mucinga Nzambi', 0, false, 'C');
        $this->Ln(5);
        
        $this->SetFont('helvetica', '', 12);
        $this->SetTextColor(242, 141, 0); // #F28D00
        $this->Cell(0, 10, 'Comprovante de Reserva', 0, false, 'C');
        $this->Ln(5);
        
        $this->SetFont('helvetica', '', 9);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(0, 5, SYSTEM_ADDRESS, 0, false, 'C');
        $this->Ln(5);
        $this->Cell(0, 5, 'Tel: ' . SYSTEM_PHONE . ' | Email: ' . SYSTEM_EMAIL, 0, false, 'C');
        
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

// Criar instância do PDF
$pdf = new PDFReserva(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Configurações do documento
$pdf->SetCreator('Hotel Mucinga Nzambi');
$pdf->SetAuthor('Hotel Mucinga Nzambi');
$pdf->SetTitle('Comprovante de Reserva - ' . $codigo);
$pdf->SetSubject('Comprovante de Reserva');

// Remover header e footer padrão
$pdf->setPrintHeader(true);
$pdf->setPrintFooter(true);

// Margens
$pdf->SetMargins(15, 40, 15);
$pdf->SetAutoPageBreak(TRUE, 25);

// Adicionar página
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 11);

// Título
$pdf->SetFont('helvetica', 'B', 16);
$pdf->SetTextColor(0, 80, 81);
$pdf->Cell(0, 10, 'COMPROVANTE DE RESERVA', 0, 1, 'C');
$pdf->Ln(5);

// Informações da Reserva
$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetTextColor(242, 141, 0);
$pdf->Cell(0, 8, 'Código da Reserva: ' . $codigo, 0, 1, 'L');
$pdf->Ln(2);

$pdf->SetFont('helvetica', '', 10);
$pdf->SetTextColor(0, 0, 0);

// Tabela de informações
$pdf->SetFillColor(245, 247, 250);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('helvetica', 'B', 10);

// Dados do Hóspede
$pdf->SetFillColor(0, 80, 81);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(0, 8, 'DADOS DO HÓSPEDE', 1, 1, 'L', true);
$pdf->SetFillColor(245, 247, 250);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('helvetica', '', 10);

$dados_hospede = [
    'Nome' => $reserva['nome_cliente'],
    'Email' => $reserva['email'],
    'Telefone' => $reserva['telefone'],
    'Documento' => $reserva['documento'] ?: 'Não informado',
];

foreach ($dados_hospede as $label => $valor) {
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(50, 7, $label . ':', 1, 0, 'L', true);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 7, $valor, 1, 1, 'L', true);
}

$pdf->Ln(3);

// Dados da Reserva
$pdf->SetFillColor(0, 80, 81);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(0, 8, 'DADOS DA RESERVA', 1, 1, 'L', true);
$pdf->SetFillColor(245, 247, 250);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('helvetica', '', 10);

$dados_reserva = [
    'Tipo de Quarto' => $reserva['tipo_quarto_nome'],
    'Quarto' => $reserva['quarto_numero'] ?: 'A definir',
    'Check-in' => formatarData($reserva['checkin'], 'd/m/Y'),
    'Check-out' => formatarData($reserva['checkout'], 'd/m/Y'),
    'Noites' => calcularNoites($reserva['checkin'], $reserva['checkout']) . ' noite(s)',
    'Adultos' => $reserva['adultos'],
    'Crianças' => $reserva['criancas'],
    'Status' => $reserva['status'],
];

foreach ($dados_reserva as $label => $valor) {
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(50, 7, $label . ':', 1, 0, 'L', true);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 7, $valor, 1, 1, 'L', true);
}

$pdf->Ln(3);

// Serviços Adicionais
if (!empty($servicos)) {
    $pdf->SetFillColor(0, 80, 81);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell(0, 8, 'SERVIÇOS ADICIONAIS', 1, 1, 'L', true);
    $pdf->SetFillColor(245, 247, 250);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('helvetica', '', 10);
    
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(100, 7, 'Serviço', 1, 0, 'L', true);
    $pdf->Cell(30, 7, 'Qtd', 1, 0, 'C', true);
    $pdf->Cell(0, 7, 'Subtotal', 1, 1, 'R', true);
    
    foreach ($servicos as $servico) {
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(100, 7, $servico['nome'], 1, 0, 'L');
        $pdf->Cell(30, 7, $servico['quantidade'], 1, 0, 'C');
        $pdf->Cell(0, 7, formatarMoeda($servico['subtotal']), 1, 1, 'R');
    }
    
    $pdf->Ln(3);
}

// Resumo Financeiro
$pdf->SetFillColor(0, 80, 81);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(0, 8, 'RESUMO FINANCEIRO', 1, 1, 'L', true);
$pdf->SetFillColor(245, 247, 250);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('helvetica', '', 10);

$resumo = [
    'Subtotal' => formatarMoeda($reserva['total_bruto']),
    'Desconto' => formatarMoeda($reserva['desconto']),
    'Taxas' => formatarMoeda($reserva['taxas']),
];

foreach ($resumo as $label => $valor) {
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(130, 7, $label . ':', 1, 0, 'R', true);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 7, $valor, 1, 1, 'R', true);
}

$pdf->SetFillColor(242, 141, 0);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(130, 10, 'TOTAL:', 1, 0, 'R', true);
$pdf->Cell(0, 10, formatarMoeda($reserva['total_liquido']), 1, 1, 'R', true);

$pdf->Ln(5);

// Informações de Pagamento
if ($reserva['nome_banco']) {
    $pdf->SetFillColor(0, 80, 81);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell(0, 8, 'FORMA DE PAGAMENTO', 1, 1, 'L', true);
    $pdf->SetFillColor(245, 247, 250);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 7, 'Transferência Bancária: ' . $reserva['nome_banco'], 1, 1, 'L', true);
}

$pdf->Ln(5);

// Observações
if ($reserva['observacoes']) {
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(0, 7, 'Observações:', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 10);
    $pdf->MultiCell(0, 6, $reserva['observacoes'], 0, 'L');
    $pdf->Ln(3);
}

// Instruções
$pdf->SetFont('helvetica', 'I', 9);
$pdf->SetTextColor(128, 128, 128);
$pdf->MultiCell(0, 6, 'Este é um comprovante de reserva. Apresente este documento no check-in. Em caso de dúvidas, entre em contato conosco.', 0, 'L');

// QR Code com código da reserva
$pdf->Ln(5);
$style = array(
    'border' => true,
    'vpadding' => 'auto',
    'hpadding' => 'auto',
    'fgcolor' => array(0, 80, 81),
    'bgcolor' => false,
    'module_width' => 1,
    'module_height' => 1
);
$pdf->write2DBarcode($codigo, 'QRCODE,L', 150, $pdf->GetY() - 30, 40, 40, $style, 'N');

// Salvar PDF
$fileName = 'comprovante_' . $codigo . '.pdf';
$filePath = STORAGE_PATH . $fileName;

if (!file_exists(STORAGE_PATH)) {
    mkdir(STORAGE_PATH, 0755, true);
}

// Salvar arquivo
$pdf->Output($filePath, 'F');

// Exibir PDF
$pdf->Output($fileName, 'I');

