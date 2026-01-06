<?php
/**
 * Upload de Comprovante
 * Hotel Mucinga Nzambi
 */

define('SYSTEM_ACCESS', true);
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/rbac.php';
require_once 'includes/helpers.php';
require_once 'includes/upload.php';

$reservaId = intval($_GET['reserva_id'] ?? 0);
$mensagem = '';
$sucesso = false;

if (!$reservaId) {
    header('Location: disponibilidade.php');
    exit;
}

$db = getDB();

// Verificar se a reserva pertence ao usuário (se logado) ou permitir para visitantes
$usuario = Auth::getUser();
if ($usuario) {
    $stmt = $db->prepare("SELECT * FROM reservas WHERE id = ? AND usuario_id = ?");
    $stmt->execute([$reservaId, $usuario['id']]);
} else {
    // Visitante pode enviar comprovante se tiver email
    $stmt = $db->prepare("SELECT * FROM reservas WHERE id = ?");
    $stmt->execute([$reservaId]);
}

$reserva = $stmt->fetch();

if (!$reserva) {
    die('Reserva não encontrada ou sem permissão');
}

// Processar upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['comprovante']['name'][0])) {
    // Validar CSRF
    if (!validarCSRFToken($_POST['csrf_token'] ?? '')) {
        $mensagem = 'Token CSRF inválido';
    } else {
        $uploadOk = true;
        $arquivosEnviados = 0;
        
        // Processar cada arquivo
        foreach ($_FILES['comprovante']['name'] as $key => $name) {
            if (!empty($name)) {
                $file = [
                    'name' => $_FILES['comprovante']['name'][$key],
                    'type' => $_FILES['comprovante']['type'][$key],
                    'tmp_name' => $_FILES['comprovante']['tmp_name'][$key],
                    'error' => $_FILES['comprovante']['error'][$key],
                    'size' => $_FILES['comprovante']['size'][$key]
                ];
                
                $resultado = UploadHandler::uploadComprovante($file, $reservaId);
                
                if ($resultado['success']) {
                    $arquivosEnviados++;
                } else {
                    $mensagem = $resultado['message'] ?? 'Erro no upload';
                    $uploadOk = false;
                    break;
                }
            }
        }
        
        if ($uploadOk && $arquivosEnviados > 0) {
            // Atualizar status da reserva para EM_ANALISE
            $stmt = $db->prepare("UPDATE reservas SET status = 'EM_ANALISE' WHERE id = ? AND status = 'PENDENTE_COMPROVANTE'");
            $stmt->execute([$reservaId]);
            
            // Log de auditoria
            logAuditoria('reserva', $reservaId, 'ENVIAR_COMPROVANTE', null, ['status' => 'EM_ANALISE']);
            
            // Enviar notificação
            require_once 'includes/notifications.php';
            Notifications::notificarComprovanteEnviado($reservaId);
            
            $sucesso = true;
            $mensagem = "Comprovante(s) enviado(s) com sucesso! {$arquivosEnviados} arquivo(s).";
        }
    }
}

// Buscar comprovantes existentes
$comprovantes = UploadHandler::getComprovantes($reservaId);

