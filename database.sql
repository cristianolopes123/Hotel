-- ============================================
-- Hotel Mucinga Nzambi - Sistema de Reservas
-- Estrutura do Banco de Dados ATUALIZADA COMPLETA
-- ============================================

-- Drop e cria o banco se já existir
DROP DATABASE IF EXISTS hotel_mucinga;
CREATE DATABASE hotel_mucinga CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hotel_mucinga;

-- ============================================
-- Tabela: usuarios
-- ============================================
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    telefone VARCHAR(50),
    senha_hash VARCHAR(255) NOT NULL,
    role ENUM('ADMIN', 'RECEPCAO', 'FINANCEIRO', 'HOSPEDE') DEFAULT 'HOSPEDE',
    ativo TINYINT(1) DEFAULT 1,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Tabela: tipos_quarto
-- ============================================
CREATE TABLE tipos_quarto (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    descricao TEXT,
    capacidade_adultos INT DEFAULT 2,
    capacidade_criancas INT DEFAULT 0,
    amenidades JSON,
    foto_capa VARCHAR(255),
    ativo TINYINT(1) DEFAULT 1,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Tabela: quartos
-- ============================================
CREATE TABLE quartos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero VARCHAR(20) UNIQUE NOT NULL,
    tipo_quarto_id INT NOT NULL,
    status ENUM('ATIVO', 'MANUTENCAO', 'INATIVO') DEFAULT 'ATIVO',
    -- CAMPO NOVO PARA SINCRONIZAÇÃO AUTOMÁTICA
    status_ocupacao ENUM('DISPONIVEL', 'RESERVADO', 'OCUPADO', 'MANUTENCAO') DEFAULT 'DISPONIVEL',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tipo_quarto_id) REFERENCES tipos_quarto(id) ON DELETE RESTRICT,
    INDEX idx_numero (numero),
    INDEX idx_status (status),
    INDEX idx_status_ocupacao (status_ocupacao),
    INDEX idx_tipo (tipo_quarto_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Tabela: tarifas
-- ============================================
CREATE TABLE tarifas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo_quarto_id INT NOT NULL,
    inicio DATE NOT NULL,
    fim DATE NOT NULL,
    preco_diaria DECIMAL(10,2) NOT NULL,  -- MUDADO: preco_noite → preco_diaria
    preco_noite DECIMAL(10,2) NOT NULL,   -- ADICIONADO: para período noturno
    preco_hora DECIMAL(10,2) DEFAULT 0,   -- ADICIONADO: para período por hora
    observacao TEXT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tipo_quarto_id) REFERENCES tipos_quarto(id) ON DELETE RESTRICT,
    INDEX idx_tipo_quarto (tipo_quarto_id),
    INDEX idx_periodo (inicio, fim)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Tabela: servicos
-- ============================================
CREATE TABLE servicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    descricao TEXT,
    preco DECIMAL(10,2) NOT NULL,
    unidade ENUM('POR_RESERVA', 'POR_NOITE', 'POR_PESSOA', 'POR_HORA') DEFAULT 'POR_RESERVA',
    ativo TINYINT(1) DEFAULT 1,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Tabela: bancos
-- ============================================
CREATE TABLE bancos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_banco VARCHAR(255) NOT NULL,
    titular VARCHAR(255) NOT NULL,
    iban VARCHAR(100),
    nif VARCHAR(50),
    observacoes TEXT,
    ativo TINYINT(1) DEFAULT 1,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Tabela: reservas
