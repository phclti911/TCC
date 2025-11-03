CREATE DATABASE editor_acessivel CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE editor_acessivel;

CREATE TABLE preferencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL,
    fonte VARCHAR(100),
    tamanho INT,
    cor VARCHAR(10),
    contraste VARCHAR(20),
    espacamento_letras FLOAT,
    espacamento_palavras FLOAT,
    espacamento_linhas FLOAT
);

CREATE TABLE textos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL,
    conteudo LONGTEXT,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
