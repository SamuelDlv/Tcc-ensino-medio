-- Better Organized - banco reconstruído a partir do código PHP
-- MySQL / MariaDB
-- ATENÇÃO: este script APAGA o banco anterior e todos os dados existentes nele.

CREATE DATABASE `better_organized`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `better_organized`;

-- ID_USER é gerado automaticamente pelo banco.
-- funcao: 1 = professor; 0 = suporte.
CREATE TABLE `Usuarios` (
  `ID_USER` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(255) NOT NULL,
  `senha` VARCHAR(255) NOT NULL,
  `nome` VARCHAR(150) NOT NULL,
  `funcao` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `escolha` TINYINT UNSIGNED NULL,
  PRIMARY KEY (`ID_USER`),
  UNIQUE KEY `uq_usuarios_email` (`email`),
  KEY `idx_usuarios_funcao` (`funcao`),
  CONSTRAINT `ck_usuarios_funcao` CHECK (`funcao` IN (0, 1)),
  CONSTRAINT `ck_usuarios_escolha` CHECK (`escolha` IS NULL OR `escolha` IN (0, 1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- status_ocorrencia:
-- 0 = funcionando / sem ocorrência ativa
-- 1 = com defeito
-- 2 = em manutenção
CREATE TABLE `Ocorrencias` (
  `ID_OCORR` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `titulo` VARCHAR(255) NOT NULL,
  `descricao` TEXT NOT NULL,
  `data_ocorr` DATE NOT NULL,
  `ID_USER` INT UNSIGNED NOT NULL,
  `status_ocorrencia` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `ID_LAB` INT UNSIGNED NOT NULL,
  `ID_BAIA` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`ID_OCORR`),
  KEY `idx_ocorrencias_lab` (`ID_LAB`),
  KEY `idx_ocorrencias_lab_status` (`ID_LAB`, `status_ocorrencia`),
  KEY `idx_ocorrencias_baia_lab` (`ID_BAIA`, `ID_LAB`),
  KEY `idx_ocorrencias_usuario` (`ID_USER`),
  CONSTRAINT `fk_ocorrencias_usuario`
    FOREIGN KEY (`ID_USER`) REFERENCES `Usuarios` (`ID_USER`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,
  CONSTRAINT `ck_ocorrencias_status`
    CHECK (`status_ocorrencia` IN (0, 1, 2)),
  CONSTRAINT `ck_ocorrencias_laboratorio`
    CHECK (`ID_LAB` BETWEEN 1 AND 4),
  CONSTRAINT `ck_ocorrencias_baia`
    CHECK (`ID_BAIA` BETWEEN 1 AND 48)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
