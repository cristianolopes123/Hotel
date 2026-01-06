<?php
/**
 * Funções Auxiliares
 * Hotel Mucinga Nzambi
 */

if (!defined('SYSTEM_ACCESS')) {
    require_once __DIR__ . '/config.php';
    require_once __DIR__ . '/db.php';
    require_once __DIR__ . '/auth.php';
}

/**
 * Gera código de referência único
 */
function gerarCodigoReserva() {
    $db = getDB();
    $ano = date('Y');
    $prefixo = CODIGO_PREFIXO;
    
    // Buscar último código do ano
    $stmt = $db->prepare("SELECT codigo FROM reservas WHERE codigo LIKE ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$prefixo . '-' . $ano . '-%']);
    $ultimo = $stmt->fetch();
    
    if ($ultimo) {
        // Extrair número sequencial
        $parts = explode('-', $ultimo['codigo']);
        $sequencial = intval(end($parts)) + 1;
    } else {
        $sequencial = 1;
    }
    
    $codigo = sprintf('%s-%s-%06d', $prefixo, $ano, $sequencial);
    
    // Garantir unicidade
    $stmt = $db->prepare("SELECT id FROM reservas WHERE codigo = ?");
    $stmt->execute([$codigo]);
    if ($stmt->fetch()) {
        // Se já existe, incrementar
        $sequencial++;
        $codigo = sprintf('%s-%s-%06d', $prefixo, $ano, $sequencial);
    }
    
    return $codigo;
}

/**
 * Calcula número de noites
 */
function calcularNoites($checkin, $checkout) {
    $inicio = new DateTime($checkin);
    $fim = new DateTime($checkout);
    return $inicio->diff($fim)->days;
}

/**
 * Busca tarifa vigente para tipo de quarto
 */
function buscarTarifa($tipoQuartoId, $checkin) {
    $db = getDB();
    
    $stmt = $db->prepare("
        SELECT preco_noite 
        FROM tarifas 
        WHERE tipo_quarto_id = ? 
        AND inicio <= ? 
        AND fim >= ? 
        ORDER BY inicio DESC 
        LIMIT 1
    ");
    $stmt->execute([$tipoQuartoId, $checkin, $checkin]);
    $tarifa = $stmt->fetch();
    
    return $tarifa ? floatval($tarifa['preco_noite']) : 0;
}

/**
 * Calcula total da reserva
 */
function calcularTotalReserva($tipoQuartoId, $checkin, $checkout, $servicosIds = [], $desconto = 0, $taxas = 0) {
    $noites = calcularNoites($checkin, $checkout);
    $tarifaNoite = buscarTarifa($tipoQuartoId, $checkin);
    $totalBruto = $tarifaNoite * $noites;
    
    // Adicionar serviços
    $totalServicos = 0;
    if (!empty($servicosIds)) {
        $db = getDB();
        $placeholders = str_repeat('?,', count($servicosIds) - 1) . '?';
        $stmt = $db->prepare("SELECT preco, unidade FROM servicos WHERE id IN ($placeholders) AND ativo = 1");
        $stmt->execute($servicosIds);
        $servicos = $stmt->fetchAll();
        
        foreach ($servicos as $servico) {
            if ($servico['unidade'] === 'POR_NOITE') {
                $totalServicos += $servico['preco'] * $noites;
            } else {
                $totalServicos += $servico['preco'];
            }
        }
    }
    
    $total = $totalBruto + $totalServicos - $desconto + $taxas;
    
    return [
        'total_bruto' => $totalBruto,
        'total_servicos' => $totalServicos,
        'desconto' => $desconto,
        'taxas' => $taxas,
        'total_liquido' => max(0, $total)
    ];
}

/**
 * Verifica disponibilidade de quarto
 */
function verificarDisponibilidade($tipoQuartoId, $checkin, $checkout, $quartoId = null) {
    $db = getDB();
    
    // Buscar quartos do tipo disponíveis
    if ($quartoId) {
        $stmt = $db->prepare("
            SELECT q.id 
            FROM quartos q 
            WHERE q.id = ? 
            AND q.tipo_quarto_id = ? 
            AND q.status = 'ATIVO'
            AND q.status_ocupacao = 'DISPONIVEL'  -- CONDIÇÃO ADICIONADA: QUARTO DEVE ESTAR DISPONÍVEL
        ");
        $stmt->execute([$quartoId, $tipoQuartoId]);
        $quartos = $stmt->fetchAll();
    } else {
        $stmt = $db->prepare("
            SELECT q.id 
            FROM quartos q 
            WHERE q.tipo_quarto_id = ? 
            AND q.status = 'ATIVO'
            AND q.status_ocupacao = 'DISPONIVEL'  -- CONDIÇÃO ADICIONADA: QUARTO DEVE ESTAR DISPONÍVEL
        ");
        $stmt->execute([$tipoQuartoId]);
        $quartos = $stmt->fetchAll();
    }
    
    if (empty($quartos)) {
        return false;
    }
    
    // Verificar reservas que conflitam
    foreach ($quartos as $quarto) {
        $stmt = $db->prepare("
            SELECT 1 
            FROM reservas r 
            WHERE r.quarto_id = ? 
            AND r.status IN ('EM_ANALISE', 'CONFIRMADA', 'CHECKIN_REALIZADO')
            AND NOT (r.checkout <= ? OR r.checkin >= ?)
            LIMIT 1
        ");
        $stmt->execute([$quarto['id'], $checkin, $checkout]);
        
        if (!$stmt->fetch()) {
            return $quarto['id']; // Retorna ID do quarto disponível
        }
    }
    
    return false;
}

/**
 * Formata moeda
 */
function formatarMoeda($valor) {
    return number_format($valor, 2, ',', '.') . ' Kz';
}

/**
 * Formata data
 */
function formatarData($data, $formato = 'd/m/Y') {
    if (empty($data)) return '';
    return date($formato, strtotime($data));
}

/**
 * Status badge HTML
 */
function statusBadge($status) {
    $badges = [
        'PENDENTE_COMPROVANTE' => '<span class="badge bg-warning text-dark">Pendente Comprovante</span>',
        'EM_ANALISE' => '<span class="badge bg-info">Em Análise</span>',
        'CONFIRMADA' => '<span class="badge bg-success">Confirmada</span>',
        'RECUSADA' => '<span class="badge bg-danger">Recusada</span>',
        'CHECKIN_REALIZADO' => '<span class="badge bg-primary">Check-in Realizado</span>',
        'CHECKOUT_REALIZADO' => '<span class="badge bg-secondary">Check-out Realizado</span>',
        'CANCELADA' => '<span class="badge bg-dark">Cancelada</span>'
    ];
    
    return $badges[$status] ?? '<span class="badge bg-secondary">' . $status . '</span>';
}

/**
 * Gera token CSRF
 */
function gerarCSRFToken() {
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Valida token CSRF
 */
function validarCSRFToken($token) {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * Sanitiza input
 */
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Log de auditoria
 */
function logAuditoria($entidade, $entidadeId, $acao, $antes = null, $depois = null, $usuarioId = null, $ip = null) {
    $db = getDB();
    
    if ($usuarioId === null) {
        $usuario = Auth::getUser();
        $usuarioId = $usuario ? $usuario['id'] : null;
    }
    
    if ($ip === null) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    $antesJson = $antes ? json_encode($antes) : null;
    $depoisJson = $depois ? json_encode($depois) : null;
    
    $stmt = $db->prepare("
        INSERT INTO auditoria (entidade, entidade_id, acao, antes, depois, usuario_id, ip)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([$entidade, $entidadeId, $acao, $antesJson, $depoisJson, $usuarioId, $ip]);
}

// ============================================
// NOVAS FUNÇÕES PARA CONTROLE DE STATUS DOS QUARTOS
// Adicionadas em 2025-12-30 para sincronização automática
// NENHUMA MODIFICAÇÃO FOI FEITA NO CÓDIGO EXISTENTE
// ============================================

/**
 * Atualiza status de ocupação de um quarto
 * Status possíveis: DISPONIVEL, RESERVADO, OCUPADO, MANUTENCAO
 * 
 * @param int $quarto_id ID do quarto
 * @param string $novo_status DISPONIVEL/RESERVADO/OCUPADO/MANUTENCAO
 * @return bool Sucesso da operação
 */
function atualizarStatusQuarto($quarto_id, $novo_status) {
    // Validação básica dos parâmetros
    if (empty($quarto_id) || empty($novo_status)) {
        return false;
    }
    
    // Lista de status válidos baseado no seu sistema
    $status_validos = ['DISPONIVEL', 'RESERVADO', 'OCUPADO', 'MANUTENCAO'];
    
    if (!in_array(strtoupper($novo_status), $status_validos)) {
        return false;
    }
    
    $db = getDB();
    
    try {
        $stmt = $db->prepare("
            UPDATE quartos 
            SET status_ocupacao = ?, 
                atualizado_em = NOW() 
            WHERE id = ?
        ");
        
        $resultado = $stmt->execute([strtoupper($novo_status), $quarto_id]);
        
        // Log de auditoria para rastreamento
        if ($resultado) {
            logAuditoria('quarto', $quarto_id, 'ATUALIZAR_STATUS', null, ['novo_status' => $novo_status]);
        }
        
        return $resultado;
    } catch (Exception $e) {
        // Em caso de erro, retorna false sem interromper o fluxo
        return false;
    }
}

/**
 * Verifica se um quarto está disponível para reserva em um período específico
 * Considera: status_ocupacao = DISPONIVEL E sem reservas conflitantes
 * Compatível com a função existente verificarDisponibilidade()
 * 
 * @param int $quarto_id ID do quarto
 * @param string $data_inicio Data de check-in (formato YYYY-MM-DD)
 * @param string $data_fim Data de check-out (formato YYYY-MM-DD)
 * @return bool True se disponível, False se ocupado/reservado
 */
function verificarDisponibilidadeQuarto($quarto_id, $data_inicio, $data_fim) {
    // Validação básica dos parâmetros
    if (empty($quarto_id) || empty($data_inicio) || empty($data_fim)) {
        return false;
    }
    
    $db = getDB();
    
    try {
        // PRIMEIRO: Verificar se o quarto está com status DISPONIVEL
        $stmt = $db->prepare("
            SELECT 1 
            FROM quartos 
            WHERE id = ? 
            AND status = 'ATIVO' 
            AND status_ocupacao = 'DISPONIVEL'
            LIMIT 1
        ");
        $stmt->execute([$quarto_id]);
        
        if (!$stmt->fetch()) {
            // Quarto não está disponível (pode estar RESERVADO, OCUPADO ou em MANUTENCAO)
            return false;
        }
        
        // SEGUNDO: Verificar se há reservas conflitantes no período
        // Baseado na função verificarDisponibilidade() existente
        $stmt = $db->prepare("
            SELECT 1 
            FROM reservas 
            WHERE quarto_id = ? 
            AND status IN ('EM_ANALISE', 'CONFIRMADA', 'CHECKIN_REALIZADO')
            AND NOT (checkout <= ? OR checkin >= ?)
            LIMIT 1
        ");
        $stmt->execute([$quarto_id, $data_inicio, $data_fim]);
        
        // Se encontrou uma reserva conflitante, retorna false
        if ($stmt->fetch()) {
            return false;
        }
        
        // Se passou em todas as verificações, o quarto está disponível
        return true;
        
    } catch (Exception $e) {
        // Em caso de erro, considera como não disponível (abordagem conservadora)
        return false;
    }
}

// ============================================
// FIM DAS NOVAS FUNÇÕES
// ============================================
?>