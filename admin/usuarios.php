<?php
/**
 * Gestão de Usuários
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
            $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
            $telefone = sanitizeInput($_POST['telefone'] ?? '');
            $senha = $_POST['senha'] ?? '';
            $role = $_POST['role'] ?? 'HOSPEDE';
            $ativo = isset($_POST['ativo']) ? 1 : 0;
            
            if ($nome && $email && $senha && strlen($senha) >= PASSWORD_MIN_LENGTH) {
                try {
                    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                    $stmt = $db->prepare("
                        INSERT INTO usuarios (nome, email, telefone, senha_hash, role, ativo)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$nome, $email, $telefone, $senha_hash, $role, $ativo]);
                    
                    logAuditoria('usuario', $db->lastInsertId(), 'CRIAR', null, ['email' => $email, 'role' => $role]);
                    $mensagem = 'Usuário criado com sucesso!';
                } catch (PDOException $e) {
                    if ($e->getCode() == 23000) {
                        $mensagem = 'Email já cadastrado';
                    } else {
                        $mensagem = 'Erro ao criar usuário: ' . $e->getMessage();
                    }
                }
            } else {
                $mensagem = 'Dados inválidos';
            }
        } elseif ($_POST['acao'] === 'editar') {
            $id = intval($_POST['id'] ?? 0);
            $nome = sanitizeInput($_POST['nome'] ?? '');
            $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
            $telefone = sanitizeInput($_POST['telefone'] ?? '');
            $role = $_POST['role'] ?? 'HOSPEDE';
            $ativo = isset($_POST['ativo']) ? 1 : 0;
            $senha = $_POST['senha'] ?? '';
            
            $stmt = $db->prepare("SELECT * FROM usuarios WHERE id = ?");
            $stmt->execute([$id]);
            $usuarioAntes = $stmt->fetch();
            
            if ($senha && strlen($senha) >= PASSWORD_MIN_LENGTH) {
                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                $stmt = $db->prepare("
                    UPDATE usuarios 
                    SET nome = ?, email = ?, telefone = ?, senha_hash = ?, role = ?, ativo = ?
                    WHERE id = ?
                ");
                $stmt->execute([$nome, $email, $telefone, $senha_hash, $role, $ativo, $id]);
            } else {
                $stmt = $db->prepare("
                    UPDATE usuarios 
                    SET nome = ?, email = ?, telefone = ?, role = ?, ativo = ?
                    WHERE id = ?
                ");
                $stmt->execute([$nome, $email, $telefone, $role, $ativo, $id]);
            }
            
            logAuditoria('usuario', $id, 'ATUALIZAR', 
                ['email' => $usuarioAntes['email'], 'role' => $usuarioAntes['role']],
                ['email' => $email, 'role' => $role]);
            $mensagem = 'Usuário atualizado com sucesso!';
        }
    }
}

// Buscar usuários
$stmt = $db->query("SELECT * FROM usuarios ORDER BY nome ASC");
$usuarios = $stmt->fetchAll();

// Usuário para edição
$usuarioEdit = null;
if (isset($_GET['editar'])) {
    $id = intval($_GET['editar']);
    $stmt = $db->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);
    $usuarioEdit = $stmt->fetch();
}

$roles = [
    'ADMIN' => 'Administrador',
    'RECEPCAO' => 'Recepção',
    'FINANCEIRO' => 'Financeiro',
    'HOSPEDE' => 'Hóspede'
];

?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Usuários | Hotel Mucinga Nzambi</title>
    
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
                    <i class="bi bi-people"></i> Gestão de Usuários
                </h2>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#usuarioModal" onclick="resetForm()">
                    <i class="bi bi-plus-circle"></i> Novo Usuário
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
                            <th>Email</th>
                            <th>Telefone</th>
                            <th>Perfil</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($usuario['nome']) ?></strong></td>
                                <td><?= htmlspecialchars($usuario['email']) ?></td>
                                <td><?= htmlspecialchars($usuario['telefone'] ?? '-') ?></td>
                                <td>
                                    <span class="badge bg-info"><?= $roles[$usuario['role']] ?? $usuario['role'] ?></span>
                                </td>
                                <td>
                                    <?php if ($usuario['ativo']): ?>
                                        <span class="badge bg-success">Ativo</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inativo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary" 
                                            onclick="editarUsuario(<?= $usuario['id'] ?>)">
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
    
    <!-- Modal Criar/Editar Usuário -->
    <div class="modal fade" id="usuarioModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?= $usuarioEdit ? 'Editar' : 'Novo' ?> Usuário</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="acao" value="<?= $usuarioEdit ? 'editar' : 'criar' ?>">
                        <?php if ($usuarioEdit): ?>
                            <input type="hidden" name="id" value="<?= $usuarioEdit['id'] ?>">
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nome <span class="text-danger">*</span></label>
                            <input type="text" name="nome" class="form-control" 
                                   value="<?= $usuarioEdit ? htmlspecialchars($usuarioEdit['nome']) : '' ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" 
                                   value="<?= $usuarioEdit ? htmlspecialchars($usuarioEdit['email']) : '' ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Telefone</label>
                            <input type="tel" name="telefone" class="form-control" 
                                   value="<?= $usuarioEdit ? htmlspecialchars($usuarioEdit['telefone'] ?? '') : '' ?>" 
                                   placeholder="+244 923 456 789">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold"><?= $usuarioEdit ? 'Nova ' : '' ?>Senha 
                                <?= $usuarioEdit ? '<small class="text-muted">(deixe em branco para manter a atual)</small>' : '' ?>
                                <span class="text-danger"><?= $usuarioEdit ? '' : '*' ?></span>
                            </label>
                            <input type="password" name="senha" class="form-control" 
                                   <?= $usuarioEdit ? '' : 'required' ?>
                                   minlength="<?= PASSWORD_MIN_LENGTH ?>">
                            <?php if (!$usuarioEdit): ?>
                                <small class="text-muted">Mínimo de <?= PASSWORD_MIN_LENGTH ?> caracteres</small>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Perfil <span class="text-danger">*</span></label>
                            <select name="role" class="form-select" required>
                                <?php foreach ($roles as $key => $label): ?>
                                    <option value="<?= $key ?>" 
                                            <?= ($usuarioEdit && $usuarioEdit['role'] === $key) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="ativo" id="ativo" 
                                       <?= ($usuarioEdit && $usuarioEdit['ativo']) ? 'checked' : 'checked' ?>>
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
            document.querySelector('#usuarioModal form').reset();
            document.querySelector('input[name="acao"]').value = 'criar';
        }
        
        function editarUsuario(id) {
            window.location.href = '?editar=' + id;
        }
    </script>
</body>
</html>

