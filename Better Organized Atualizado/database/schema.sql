-- ============================================================================
-- BetterOrganized — instalação completa
-- Cria o banco, todas as tabelas, usuários de teste, laboratórios e 20
-- máquinas de demonstração no Laboratório 1.
-- Execute este único arquivo em um MySQL/MariaDB novo.
-- ============================================================================

-- =============================================================================
-- Script de criação do banco de dados do projeto BetterOrganized (schema novo)
-- Estrutura: Usuarios, Ocorrencias/Historico, e o modelo de hardware
-- (Lab -> Maquina -> Pec -> Memory/Memory_ram/Placa_video/Perifericos, Baia -> Maquina)
-- =============================================================================

CREATE DATABASE IF NOT EXISTS betterorganized CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE betterorganized;

-- ---------- Usuários ----------------------------------------------------
-- funcao: 1 = Professor, 0 = Suporte
CREATE TABLE Usuarios (
    ID_USER INT AUTO_INCREMENT PRIMARY KEY,
    nome    VARCHAR(100) NOT NULL,
    email   VARCHAR(150) NOT NULL UNIQUE,
    senha   VARCHAR(255) NOT NULL,
    funcao  TINYINT NOT NULL
);

-- ---------- Componentes de hardware (peças reutilizáveis) ---------------

CREATE TABLE Memory_ram (
    MRAM_SET INT AUTO_INCREMENT PRIMARY KEY,
    slot1    VARCHAR(25),
    slot2    VARCHAR(25),
    slot3    VARCHAR(25),
    slot4    VARCHAR(25),
    modelo   VARCHAR(15)
);

CREATE TABLE Memory (
    MEMO_SET INT AUTO_INCREMENT PRIMARY KEY,
    HD1      VARCHAR(25),
    HD2      VARCHAR(25),
    SSD1     VARCHAR(25),
    SSD2     VARCHAR(25),
    modelo   VARCHAR(15)
);

CREATE TABLE Placa_video (
    PLAC_VID INT AUTO_INCREMENT PRIMARY KEY,
    nome     VARCHAR(25),
    modelo   VARCHAR(15),
    marca    VARCHAR(25),
    verifica TINYINT(1) DEFAULT 0
);

CREATE TABLE Perifericos (
    PERI_SET      INT AUTO_INCREMENT PRIMARY KEY,
    monitor       VARCHAR(50),
    teclado       VARCHAR(50),
    estabilizador VARCHAR(50)
);

-- ---------- Conjunto de peças de uma máquina -----------------------------
-- Um "Pec" agrupa tudo que compõe uma máquina: memórias, placa de vídeo,
-- periféricos, processador e placa-mãe. Todas as FKs são opcionais (uma
-- máquina pode não ter, por exemplo, placa de vídeo dedicada cadastrada).
CREATE TABLE Pec (
    ID_PEC_SET    INT AUTO_INCREMENT PRIMARY KEY,
    MRAM_SET      INT,
    MEMO_SET      INT,
    PLAC_VID      INT,
    PERI_SET      INT,
    processador   VARCHAR(50),
    motherboard   VARCHAR(50),
    gravador_dvd  VARCHAR(25),
    fonte         VARCHAR(25),
    placa_rede    VARCHAR(25),
    FOREIGN KEY (MRAM_SET) REFERENCES Memory_ram(MRAM_SET) ON DELETE SET NULL,
    FOREIGN KEY (MEMO_SET) REFERENCES Memory(MEMO_SET) ON DELETE SET NULL,
    FOREIGN KEY (PLAC_VID) REFERENCES Placa_video(PLAC_VID) ON DELETE SET NULL,
    FOREIGN KEY (PERI_SET) REFERENCES Perifericos(PERI_SET) ON DELETE SET NULL
);

-- ---------- Laboratórios --------------------------------------------------
-- QUANT_MAQ não é uma coluna física: é sempre calculada por
-- "SELECT COUNT(*) FROM Maquina WHERE ID_LAB = ?" (função getQuantMaquinas()
-- em helpers.php), pra nunca ficar desatualizada.
-- status_laboratorio também é calculado: pior status entre as máquinas do lab
-- (0 = funcionando | 1 = com defeito | 2 = manutenção).
CREATE TABLE Lab (
    ID_LAB INT AUTO_INCREMENT PRIMARY KEY,
    nome   VARCHAR(50) NOT NULL
);

-- ---------- Máquinas -------------------------------------------------------
-- status_maquina: 0 = funcionando | 1 = com defeito | 2 = manutenção
CREATE TABLE Maquina (
    ID_MAQ         INT AUTO_INCREMENT PRIMARY KEY,
    ID_PEC_SET     INT,
    ID_LAB         INT NOT NULL,
    status_maquina TINYINT NOT NULL DEFAULT 0,
    apelido        VARCHAR(50),
    FOREIGN KEY (ID_PEC_SET) REFERENCES Pec(ID_PEC_SET) ON DELETE SET NULL,
    FOREIGN KEY (ID_LAB) REFERENCES Lab(ID_LAB)
);

