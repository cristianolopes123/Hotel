<?php
/**
 * Gestão de Serviços Adicionais
 * Hotel Mucinga Nzambi - Admin
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


$db = getDB();
$mensagem = '';

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['acao'])) {
        if ($_POST['acao'] === 'criar') {
            $nome = sanitizeInput($_POST['nome'] ?? '');
            $descricao = sanitizeInput($_POST['descricao'] ?? '');
            $preco = floatval($_POST['preco'] ?? 0);
            $unidade = $_POST['unidade'] ?? 'POR_RESERVA';
            $ativo = isset($_POST['ativo']) ? 1 : 0;
            
            if ($nome && $preco > 0) {
                try {
                    $stmt = $db->prepare("
                        INSERT INTO servicos (nome, descricao, preco, unidade, ativo)
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$nome, $descricao, $preco, $unidade, $ativo]);
                    
                    logAuditoria('servico', $db->lastInsertId(), 'CRIAR', null, ['nome' => $nome]);
                    $mensagem = 'Serviço criado com sucesso!';
                } catch (PDOException $e) {
                    $mensagem = 'Erro ao criar serviço: ' . $e->getMessage();
                }
            }
        } elseif ($_POST['acao'] === 'editar') {
            $id = intval($_POST['id'] ?? 0);
            $nome = sanitizeInput($_POST['nome'] ?? '');
            $descricao = sanitizeInput($_POST['descricao'] ?? '');
            $preco = floatval($_POST['preco'] ?? 0);
            $unidade = $_POST['unidade'] ?? 'POR_RESERVA';
            $ativo = isset($_POST['ativo']) ? 1 : 0;
            
            $stmt = $db->prepare("
                UPDATE servicos 
                SET nome = ?, descricao = ?, preco = ?, unidade = ?, ativo = ?
                WHERE id = ?
            ");
            $stmt->execute([$nome, $descricao, $preco, $unidade, $ativo, $id]);
            
            logAuditoria('servico', $id, 'ATUALIZAR', null, ['nome' => $nome]);
            $mensagem = 'Serviço atualizado com sucesso!';
        } elseif ($_POST['acao'] === 'excluir') {
            $id = intval($_POST['id'] ?? 0);
            
            $stmt = $db->prepare("DELETE FROM servicos WHERE id = ?");
            $stmt->execute([$id]);
            
            logAuditoria('servico', $id, 'EXCLUIR', null, null);
            $mensagem = 'Serviço excluído com sucesso!';
        }
    }
}

// Buscar serviços
$stmt = $db->query("SELECT * FROM servicos ORDER BY nome ASC");
$servicos = $stmt->fetchAll();

// Serviço para edição
$servicoEdit = null;
if (isset($_GET['editar'])) {
    $id = intval($_GET['editar']);
    $stmt = $db->prepare("SELECT * FROM servicos WHERE id = ?");
    $stmt->execute([$id]);
    $servicoEdit = $stmt->fetch();
}

$unidades = [
    'POR_RESERVA' => 'Por Reserva',
    'POR_NOITE' => 'Por Noite',
    'POR_PESSOA' => 'Por Pessoa'
];

?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Serviços | Hotel Mucinga Nzambi</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #F5F5F5;
        }
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 250px;
            background: linear-gradient(135deg, #005051 0%, #006b6d 100%);
            color: #fff;
            padding: 20px 0;
            overflow-y: auto;
            z-index: 1000;
        }
        .main-content {
            margin-left: 250px;
            padding: 30px;
        }
        .content-card {
            background: #fff;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #005051 0%, #006b6d 100%);
            border: none;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 style="color: #005051; font-weight: 700;">
                    <i class="bi bi-list-ul"></i> Gestão de Serviços Adicionais
                </h2>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#servicoModal" onclick="resetForm()">
                    <i class="bi bi-plus-circle"></i> Novo Serviço
                </button>
            </div>
            
            <?php if ($mensagem): ?>
                <div class="alert <?= strpos($mensagem, 'sucesso') !== false ? 'alert-success' : 'alert-danger' ?>" role="alert">
                    <?= htmlspecialchars($mensagem) ?>
                </div>
            <?php endif; ?>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Descrição</th>
                            <th>Preço</th>
                            <th>Unidade</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($servicos as $servico): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($servico['nome']) ?></strong></td>
                                <td><?= htmlspecialchars(substr($servico['descricao'] ?? '', 0, 50)) ?><?= strlen($servico['descricao'] ?? '') > 50 ? '...' : '' ?></td>
                                <td><strong style="color: #005051;"><?= formatarMoeda($servico['preco']) ?></strong></td>
                                <td><?= $unidades[$servico['unidade']] ?? $servico['unidade'] ?></td>
                                <td>
                                    <?php if ($servico['ativo']): ?>
                                        <span class="badge bg-success">Ativo</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inativo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary" 
                                            onclick="editarServico(<?= $servico['id'] ?>)">
                                        <i class="bi bi-pencil"></i> Editar
                                    </button>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Tem certeza que deseja excluir este serviço?');">
                                        <input type="hidden" name="acao" value="excluir">
                                        <input type="hidden" name="id" value="<?= $servico['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="bi bi-trash"></i> Excluir
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Modal Criar/Editar Serviço -->
    <div class="modal fade" id="servicoModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?= $servicoEdit ? 'Editar' : 'Novo' ?> Serviço</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="acao" value="<?= $servicoEdit ? 'editar' : 'criar' ?>">
                        <?php if ($servicoEdit): ?>
                            <input type="hidden" name="id" value="<?= $servicoEdit['id'] ?>">
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nome <span class="text-danger">*</span></label>
                            <input type="text" name="nome" class="form-control" 
                                   value="<?= $servicoEdit ? htmlspecialchars($servicoEdit['nome']) : '' ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Descrição</label>
                            <textarea name="descricao" class="form-control" rows="3"><?= $servicoEdit ? htmlspecialchars($servicoEdit['descricao'] ?? '') : '' ?></textarea>
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Preço (Kz) <span class="text-danger">*</span></label>
                                <input type="number" name="preco" class="form-control" 
                                       value="<?= $servicoEdit ? $servicoEdit['preco'] : '' ?>" 
                                       step="0.01" min="0" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Unidade <span class="text-danger">*</span></label>
                                <select name="unidade" class="form-select" required>
                                    <?php foreach ($unidades as $key => $label): ?>
                                        <option value="<?= $key ?>" 
                                                <?= ($servicoEdit && $servicoEdit['unidade'] === $key) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($label) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3 mt-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="ativo" id="ativo" 
                                       <?= ($servicoEdit && $servicoEdit['ativo']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="ativo">
                                    Ativo
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        function resetForm() {
            document.querySelector('#servicoModal form').reset();
            document.querySelector('input[name="acao"]').value = 'criar';
        }
        
        function editarServico(id) {
            window.location.href = '?editar=' + id;
        }
    </script>
</body>
</html>

