<?php
/**
 * Página de Reserva (Wizard)
 * Hotel Mucinga Nzambi
 */

define('SYSTEM_ACCESS', true);
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/rbac.php';
require_once 'includes/helpers.php';
require_once 'includes/upload.php';

$usuario = Auth::getUser();
$csrf_token = gerarCSRFToken();

// Dados iniciais do formulário
$tipoQuartoId = intval($_GET['tipo_quarto_id'] ?? $_POST['tipo_quarto_id'] ?? 0);
$checkin = $_GET['checkin'] ?? $_POST['checkin'] ?? '';
$checkout = $_GET['checkout'] ?? $_POST['checkout'] ?? '';
$adultos = intval($_GET['adultos'] ?? $_POST['adultos'] ?? 1);
$criancas = intval($_GET['criancas'] ?? $_POST['criancas'] ?? 0);

$db = getDB();

// Buscar tipo de quarto
if ($tipoQuartoId) {
    $stmt = $db->prepare("SELECT * FROM tipos_quarto WHERE id = ? AND ativo = 1");
    $stmt->execute([$tipoQuartoId]);
    $tipoQuarto = $stmt->fetch();
    
    if (!$tipoQuarto) {
        header('Location: disponibilidade.php');
        exit;
    }
    
    // Calcular total
    $totais = calcularTotalReserva($tipoQuartoId, $checkin, $checkout);
    $noites = calcularNoites($checkin, $checkout);
} else {
    header('Location: disponibilidade.php');
    exit;
}

// Buscar bancos disponíveis
$stmt = $db->query("SELECT * FROM bancos WHERE ativo = 1 ORDER BY nome_banco ASC");
$bancos = $stmt->fetchAll();

// Buscar serviços adicionais
$stmt = $db->query("SELECT * FROM servicos WHERE ativo = 1 ORDER BY nome ASC");
$servicos = $stmt->fetchAll();