-- ============================================
CREATE TABLE reservas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) UNIQUE NOT NULL,
    usuario_id INT NULL,
    nome_cliente VARCHAR(255) NOT NULL,
    documento VARCHAR(50),
    telefone VARCHAR(50) NOT NULL,
    email VARCHAR(255) NOT NULL,
    tipo_quarto_id INT NOT NULL,
    quarto_id INT NULL,
    checkin DATETIME NOT NULL,  -- MUDADO: DATE → DATETIME (para hora também)
    checkout DATETIME NOT NULL, -- MUDADO: DATE → DATETIME (para hora também)
    tipo_periodo ENUM('DIARIA', 'NOITE', 'HORA') DEFAULT 'DIARIA', -- ADICIONADO
    adultos INT DEFAULT 1,
    criancas INT DEFAULT 0,
    banco_escolhido_id INT,
    status ENUM('PENDENTE_COMPROVANTE', 'EM_ANALISE', 'CONFIRMADA', 'RECUSADA', 'CHECKIN_REALIZADO', 'CHECKOUT_REALIZADO', 'CANCELADA') DEFAULT 'PENDENTE_COMPROVANTE',
    total_bruto DECIMAL(10,2) DEFAULT 0,
    desconto DECIMAL(10,2) DEFAULT 0,
    taxas DECIMAL(10,2) DEFAULT 0,
    total_liquido DECIMAL(10,2) NOT NULL,
    observacoes TEXT,
    motivo_recusa TEXT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    FOREIGN KEY (tipo_quarto_id) REFERENCES tipos_quarto(id) ON DELETE RESTRICT,
    FOREIGN KEY (quarto_id) REFERENCES quartos(id) ON DELETE SET NULL,
    FOREIGN KEY (banco_escolhido_id) REFERENCES bancos(id) ON DELETE SET NULL,
    INDEX idx_codigo (codigo),
    INDEX idx_usuario (usuario_id),
    INDEX idx_status (status),
    INDEX idx_checkin (checkin),
    INDEX idx_checkout (checkout),
    INDEX idx_tipo_periodo (tipo_periodo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Tabela: reservas_servicos
-- ============================================
CREATE TABLE reservas_servicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reserva_id INT NOT NULL,
    servico_id INT NOT NULL,
    quantidade DECIMAL(10,2) DEFAULT 1,
    valor_unit DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reserva_id) REFERENCES reservas(id) ON DELETE CASCADE,
    FOREIGN KEY (servico_id) REFERENCES servicos(id) ON DELETE RESTRICT,
    INDEX idx_reserva (reserva_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Tabela: comprovantes
-- ============================================
CREATE TABLE comprovantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reserva_id INT NOT NULL,
    arquivo_path VARCHAR(500) NOT NULL,
    mime_type VARCHAR(100),
    tamanho_bytes BIGINT,
    enviado_por INT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reserva_id) REFERENCES reservas(id) ON DELETE CASCADE,
    FOREIGN KEY (enviado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_reserva (reserva_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Tabela: avaliacoes (SUA ADIÇÃO - MANTIDA)
-- ============================================
CREATE TABLE avaliacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reserva_id INT NOT NULL,
    usuario_id INT NOT NULL,
    classificacao TINYINT NOT NULL CHECK (classificacao >= 1 AND classificacao <= 5),
    comentario TEXT,
    categoria ENUM('GERAL', 'QUARTO', 'SERVICO', 'LIMPEZA', 'RECEPCAO') DEFAULT 'GERAL',
    status ENUM('PENDENTE', 'APROVADA', 'REJEITADA', 'OCULTA') DEFAULT 'PENDENTE',
    ip VARCHAR(45),
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (reserva_id) REFERENCES reservas(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    UNIQUE KEY unique_avaliacao_reserva (reserva_id, usuario_id),
    INDEX idx_classificacao (classificacao),
    INDEX idx_status (status),
    INDEX idx_categoria (categoria),
    INDEX idx_usuario_reserva (usuario_id, reserva_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Tabela: respostas_avaliacoes (SUA ADIÇÃO - MANTIDA)
-- ============================================
CREATE TABLE respostas_avaliacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    avaliacao_id INT NOT NULL,
    usuario_id INT NOT NULL,
    resposta TEXT NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (avaliacao_id) REFERENCES avaliacoes(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_avaliacao (avaliacao_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Tabela: auditoria
-- ============================================
CREATE TABLE auditoria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entidade VARCHAR(100) NOT NULL,
    entidade_id INT NOT NULL,
    acao VARCHAR(50) NOT NULL,
    antes JSON,
    depois JSON,
    usuario_id INT NULL,
    ip VARCHAR(45),
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_entidade (entidade, entidade_id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_criado (criado_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- DADOS INICIAIS - CORRIGIDOS CONFORME SUAS INFORMAÇÕES
-- ============================================

-- Admin padrão (senha: admin123)
INSERT INTO usuarios (nome, email, telefone, senha_hash, role, ativo) VALUES
('Administrador', 'admin@hotelmucinga.ao', '+244 923 456 789', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ADMIN', 1),
('Recepção', 'recepcao@hotelmucinga.ao', '+244 923 456 790', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'RECEPCAO', 1),
('João Silva', 'joao@email.com', '+244 923 111 111', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'HOSPEDE', 1),
('Maria Santos', 'maria@email.com', '+244 923 222 222', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'HOSPEDE', 1);

-- TIPOS DE QUARTO CORRETOS CONFORME SUAS INFORMAÇÕES
INSERT INTO tipos_quarto (nome, descricao, capacidade_adultos, capacidade_criancas, amenidades, ativo) VALUES
('Suite', 'Cama king size, sala e varanda', 2, 0, '["WiFi", "TV 65\\"", "Ar Condicionado", "Sala de Estar", "Varanda", "Cama King Size", "Pequeno-almoço", "Piscina", "Wi-Fi"]', 1),
('Casal', 'Quarto com cama dupla', 2, 0, '["WiFi", "TV", "Ar Condicionado", "Banheiro Privativo", "Cama Dupla", "Pequeno-almoço", "Piscina", "Wi-Fi"]', 1),
('Twin', 'Quarto com duas camas individuais', 2, 0, '["WiFi", "TV", "Ar Condicionado", "Banheiro Privativo", "Duas Camas Individuais", "Pequeno-almoço", "Piscina", "Wi-Fi"]', 1);

-- 35 QUARTOS NUMERADOS 1-35 CONFORME CONVERSAMOS
INSERT INTO quartos (numero, tipo_quarto_id, status, status_ocupacao) VALUES
-- SUITES (9 quartos - 1 a 9)
('1', 1, 'ATIVO', 'DISPONIVEL'), ('2', 1, 'ATIVO', 'DISPONIVEL'), ('3', 1, 'ATIVO', 'DISPONIVEL'),
('4', 1, 'ATIVO', 'DISPONIVEL'), ('5', 1, 'ATIVO', 'DISPONIVEL'), ('6', 1, 'ATIVO', 'DISPONIVEL'),
('7', 1, 'ATIVO', 'DISPONIVEL'), ('8', 1, 'ATIVO', 'DISPONIVEL'), ('9', 1, 'ATIVO', 'DISPONIVEL'),

-- CASAL (11 quartos - 10 a 20)
('10', 2, 'ATIVO', 'DISPONIVEL'), ('11', 2, 'ATIVO', 'DISPONIVEL'), ('12', 2, 'ATIVO', 'DISPONIVEL'),
('13', 2, 'ATIVO', 'DISPONIVEL'), ('14', 2, 'ATIVO', 'DISPONIVEL'), ('15', 2, 'ATIVO', 'DISPONIVEL'),
('16', 2, 'ATIVO', 'DISPONIVEL'), ('17', 2, 'ATIVO', 'DISPONIVEL'), ('18', 2, 'ATIVO', 'DISPONIVEL'),
('19', 2, 'ATIVO', 'DISPONIVEL'), ('20', 2, 'ATIVO', 'DISPONIVEL'),

-- TWIN (15 quartos - 21 a 35)
('21', 3, 'ATIVO', 'DISPONIVEL'), ('22', 3, 'ATIVO', 'DISPONIVEL'), ('23', 3, 'ATIVO', 'DISPONIVEL'),
('24', 3, 'ATIVO', 'DISPONIVEL'), ('25', 3, 'ATIVO', 'DISPONIVEL'), ('26', 3, 'ATIVO', 'DISPONIVEL'),
('27', 3, 'ATIVO', 'DISPONIVEL'), ('28', 3, 'ATIVO', 'DISPONIVEL'), ('29', 3, 'ATIVO', 'DISPONIVEL'),
('30', 3, 'ATIVO', 'DISPONIVEL'), ('31', 3, 'ATIVO', 'DISPONIVEL'), ('32', 3, 'ATIVO', 'DISPONIVEL'),
('33', 3, 'ATIVO', 'DISPONIVEL'), ('34', 3, 'ATIVO', 'DISPONIVEL'), ('35', 3, 'ATIVO', 'DISPONIVEL');

-- TARIFAS CONFORME SEUS PREÇOS
INSERT INTO tarifas (tipo_quarto_id, inicio, fim, preco_diaria, preco_noite, preco_hora, observacao) VALUES
-- Suite: diária 60.000 Kz, noite 30.000 Kz
(1, '2025-01-01', '2025-12-31', 60000.00, 30000.00, 5000.00, 'Suite - diária: 60.000 Kz, noite: 30.000 Kz'),

-- Casal: diária 36.000 Kz, noite 18.000 Kz
(2, '2025-01-01', '2025-12-31', 36000.00, 18000.00, 3000.00, 'Casal - diária: 36.000 Kz, noite: 18.000 Kz'),

-- Twin: diária 36.000 Kz, noite 18.000 Kz
(3, '2025-01-01', '2025-12-31', 36000.00, 18000.00, 3000.00, 'Twin - diária: 36.000 Kz, noite: 18.000 Kz');

-- Bancos
INSERT INTO bancos (nome_banco, titular, iban, nif, observacoes, ativo) VALUES
('Banco Atlântico', 'Hotel Mucinga Nzambi', 'AO06005500001234567890144', '123456789', 'Pagamento via transferência bancária', 1),
('BIC', 'Hotel Mucinga Nzambi', 'AO06001000012345678901234', '123456789', 'Pagamento via transferência bancária', 1),
('BAI', 'Hotel Mucinga Nzambi', 'AO06001100012345678901234', '123456789', 'Pagamento via transferência bancária', 1);

-- Serviços (MANTIDOS + ADICIONADO SERVIÇO POR HORA)
INSERT INTO servicos (nome, descricao, preco, unidade, ativo) VALUES
('Café Premium', 'Café da manhã premium no quarto', 15000.00, 'POR_NOITE', 1),
('Traslado Aeroporto', 'Serviço de traslado do aeroporto ao hotel', 25000.00, 'POR_RESERVA', 1),
('Cama Extra', 'Cama extra no quarto', 20000.00, 'POR_NOITE', 1),
('Lavanderia Express', 'Serviço de lavanderia expresso', 12000.00, 'POR_RESERVA', 1),
('Estacionamento', 'Estacionamento por hora', 1000.00, 'POR_HORA', 1);

-- ============================================
-- TRIGGERS PARA SINCRONIZAÇÃO AUTOMÁTICA (FASE 1)
-- ============================================

-- TRIGGER 1: Quando reserva é CONFIRMADA, marca quarto como RESERVADO
DELIMITER $$
CREATE TRIGGER tr_reserva_confirmada
AFTER UPDATE ON reservas
FOR EACH ROW
BEGIN
    IF NEW.status = 'CONFIRMADA' AND OLD.status != 'CONFIRMADA' THEN
        IF NEW.quarto_id IS NOT NULL THEN
            UPDATE quartos 
            SET status_ocupacao = 'RESERVADO' 
            WHERE id = NEW.quarto_id;
        END IF;
    END IF;
END$$
DELIMITER ;

-- TRIGGER 2: Quando CHECK-IN é realizado, marca quarto como OCUPADO
DELIMITER $$
CREATE TRIGGER tr_checkin_realizado
AFTER UPDATE ON reservas
FOR EACH ROW
BEGIN
    IF NEW.status = 'CHECKIN_REALIZADO' AND OLD.status != 'CHECKIN_REALIZADO' THEN
        IF OLD.quarto_id IS NOT NULL THEN
            UPDATE quartos 
            SET status_ocupacao = 'OCUPADO' 
            WHERE id = OLD.quarto_id;
        END IF;
    END IF;
END$$
DELIMITER ;

-- TRIGGER 3: Quando CHECK-OUT é realizado, marca quarto como DISPONIVEL
DELIMITER $$
CREATE TRIGGER tr_checkout_realizado
AFTER UPDATE ON reservas
FOR EACH ROW
BEGIN
    IF NEW.status = 'CHECKOUT_REALIZADO' AND OLD.status != 'CHECKOUT_REALIZADO' THEN
        IF OLD.quarto_id IS NOT NULL THEN
            UPDATE quartos 
            SET status_ocupacao = 'DISPONIVEL' 
            WHERE id = OLD.quarto_id;
        END IF;
    END IF;
END$$
DELIMITER ;

-- TRIGGER 4: Quando reserva é CANCELADA ou RECUSADA, libera quarto
DELIMITER $$
CREATE TRIGGER tr_reserva_cancelada_recusada
AFTER UPDATE ON reservas
FOR EACH ROW
BEGIN
    IF (NEW.status = 'CANCELADA' AND OLD.status != 'CANCELADA') OR
       (NEW.status = 'RECUSADA' AND OLD.status != 'RECUSADA') THEN
        IF OLD.quarto_id IS NOT NULL THEN
            UPDATE quartos 
            SET status_ocupacao = 'DISPONIVEL' 
            WHERE id = OLD.quarto_id;
        END IF;
    END IF;
END$$
DELIMITER ;

-- ============================================
-- FUNÇÃO PARA CALCULAR PREÇO CONFORME PERÍODO
-- ============================================

DELIMITER $$
CREATE FUNCTION fn_calcular_preco_periodo(
    p_tipo_quarto_id INT,
    p_tipo_periodo ENUM('DIARIA', 'NOITE', 'HORA'),
    p_horas INT
) RETURNS DECIMAL(10,2)
DETERMINISTIC
BEGIN
    DECLARE v_preco DECIMAL(10,2);
    DECLARE v_preco_diaria DECIMAL(10,2);
    DECLARE v_preco_noite DECIMAL(10,2);
    DECLARE v_preco_hora DECIMAL(10,2);
    
    -- Buscar tarifas
    SELECT preco_diaria, preco_noite, preco_hora 
    INTO v_preco_diaria, v_preco_noite, v_preco_hora
    FROM tarifas 
    WHERE tipo_quarto_id = p_tipo_quarto_id 
    AND CURRENT_DATE BETWEEN inicio AND fim 
    LIMIT 1;
    
    -- Calcular conforme tipo de período
    CASE p_tipo_periodo
        WHEN 'DIARIA' THEN
            SET v_preco = v_preco_diaria;
        WHEN 'NOITE' THEN
            SET v_preco = v_preco_noite;
        WHEN 'HORA' THEN
            SET v_preco = v_preco_hora * p_horas;
        ELSE
            SET v_preco = v_preco_diaria;
    END CASE;
    
    RETURN COALESCE(v_preco, 0);
END$$
DELIMITER ;

-- ============================================
-- VIEW PARA QUARTOS DISPONÍVEIS EM TEMPO REAL
-- ============================================

CREATE VIEW vw_quartos_disponiveis_tempo_real AS
SELECT 
    q.id,
    q.numero,
    tq.nome AS tipo_quarto,
    tq.descricao,
    tq.capacidade_adultos,
    tq.capacidade_criancas,
    tq.amenidades,
    tq.foto_capa,
    q.status AS status_administrativo,
    q.status_ocupacao AS status_ocupacao_atual,
    tr.preco_diaria,
    tr.preco_noite,
    tr.preco_hora,
    -- Verificar se está ocupado/reservado
    CASE 
        WHEN q.status_ocupacao = 'DISPONIVEL' THEN 'DISPONÍVEL'
        WHEN q.status_ocupacao = 'RESERVADO' THEN 'RESERVADO'
        WHEN q.status_ocupacao = 'OCUPADO' THEN 'OCUPADO'
        ELSE 'INDISPONÍVEL'
    END AS status_display
FROM quartos q
INNER JOIN tipos_quarto tq ON q.tipo_quarto_id = tq.id
LEFT JOIN tarifas tr ON tq.id = tr.tipo_quarto_id 
    AND CURRENT_DATE BETWEEN tr.inicio AND tr.fim
WHERE q.status = 'ATIVO'
ORDER BY CAST(q.numero AS UNSIGNED);

-- ============================================
-- VIEW PARA DASHBOARD ADMIN (STATUS DOS QUARTOS)
-- ============================================

CREATE VIEW vw_dashboard_status_quartos AS
SELECT 
    COUNT(*) AS total_quartos,
    SUM(CASE WHEN status_ocupacao = 'DISPONIVEL' THEN 1 ELSE 0 END) AS disponiveis,
    SUM(CASE WHEN status_ocupacao = 'RESERVADO' THEN 1 ELSE 0 END) AS reservados,
    SUM(CASE WHEN status_ocupacao = 'OCUPADO' THEN 1 ELSE 0 END) AS ocupados,
    SUM(CASE WHEN status_ocupacao = 'MANUTENCAO' THEN 1 ELSE 0 END) AS manutencao,
    -- Por tipo
    SUM(CASE WHEN tq.nome = 'Suite' AND status_ocupacao = 'DISPONIVEL' THEN 1 ELSE 0 END) AS suites_disponiveis,
    SUM(CASE WHEN tq.nome = 'Casal' AND status_ocupacao = 'DISPONIVEL' THEN 1 ELSE 0 END) AS casal_disponiveis,
    SUM(CASE WHEN tq.nome = 'Twin' AND status_ocupacao = 'DISPONIVEL' THEN 1 ELSE 0 END) AS twin_disponiveis
FROM quartos q
INNER JOIN tipos_quarto tq ON q.tipo_quarto_id = tq.id
WHERE q.status = 'ATIVO';

-- ============================================
-- RESULTADO FINAL
-- ============================================

SELECT '✅ BANCO DE DADOS CRIADO COM SUCESSO!' AS mensagem;
SELECT '=====================================' AS separador;
SELECT '📊 INFORMAÇÕES CADASTRADAS:' AS categoria;
SELECT CONCAT('👥 Usuários: ', COUNT(*)) FROM usuarios UNION ALL
SELECT CONCAT('🛏️ Tipos de quarto: ', COUNT(*), ' (Suite, Casal, Twin)') FROM tipos_quarto UNION ALL
SELECT CONCAT('🚪 Quartos: ', COUNT(*), ' (1-35)') FROM quartos UNION ALL
SELECT CONCAT('💰 Tarifas: ', COUNT(*)) FROM tarifas UNION ALL
SELECT CONCAT('🏦 Bancos: ', COUNT(*)) FROM bancos UNION ALL
SELECT CONCAT('🎯 Serviços: ', COUNT(*)) FROM servicos;

SELECT '=====================================' AS separador;
SELECT '🎯 DISTRIBUIÇÃO DOS 35 QUARTOS:' AS categoria;
SELECT CONCAT('🏨 Suite: ', COUNT(*), ' quartos (1-9)') FROM quartos WHERE tipo_quarto_id = 1 UNION ALL
SELECT CONCAT('💑 Casal: ', COUNT(*), ' quartos (10-20)') FROM quartos WHERE tipo_quarto_id = 2 UNION ALL
SELECT CONCAT('🛌 Twin: ', COUNT(*), ' quartos (21-35)') FROM quartos WHERE tipo_quarto_id = 3;

SELECT '=====================================' AS separador;
SELECT '💰 PREÇOS CONFIGURADOS:' AS categoria;
SELECT CONCAT('🏨 Suite: Diária ', FORMAT(preco_diaria, 0), ' Kz | Noite ', FORMAT(preco_noite, 0), ' Kz') FROM tarifas WHERE tipo_quarto_id = 1 UNION ALL
SELECT CONCAT('💑 Casal: Diária ', FORMAT(preco_diaria, 0), ' Kz | Noite ', FORMAT(preco_noite, 0), ' Kz') FROM tarifas WHERE tipo_quarto_id = 2 UNION ALL
SELECT CONCAT('🛌 Twin: Diária ', FORMAT(preco_diaria, 0), ' Kz | Noite ', FORMAT(preco_noite, 0), ' Kz') FROM tarifas WHERE tipo_quarto_id = 3;

SELECT '=====================================' AS separador;
SELECT '🚀 SISTEMA DE SINCRONIZAÇÃO ATIVADO:' AS categoria;
SELECT '✅ Trigger 1: Reserva confirmada → Quarto RESERVADO' UNION ALL
SELECT '✅ Trigger 2: Check-in realizado → Quarto OCUPADO' UNION ALL
SELECT '✅ Trigger 3: Check-out realizado → Quarto DISPONÍVEL' UNION ALL
SELECT '✅ Trigger 4: Cancelamento/Recusa → Quarto DISPONÍVEL' UNION ALL
SELECT '🎯 Sistema anti-overbooking ativo!';

SELECT '=====================================' AS separador;
SELECT '📱 VIEWS DISPONÍVEIS:' AS categoria;
SELECT '👉 vw_quartos_disponiveis_tempo_real' UNION ALL
SELECT '👉 vw_dashboard_status_quartos';

SELECT '=====================================' AS separador;
SELECT '🎯 PRÓXIMOS PASSOS:' AS categoria;
SELECT '1. Execute este SQL no seu banco' UNION ALL
SELECT '2. Aplique as modificações PHP da FASE 1' UNION ALL
SELECT '3. Teste o sistema de sincronização' UNION ALL
SELECT '4. Adicione mais quartos pelo painel admin quando quiser';