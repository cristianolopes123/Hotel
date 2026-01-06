<?php
/**
 * Sistema de Upload de Arquivos
 * Hotel Mucinga Nzambi
 */

if (!defined('SYSTEM_ACCESS')) {
    require_once __DIR__ . '/config.php';
    require_once __DIR__ . '/auth.php';
}

class UploadHandler {
    
    /**
     * Upload de comprovante
     */
    public static function uploadComprovante($file, $reservaId) {
        // Validar arquivo
        $validation = self::validateFile($file);
        if (!$validation['valid']) {
            return $validation;
        }
        
        // Criar diretório se não existir
        if (!file_exists(UPLOAD_PATH)) {
            mkdir(UPLOAD_PATH, 0755, true);
        }
        
        // Gerar nome único
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = uniqid('comprovante_' . $reservaId . '_', true) . '.' . $extension;
        $filePath = UPLOAD_PATH . $fileName;
        
        // Mover arquivo
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            $fileUrl = UPLOAD_URL . $fileName;
            
            // Salvar no banco
            $db = getDB();
            $usuario = Auth::getUser();
            $usuarioId = $usuario ? $usuario['id'] : null;
            
            $stmt = $db->prepare("
                INSERT INTO comprovantes (reserva_id, arquivo_path, mime_type, tamanho_bytes, enviado_por)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $reservaId,
                $fileUrl,
                $file['type'],
                $file['size'],
                $usuarioId
            ]);
            
            return [
                'success' => true,
                'file_path' => $fileUrl,
                'file_name' => $fileName,
                'comprovante_id' => $db->lastInsertId()
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Erro ao fazer upload do arquivo'
        ];
    }
    
    /**
     * Valida arquivo antes do upload
     */
    private static function validateFile($file) {
        // Verificar se há erro no upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return [
                'valid' => false,
                'message' => 'Erro no upload do arquivo'
            ];
        }
        
        // Verificar tamanho
        if ($file['size'] > MAX_UPLOAD_SIZE) {
            return [
                'valid' => false,
                'message' => 'Arquivo muito grande. Máximo: ' . (MAX_UPLOAD_SIZE / 1024 / 1024) . 'MB'
            ];
        }
        
        // Verificar extensão
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ALLOWED_EXTENSIONS)) {
            return [
                'valid' => false,
                'message' => 'Extensão não permitida. Use: ' . implode(', ', ALLOWED_EXTENSIONS)
            ];
        }
        
        // Verificar tipo MIME
        $allowedMimes = [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'application/pdf'
        ];
        
        if (!in_array($file['type'], $allowedMimes)) {
            return [
                'valid' => false,
                'message' => 'Tipo de arquivo não permitido'
            ];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Remove comprovante
     */
    public static function deleteComprovante($comprovanteId) {
        $db = getDB();
        
        // Buscar arquivo
        $stmt = $db->prepare("SELECT arquivo_path FROM comprovantes WHERE id = ?");
        $stmt->execute([$comprovanteId]);
        $comprovante = $stmt->fetch();
        
        if ($comprovante) {
            // Remover arquivo físico
            $filePath = str_replace(UPLOAD_URL, UPLOAD_PATH, $comprovante['arquivo_path']);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            
            // Remover do banco
            $stmt = $db->prepare("DELETE FROM comprovantes WHERE id = ?");
            $stmt->execute([$comprovanteId]);
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Busca comprovantes de uma reserva
     */
    public static function getComprovantes($reservaId) {
        $db = getDB();
        
        $stmt = $db->prepare("
            SELECT c.*, u.nome as enviado_por_nome
            FROM comprovantes c
            LEFT JOIN usuarios u ON c.enviado_por = u.id
            WHERE c.reserva_id = ?
            ORDER BY c.criado_em DESC
        ");
        $stmt->execute([$reservaId]);
        
        return $stmt->fetchAll();
    }
}

