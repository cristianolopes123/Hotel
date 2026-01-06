<?php
/**
 * Gestão de Bancos
 * Hotel Mucinga Nzambi - Admin
 */

// Carrega o sistema de proteção
require_once __DIR__ . '/_guard.php';

// 🔐 Permissão específica para Bancos (FINANCEIRO e ADMIN)
if (!RBAC::hasPermission('GERIR_BANCOS')) {
    $_SESSION['error_message'] = 'Você não tem permissão para gerenciar bancos.';
    header('Location: index.php');
    exit;
}

$user = Auth::getUser();
$db = getDB();
$mensagem = '';

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['acao'])) {
        if ($_POST['acao'] === 'criar') {
            $nomeBanco = sanitizeInput($_POST['nome_banco'] ?? '');
            $titular = sanitizeInput($_POST['titular'] ?? '');
            $iban = sanitizeInput($_POST['iban'] ?? '');
            $nif = sanitizeInput($_POST['nif'] ?? '');
            $observacoes = sanitizeInput($_POST['observacoes'] ?? '');
            $ativo = isset($_POST['ativo']) ? 1 : 0;
            
            try {
                $stmt = $db->prepare("
                    INSERT INTO bancos (nome_banco, titular, iban, nif, observacoes, ativo)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$nomeBanco, $titular, $iban, $nif, $observacoes, $ativo]);
                
                logAuditoria('banco', $db->lastInsertId(), 'CRIAR', null, ['nome_banco' => $nomeBanco]);
                $mensagem = 'Banco criado com sucesso!';
            } catch (PDOException $e) {
                $mensagem = 'Erro ao criar banco: ' . $e->getMessage();
            }
        } elseif ($_POST['acao'] === 'editar') {
            $id = intval($_POST['id'] ?? 0);
            $nomeBanco = sanitizeInput($_POST['nome_banco'] ?? '');
            $titular = sanitizeInput($_POST['titular'] ?? '');
            $iban = sanitizeInput($_POST['iban'] ?? '');
            $nif = sanitizeInput($_POST['nif'] ?? '');
            $observacoes = sanitizeInput($_POST['observacoes'] ?? '');
            $ativo = isset($_POST['ativo']) ? 1 : 0;
            
            $stmt = $db->prepare("
                UPDATE bancos 
                SET nome_banco = ?, titular = ?, iban = ?, nif = ?, observacoes = ?, ativo = ?
                WHERE id = ?
            ");
            $stmt->execute([$nomeBanco, $titular, $iban, $nif, $observacoes, $ativo, $id]);
            
            logAuditoria('banco', $id, 'ATUALIZAR', null, ['nome_banco' => $nomeBanco]);
            $mensagem = 'Banco atualizado com sucesso!';
        } elseif ($_POST['acao'] === 'excluir') {
            $id = intval($_POST['id'] ?? 0);
            
            // Verificar se há reservas
            $stmt = $db->prepare("SELECT COUNT(*) as total FROM reservas WHERE banco_escolhido_id = ?");
            $stmt->execute([$id]);
            $total = $stmt->fetch()['total'];
            
            if ($total > 0) {
                $mensagem = 'Não é possível excluir banco com reservas associadas.';
            } else {
                $stmt = $db->prepare("DELETE FROM bancos WHERE id = ?");
                $stmt->execute([$id]);
                
                logAuditoria('banco', $id, 'EXCLUIR', null, null);
                $mensagem = 'Banco excluído com sucesso!';
            }
        }
    }
}

// Buscar bancos
$stmt = $db->query("SELECT * FROM bancos ORDER BY nome_banco ASC");
$bancos = $stmt->fetchAll();

// Banco para edição
$bancoEdit = null;
if (isset($_GET['editar'])) {
    $id = intval($_GET['editar']);
    $stmt = $db->prepare("SELECT * FROM bancos WHERE id = ?");
    $stmt->execute([$id]);
    $bancoEdit = $stmt->fetch();
}

?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Bancos | Hotel Mucinga Nzambi</title>
    
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
                    <i class="bi bi-bank"></i> Gestão de Bancos
                </h2>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bancoModal" onclick="resetForm()">
                    <i class="bi bi-plus-circle"></i> Novo Banco
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
                            <th>Nome do Banco</th>
                            <th>Titular</th>
                            <th>IBAN</th>
                            <th>NIF</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bancos as $banco): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($banco['nome_banco']) ?></strong></td>
                                <td><?= htmlspecialchars($banco['titular']) ?></td>
                                <td><?= htmlspecialchars($banco['iban'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($banco['nif'] ?? '-') ?></td>
                                <td>
                                    <?php if ($banco['ativo']): ?>
                                        <span class="badge bg-success">Ativo</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inativo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary" 
                                            onclick="editarBanco(<?= $banco['id'] ?>)">
                                        <i class="bi bi-pencil"></i> Editar
                                    </button>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Tem certeza que deseja excluir este banco?');">
                                        <input type="hidden" name="acao" value="excluir">
                                        <input type="hidden" name="id" value="<?= $banco['id'] ?>">
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
    
    <!-- Modal Criar/Editar Banco -->
    <div class="modal fade" id="bancoModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?= $bancoEdit ? 'Editar' : 'Novo' ?> Banco</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="acao" value="<?= $bancoEdit ? 'editar' : 'criar' ?>">
                        <?php if ($bancoEdit): ?>
                            <input type="hidden" name="id" value="<?= $bancoEdit['id'] ?>">
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nome do Banco <span class="text-danger">*</span></label>
                            <input type="text" name="nome_banco" class="form-control" 
                                   value="<?= $bancoEdit ? htmlspecialchars($bancoEdit['nome_banco']) : '' ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Titular <span class="text-danger">*</span></label>
                            <input type="text" name="titular" class="form-control" 
                                   value="<?= $bancoEdit ? htmlspecialchars($bancoEdit['titular']) : '' ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">IBAN</label>
                            <input type="text" name="iban" class="form-control" 
                                   value="<?= $bancoEdit ? htmlspecialchars($bancoEdit['iban'] ?? '') : '' ?>" 
                                   placeholder="AO06005500001234567890144">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">NIF</label>
                            <input type="text" name="nif" class="form-control" 
                                   value="<?= $bancoEdit ? htmlspecialchars($bancoEdit['nif'] ?? '') : '' ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Observações</label>
                            <textarea name="observacoes" class="form-control" rows="3"><?= $bancoEdit ? htmlspecialchars($bancoEdit['observacoes'] ?? '') : '' ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="ativo" id="ativo" 
                                       <?= ($bancoEdit && $bancoEdit['ativo']) ? 'checked' : 'checked' ?>>
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
            document.querySelector('#bancoModal form').reset();
            document.querySelector('input[name="acao"]').value = 'criar';
        }
        
        function editarBanco(id) {
            window.location.href = '?editar=' + id;
        }
    </script>
</body>
</html>

