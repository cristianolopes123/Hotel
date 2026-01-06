<?php
/**
 * Sistema de Autenticação ATUALIZADO (CORRIGIDO)
 * Hotel Mucinga Nzambi
 *
 * Correções aplicadas (sem mudanças desnecessárias):
 * ✅ Removeu "dica" de senha (segurança)
 * ✅ Removeu logs com senha em texto (segurança)
 * ✅ Evitou session_start duplicado (causa warnings no config)
 * ✅ Normaliza ROLE (trim + strtoupper) para evitar falhas no redirect do admin/recepção
 * ✅ Mantém conversão automática para hash PHP (texto plano e hash Laravel comum)
 */

if (!defined('SYSTEM_ACCESS')) {
    require_once __DIR__ . '/config.php';
    require_once __DIR__ . '/db.php';
}

class Auth {

    /**
     * VERIFICAÇÃO INTELIGENTE DE SENHA
     * Aceita: texto plano, hash PHP, hash Laravel
     * Converte automaticamente para hash PHP
     */
    private static function verificarSenhaInteligente($senha_texto, $hash_banco, $usuario_id, $usuario_email) {
        $db = getDB();

        // Debug seguro (NUNCA logar senha)
        error_log("=== VERIFICAÇÃO SENHA ===");
        error_log("Usuário: $usuario_email (ID: $usuario_id)");
        error_log("Hash no banco (início): " . substr((string)$hash_banco, 0, 30));
        error_log("Tamanho hash: " . strlen((string)$hash_banco));

        // Garantir string
        $hash_banco = (string)$hash_banco;

        // 1. Se já é hash PHP válido, verifica normalmente
        if (password_verify($senha_texto, $hash_banco)) {
            error_log("✅ Login via hash PHP");
            return true;
        }

        // 2. Se for senha em TEXTO PLANO (igual ao que está no banco)
        if ($hash_banco === $senha_texto) {
            error_log("⚠️ Senha em texto plano detectada - CONVERTENDO para hash");

            $novo_hash = password_hash($senha_texto, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE usuarios SET senha_hash = ?, atualizado_em = NOW() WHERE id = ?");
            $stmt->execute([$novo_hash, $usuario_id]);

            error_log("✅ Senha convertida para hash PHP - Usuário: $usuario_email");
            return true;
        }

        // 3. Hash do Laravel mais comum (seed) - senha: "password"
        $hash_laravel_password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
        if ($hash_banco === $hash_laravel_password && $senha_texto === 'password') {
            error_log("⚠️ Hash Laravel detectado (password) - CONVERTENDO para hash PHP");

            $novo_hash = password_hash('password', PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE usuarios SET senha_hash = ?, atualizado_em = NOW() WHERE id = ?");
            $stmt->execute([$novo_hash, $usuario_id]);

            error_log("✅ Hash Laravel convertido - Usuário: $usuario_email");
            return true;
        }

        // 4. Testar hashes comuns do Laravel
        $senhas_laravel_comuns = [
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
            'secret'   => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm'
        ];

        foreach ($senhas_laravel_comuns as $senha_teste => $hash_teste) {
            if ($hash_banco === $hash_teste && $senha_texto === $senha_teste) {
                error_log("⚠️ Hash Laravel comum detectado ($senha_teste) - CONVERTENDO");

                $novo_hash = password_hash($senha_teste, PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE usuarios SET senha_hash = ?, atualizado_em = NOW() WHERE id = ?");
                $stmt->execute([$novo_hash, $usuario_id]);

                error_log("✅ Hash convertido - Usuário: $usuario_email");
                return true;
            }
        }

        error_log("❌ Nenhum método de verificação funcionou");
        return false;
    }

    /**
     * Login de usuário ATUALIZADO
     */
    public static function login($email, $senha) {
        $email = trim($email);

        // ⚠️ Não fazer trim na senha
        $senha = (string)$senha;

        if (empty($email) || $senha === '') {
            return ['success' => false, 'message' => 'Por favor, preencha todos os campos'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Email inválido'];
        }

        $db = getDB();

        // Busca exata
        $stmt = $db->prepare("SELECT id, nome, email, telefone, senha_hash, role, ativo FROM usuarios WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        // Busca case-insensitive
        if (!$usuario || empty($usuario)) {
            $stmt = $db->prepare("SELECT id, nome, email, telefone, senha_hash, role, ativo FROM usuarios WHERE LOWER(TRIM(email)) = LOWER(TRIM(?)) LIMIT 1");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        if (!$usuario || empty($usuario)) {
            error_log('Login Failed: User not found for email: ' . $email);
            return ['success' => false, 'message' => 'Email não encontrado. Verifique se digitou corretamente.'];
        }

        if (!isset($usuario['senha_hash']) || $usuario['senha_hash'] === '' || $usuario['senha_hash'] === null) {
            error_log('Login Error: User ' . $usuario['id'] . ' has empty password hash');
            return ['success' => false, 'message' => 'Erro na conta. Entre em contato com o suporte.'];
        }

        if (!isset($usuario['ativo']) || (int)$usuario['ativo'] !== 1) {
            error_log('Login Failed: Account deactivated for email: ' . $email);
            return ['success' => false, 'message' => 'Sua conta está desativada. Entre em contato com o suporte.'];
        }

        // ✅ VERIFICAÇÃO INTELIGENTE
        $senha_verificada = self::verificarSenhaInteligente(
            $senha,
            $usuario['senha_hash'],
            (int)$usuario['id'],
            $usuario['email']
        );

        if (!$senha_verificada) {
            error_log('Login Failed: All password methods failed for email: ' . $email);
            return ['success' => false, 'message' => 'Email ou senha incorretos.'];
        }

        // ✅ Sessão (evitar duplicado)
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // ✅ NORMALIZAR ROLE (isso é essencial para ADMIN/RECEPCAO/FINANCEIRO redirecionar certo)
        $role_normalizado = strtoupper(trim((string)($usuario['role'] ?? 'HOSPEDE')));

        $_SESSION['user_id'] = (int)$usuario['id'];
        $_SESSION['user_nome'] = $usuario['nome'];
        $_SESSION['user_email'] = $usuario['email'];
        $_SESSION['user_telefone'] = $usuario['telefone'] ?? '';
        $_SESSION['user_role'] = $role_normalizado;
        $_SESSION['logged_in'] = true;
        $_SESSION['last_activity'] = time();

        return [
            'success' => true,
            'user' => [
                'id' => (int)$usuario['id'],
                'nome' => $usuario['nome'],
                'email' => $usuario['email'],
                'telefone' => $usuario['telefone'] ?? '',
                'role' => $role_normalizado
            ]
        ];
    }

    /**
     * Logout de usuário
     */
    public static function logout() {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $_SESSION = array();

        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 42000, '/');
        }

        session_destroy();

        return true;
    }

    /**
     * Verifica se usuário está logado
     */
    public static function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    /**
     * Retorna dados do usuário logado
     */
    public static function getUser() {
        if (!self::isLoggedIn()) {
            return null;
        }

        return [
            'id' => $_SESSION['user_id'] ?? null,
            'nome' => $_SESSION['user_nome'] ?? null,
            'email' => $_SESSION['user_email'] ?? null,
            'telefone' => $_SESSION['user_telefone'] ?? null,
            'role' => $_SESSION['user_role'] ?? null
        ];
    }

    /**
     * Registro de novo usuário (hóspede)
     */
    public static function register($nome, $email, $telefone, $senha) {
        $db = getDB();

        $stmt = $db->prepare("SELECT id FROM usuarios WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Email já cadastrado'];
        }

        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

        try {
            $stmt = $db->prepare("INSERT INTO usuarios (nome, email, telefone, senha_hash, role) VALUES (?, ?, ?, ?, 'HOSPEDE')");
            $stmt->execute([$nome, $email, $telefone, $senha_hash]);

            $userId = $db->lastInsertId();

            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }

            $_SESSION['user_id'] = (int)$userId;
            $_SESSION['user_nome'] = $nome;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_telefone'] = $telefone;
            $_SESSION['user_role'] = 'HOSPEDE';
            $_SESSION['logged_in'] = true;

            error_log("✅ Novo usuário registrado: $email (senha convertida para hash)");

            return ['success' => true, 'user_id' => (int)$userId];
        } catch (PDOException $e) {
            error_log('Erro registro: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Erro ao registrar usuário'];
        }
    }

    /**
     * Atualizar senha
     */
    public static function updatePassword($userId, $novaSenha) {
        $db = getDB();

        $senha_hash = password_hash($novaSenha, PASSWORD_DEFAULT);

        $stmt = $db->prepare("UPDATE usuarios SET senha_hash = ?, atualizado_em = NOW() WHERE id = ?");
        return $stmt->execute([$senha_hash, $userId]);
    }

    /**
     * Cadastrar usuário pelo painel admin
     */
    public static function cadastrarUsuarioAdmin($nome, $email, $telefone, $senha, $role = 'HOSPEDE') {
        $db = getDB();

        $stmt = $db->prepare("SELECT id FROM usuarios WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Email já cadastrado'];
        }

        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

        // ✅ normalizar role antes de gravar (evita "Recepção" vs "RECEPCAO")
        $role = strtoupper(trim((string)$role));

        try {
            $stmt = $db->prepare("INSERT INTO usuarios (nome, email, telefone, senha_hash, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nome, $email, $telefone, $senha_hash, $role]);

            $userId = $db->lastInsertId();

            error_log("✅ Usuário admin cadastrado: $email (role: $role)");

            return [
                'success' => true,
                'message' => 'Usuário cadastrado com sucesso!',
                'user_id' => (int)$userId
            ];
        } catch (PDOException $e) {
            error_log('Erro cadastro admin: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Erro: ' . $e->getMessage()];
        }
    }

    /**
     * Resetar senha para texto (para testes)
     * (mantido, mas use apenas em DEV)
     */
    public static function resetarSenhaParaTexto($email, $novaSenhaTexto) {
        $db = getDB();

        $stmt = $db->prepare("UPDATE usuarios SET senha_hash = ?, atualizado_em = NOW() WHERE email = ?");
        $result = $stmt->execute([$novaSenhaTexto, $email]);

        if ($result) {
            error_log("✅ Senha resetada para texto: $email");
            return ['success' => true, 'message' => 'Senha resetada. Será convertida no próximo login.'];
        }

        return ['success' => false, 'message' => 'Usuário não encontrado'];
    }
}
