<?php
/**
 * Configuração do Sistema
 * Hotel Mucinga Nzambi
 */

// Prevenir acesso direto (mantém como tens)
if (!defined('SYSTEM_ACCESS')) {
    define('SYSTEM_ACCESS', true);
}

// ✅ Configurações de Sessão (ANTES de iniciar)
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // 1 em produção com HTTPS
    session_start();
}

// Configurações do Banco de Dados
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'hotel_mucinga');
define('DB_CHARSET', 'utf8mb4');

// Configurações do Sistema
define('SYSTEM_NAME', 'Hotel Mucinga Nzambi');
define('SYSTEM_EMAIL', 'reservas@hotelmucinga.ao');
define('SYSTEM_PHONE', '+244 923 456 789');
define('SYSTEM_ADDRESS', 'Rua Major Kanhangulo, 100, Ingombota, Luanda, Angola');

// URLs Base
define('BASE_URL', 'http://localhost/hotelagem/');
define('ADMIN_URL', BASE_URL . 'admin/');
define('INCLUDES_URL', BASE_URL . 'includes/');

// Diretórios
define('ROOT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('INCLUDES_PATH', ROOT_PATH . 'includes' . DIRECTORY_SEPARATOR);
define('ADMIN_PATH', ROOT_PATH . 'admin' . DIRECTORY_SEPARATOR);
define('UPLOAD_PATH', ROOT_PATH . 'uploads' . DIRECTORY_SEPARATOR . 'comprovantes' . DIRECTORY_SEPARATOR);
define('STORAGE_PATH', ROOT_PATH . 'storage' . DIRECTORY_SEPARATOR . 'pdfs' . DIRECTORY_SEPARATOR);
define('TCPDF_PATH', ROOT_PATH . 'tcpdf' . DIRECTORY_SEPARATOR);

// URLs de Upload
define('UPLOAD_URL', BASE_URL . 'uploads/comprovantes/');
define('STORAGE_URL', BASE_URL . 'storage/pdfs/');

// Configurações de Upload
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'pdf']);
define('MAX_FILES_PER_RESERVA', 3);

// Fuso Horário
date_default_timezone_set('Africa/Luanda');

// Configurações de Email (SMTP)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'seu-email@gmail.com');
define('SMTP_PASS', 'sua-senha');
define('SMTP_FROM', 'reservas@hotelmucinga.ao');
define('SMTP_FROM_NAME', 'Hotel Mucinga Nzambi');

// WhatsApp
define('WHATSAPP_API', '');
define('WHATSAPP_NUMBER', '+244923456789');

// Validade
define('COMPROVANTE_VALIDADE_HORAS', 48);

// Código
define('CODIGO_PREFIXO', 'HZ');
define('CODIGO_ANO', date('Y'));

// Segurança
define('CSRF_TOKEN_NAME', 'csrf_token');
define('PASSWORD_MIN_LENGTH', 8);

// Cores
define('COLOR_PRIMARY', '#005051');
define('COLOR_SECONDARY', '#F28D00');
define('COLOR_ACCENT', '#FFC107');

// Erros (desabilitar em produção)
error_reporting(E_ALL);
ini_set('display_errors', 1);