-- ---------- Baias -----------------------------------------------------------
-- Uma baia é a posição física onde uma máquina está instalada dentro do
-- laboratório. ID_MAQ é UNIQUE: uma máquina só pode estar em uma baia por vez.
-- Baia só existe quando alguém cadastra uma máquina nela (lista dinâmica,
-- sem mapa fixo de 48 posições).
CREATE TABLE Baia (
    ID_BAIA INT AUTO_INCREMENT PRIMARY KEY,
    ID_LAB  INT NOT NULL,
    ID_MAQ  INT UNIQUE,
    numero  INT NOT NULL,
    FOREIGN KEY (ID_LAB) REFERENCES Lab(ID_LAB),
    FOREIGN KEY (ID_MAQ) REFERENCES Maquina(ID_MAQ) ON DELETE SET NULL
);

-- ---------- Ocorrências ------------------------------------------------------
-- status_ocorrencia: 0 = ok (verde) | 1 = com defeito (vermelho) | 2 = manutenção (amarelo)
-- tipo_problema: 'Hardware' | 'Software' | 'Rede' | 'Outro'
-- Referencia o laboratório diretamente (não mais ID_BAIA obrigatório);
-- ID_MAQ é opcional, pra permitir apontar pra uma máquina específica quando aplicável.
CREATE TABLE Ocorrencias (
    ID_OCORR           INT AUTO_INCREMENT PRIMARY KEY,
    titulo             VARCHAR(150),
    descricao          TEXT,
    data_ocorr         DATE,
    ID_USER            INT NOT NULL,
    ID_LAB             INT NOT NULL,
    ID_MAQ             INT,
    tipo_problema      VARCHAR(20) NOT NULL DEFAULT 'Outro',
    status_ocorrencia  TINYINT NOT NULL DEFAULT 0,
    FOREIGN KEY (ID_USER) REFERENCES Usuarios(ID_USER),
    FOREIGN KEY (ID_LAB) REFERENCES Lab(ID_LAB),
    FOREIGN KEY (ID_MAQ) REFERENCES Maquina(ID_MAQ) ON DELETE SET NULL
);

-- ---------- Histórico (log de máquina e usuário) -----------------------------
-- Guarda um registro permanente de tudo que acontece com cada ocorrência,
-- inclusive depois que a ocorrência original é concluída/excluída — por isso
-- não tem FOREIGN KEY para Ocorrencias (o registro precisa sobreviver).
-- acao: 'criada' | 'concluida'
CREATE TABLE Historico (
    ID_HIST            INT AUTO_INCREMENT PRIMARY KEY,
    ID_OCORR           INT NOT NULL,
    ID_LAB             INT NOT NULL,
    ID_MAQ             INT,
    titulo             VARCHAR(150),
    tipo_problema      VARCHAR(20),
    status_ocorrencia  TINYINT NOT NULL DEFAULT 0,
    acao               VARCHAR(20) NOT NULL,
    ID_USER            INT NOT NULL,
    usuario_nome       VARCHAR(100) NOT NULL,
    data_evento        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ID_USER) REFERENCES Usuarios(ID_USER)
);

-- ---------- Dados iniciais ----------------------------------------------------

INSERT INTO Usuarios (nome, email, senha, funcao) VALUES
('Professor Teste', 'professor@teste.com', '123456', 1),
('Suporte Teste',   'suporte@teste.com',   '123456', 0);

INSERT INTO Lab (nome) VALUES
('Laboratório 1'), ('Laboratório 2'), ('Laboratório 3'), ('Laboratório 4');

-- ============================================================================
-- Dados de demonstração: 20 máquinas no Laboratório 1
-- ============================================================================

-- ============================================================================
-- Teste: 20 máquinas no Laboratório 1
-- Banco: betterorganized
--
-- IDs usados neste teste:
--   Máquinas: 5001 a 5020
--   Conjuntos de peças: 6001 a 6020
--   Baias: 7001 a 7020
--
-- O script pode ser executado mais de uma vez sem duplicar esses registros,
-- pois usa INSERT IGNORE.
-- ============================================================================

USE betterorganized;

-- Garante que o Laboratório 1 exista.
INSERT IGNORE INTO Lab (ID_LAB, nome)
VALUES (1, 'Laboratório 1');

-- Cada máquina recebe um conjunto mínimo de peças.
INSERT IGNORE INTO Pec
    (ID_PEC_SET, processador, motherboard, gravador_dvd, fonte, placa_rede)