?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enviar Comprovante | Hotel Mucinga Nzambi</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    
    <?php include 'includes/navbar-styles.php'; ?>
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #FDF7E6;
            padding-top: 100px;
        }
        
        .upload-container {
            max-width: 700px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        
        .upload-card {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 40px;
        }
        
        .upload-card h2 {
            color: #005051;
            font-weight: 700;
            margin-bottom: 25px;
        }
        
        .upload-zone {
            border: 3px dashed #005051;
            border-radius: 10px;
            padding: 60px 40px;
            text-align: center;
            background: #f8f9fa;
            transition: all 0.3s ease;
            cursor: pointer;
            margin-bottom: 20px;
        }
        
        .upload-zone:hover {
            background: rgba(0, 80, 81, 0.05);
            border-color: #006b6d;
        }
        
        .upload-zone.dragover {
            background: rgba(0, 80, 81, 0.1);
            border-color: #005051;
        }
        
        .upload-icon {
            font-size: 4rem;
            color: #005051;
            margin-bottom: 15px;
        }
        
        .file-preview {
            margin-top: 20px;
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
        
        .btn-primary {
            background: linear-gradient(135deg, #005051 0%, #006b6d 100%);
            border: none;
            padding: 12px 40px;
            font-weight: 600;
            border-radius: 30px;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #006b6d 0%, #005051 100%);
        }
        
        .reserva-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="upload-container">
        <div class="upload-card">
            <h2><i class="bi bi-cloud-upload"></i> Enviar Comprovante de Pagamento</h2>
            
            <!-- Informações da Reserva -->
            <div class="reserva-info">
                <strong style="color: #005051;">Reserva:</strong> <?= htmlspecialchars($reserva['codigo']) ?><br>
                <strong style="color: #005051;">Total:</strong> <?= formatarMoeda($reserva['total_liquido']) ?><br>
                <strong style="color: #005051;">Status:</strong> <?= statusBadge($reserva['status']) ?>
            </div>
            
            <?php if ($mensagem): ?>
                <div class="alert <?= $sucesso ? 'alert-success' : 'alert-danger' ?>" role="alert">
                    <i class="bi bi-<?= $sucesso ? 'check-circle' : 'exclamation-triangle' ?>"></i> 
                    <?= htmlspecialchars($mensagem) ?>
                </div>
                
                <?php if ($sucesso): ?>
                    <div class="text-center mt-4">
                        <?php if (Auth::isLoggedIn()): ?>
                            <a href="minhas-reservas.php" class="btn btn-primary">
                                <i class="bi bi-list-check"></i> Minhas Reservas
                            </a>
                        <?php else: ?>
                            <a href="disponibilidade.php" class="btn btn-primary">
                                <i class="bi bi-house"></i> Voltar ao Início
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <?php if (!$sucesso): ?>
                <form method="POST" action="" enctype="multipart/form-data" id="uploadForm">
                    <input type="hidden" name="csrf_token" value="<?= gerarCSRFToken() ?>">
                    
                    <div class="upload-zone" id="uploadZone">
                        <div class="upload-icon">
                            <i class="bi bi-cloud-upload"></i>
                        </div>
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
                    
                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-primary" id="submitBtn" style="display: none;">
                            <i class="bi bi-upload"></i> Enviar Comprovante(s)
                        </button>
                    </div>
                </form>
            <?php endif; ?>
            
            <!-- Comprovantes já enviados -->
            <?php if (!empty($comprovantes)): ?>
                <div class="mt-4">
                    <h5 style="color: #005051; margin-bottom: 15px;">Comprovantes já enviados:</h5>
                    <?php foreach ($comprovantes as $comprovante): ?>
                        <div class="file-item">
                            <div>
                                <i class="bi bi-file-earmark-image"></i> 
                                <a href="<?= htmlspecialchars($comprovante['arquivo_path']) ?>" 
                                   target="_blank" class="text-decoration-none">
                                    Comprovante enviado em <?= formatarData($comprovante['criado_em'], 'd/m/Y H:i') ?>
                                </a>
                            </div>
                            <a href="<?= htmlspecialchars($comprovante['arquivo_path']) ?>" 
                               target="_blank" class="btn btn-sm btn-primary">
                                <i class="bi bi-eye"></i> Ver
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        const uploadZone = document.getElementById('uploadZone');
        const fileInput = document.getElementById('fileInput');
        const filePreview = document.getElementById('filePreview');
        const submitBtn = document.getElementById('submitBtn');
        
        // Drag and drop
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
            filePreview.innerHTML = '';
            submitBtn.style.display = 'none';
            
            if (files.length === 0) return;
            
            const maxFiles = 3;
            const filesToShow = Array.from(files).slice(0, maxFiles);
            
            filesToShow.forEach((file, index) => {
                const fileItem = document.createElement('div');
                fileItem.className = 'file-item';
                fileItem.innerHTML = `
                    <div>
                        <i class="bi bi-file-earmark"></i> 
                        ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)
                    </div>
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeFile(${index})">
                        <i class="bi bi-trash"></i>
                    </button>
                `;
                filePreview.appendChild(fileItem);
            });
            
            if (files.length > maxFiles) {
                const warning = document.createElement('div');
                warning.className = 'alert alert-warning mt-2';
                warning.textContent = `Apenas os primeiros ${maxFiles} arquivos serão enviados.`;
                filePreview.appendChild(warning);
            }
            
            submitBtn.style.display = 'block';
        }
        
        function removeFile(index) {
            // Implementar remoção de arquivo da lista
            const dt = new DataTransfer();
            const files = Array.from(fileInput.files);
            files.splice(index, 1);
            files.forEach(file => dt.items.add(file));
            fileInput.files = dt.files;
            handleFiles(fileInput.files);
        }
    </script>
</body>
</html>

