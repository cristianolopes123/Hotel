<?php
/**
 * Role-Based Access Control (RBAC)
 * Hotel Mucinga Nzambi
 * 
 * Sistema de permissões baseado em papéis (roles)
 */

if (!defined('SYSTEM_ACCESS')) {
    require_once __DIR__ . '/config.php';
    require_once __DIR__ . '/auth.php';
}

class RBAC {
    /**
     * Matriz de permissões
     * Chave: Nome da permissão (em maiúsculas)
     * Valor: Array de roles que têm essa permissão
     */
    private static $permissions = [
        // Público / Reservas
        'VERIFICAR_DISPONIBILIDADE' => ['VISITANTE', 'HOSPEDE', 'RECEPCAO', 'FINANCEIRO', 'ADMIN'],
        'CRIAR_RESERVA'             => ['VISITANTE', 'HOSPEDE', 'RECEPCAO', 'FINANCEIRO', 'ADMIN'],
        'ENVIAR_COMPROVANTE'        => ['VISITANTE', 'HOSPEDE', 'RECEPCAO', 'FINANCEIRO', 'ADMIN'],
        'VER_STATUS_PROPRIO'        => ['HOSPEDE', 'RECEPCAO', 'FINANCEIRO', 'ADMIN'],
        'ACESSAR_MINHA_CONTA'       => ['HOSPEDE', 'RECEPCAO', 'FINANCEIRO', 'ADMIN'],

        // Painel Administrativo
        'VIEW_DASHBOARD'            => ['RECEPCAO', 'FINANCEIRO', 'ADMIN'],
        'ACESSAR_PAINEL'            => ['RECEPCAO', 'FINANCEIRO', 'ADMIN'],

        // Operações de Reserva
        'CONFIRMAR_RESERVA'         => ['RECEPCAO', 'FINANCEIRO', 'ADMIN'],
        'RECUSAR_RESERVA'           => ['RECEPCAO', 'FINANCEIRO', 'ADMIN'],
        'CANCELAR_RESERVA'          => ['RECEPCAO', 'FINANCEIRO', 'ADMIN'],
        'CHECKIN_CHECKOUT'          => ['RECEPCAO', 'ADMIN'],
        'GERENCIAR_RESERVAS'        => ['RECEPCAO', 'FINANCEIRO', 'ADMIN'],

        // Gestão de Conteúdo
        'GERIR_QUARTOS'             => ['ADMIN'],
        'GERIR_TARIFAS'             => ['ADMIN'],
        'GERIR_SERVICOS'            => ['ADMIN'],
        'GERIR_BANCOS'              => ['FINANCEIRO', 'ADMIN'],
        'GERIR_PAGAMENTOS'          => ['FINANCEIRO', 'ADMIN'],
        'GERIR_USUARIOS'            => ['ADMIN'],
        'GERIR_AVALIACOES'          => ['ADMIN'],
        'GERIR_CONFIGURACOES'       => ['ADMIN'],

        // Relatórios
        'VER_RELATORIOS'            => ['RECEPCAO', 'FINANCEIRO', 'ADMIN'],
        'GERAR_RELATORIOS'          => ['FINANCEIRO', 'ADMIN'],

        // Super Admin
        'GERIR_TUDO'                => ['ADMIN']
    ];

    /**
     * Verifica se o usuário atual tem uma determinada permissão
     * 
     * @param string $permission Nome da permissão (em maiúsculas)
     * @return bool True se o usuário tem a permissão, False caso contrário
     */
    public static function hasPermission($permission) {
        // Se a permissão não existe, nega por padrão
        if (!isset(self::$permissions[$permission])) {
            error_log("Permissão não encontrada: " . $permission);
            return false;
        }

        // Se for um ADMIN, tem todas as permissões
        if (self::isAdmin()) {
            return true;
        }

        // Obtém o usuário atual
        $user = Auth::getUser();
        
        // Se não estiver logado, verifica se a permissão está disponível para visitantes
        if (!$user) {
            return in_array('VISITANTE', self::$permissions[$permission], true);
        }

        // Verifica se a role do usuário está na lista de permissões
        $userRole = strtoupper(trim($user['role'] ?? 'VISITANTE'));
        return in_array($userRole, self::$permissions[$permission], true);
    }

    /**
     * Exige uma permissão específica. Se o usuário não tiver, redireciona.
     * 
     * @param string $permission Nome da permissão
     * @param string $redirectUrl URL para redirecionar em caso de falha
     * @param string $errorMessage Mensagem de erro opcional
     * @return void
     */
    public static function requirePermission($permission, $redirectUrl = 'login.php', $errorMessage = '') {
        if (!self::hasPermission($permission)) {
            if ($errorMessage) {
                $_SESSION['error_message'] = $errorMessage;
            }
            header('Location: ' . $redirectUrl);
            exit;
        }
    }

    /**
     * Verifica se o usuário atual é administrador
     * 
     * @return bool True se for admin, False caso contrário
     */
    public static function isAdmin() {
        $user = Auth::getUser();
        return $user && strtoupper(trim($user['role'] ?? '')) === 'ADMIN';
    }

    /**
     * Verifica se o usuário atual é da recepção
     * 
     * @return bool True se for recepção ou admin, False caso contrário
     */
    public static function isRecepcao() {
        $user = Auth::getUser();
        if (!$user) return false;
        
        $role = strtoupper(trim($user['role'] ?? ''));
        return $role === 'RECEPCAO' || $role === 'ADMIN';
    }

    /**
     * Verifica se o usuário atual é do financeiro
     * 
     * @return bool True se for financeiro ou admin, False caso contrário
     */
    public static function isFinanceiro() {
        $user = Auth::getUser();
        if (!$user) return false;
        
        $role = strtoupper(trim($user['role'] ?? ''));
        return $role === 'FINANCEIRO' || $role === 'ADMIN';
    }

    /**
     * Verifica se o usuário atual é hóspede
     * 
     * @return bool True se for hóspede, False caso contrário
     */
    public static function isHospede() {
        $user = Auth::getUser();
        return $user && strtoupper(trim($user['role'] ?? '')) === 'HOSPEDE';
    }

    /**
     * Obtém todas as permissões disponíveis no sistema
     * 
     * @return array Lista de permissões disponíveis
     */
    public static function getAllPermissions() {
        return array_keys(self::$permissions);
    }

    /**
     * Obtém todas as permissões de um determinado papel (role)
     * 
     * @param string $role Nome do papel (em maiúsculas)
     * @return array Lista de permissões do papel
     */
    public static function getPermissionsByRole($role) {
        $role = strtoupper(trim($role));
        $permissions = [];
        
        foreach (self::$permissions as $permission => $roles) {
            if (in_array($role, $roles, true)) {
                $permissions[] = $permission;
            }
        }
        
        return $permissions;
    }
}
