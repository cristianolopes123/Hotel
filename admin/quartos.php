<?php
/**
 * Gestão de Quartos
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
            $numero = sanitizeInput($_POST['numero'] ?? '');
            $tipoQuartoId = intval($_POST['tipo_quarto_id'] ?? 0);
            $status = $_POST['status'] ?? 'ATIVO';
            
            if ($numero && $tipoQuartoId) {
                try {
                    $stmt = $db->prepare("INSERT INTO quartos (numero, tipo_quarto_id, status) VALUES (?, ?, ?)");
                    $stmt->execute([$numero, $tipoQuartoId, $status]);
                    
                    logAuditoria('quarto', $db->lastInsertId(), 'CRIAR', null, ['numero' => $numero, 'tipo_quarto_id' => $tipoQuartoId]);
                    $mensagem = 'Quarto criado com sucesso!';
                } catch (PDOException $e) {
                    $mensagem = 'Erro ao criar quarto: ' . $e->getMessage();
                }
            }
        } elseif ($_POST['acao'] === 'editar') {
            $id = intval($_POST['id'] ?? 0);
            $numero = sanitizeInput($_POST['numero'] ?? '');
            $tipoQuartoId = intval($_POST['tipo_quarto_id'] ?? 0);
            $status = $_POST['status'] ?? 'ATIVO';
            
            $stmt = $db->prepare("SELECT * FROM quartos WHERE id = ?");
            $stmt->execute([$id]);
            $quartoAntes = $stmt->fetch();
            
            $stmt = $db->prepare("UPDATE quartos SET numero = ?, tipo_quarto_id = ?, status = ? WHERE id = ?");
            $stmt->execute([$numero, $tipoQuartoId, $status, $id]);
            
            logAuditoria('quarto', $id, 'ATUALIZAR', 
                ['numero' => $quartoAntes['numero'], 'status' => $quartoAntes['status']],
                ['numero' => $numero, 'status' => $status]);
            $mensagem = 'Quarto atualizado com sucesso!';
        } elseif ($_POST['acao'] === 'excluir') {
            $id = intval($_POST['id'] ?? 0);
            
            // Verificar se há reservas
            $stmt = $db->prepare("SELECT COUNT(*) as total FROM reservas WHERE quarto_id = ?");
            $stmt->execute([$id]);
            $total = $stmt->fetch()['total'];
            
            if ($total > 0) {
                $mensagem = 'Não é possível excluir quarto com reservas associadas.';
            } else {
                $stmt = $db->prepare("DELETE FROM quartos WHERE id = ?");
                $stmt->execute([$id]);
                
                logAuditoria('quarto', $id, 'EXCLUIR', null, null);
                $mensagem = 'Quarto excluído com sucesso!';
            }
        }
    }
}

// Buscar quartos
$stmt = $db->query("
    SELECT q.*, tq.nome as tipo_quarto_nome
    FROM quartos q
    INNER JOIN tipos_quarto tq ON q.tipo_quarto_id = tq.id
    ORDER BY q.numero ASC
");
$quartos = $stmt->fetchAll();

// Buscar tipos de quarto
$stmt = $db->query("SELECT id, nome FROM tipos_quarto WHERE ativo = 1 ORDER BY nome");
$tiposQuarto = $stmt->fetchAll();

// Quarto para edição
$quartoEdit = null;
if (isset($_GET['editar'])) {
    $id = intval($_GET['editar']);
    $stmt = $db->prepare("SELECT * FROM quartos WHERE id = ?");
    $stmt->execute([$id]);
    $quartoEdit = $stmt->fetch();
}

?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Quartos | Hotel Mucinga Nzambi</title>
    
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
                    <i class="bi bi-door-open"></i> Gestão de Quartos
                </h2>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#quartoModal" onclick="resetForm()">
                    <i class="bi bi-plus-circle"></i> Novo Quarto
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
                            <th>Número</th>
                            <th>Tipo de Quarto</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($quartos as $quarto): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($quarto['numero']) ?></strong></td>
                                <td><?= htmlspecialchars($quarto['tipo_quarto_nome']) ?></td>
                                <td>
                                    <?php if ($quarto['status'] === 'ATIVO'): ?>
                                        <span class="badge bg-success">Ativo</span>
                                    <?php elseif ($quarto['status'] === 'MANUTENCAO'): ?>
                                        <span class="badge bg-warning">Manutenção</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inativo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary" 
                                            onclick="editarQuarto(<?= $quarto['id'] ?>, '<?= htmlspecialchars($quarto['numero']) ?>', <?= $quarto['tipo_quarto_id'] ?>, '<?= $quarto['status'] ?>')">
                                        <i class="bi bi-pencil"></i> Editar
                                    </button>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Tem certeza que deseja excluir este quarto?');">
                                        <input type="hidden" name="acao" value="excluir">
                                        <input type="hidden" name="id" value="<?= $quarto['id'] ?>">
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
    
    <!-- Modal Criar/Editar Quarto -->
    <div class="modal fade" id="quartoModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?= $quartoEdit ? 'Editar' : 'Novo' ?> Quarto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="acao" value="<?= $quartoEdit ? 'editar' : 'criar' ?>">
                        <?php if ($quartoEdit): ?>
                            <input type="hidden" name="id" value="<?= $quartoEdit['id'] ?>">
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Número do Quarto <span class="text-danger">*</span></label>
                            <input type="text" name="numero" class="form-control" 
                                   value="<?= $quartoEdit ? htmlspecialchars($quartoEdit['numero']) : '' ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tipo de Quarto <span class="text-danger">*</span></label>
                            <select name="tipo_quarto_id" class="form-select" required>
                                <option value="">Selecione...</option>
                                <?php foreach ($tiposQuarto as $tipo): ?>
                                    <option value="<?= $tipo['id'] ?>" 
                                            <?= ($quartoEdit && $quartoEdit['tipo_quarto_id'] == $tipo['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($tipo['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="ATIVO" <?= ($quartoEdit && $quartoEdit['status'] === 'ATIVO') ? 'selected' : '' ?>>Ativo</option>
                                <option value="MANUTENCAO" <?= ($quartoEdit && $quartoEdit['status'] === 'MANUTENCAO') ? 'selected' : '' ?>>Manutenção</option>
                                <option value="INATIVO" <?= ($quartoEdit && $quartoEdit['status'] === 'INATIVO') ? 'selected' : '' ?>>Inativo</option>
                            </select>
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
            document.querySelector('#quartoModal form').reset();
            document.querySelector('input[name="acao"]').value = 'criar';
            document.querySelector('input[name="id"]')?.remove();
        }
        
        function editarQuarto(id, numero, tipoQuartoId, status) {
            const form = document.querySelector('#quartoModal form');
            form.querySelector('input[name="acao"]').value = 'editar';
            
            if (!form.querySelector('input[name="id"]')) {
                const inputId = document.createElement('input');
                inputId.type = 'hidden';
                inputId.name = 'id';
                form.appendChild(inputId);
            }
            form.querySelector('input[name="id"]').value = id;
            
            form.querySelector('input[name="numero"]').value = numero;
            form.querySelector('select[name="tipo_quarto_id"]').value = tipoQuartoId;
            form.querySelector('select[name="status"]').value = status;
            
            new bootstrap.Modal(document.getElementById('quartoModal')).show();
        }
    </script>
</body>
</html>

