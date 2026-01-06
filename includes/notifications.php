<?php
/**
 * Sistema de Notificações
 * Hotel Mucinga Nzambi
 */

if (!defined('SYSTEM_ACCESS')) {
    require_once __DIR__ . '/config.php';
    require_once __DIR__ . '/db.php';
    require_once __DIR__ . '/helpers.php';
}

class Notifications {
    
    /**
     * Envia notificação por email
     */
    public static function sendEmail($to, $subject, $message, $html = true) {
        // Verificar se SMTP está configurado
        if (SMTP_USER === 'seu-email@gmail.com' || empty(SMTP_USER)) {
            error_log("SMTP não configurado para enviar email para: {$to}");
            return false;
        }
        
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: " . ($html ? "text/html" : "text/plain") . "; charset=UTF-8\r\n";
        $headers .= "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM . ">\r\n";
        $headers .= "Reply-To: " . SMTP_FROM . "\r\n";
        
        // Para usar SMTP real, você precisaria de uma biblioteca como PHPMailer
        // Por enquanto, usamos mail() do PHP
        return @mail($to, $subject, $message, $headers);
    }
    
    /**
     * Envia notificação por WhatsApp (se configurado)
     */
    public static function sendWhatsApp($telefone, $mensagem) {
        if (empty(WHATSAPP_API) || empty($telefone)) {
            return false;
        }
        
        // Implementação depende da API de WhatsApp escolhida
        // Exemplo com API REST (ajuste conforme sua API)
        $url = WHATSAPP_API;
        $data = [
            'phone' => $telefone,
            'message' => $mensagem
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return $response !== false;
    }
    
    /**
     * Notificar criação de reserva
     */
    public static function notificarCriacaoReserva($reservaId) {
        $db = getDB();
        
        $stmt = $db->prepare("
            SELECT r.*, tq.nome as tipo_quarto_nome
            FROM reservas r
            LEFT JOIN tipos_quarto tq ON r.tipo_quarto_id = tq.id
            WHERE r.id = ?
        ");
        $stmt->execute([$reservaId]);
        $reserva = $stmt->fetch();
        
        if (!$reserva) return false;
        
        $subject = "Reserva Criada - " . $reserva['codigo'];
        $message = self::templateReservaCriada($reserva);
        
        // Enviar por email
        self::sendEmail($reserva['email'], $subject, $message);
        
        // Enviar por WhatsApp se tiver telefone
        if (!empty($reserva['telefone'])) {
            $whatsappMsg = self::templateReservaCriadaWhatsApp($reserva);
            self::sendWhatsApp($reserva['telefone'], $whatsappMsg);
        }
        
        return true;
    }
    
    /**
     * Notificar envio de comprovante
     */
    public static function notificarComprovanteEnviado($reservaId) {
        $db = getDB();
        
        $stmt = $db->prepare("SELECT * FROM reservas WHERE id = ?");
        $stmt->execute([$reservaId]);
        $reserva = $stmt->fetch();
        
        if (!$reserva) return false;
        
        $subject = "Comprovante Recebido - " . $reserva['codigo'];
        $message = self::templateComprovanteRecebido($reserva);
        
        self::sendEmail($reserva['email'], $subject, $message);
        
        if (!empty($reserva['telefone'])) {
            $whatsappMsg = "O comprovante da reserva {$reserva['codigo']} foi recebido e está em análise. Em breve retornaremos.";
            self::sendWhatsApp($reserva['telefone'], $whatsappMsg);
        }
        
        return true;
    }
    
    /**
     * Notificar confirmação de reserva
     */
    public static function notificarConfirmacaoReserva($reservaId) {
        $db = getDB();
        
        $stmt = $db->prepare("
            SELECT r.*, q.numero as quarto_numero, tq.nome as tipo_quarto_nome
            FROM reservas r
            LEFT JOIN quartos q ON r.quarto_id = q.id
            LEFT JOIN tipos_quarto tq ON r.tipo_quarto_id = tq.id
            WHERE r.id = ?
        ");
        $stmt->execute([$reservaId]);
        $reserva = $stmt->fetch();
        
        if (!$reserva) return false;
        
        $subject = "Reserva Confirmada - " . $reserva['codigo'];
        $message = self::templateReservaConfirmada($reserva);
        
        self::sendEmail($reserva['email'], $subject, $message);
        
        if (!empty($reserva['telefone'])) {
            $whatsappMsg = "🎉 Sua reserva {$reserva['codigo']} foi CONFIRMADA. Apresente este código no check-in. Quarto: " . ($reserva['quarto_numero'] ?? 'A definir') . ". Boa estadia!";
            self::sendWhatsApp($reserva['telefone'], $whatsappMsg);
        }
        
        return true;
    }
    
    /**
     * Notificar recusa de reserva
     */
    public static function notificarRecusaReserva($reservaId) {
        $db = getDB();
        
        $stmt = $db->prepare("SELECT * FROM reservas WHERE id = ?");
        $stmt->execute([$reservaId]);
        $reserva = $stmt->fetch();
        
        if (!$reserva) return false;
        
        $motivo = $reserva['motivo_recusa'] ?? 'Não especificado';
        
        $subject = "Reserva Recusada - " . $reserva['codigo'];
        $message = self::templateReservaRecusada($reserva, $motivo);
        
        self::sendEmail($reserva['email'], $subject, $message);
        
        if (!empty($reserva['telefone'])) {
            $whatsappMsg = "⚠ Reserva {$reserva['codigo']} – comprovante recusado. Motivo: {$motivo}. Reenvie, por favor.";
            self::sendWhatsApp($reserva['telefone'], $whatsappMsg);
        }
        
        return true;
    }
    
    // Templates de Email
    private static function templateReservaCriada($reserva) {
        $checkin = formatarData($reserva['checkin']);
        $checkout = formatarData($reserva['checkout']);
        
        return "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #005051; color: #fff; padding: 20px; text-align: center; }
                .content { background: #f9f9f9; padding: 20px; }
                .code { background: #F28D00; color: #000; padding: 15px; text-align: center; font-size: 24px; font-weight: bold; margin: 20px 0; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Hotel Mucinga Nzambi</h2>
                </div>
                <div class='content'>
                    <p>Olá, <strong>{$reserva['nome_cliente']}</strong>!</p>
                    <p>Recebemos sua solicitação de reserva.</p>
                    <div class='code'>{$reserva['codigo']}</div>
                    <p><strong>Detalhes da Reserva:</strong></p>
                    <ul>
                        <li>Quarto: {$reserva['tipo_quarto_nome']}</li>
                        <li>Check-in: {$checkin}</li>
                        <li>Check-out: {$checkout}</li>
                        <li>Total: " . formatarMoeda($reserva['total_liquido']) . "</li>
                    </ul>
                    <p>Pague via transferência bancária (dados no site) e envie o comprovante.</p>
                    <p>Qualquer dúvida, fale conosco: " . SYSTEM_PHONE . "</p>
                </div>
                <div class='footer'>
                    <p>Hotel Mucinga Nzambi © " . date('Y') . "</p>
                </div>
            </div>
        </body>
        </html>";
    }
    
    private static function templateReservaCriadaWhatsApp($reserva) {
        return "Olá, {$reserva['nome_cliente']}! Recebemos sua solicitação de reserva {$reserva['codigo']} para " . formatarData($reserva['checkin']) . " a " . formatarData($reserva['checkout']) . ". Pague via transferência e envie o comprovante. Qualquer dúvida, fale conosco.";
    }
    
    private static function templateComprovanteRecebido($reserva) {
        return "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #005051; color: #fff; padding: 20px; text-align: center; }
                .content { background: #f9f9f9; padding: 20px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Hotel Mucinga Nzambi</h2>
                </div>
                <div class='content'>
                    <p>Olá, <strong>{$reserva['nome_cliente']}</strong>!</p>
                    <p>O comprovante da reserva <strong>{$reserva['codigo']}</strong> foi recebido e está em análise.</p>
                    <p>Em breve retornaremos com mais informações.</p>
                    <p>Obrigado!</p>
                </div>
            </div>
        </body>
        </html>";
    }
    
    private static function templateReservaConfirmada($reserva) {
        $quarto = $reserva['quarto_numero'] ? "Quarto {$reserva['quarto_numero']}" : "A definir";
        
        return "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #005051; color: #fff; padding: 20px; text-align: center; }
                .content { background: #f9f9f9; padding: 20px; }
                .code { background: #28a745; color: #fff; padding: 15px; text-align: center; font-size: 24px; font-weight: bold; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Hotel Mucinga Nzambi</h2>
                </div>
                <div class='content'>
                    <p>Olá, <strong>{$reserva['nome_cliente']}</strong>!</p>
                    <p><strong style='font-size: 18px; color: #28a745;'>🎉 Sua reserva foi CONFIRMADA!</strong></p>
                    <div class='code'>{$reserva['codigo']}</div>
                    <p><strong>Detalhes:</strong></p>
                    <ul>
                        <li>Quarto: {$quarto}</li>
                        <li>Check-in: " . formatarData($reserva['checkin']) . "</li>
                        <li>Check-out: " . formatarData($reserva['checkout']) . "</li>
                    </ul>
                    <p><strong>Apresente este código no check-in.</strong></p>
                    <p>Boa estadia!</p>
                </div>
            </div>
        </body>
        </html>";
    }
    
    private static function templateReservaRecusada($reserva, $motivo) {
        return "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #005051; color: #fff; padding: 20px; text-align: center; }
                .content { background: #f9f9f9; padding: 20px; }
                .alert { background: #dc3545; color: #fff; padding: 15px; border-radius: 5px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Hotel Mucinga Nzambi</h2>
                </div>
                <div class='content'>
                    <p>Olá, <strong>{$reserva['nome_cliente']}</strong>!</p>
                    <div class='alert'>
                        <strong>⚠ Reserva {$reserva['codigo']} – Comprovante Recusado</strong>
                    </div>
                    <p><strong>Motivo:</strong> {$motivo}</p>
                    <p>Por favor, reenvie o comprovante através do link da sua reserva.</p>
                    <p>Se tiver dúvidas, entre em contato: " . SYSTEM_PHONE . "</p>
                </div>
            </div>
        </body>
        </html>";
    }
}

