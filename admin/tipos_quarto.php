<?php
/**
 * Gestão de Tipos de Quarto
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
            $capacidadeAdultos = intval($_POST['capacidade_adultos'] ?? 2);
            $capacidadeCriancas = intval($_POST['capacidade_criancas'] ?? 0);
            $amenidades = $_POST['amenidades'] ?? [];
            $ativo = isset($_POST['ativo']) ? 1 : 0;
            
            $amenidadesJson = json_encode($amenidades);
            
            try {
                $stmt = $db->prepare("
                    INSERT INTO tipos_quarto (nome, descricao, capacidade_adultos, capacidade_criancas, amenidades, ativo)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$nome, $descricao, $capacidadeAdultos, $capacidadeCriancas, $amenidadesJson, $ativo]);
                
                logAuditoria('tipo_quarto', $db->lastInsertId(), 'CRIAR', null, ['nome' => $nome]);
                $mensagem = 'Tipo de quarto criado com sucesso!';
            } catch (PDOException $e) {
                $mensagem = 'Erro ao criar tipo de quarto: ' . $e->getMessage();
            }
        } elseif ($_POST['acao'] === 'editar') {
            $id = intval($_POST['id'] ?? 0);
            $nome = sanitizeInput($_POST['nome'] ?? '');
            $descricao = sanitizeInput($_POST['descricao'] ?? '');
            $capacidadeAdultos = intval($_POST['capacidade_adultos'] ?? 2);
            $capacidadeCriancas = intval($_POST['capacidade_criancas'] ?? 0);
            $amenidades = $_POST['amenidades'] ?? [];
            $ativo = isset($_POST['ativo']) ? 1 : 0;
            
            $amenidadesJson = json_encode($amenidades);
            
            $stmt = $db->prepare("
                UPDATE tipos_quarto 
                SET nome = ?, descricao = ?, capacidade_adultos = ?, capacidade_criancas = ?, amenidades = ?, ativo = ?
                WHERE id = ?
            ");
            $stmt->execute([$nome, $descricao, $capacidadeAdultos, $capacidadeCriancas, $amenidadesJson, $ativo, $id]);
            
            logAuditoria('tipo_quarto', $id, 'ATUALIZAR', null, ['nome' => $nome]);
            $mensagem = 'Tipo de quarto atualizado com sucesso!';
        }
    }
}

// Buscar tipos de quarto
$stmt = $db->query("SELECT * FROM tipos_quarto ORDER BY nome ASC");
$tiposQuarto = $stmt->fetchAll();

// Tipo para edição
$tipoEdit = null;
if (isset($_GET['editar'])) {
    $id = intval($_GET['editar']);
    $stmt = $db->prepare("SELECT * FROM tipos_quarto WHERE id = ?");
    $stmt->execute([$id]);
    $tipoEdit = $stmt->fetch();
    if ($tipoEdit) {
        $tipoEdit['amenidades_array'] = json_decode($tipoEdit['amenidades'] ?? '[]', true);
    }
}

$amenidadesComuns = ['WiFi', 'TV', 'Ar Condicionado', 'Banheiro Privativo', 'Minibar', 'Cofre', 'Vista Panorâmica', 'Sala de Estar', 'Jacuzzi', 'Sala de Jantar'];

?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tipos de Quarto | Hotel Mucinga Nzambi</title>
    
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
                    <i class="bi bi-house"></i> Tipos de Quarto
                </h2>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tipoModal" onclick="resetForm()">
                    <i class="bi bi-plus-circle"></i> Novo Tipo
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
                            <th>Capacidade</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tiposQuarto as $tipo): ?>
                            <?php
                            $amenidades = json_decode($tipo['amenidades'] ?? '[]', true);
                            ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($tipo['nome']) ?></strong></td>
                                <td><?= htmlspecialchars(substr($tipo['descricao'] ?? '', 0, 50)) ?><?= strlen($tipo['descricao'] ?? '') > 50 ? '...' : '' ?></td>
                                <td>
                                    <?= $tipo['capacidade_adultos'] ?> adulto(s), 
                                    <?= $tipo['capacidade_criancas'] ?> criança(s)
                                </td>
                                <td>
                                    <?php if ($tipo['ativo']): ?>
                                        <span class="badge bg-success">Ativo</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inativo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary" 
                                            onclick="editarTipo(<?= $tipo['id'] ?>)">
                                        <i class="bi bi-pencil"></i> Editar
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Modal Criar/Editar Tipo -->
    <div class="modal fade" id="tipoModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?= $tipoEdit ? 'Editar' : 'Novo' ?> Tipo de Quarto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="acao" value="<?= $tipoEdit ? 'editar' : 'criar' ?>">
                        <?php if ($tipoEdit): ?>
                            <input type="hidden" name="id" value="<?= $tipoEdit['id'] ?>">
                        <?php endif; ?>
                        
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Nome <span class="text-danger">*</span></label>
                                <input type="text" name="nome" class="form-control" 
                                       value="<?= $tipoEdit ? htmlspecialchars($tipoEdit['nome']) : '' ?>" required>
                            </div>
                            
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Descrição</label>
                                <textarea name="descricao" class="form-control" rows="3"><?= $tipoEdit ? htmlspecialchars($tipoEdit['descricao'] ?? '') : '' ?></textarea>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Capacidade Adultos</label>
                                <input type="number" name="capacidade_adultos" class="form-control" 
                                       value="<?= $tipoEdit ? $tipoEdit['capacidade_adultos'] : 2 ?>" min="1" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Capacidade Crianças</label>
                                <input type="number" name="capacidade_criancas" class="form-control" 
                                       value="<?= $tipoEdit ? $tipoEdit['capacidade_criancas'] : 0 ?>" min="0">
                            </div>
                            
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Comodidades</label>
                                <div class="row">
                                    <?php foreach ($amenidadesComuns as $amenidade): ?>
                                        <div class="col-md-4 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="amenidades[]" value="<?= htmlspecialchars($amenidade) ?>"
                                                       id="amen_<?= $amenidade ?>"
                                                       <?= ($tipoEdit && in_array($amenidade, $tipoEdit['amenidades_array'] ?? [])) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="amen_<?= $amenidade ?>">
                                                    <?= htmlspecialchars($amenidade) ?>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <small class="text-muted">Adicione outras comodidades personalizadas abaixo:</small>
                                <input type="text" class="form-control mt-2" id="amenidadePersonalizada" 
                                       placeholder="Digite uma comodidade e pressione Enter">
                            </div>
                            
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="ativo" id="ativo" 
                                           <?= ($tipoEdit && $tipoEdit['ativo']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="ativo">
                                        Ativo
                                    </label>
                                </div>
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
            document.querySelector('#tipoModal form').reset();
            document.querySelector('input[name="acao"]').value = 'criar';
        }
        
        function editarTipo(id) {
            window.location.href = '?editar=' + id;
        }
        
        // Adicionar amenidade personalizada
        document.getElementById('amenidadePersonalizada')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const valor = this.value.trim();
                if (valor) {
                    const checkboxes = document.querySelectorAll('input[name="amenidades[]"]');
                    let existe = false;
                    checkboxes.forEach(cb => {
                        if (cb.value === valor) {
                            existe = true;
                            cb.checked = true;
                        }
                    });
                    
                    if (!existe) {
                        // Criar novo checkbox dinamicamente
                        const col = document.createElement('div');
                        col.className = 'col-md-4 mb-2';
                        col.innerHTML = `
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="amenidades[]" value="${valor}" id="amen_${valor}" checked>
                                <label class="form-check-label" for="amen_${valor}">${valor}</label>
                            </div>
                        `;
                        document.querySelector('.row:has(#amenidadePersonalizada)').before(col);
                    }
                    
                    this.value = '';
                }
            }
        });
    </script>
</body>
</html>

