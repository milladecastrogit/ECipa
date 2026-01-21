-- Banco de Dados E-CIPA
CREATE DATABASE IF NOT EXISTS ecipa DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE ecipa;

-- Tabela de Funcionários
CREATE TABLE IF NOT EXISTS funcionario (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cpf VARCHAR(14) UNIQUE NOT NULL,
    matricula VARCHAR(20),
    setor VARCHAR(50),
    cargo VARCHAR(50),
    email VARCHAR(100) UNIQUE NOT NULL,
    telefone VARCHAR(20),
    senha VARCHAR(255) NOT NULL,
    tipo ENUM('Administrador', 'Funcionario', 'Comissao') DEFAULT 'Funcionario',
    status ENUM('Ativo', 'Pendente', 'Inativo') DEFAULT 'Pendente',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabela de Eleições
CREATE TABLE IF NOT EXISTS eleicao (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(100) NOT NULL,
    data_posse DATE NOT NULL,
    data_inicio_inscricao DATE,
    data_fim_inscricao DATE,
    data_eleicao DATE,
    status ENUM('Planejamento', 'Inscricoes', 'Votacao', 'Finalizada') DEFAULT 'Planejamento',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabela de Candidatos
CREATE TABLE IF NOT EXISTS candidatura (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    id_funcionario INT NOT NULL,
    id_eleicao INT NOT NULL,
    foto_path VARCHAR(255),
    proposta TEXT,
    status ENUM('Pendente', 'Aprovado', 'Rejeitado') DEFAULT 'Pendente',
    votos_count INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_funcionario) REFERENCES funcionario(id) ON DELETE CASCADE,
    FOREIGN KEY (id_eleicao) REFERENCES eleicao(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Tabela de Votos
CREATE TABLE IF NOT EXISTS voto (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    id_eleicao INT NOT NULL,
    id_funcionario INT NOT NULL, -- Quem votou (para evitar voto duplo)
    id_candidato INT, -- NULL para branco/nulo
    tipo_voto ENUM('Nominal', 'Branco', 'Nulo', 'Fisico') DEFAULT 'Nominal',
    voto_fisico TINYINT(1) DEFAULT 0, -- 1 se voto físico (cédula)
    cod_verificacao VARCHAR(64),
    data_voto DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_eleicao) REFERENCES eleicao(id) ON DELETE CASCADE,
    FOREIGN KEY (id_funcionario) REFERENCES funcionario(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Tabela de Códigos de Verificação (2FA/Voto)
CREATE TABLE IF NOT EXISTS verification_code (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    id_funcionario INT NOT NULL,
    id_eleicao INT,
    code VARCHAR(32) NOT NULL,
    sent_via ENUM('SMS', 'WhatsApp', 'Email') DEFAULT 'Email',
    expires_at DATETIME NOT NULL,
    consumed_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_funcionario) REFERENCES funcionario(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Tabela de Recibos de Voto
CREATE TABLE IF NOT EXISTS recibo_voto (
    id_recibo INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    id_voto INT NOT NULL,
    uuid_recibo CHAR(36) NOT NULL,
    hash_recibo CHAR(64) NOT NULL,
    arquivo_path VARCHAR(255),
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_voto) REFERENCES voto(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Tabela de Auditoria (Logs)
CREATE TABLE IF NOT EXISTS audit_log (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_type ENUM('Administrador', 'Funcionario', 'Comissao') NOT NULL,
    user_id INT,
    action VARCHAR(120) NOT NULL,
    alvo_tipo VARCHAR(80),
    alvo_id INT,
    detalhes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Inserir Administrador Padrão
INSERT INTO funcionario (nome, cpf, matricula, email, senha, tipo, status) 
VALUES ('Administrador E-CIPA', '000.000.000-00', 'ADM001', 'admin@ecipa.com.br', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador', 'Ativo');
-- Senha padrão: password (hash bcrypt)
