<?php
/**
 * Gestão de Tarifas
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
            $tipoQuartoId = intval($_POST['tipo_quarto_id'] ?? 0);
            $inicio = $_POST['inicio'] ?? '';
            $fim = $_POST['fim'] ?? '';
            $precoNoite = floatval($_POST['preco_noite'] ?? 0);
            $observacao = sanitizeInput($_POST['observacao'] ?? '');
            
            if ($tipoQuartoId && $inicio && $fim && $precoNoite > 0) {
                try {
                    $stmt = $db->prepare("
                        INSERT INTO tarifas (tipo_quarto_id, inicio, fim, preco_noite, observacao)
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$tipoQuartoId, $inicio, $fim, $precoNoite, $observacao]);
                    
                    logAuditoria('tarifa', $db->lastInsertId(), 'CRIAR', null, ['preco_noite' => $precoNoite]);
                    $mensagem = 'Tarifa criada com sucesso!';
                } catch (PDOException $e) {
                    $mensagem = 'Erro ao criar tarifa: ' . $e->getMessage();
                }
            }
        } elseif ($_POST['acao'] === 'editar') {
            $id = intval($_POST['id'] ?? 0);
            $tipoQuartoId = intval($_POST['tipo_quarto_id'] ?? 0);
            $inicio = $_POST['inicio'] ?? '';
            $fim = $_POST['fim'] ?? '';
            $precoNoite = floatval($_POST['preco_noite'] ?? 0);
            $observacao = sanitizeInput($_POST['observacao'] ?? '');
            
            $stmt = $db->prepare("
                UPDATE tarifas 
                SET tipo_quarto_id = ?, inicio = ?, fim = ?, preco_noite = ?, observacao = ?
                WHERE id = ?
            ");
            $stmt->execute([$tipoQuartoId, $inicio, $fim, $precoNoite, $observacao, $id]);
            
            logAuditoria('tarifa', $id, 'ATUALIZAR', null, ['preco_noite' => $precoNoite]);
            $mensagem = 'Tarifa atualizada com sucesso!';
        } elseif ($_POST['acao'] === 'excluir') {
            $id = intval($_POST['id'] ?? 0);
            
            $stmt = $db->prepare("DELETE FROM tarifas WHERE id = ?");
            $stmt->execute([$id]);
            
            logAuditoria('tarifa', $id, 'EXCLUIR', null, null);
            $mensagem = 'Tarifa excluída com sucesso!';
        }
    }
}

// Buscar tarifas
$stmt = $db->query("
    SELECT t.*, tq.nome as tipo_quarto_nome
    FROM tarifas t
    INNER JOIN tipos_quarto tq ON t.tipo_quarto_id = tq.id
    ORDER BY t.inicio DESC, tq.nome ASC
");
$tarifas = $stmt->fetchAll();

// Buscar tipos de quarto
$stmt = $db->query("SELECT id, nome FROM tipos_quarto WHERE ativo = 1 ORDER BY nome");
$tiposQuarto = $stmt->fetchAll();

// Tarifa para edição
$tarifaEdit = null;
if (isset($_GET['editar'])) {
    $id = intval($_GET['editar']);
    $stmt = $db->prepare("SELECT * FROM tarifas WHERE id = ?");
    $stmt->execute([$id]);
    $tarifaEdit = $stmt->fetch();
}

?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Tarifas | Hotel Mucinga Nzambi</title>
    
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
                    <i class="bi bi-currency-exchange"></i> Gestão de Tarifas
                </h2>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tarifaModal" onclick="resetForm()">
                    <i class="bi bi-plus-circle"></i> Nova Tarifa
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
                            <th>Tipo de Quarto</th>
                            <th>Início</th>
                            <th>Fim</th>
                            <th>Preço/Noite</th>
                            <th>Observações</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tarifas as $tarifa): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($tarifa['tipo_quarto_nome']) ?></strong></td>
                                <td><?= formatarData($tarifa['inicio']) ?></td>
                                <td><?= formatarData($tarifa['fim']) ?></td>
                                <td><strong style="color: #005051;"><?= formatarMoeda($tarifa['preco_noite']) ?></strong></td>
                                <td><?= htmlspecialchars(substr($tarifa['observacao'] ?? '', 0, 50)) ?><?= strlen($tarifa['observacao'] ?? '') > 50 ? '...' : '' ?></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary" 
                                            onclick="editarTarifa(<?= $tarifa['id'] ?>)">
                                        <i class="bi bi-pencil"></i> Editar
                                    </button>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Tem certeza que deseja excluir esta tarifa?');">
                                        <input type="hidden" name="acao" value="excluir">
                                        <input type="hidden" name="id" value="<?= $tarifa['id'] ?>">
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
    
    <!-- Modal Criar/Editar Tarifa -->
    <div class="modal fade" id="tarifaModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?= $tarifaEdit ? 'Editar' : 'Nova' ?> Tarifa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="acao" value="<?= $tarifaEdit ? 'editar' : 'criar' ?>">
                        <?php if ($tarifaEdit): ?>
                            <input type="hidden" name="id" value="<?= $tarifaEdit['id'] ?>">
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tipo de Quarto <span class="text-danger">*</span></label>
                            <select name="tipo_quarto_id" class="form-select" required>
                                <option value="">Selecione...</option>
                                <?php foreach ($tiposQuarto as $tipo): ?>
                                    <option value="<?= $tipo['id'] ?>" 
                                            <?= ($tarifaEdit && $tarifaEdit['tipo_quarto_id'] == $tipo['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($tipo['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Data Início <span class="text-danger">*</span></label>
                                <input type="date" name="inicio" class="form-control" 
                                       value="<?= $tarifaEdit ? $tarifaEdit['inicio'] : '' ?>" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Data Fim <span class="text-danger">*</span></label>
                                <input type="date" name="fim" class="form-control" 
                                       value="<?= $tarifaEdit ? $tarifaEdit['fim'] : '' ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3 mt-3">
                            <label class="form-label fw-bold">Preço por Noite (Kz) <span class="text-danger">*</span></label>
                            <input type="number" name="preco_noite" class="form-control" 
                                   value="<?= $tarifaEdit ? $tarifaEdit['preco_noite'] : '' ?>" 
                                   step="0.01" min="0" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Observações</label>
                            <textarea name="observacao" class="form-control" rows="3"><?= $tarifaEdit ? htmlspecialchars($tarifaEdit['observacao'] ?? '') : '' ?></textarea>
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
            document.querySelector('#tarifaModal form').reset();
            document.querySelector('input[name="acao"]').value = 'criar';
        }
        
        function editarTarifa(id) {
            window.location.href = '?editar=' + id;
        }
    </script>
</body>
</html>