VALUES
    (6001,  'Intel Core i3', 'Placa-mãe padrão 01', 'Não', '450 W', 'Gigabit'),
    (6002,  'Intel Core i3', 'Placa-mãe padrão 02', 'Não', '450 W', 'Gigabit'),
    (6003,  'Intel Core i3', 'Placa-mãe padrão 03', 'Não', '450 W', 'Gigabit'),
    (6004,  'Intel Core i3', 'Placa-mãe padrão 04', 'Não', '450 W', 'Gigabit'),
    (6005,  'Intel Core i5', 'Placa-mãe padrão 05', 'Não', '500 W', 'Gigabit'),
    (6006,  'Intel Core i5', 'Placa-mãe padrão 06', 'Não', '500 W', 'Gigabit'),
    (6007,  'Intel Core i5', 'Placa-mãe padrão 07', 'Não', '500 W', 'Gigabit'),
    (6008,  'Intel Core i5', 'Placa-mãe padrão 08', 'Não', '500 W', 'Gigabit'),
    (6009,  'Intel Core i5', 'Placa-mãe padrão 09', 'Não', '500 W', 'Gigabit'),
    (6010,  'Intel Core i5', 'Placa-mãe padrão 10', 'Não', '500 W', 'Gigabit'),
    (6011,  'AMD Ryzen 3',  'Placa-mãe padrão 11', 'Não', '450 W', 'Gigabit'),
    (6012,  'AMD Ryzen 3',  'Placa-mãe padrão 12', 'Não', '450 W', 'Gigabit'),
    (6013,  'AMD Ryzen 5',  'Placa-mãe padrão 13', 'Não', '500 W', 'Gigabit'),
    (6014,  'AMD Ryzen 5',  'Placa-mãe padrão 14', 'Não', '500 W', 'Gigabit'),
    (6015,  'AMD Ryzen 5',  'Placa-mãe padrão 15', 'Não', '500 W', 'Gigabit'),
    (6016,  'AMD Ryzen 5',  'Placa-mãe padrão 16', 'Não', '500 W', 'Gigabit'),
    (6017,  'AMD Ryzen 7',  'Placa-mãe padrão 17', 'Não', '650 W', 'Gigabit'),
    (6018,  'AMD Ryzen 7',  'Placa-mãe padrão 18', 'Não', '650 W', 'Gigabit'),
    (6019,  'Intel Core i7', 'Placa-mãe padrão 19', 'Não', '650 W', 'Gigabit'),
    (6020,  'Intel Core i7', 'Placa-mãe padrão 20', 'Não', '650 W', 'Gigabit');

-- Insere 20 máquinas no Laboratório 1.
-- Status: 0 = funcionando, 1 = com defeito, 2 = manutenção.
INSERT IGNORE INTO Maquina
    (ID_MAQ, ID_PEC_SET, ID_LAB, status_maquina, apelido)
VALUES
    (5001, 6001,  1, 0, NULL),
    (5002, 6002,  1, 0, NULL),
    (5003, 6003,  1, 0, NULL),
    (5004, 6004,  1, 0, NULL),
    (5005, 6005,  1, 0, NULL),
    (5006, 6006,  1, 0, NULL),
    (5007, 6007,  1, 0, NULL),
    (5008, 6008,  1, 0, NULL),
    (5009, 6009,  1, 0, NULL),
    (5010, 6010,  1, 0, NULL),
    (5011, 6011,  1, 0, NULL),
    (5012, 6012,  1, 0, NULL),
    (5013, 6013,  1, 0, NULL),
    (5014, 6014,  1, 0, NULL),
    (5015, 6015,  1, 0, NULL),
    (5016, 6016,  1, 0, NULL),
    (5017, 6017,  1, 0, NULL),
    (5018, 6018,  1, 1, NULL),
    (5019, 6019,  1, 2, NULL),
    (5020, 6020, 1, 0, NULL);

-- Baias 1 a 20 do Laboratório 1.
INSERT IGNORE INTO Baia
    (ID_BAIA, ID_LAB, ID_MAQ, numero)
VALUES
    (7001,  1, 5001,  1),
    (7002,  1, 5002,  2),
    (7003,  1, 5003,  3),
    (7004,  1, 5004,  4),
    (7005,  1, 5005,  5),
    (7006,  1, 5006,  6),
    (7007,  1, 5007,  7),
    (7008,  1, 5008,  8),
    (7009,  1, 5009,  9),
    (7010,  1, 5010, 10),
    (7011,  1, 5011, 11),
    (7012,  1, 5012, 12),
    (7013,  1, 5013, 13),
    (7014,  1, 5014, 14),
    (7015,  1, 5015, 15),
    (7016,  1, 5016, 16),
    (7017,  1, 5017, 17),
    (7018,  1, 5018, 18),
    (7019,  1, 5019, 19),
    (7020,  1, 5020, 20);

-- Conferência: deve retornar 20 máquinas no Laboratório 1.
SELECT COUNT(*) AS total_maquinas_lab1
FROM Maquina
WHERE ID_LAB = 1 AND ID_MAQ BETWEEN 5001 AND 5020;
