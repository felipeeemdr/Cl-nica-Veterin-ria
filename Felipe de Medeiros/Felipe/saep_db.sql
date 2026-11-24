CREATE DATABASE IF NOT EXISTS saep_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE saep_db;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS tutores (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    cpf_criptografado VARCHAR(255) NOT NULL,
    cpf_hash CHAR(64) NOT NULL UNIQUE,
    telefone VARCHAR(20) NOT NULL,
    email VARCHAR(120) NULL,
    endereco VARCHAR(180) NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS pets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tutor_id INT UNSIGNED NOT NULL,
    nome VARCHAR(100) NOT NULL,
    especie VARCHAR(40) NOT NULL,
    raca VARCHAR(80) NULL,
    sexo VARCHAR(15) NULL,
    data_nascimento DATE NULL,
    observacoes TEXT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_pets_tutor (tutor_id),
    CONSTRAINT fk_pets_tutores FOREIGN KEY (tutor_id)
        REFERENCES tutores(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS agendamentos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pet_id INT UNSIGNED NOT NULL,
    data_agendamento DATETIME NOT NULL,
    motivo VARCHAR(180) NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'Agendado',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_agendamentos_data (data_agendamento),
    INDEX idx_agendamentos_pet (pet_id),
    CONSTRAINT fk_agendamentos_pets FOREIGN KEY (pet_id)
        REFERENCES pets(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Senha de demonstração dos três usuários: Senai@123
INSERT INTO usuarios (id, nome, email, senha_hash) VALUES
(1, 'Administrador', 'admin@clinica.com', '$2y$12$q3LDJF2I8Q4XucncNhze2unBpcM9MLVXorQgUtxrVr5QdHcsH62n6'),
(2, 'Recepção', 'recepcao@clinica.com', '$2y$12$q3LDJF2I8Q4XucncNhze2unBpcM9MLVXorQgUtxrVr5QdHcsH62n6'),
(3, 'Veterinário', 'veterinario@clinica.com', '$2y$12$q3LDJF2I8Q4XucncNhze2unBpcM9MLVXorQgUtxrVr5QdHcsH62n6')
ON DUPLICATE KEY UPDATE nome = VALUES(nome), senha_hash = VALUES(senha_hash);

-- Os CPFs abaixo são dados fictícios de demonstração e ficam criptografados.
INSERT INTO tutores (id, nome, cpf_criptografado, cpf_hash, telefone, email, endereco) VALUES
(1, 'Mariana Costa', 'Nofvkaei3I88Wp/OgpLsk5UmKx6HFoHAGCOG18jXHdcyTJhyzNSY', '14b3480ab4772e57c809a4083342deea6d1358ca454a481aabe047fd172c4ae9', '(81) 99911-2233', 'mariana@email.com', 'Boa Viagem, Recife - PE'),
(2, 'Rafael Santos', 'BLTDDyGo0dLRcD1vssLyIqpQnktru+UylLhOfe6u1FLUobAu1d91', '4aea734a22bb42804875499a327f364594a75ebad7807cf07112a66801d06744', '(81) 98822-3344', 'rafael@email.com', 'Madalena, Recife - PE'),
(3, 'Camila Oliveira', '5kRQ6qHVwTMV22mPgmKjQnYmz0+XM3+5MW4WviOauOy1S542SWys', 'bfb7d92266ada4bd13f2be4d7cdec3eeebb606126712b314dd75c1b147f75adf', '(81) 97733-4455', 'camila@email.com', 'Casa Amarela, Recife - PE')
ON DUPLICATE KEY UPDATE
    nome = VALUES(nome), cpf_criptografado = VALUES(cpf_criptografado),
    telefone = VALUES(telefone), email = VALUES(email), endereco = VALUES(endereco);

INSERT INTO pets (id, tutor_id, nome, especie, raca, sexo, data_nascimento, observacoes) VALUES
(1, 1, 'Thor', 'Cachorro', 'Labrador', 'Macho', '2021-03-10', 'Acompanhamento de rotina.'),
(2, 2, 'Mimi', 'Gato', 'Siamês', 'Fêmea', '2022-08-21', 'Possui carteira de vacinação.'),
(3, 3, 'Bidu', 'Cachorro', 'Vira-lata', 'Macho', '2020-11-05', 'Retorno para avaliação.')
ON DUPLICATE KEY UPDATE
    tutor_id = VALUES(tutor_id), nome = VALUES(nome), especie = VALUES(especie),
    raca = VALUES(raca), sexo = VALUES(sexo), data_nascimento = VALUES(data_nascimento),
    observacoes = VALUES(observacoes);

INSERT INTO agendamentos (id, pet_id, data_agendamento, motivo, status) VALUES
(1, 1, '2026-11-25 09:00:00', 'Consulta de rotina', 'Agendado'),
(2, 2, '2026-11-25 10:30:00', 'Vacinação', 'Agendado'),
(3, 3, '2026-11-26 14:00:00', 'Retorno', 'Agendado')
ON DUPLICATE KEY UPDATE
    pet_id = VALUES(pet_id), data_agendamento = VALUES(data_agendamento),
    motivo = VALUES(motivo), status = VALUES(status);