// Processar reserva
$reservaCriada = false;
$codigoReserva = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['finalizar_reserva'])) {
    if (!validarCSRFToken($_POST['csrf_token'] ?? '')) {
        die('Token CSRF inválido');
    }
    
    // Validar dados
    $nome = sanitizeInput($_POST['nome'] ?? '');
    $documento = sanitizeInput($_POST['documento'] ?? '');
    $telefone = sanitizeInput($_POST['telefone'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $observacoes = sanitizeInput($_POST['observacoes'] ?? '');
    $horarioChegada = sanitizeInput($_POST['horario_chegada'] ?? '');
    $bancoEscolhidoId = intval($_POST['banco_escolhido_id'] ?? 0);
    $servicosIds = $_POST['servicos'] ?? [];
    
    if ($nome && $telefone && $email && $checkin && $checkout) {
        // Calcular total com serviços
        $totais = calcularTotalReserva($tipoQuartoId, $checkin, $checkout, $servicosIds);
        
        // Gerar código
        $codigo = gerarCodigoReserva();
        
        // Criar reserva
        $stmt = $db->prepare("
            INSERT INTO reservas (
                codigo, usuario_id, nome_cliente, documento, telefone, email,
                tipo_quarto_id, checkin, checkout, adultos, criancas,
                banco_escolhido_id, status, total_bruto, total_liquido, observacoes
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'PENDENTE_COMPROVANTE', ?, ?, ?)
        ");
        
        $stmt->execute([
            $codigo,
            $usuario ? $usuario['id'] : null,
            $nome,
            $documento,
            $telefone,
            $email,
            $tipoQuartoId,
            $checkin,
            $checkout,
            $adultos,
            $criancas,
            $bancoEscolhidoId ?: null,
            $totais['total_bruto'],
            $totais['total_liquido'],
            $observacoes
        ]);
        
        $reservaId = $db->lastInsertId();
        
        // Adicionar serviços
        if (!empty($servicosIds)) {
            $servicosStmt = $db->prepare("SELECT id, preco, unidade FROM servicos WHERE id IN (" . implode(',', array_fill(0, count($servicosIds), '?')) . ")");
            $servicosStmt->execute($servicosIds);
            $servicosSelecionados = $servicosStmt->fetchAll();
            
            $insertServico = $db->prepare("
                INSERT INTO reservas_servicos (reserva_id, servico_id, quantidade, valor_unit, subtotal)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            foreach ($servicosSelecionados as $servico) {
                $quantidade = 1;
                $valorUnit = $servico['preco'];
                
                if ($servico['unidade'] === 'POR_NOITE') {
                    $quantidade = $noites;
                }
                
                $subtotal = $valorUnit * $quantidade;
                
                $insertServico->execute([
                    $reservaId,
                    $servico['id'],
                    $quantidade,
                    $valorUnit,
                    $subtotal
                ]);
            }
        }
        
        // Processar upload de comprovante se houver
        if (!empty($_FILES['comprovante']['name'][0])) {
            foreach ($_FILES['comprovante']['name'] as $key => $name) {
                if (!empty($name)) {
                    $file = [
                        'name' => $_FILES['comprovante']['name'][$key],
                        'type' => $_FILES['comprovante']['type'][$key],
                        'tmp_name' => $_FILES['comprovante']['tmp_name'][$key],
                        'error' => $_FILES['comprovante']['error'][$key],
                        'size' => $_FILES['comprovante']['size'][$key]
                    ];
                    
                    UploadHandler::uploadComprovante($file, $reservaId);
                    
                    // Atualizar status para EM_ANALISE
                    $db->prepare("UPDATE reservas SET status = 'EM_ANALISE' WHERE id = ?")->execute([$reservaId]);
                }
            }
        }
        
        // Log de auditoria
        logAuditoria('reserva', $reservaId, 'CRIAR', null, ['codigo' => $codigo, 'status' => 'PENDENTE_COMPROVANTE']);
        
        // Enviar notificação
        require_once 'includes/notifications.php';
        Notifications::notificarCriacaoReserva($reservaId);
        
        $reservaCriada = true;
        $codigoReserva = $codigo;
    }
}

?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserva | Hotel Mucinga Nzambi</title>
    
    <!-- Google Fonts Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Bootstrap Local -->
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    
    <?php include 'includes/navbar-styles.php'; ?>
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #FDF7E6;
            padding-top: 100px;
        }
        
        .wizard-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        
        .wizard-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            position: relative;
        }
        
        .wizard-steps::before {
            content: '';
            position: absolute;
            top: 25px;
            left: 0;
            right: 0;
            height: 3px;
            background: #e0e0e0;
            z-index: 0;
        }
        
        .wizard-step {
            flex: 1;
            text-align: center;
            position: relative;
            z-index: 1;
        }
        
        .step-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #e0e0e0;
            color: #666;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            margin: 0 auto 10px;
            transition: all 0.3s ease;
        }
        
        .wizard-step.active .step-circle {
            background: linear-gradient(135deg, #005051 0%, #006b6d 100%);
            color: #fff;
            box-shadow: 0 4px 15px rgba(0, 80, 81, 0.3);
        }
        
        .wizard-step.completed .step-circle {
            background: linear-gradient(135deg, #F28D00 0%, #FFC107 100%);
            color: #000;
        }
        
        .step-label {
            font-size: 0.85rem;
            color: #666;
            font-weight: 500;
        }
        
        .wizard-step.active .step-label {
            color: #005051;
            font-weight: 700;
        }
        
        .wizard-content {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 40px;
            margin-bottom: 30px;
        }
        
        .wizard-content h3 {
            color: #005051;
            font-weight: 700;
            margin-bottom: 25px;
        }
        
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        
        .form-control, .form-select {
            border-radius: 8px;
            border: 2px solid #e0e0e0;
            padding: 10px 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #005051;
            box-shadow: 0 0 0 0.2rem rgba(0, 80, 81, 0.25);
        }
        
        .banco-card {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .banco-card:hover {
            border-color: #005051;
            box-shadow: 0 4px 15px rgba(0, 80, 81, 0.1);
        }
        
        .banco-card.selected {
            border-color: #005051;
            background: rgba(0, 80, 81, 0.05);
        }
        
        .banco-info {
            margin-top: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            display: none;
        }
        
        .banco-card.selected .banco-info {
            display: block;
        }
        
        .btn-copiar {
            background: #005051;
            color: #fff;
            border: none;
            padding: 8px 15px;
            border-radius: 6px;
            font-size: 0.85rem;
            margin-left: 10px;
            transition: all 0.3s ease;
        }
        
        .btn-copiar:hover {
            background: #006b6d;
            transform: translateY(-2px);
        }
        
        .upload-zone {
            border: 3px dashed #005051;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            background: #f8f9fa;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .upload-zone:hover {
            background: rgba(0, 80, 81, 0.05);
            border-color: #006b6d;
        }
        
        .upload-zone.dragover {
            background: rgba(0, 80, 81, 0.1);
            border-color: #005051;
        }
        
        .file-preview {
            margin-top: 20px;
            display: none;
        }
        
        .file-item {
            background: #fff;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .resumo-box {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 25px;
        }
        
        .resumo-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .resumo-item:last-child {
            border-bottom: none;
            font-weight: 700;
            font-size: 1.2rem;
            color: #005051;
            margin-top: 10px;
            padding-top: 20px;
            border-top: 2px solid #005051;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #005051 0%, #006b6d 100%);
            border: none;
            padding: 12px 40px;
            font-weight: 600;
            border-radius: 30px;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #006b6d 0%, #005051 100%);
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #6c757d;
            border: none;
            padding: 12px 30px;
            font-weight: 600;
            border-radius: 30px;
        }
        
        .success-message {
            background: linear-gradient(135deg, rgba(0, 80, 81, 0.1) 0%, rgba(242, 141, 0, 0.1) 100%);
            border: 2px solid #005051;
            border-radius: 15px;
            padding: 40px;
            text-align: center;
        }
        
        .success-icon {
            font-size: 4rem;
            color: #005051;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="wizard-container">
        <?php if ($reservaCriada): ?>
            <!-- Mensagem de Sucesso -->
            <div class="success-message">
                <div class="success-icon">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <h2 style="color: #005051; font-weight: 700; margin-bottom: 15px;">
                    Reserva Criada com Sucesso!
                </h2>
                <p style="font-size: 1.2rem; color: #666; margin-bottom: 30px;">
                    Seu código de referência é:
                </p>
                <div style="background: #fff; border: 3px solid #005051; border-radius: 10px; padding: 20px; display: inline-block; margin-bottom: 30px;">
                    <strong style="font-size: 1.5rem; color: #005051; letter-spacing: 2px;">
                        <?= htmlspecialchars($codigoReserva) ?>
                    </strong>
                </div>
                <p style="color: #666; margin-bottom: 30px;">
                    Enviamos um email com os detalhes da sua reserva.<br>
                    Se ainda não enviou o comprovante de pagamento, pode enviá-lo pelo link abaixo.
                </p>
                <div class="d-flex gap-3 justify-content-center">
                    <?php if ($usuario): ?>
                        <a href="minhas-reservas.php" class="btn btn-primary">
                            <i class="bi bi-list-check"></i> Minhas Reservas
                        </a>
                    <?php endif; ?>
                    <a href="disponibilidade.php" class="btn btn-secondary">
                        <i class="bi bi-house"></i> Voltar ao Início
                    </a>
                </div>
            </div>
        <?php else: ?>
            <!-- Wizard Steps -->
            <div class="wizard-steps">
                <div class="wizard-step active" id="step-1">
                    <div class="step-circle">1</div>
                    <div class="step-label">Dados do Hóspede</div>
                </div>
                <div class="wizard-step" id="step-2">
                    <div class="step-circle">2</div>
                    <div class="step-label">Pagamento</div>
                </div>
                <div class="wizard-step" id="step-3">
                    <div class="step-circle">3</div>
                    <div class="step-label">Comprovante</div>
                </div>
                <div class="wizard-step" id="step-4">
                    <div class="step-circle">4</div>
                    <div class="step-label">Revisão</div>
                </div>
            </div>
            
            <form method="POST" action="" enctype="multipart/form-data" id="reservaForm">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="tipo_quarto_id" value="<?= $tipoQuartoId ?>">
                <input type="hidden" name="checkin" value="<?= $checkin ?>">
                <input type="hidden" name="checkout" value="<?= $checkout ?>">
                <input type="hidden" name="adultos" value="<?= $adultos ?>">
                <input type="hidden" name="criancas" value="<?= $criancas ?>">
                
                <!-- Etapa 1: Dados do Hóspede -->
                <div class="wizard-content" id="content-1">
                    <h3><i class="bi bi-person"></i> Dados do Hóspede</h3>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nome Completo <span class="text-danger">*</span></label>
                            <input type="text" name="nome" class="form-control" 
                                   value="<?= $usuario ? htmlspecialchars($usuario['nome']) : '' ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Documento</label>
                            <input type="text" name="documento" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">WhatsApp/Telefone <span class="text-danger">*</span></label>
                            <input type="tel" name="telefone" class="form-control" 
                                   value="<?= $usuario ? htmlspecialchars($usuario['telefone'] ?? '') : '' ?>" 
                                   placeholder="+244 923 456 789" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" 
                                   value="<?= $usuario ? htmlspecialchars($usuario['email']) : '' ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Horário Estimado de Chegada</label>
                            <input type="time" name="horario_chegada" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Observações</label>
                            <textarea name="observacoes" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-secondary" onclick="window.location.href='disponibilidade.php'">
                            <i class="bi bi-arrow-left"></i> Cancelar
                        </button>
                        <button type="button" class="btn btn-primary" onclick="nextStep(2)">
                            Próximo <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Etapa 2: Pagamento -->
                <div class="wizard-content" id="content-2" style="display: none;">
                    <h3><i class="bi bi-credit-card"></i> Forma de Pagamento</h3>
                    <p class="text-muted mb-4">Escolha o banco para transferência:</p>
                    
                    <?php foreach ($bancos as $banco): ?>
                        <div class="banco-card" onclick="selectBanco(<?= $banco['id'] ?>)">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="banco_escolhido_id" 
                                       value="<?= $banco['id'] ?>" id="banco<?= $banco['id'] ?>" required>
                                <label class="form-check-label fw-bold" for="banco<?= $banco['id'] ?>">
                                    <?= htmlspecialchars($banco['nome_banco']) ?>
                                </label>
                            </div>
                            <div class="banco-info">
                                <div class="mt-3">
                                    <p class="mb-2"><strong>Titular:</strong> <?= htmlspecialchars($banco['titular']) ?></p>
                                    <?php if ($banco['iban']): ?>
                                        <p class="mb-2">
                                            <strong>IBAN:</strong> 
                                            <span id="iban<?= $banco['id'] ?>"><?= htmlspecialchars($banco['iban']) ?></span>
                                            <button type="button" class="btn-copiar" onclick="copiarIBAN('iban<?= $banco['id'] ?>')">
                                                <i class="bi bi-clipboard"></i> Copiar
                                            </button>
                                        </p>
                                    <?php endif; ?>
                                    <?php if ($banco['nif']): ?>
                                        <p class="mb-2"><strong>NIF:</strong> <?= htmlspecialchars($banco['nif']) ?></p>
                                    <?php endif; ?>
                                    <?php if ($banco['observacoes']): ?>
                                        <p class="mb-0"><strong>Instruções:</strong> <?= htmlspecialchars($banco['observacoes']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="resumo-box mt-4">
                        <h5 class="mb-3" style="color: #005051;">Resumo do Pagamento</h5>
                        <div class="resumo-item">
                            <span>Quarto:</span>
                            <span><?= htmlspecialchars($tipoQuarto['nome']) ?></span>
                        </div>
                        <div class="resumo-item">
                            <span>Período:</span>
                            <span><?= formatarData($checkin) ?> - <?= formatarData($checkout) ?></span>
                        </div>
                        <div class="resumo-item">
                            <span>Noites:</span>
                            <span><?= $noites ?> noite(s)</span>
                        </div>
                        <div class="resumo-item">
                            <span>Total:</span>
                            <span><?= formatarMoeda($totais['total_liquido']) ?></span>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-secondary" onclick="prevStep(1)">
                            <i class="bi bi-arrow-left"></i> Anterior
                        </button>
                        <button type="button" class="btn btn-primary" onclick="nextStep(3)">
                            Próximo <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Etapa 3: Comprovante -->
                <div class="wizard-content" id="content-3" style="display: none;">
                    <h3><i class="bi bi-upload"></i> Comprovante de Pagamento</h3>
                    <p class="text-muted mb-4">Envie o comprovante de transferência bancária (opcional - pode enviar depois):</p>
                    
                    <div class="upload-zone" id="uploadZone">
                        <i class="bi bi-cloud-upload" style="font-size: 3rem; color: #005051; margin-bottom: 15px;"></i>
                        <h5 style="color: #005051; margin-bottom: 10px;">Arraste e solte o arquivo aqui</h5>
                        <p class="text-muted mb-3">ou</p>
                        <input type="file" name="comprovante[]" id="fileInput" 
                               accept=".jpg,.jpeg,.png,.pdf" multiple 
                               class="form-control" style="display: none;">
                        <button type="button" class="btn btn-primary" onclick="document.getElementById('fileInput').click()">
                            <i class="bi bi-folder"></i> Escolher Arquivos
                        </button>
                        <p class="text-muted mt-3 small">
                            Formatos aceitos: JPG, PNG, PDF (máx. 5MB cada, até 3 arquivos)
                        </p>
                    </div>
                    
                    <div class="file-preview" id="filePreview"></div>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-secondary" onclick="prevStep(2)">
                            <i class="bi bi-arrow-left"></i> Anterior
                        </button>
                        <button type="button" class="btn btn-primary" onclick="nextStep(4)">
                            Próximo <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Etapa 4: Revisão -->
                <div class="wizard-content" id="content-4" style="display: none;">
                    <h3><i class="bi bi-check-circle"></i> Revisão e Confirmação</h3>
                    
                    <div class="resumo-box">
                        <h5 class="mb-3" style="color: #005051;">Dados da Reserva</h5>
                        <div class="resumo-item">
                            <span>Hóspede:</span>
                            <span id="resumo-nome">-</span>
                        </div>
                        <div class="resumo-item">
                            <span>Email:</span>
                            <span id="resumo-email">-</span>
                        </div>
                        <div class="resumo-item">
                            <span>Telefone:</span>
                            <span id="resumo-telefone">-</span>
                        </div>
                        <div class="resumo-item">
                            <span>Quarto:</span>
                            <span><?= htmlspecialchars($tipoQuarto['nome']) ?></span>
                        </div>
                        <div class="resumo-item">
                            <span>Período:</span>
                            <span><?= formatarData($checkin) ?> - <?= formatarData($checkout) ?></span>
                        </div>
                        <div class="resumo-item">
                            <span>Hóspedes:</span>
                            <span><?= $adultos ?> adulto(s), <?= $criancas ?> criança(s)</span>
                        </div>
                        <div class="resumo-item">
                            <span>Total:</span>
                            <span><?= formatarMoeda($totais['total_liquido']) ?></span>
                        </div>
                    </div>
                    
                    <div class="form-check mt-4 mb-4">
                        <input class="form-check-input" type="checkbox" id="aceitoPoliticas" required>
                        <label class="form-check-label" for="aceitoPoliticas">
                            Li e aceito as <a href="#">políticas de reserva</a> e <a href="#">termos de uso</a>
                        </label>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-secondary" onclick="prevStep(3)">
                            <i class="bi bi-arrow-left"></i> Anterior
                        </button>
                        <button type="submit" name="finalizar_reserva" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Enviar Reserva
                        </button>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentStep = 1;
        
        function nextStep(step) {
            // Validar etapa atual
            if (currentStep === 1) {
                if (!document.querySelector('[name="nome"]').value || 
                    !document.querySelector('[name="telefone"]').value ||
                    !document.querySelector('[name="email"]').value) {
                    alert('Por favor, preencha todos os campos obrigatórios.');
                    return;
                }
                
                // Preencher resumo
                document.getElementById('resumo-nome').textContent = document.querySelector('[name="nome"]').value;
                document.getElementById('resumo-email').textContent = document.querySelector('[name="email"]').value;
                document.getElementById('resumo-telefone').textContent = document.querySelector('[name="telefone"]').value;
            }
            
            if (currentStep === 2) {
                if (!document.querySelector('[name="banco_escolhido_id"]:checked')) {
                    alert('Por favor, selecione um banco.');
                    return;
                }
            }
            
            // Ocultar etapa atual
            document.getElementById('content-' + currentStep).style.display = 'none';
            document.getElementById('step-' + currentStep).classList.remove('active');
            document.getElementById('step-' + currentStep).classList.add('completed');
            
            // Mostrar próxima etapa
            currentStep = step;
            document.getElementById('content-' + currentStep).style.display = 'block';
            document.getElementById('step-' + currentStep).classList.add('active');
        }
        
        function prevStep(step) {
            // Ocultar etapa atual
            document.getElementById('content-' + currentStep).style.display = 'none';
            document.getElementById('step-' + currentStep).classList.remove('active');
            
            // Mostrar etapa anterior
            currentStep = step;
            document.getElementById('content-' + currentStep).style.display = 'block';
            document.getElementById('step-' + currentStep).classList.add('active');
            document.getElementById('step-' + currentStep).classList.remove('completed');
        }
        
        function selectBanco(bancoId) {
            document.getElementById('banco' + bancoId).checked = true;
            document.querySelectorAll('.banco-card').forEach(card => {
                card.classList.remove('selected');
            });
            document.querySelector('.banco-card').closest('.banco-card').classList.add('selected');
            event.currentTarget.classList.add('selected');
        }
        
        function copiarIBAN(elementId) {
            const iban = document.getElementById(elementId).textContent;
            navigator.clipboard.writeText(iban).then(() => {
                alert('IBAN copiado para a área de transferência!');
            });
        }
        
        // Upload
        const uploadZone = document.getElementById('uploadZone');
        const fileInput = document.getElementById('fileInput');
        const filePreview = document.getElementById('filePreview');
        
        uploadZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadZone.classList.add('dragover');
        });
        
        uploadZone.addEventListener('dragleave', () => {
            uploadZone.classList.remove('dragover');
        });
        
        uploadZone.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadZone.classList.remove('dragover');
            fileInput.files = e.dataTransfer.files;
            handleFiles(fileInput.files);
        });
        
        fileInput.addEventListener('change', (e) => {
            handleFiles(e.target.files);
        });
        
        function handleFiles(files) {
            filePreview.style.display = 'block';
            filePreview.innerHTML = '';
            
            Array.from(files).forEach((file, index) => {
                if (index >= 3) return; // Máximo 3 arquivos
                
                const fileItem = document.createElement('div');
                fileItem.className = 'file-item';
                fileItem.innerHTML = `
                    <div>
                        <i class="bi bi-file-earmark"></i> ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)
                    </div>
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeFile(${index})">
                        <i class="bi bi-trash"></i>
                    </button>
                `;
                filePreview.appendChild(fileItem);
            });
        }
        
        function removeFile(index) {
            // Implementar remoção de arquivo
            alert('Funcionalidade de remoção será implementada');
        }
    </script>
</body>
</html>

