-- ============================================
-- SCRIPT DE EXPORTAÇÃO DO BANCO DE DADOS
-- ============================================
-- 
-- IMPORTANTE: Este é um template. Você precisa exportar 
-- seu banco de dados real usando phpMyAdmin ou MySQL Workbench
--
-- COMO EXPORTAR SEU BANCO:
--
-- OPÇÃO 1 - phpMyAdmin:
-- 1. Acesse phpMyAdmin (http://localhost/phpmyadmin)
-- 2. Selecione seu banco de dados no menu lateral
-- 3. Clique na aba "Exportar"
-- 4. Escolha "Método rápido" ou "Personalizado"
-- 5. Formato: SQL
-- 6. Clique em "Executar"
-- 7. Salve o arquivo como "database.sql"
--
-- OPÇÃO 2 - Linha de Comando:
-- Execute este comando (substitua os valores):
-- mysqldump -u seu_usuario -p seu_banco > database.sql
--
-- OPÇÃO 3 - MySQL Workbench:
-- 1. Abra MySQL Workbench
-- 2. Conecte ao seu servidor local
-- 3. Server > Data Export
-- 4. Selecione seu banco
-- 5. Export to Self-Contained File
-- 6. Escolha o local e nome "database.sql"
-- 7. Clique em "Start Export"
--
-- ============================================
-- Após exportar, você terá um arquivo .sql
-- que será usado no Railway para criar o banco
-- ============================================

-- Estrutura básica esperada (exemplo):

-- Tabela de configurações
CREATE TABLE IF NOT EXISTS `config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome_site` varchar(255) DEFAULT 'RaspaGreen',
  `logo` varchar(255) DEFAULT '',
  `deposito_min` decimal(10,2) DEFAULT 10.00,
  `saque_min` decimal(10,2) DEFAULT 50.00,
  `cpa_padrao` decimal(10,2) DEFAULT 10.00,
  `revshare_padrao` decimal(10,2) DEFAULT 10.00,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insira dados iniciais se necessário
INSERT INTO `config` (`nome_site`, `logo`, `deposito_min`, `saque_min`, `cpa_padrao`, `revshare_padrao`) 
VALUES ('RaspaGreen', '', 10.00, 50.00, 10.00, 10.00);

-- Adicione aqui as outras tabelas do seu sistema
-- (usuários, transações, raspadinhas, etc.)
