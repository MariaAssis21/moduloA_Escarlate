-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 21/10/2026 às 20:38
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `senn_logistica`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `logs_notificacoes`
--

CREATE TABLE `logs_notificacoes` (
  `id` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `mensagem` text NOT NULL,
  `status` enum('Não Lido','Resolvido') DEFAULT 'Não Lido',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `resolvido_em` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `logs_notificacoes`
--

INSERT INTO `logs_notificacoes` (`id`, `tipo`, `mensagem`, `status`, `criado_em`, `resolvido_em`) VALUES
(1, 'pedido', 'Pedido PED-2004 (Editora Abril) está ATRASADO.', 'Não Lido', '2026-10-21 17:52:03', NULL),
(2, 'ia_risco', 'IA: Pedido PED-2004 com alto risco de falha (100%).', 'Não Lido', '2026-10-21 17:52:03', NULL),
(3, 'ia_risco', 'IA: Pedido PED-2004 com 99% de risco de falha. Ação: Replanejar rota.', 'Não Lido', '2026-10-21 17:54:18', NULL),
(4, 'ia_risco', 'IA: Pedido PED-9008 com 99% de risco de falha. Ação: Replanejar rota.', 'Não Lido', '2026-10-21 18:01:03', NULL),
(5, 'pedido', 'Pedido PED-9008 (Editora Globo) está ATRASADO.', 'Não Lido', '2026-10-21 18:01:03', NULL),
(6, 'ia_risco', 'IA: Pedido PED-9008 com alto risco de falha (100%).', 'Não Lido', '2026-10-21 18:01:03', NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `motoristas`
--

CREATE TABLE `motoristas` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `contato` varchar(20) NOT NULL,
  `categoria_cnh` enum('B','C','D','E') NOT NULL,
  `validade_cnh` date NOT NULL,
  `disponibilidade` enum('Disponível','Em Rota','Folga') NOT NULL DEFAULT 'Disponível',
  `veiculo_id` int(11) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `usuario_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `motoristas`
--

INSERT INTO `motoristas` (`id`, `nome`, `contato`, `categoria_cnh`, `validade_cnh`, `disponibilidade`, `veiculo_id`, `criado_em`, `usuario_id`) VALUES
(1, 'João Silva', '12999999999', 'C', '2028-11-30', 'Disponível', 1, '2026-10-21 17:55:49', 3);

-- --------------------------------------------------------

--
-- Estrutura para tabela `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `cliente` varchar(100) NOT NULL,
  `endereco` varchar(255) NOT NULL,
  `peso_kg` decimal(10,2) NOT NULL DEFAULT 1.00,
  `volume_m3` decimal(10,2) NOT NULL DEFAULT 0.01,
  `prioridade` enum('Baixa','Normal','Alta') NOT NULL DEFAULT 'Normal',
  `janela_inicio` datetime NOT NULL,
  `janela_fim` datetime NOT NULL,
  `status` enum('Pendente','Em Rota','Entregue','Atrasado') NOT NULL DEFAULT 'Pendente',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `comprovante_foto` longtext DEFAULT NULL,
  `comprovante_assinatura` longtext DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `entregue_em` datetime DEFAULT NULL,
  `ordem_rota` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `pedidos`
--

INSERT INTO `pedidos` (`id`, `codigo`, `cliente`, `endereco`, `peso_kg`, `volume_m3`, `prioridade`, `janela_inicio`, `janela_fim`, `status`, `criado_em`, `comprovante_foto`, `comprovante_assinatura`, `latitude`, `longitude`, `entregue_em`, `ordem_rota`) VALUES
(9, 'PED-H01', 'Padaria Real', 'Av. Paulista, 1000', 10.00, 0.10, 'Baixa', '2026-09-22 08:00:00', '2026-09-22 12:00:00', 'Entregue', '2026-09-21 13:00:00', NULL, NULL, -23.56168400, -46.65598100, '2026-09-22 09:30:00', NULL),
(10, 'PED-H02', 'Mercado Extra', 'Rua Augusta, 1200', 45.00, 0.50, 'Normal', '2026-09-23 10:00:00', '2026-09-23 14:00:00', 'Entregue', '2026-09-22 18:00:00', NULL, NULL, -23.55386200, -46.65491800, '2026-09-23 11:45:00', NULL),
(11, 'PED-H03', 'Farmácia Pague Menos', 'Av. Ibirapuera, 3000', 5.00, 0.05, 'Alta', '2026-09-24 09:00:00', '2026-09-24 11:00:00', 'Entregue', '2026-09-23 11:00:00', NULL, NULL, -23.61040900, -46.66645200, '2026-09-24 09:15:00', NULL),
(12, 'PED-H04', 'Loja Zara', 'Shopping Iguatemi', 25.00, 0.40, 'Normal', '2026-09-25 14:00:00', '2026-09-25 18:00:00', 'Entregue', '2026-09-24 14:00:00', NULL, NULL, -23.57555900, -46.68682800, '2026-09-25 15:30:00', NULL),
(13, 'PED-H05', 'Restaurante Fasano', 'Rua Vittorio Fasano, 88', 15.00, 0.20, 'Normal', '2026-09-26 11:00:00', '2026-09-26 15:00:00', 'Entregue', '2026-09-25 12:00:00', NULL, NULL, -23.56633500, -46.67135400, '2026-09-26 12:20:00', NULL),
(14, 'PED-H06', 'Hospital Sírio', 'Rua Dona Adma Jafet, 91', 120.00, 2.00, 'Alta', '2026-09-27 08:00:00', '2026-09-27 12:00:00', 'Entregue', '2026-09-26 19:00:00', NULL, NULL, -23.55563700, -46.65174200, '2026-09-27 10:00:00', NULL),
(15, 'PED-H07', 'Livraria Cultura', 'Av. Paulista, 2073', 35.00, 0.80, 'Baixa', '2026-09-28 10:00:00', '2026-09-28 16:00:00', 'Entregue', '2026-09-27 17:00:00', NULL, NULL, -23.56371100, -46.65847700, '2026-09-28 14:30:00', NULL),
(16, 'PED-H08', 'Supermercado Dia', 'Rua Pamplona, 800', 60.00, 1.00, 'Normal', '2026-09-29 09:00:00', '2026-09-29 13:00:00', 'Entregue', '2026-09-28 13:00:00', NULL, NULL, -23.56490800, -46.65253900, '2026-09-29 11:10:00', NULL),
(17, 'PED-H09', 'Magazine Luiza', 'Av. das Nações Unidas, 5000', 80.00, 1.50, 'Normal', '2026-09-30 08:00:00', '2026-09-30 12:00:00', 'Entregue', '2026-09-29 20:00:00', NULL, NULL, -23.62258800, -46.69867100, '2026-09-30 09:45:00', NULL),
(18, 'PED-H10', 'Pet Shop Cobasi', 'Rua Oscar Freire, 200', 20.00, 0.30, 'Baixa', '2026-10-01 13:00:00', '2026-10-01 17:00:00', 'Entregue', '2026-09-30 14:00:00', NULL, NULL, -23.56291200, -46.66981400, '2026-10-01 15:20:00', NULL),
(19, 'PED-H11', 'Rede DOr', 'Av. Brig. Faria Lima, 4000', 150.00, 2.50, 'Alta', '2026-10-02 10:00:00', '2026-10-02 14:00:00', 'Entregue', '2026-10-01 12:00:00', NULL, NULL, -23.58516000, -46.68204900, '2026-10-02 12:00:00', NULL),
(20, 'PED-H12', 'Cacau Show', 'Shopping Eldorado', 10.00, 0.15, 'Normal', '2026-10-03 11:00:00', '2026-10-03 15:00:00', 'Entregue', '2026-10-02 17:00:00', NULL, NULL, -23.57205000, -46.69632000, '2026-10-03 12:45:00', NULL),
(21, 'PED-H13', 'Havan', 'Av. Ibirapuera, 2000', 200.00, 4.00, 'Alta', '2026-10-04 08:00:00', '2026-10-04 12:00:00', 'Entregue', '2026-10-03 13:00:00', NULL, NULL, -23.60456500, -46.66521600, '2026-10-04 09:30:00', NULL),
(22, 'PED-H14', 'Carrefour', 'Av. Rebouças, 1500', 300.00, 5.50, 'Normal', '2026-10-05 09:00:00', '2026-10-05 13:00:00', 'Entregue', '2026-10-04 18:00:00', NULL, NULL, -23.56615000, -46.67702000, '2026-10-05 11:20:00', NULL),
(23, 'PED-H15', 'Drogasil', 'Av. Angélica, 1000', 25.00, 0.30, 'Baixa', '2026-10-06 10:00:00', '2026-10-06 14:00:00', 'Entregue', '2026-10-05 12:00:00', NULL, NULL, -23.54113000, -46.66238000, '2026-10-06 12:00:00', NULL),
(24, 'PED-H16', 'Centauro', 'Av. Paulista, 1230', 40.00, 0.60, 'Normal', '2026-10-07 14:00:00', '2026-10-07 18:00:00', 'Entregue', '2026-10-06 19:00:00', NULL, NULL, -23.56231000, -46.65625000, '2026-10-07 15:45:00', NULL),
(25, 'PED-H17', 'Kalunga', 'Av. Liberdade, 500', 60.00, 1.00, 'Normal', '2026-10-08 08:00:00', '2026-10-08 12:00:00', 'Entregue', '2026-10-07 14:00:00', NULL, NULL, -23.55778000, -46.63587000, '2026-10-08 10:10:00', NULL),
(26, 'PED-H18', 'Ponto Frio', 'Rua Teodoro Sampaio, 2000', 90.00, 1.20, 'Alta', '2026-10-09 09:00:00', '2026-10-09 11:00:00', 'Entregue', '2026-10-08 17:00:00', NULL, NULL, -23.55984000, -46.69176000, '2026-10-09 10:00:00', NULL),
(27, 'PED-H19', 'Riachuelo', 'Shopping Morumbi', 70.00, 1.00, 'Normal', '2026-10-10 10:00:00', '2026-10-10 16:00:00', 'Entregue', '2026-10-09 12:00:00', NULL, NULL, -23.62352000, -46.69969000, '2026-10-10 14:30:00', NULL),
(28, 'PED-H20', 'Giraffas', 'Av. Guarapiranga, 1000', 30.00, 0.40, 'Baixa', '2026-10-11 11:00:00', '2026-10-11 15:00:00', 'Entregue', '2026-10-10 16:00:00', NULL, NULL, -23.67158000, -46.72295000, '2026-10-11 12:15:00', NULL),
(29, 'PED-H21', 'Mercado Livre', 'Av. do Estado, 5000', 500.00, 10.00, 'Normal', '2026-10-12 08:00:00', '2026-10-12 17:00:00', 'Entregue', '2026-10-11 13:00:00', NULL, NULL, -23.57852000, -46.62257000, '2026-10-12 15:00:00', NULL),
(30, 'PED-H22', 'Boticário', 'Rua 25 de Março, 800', 15.00, 0.20, 'Alta', '2026-10-13 09:00:00', '2026-10-13 12:00:00', 'Entregue', '2026-10-12 17:00:00', NULL, NULL, -23.54228000, -46.63536000, '2026-10-13 10:30:00', NULL),
(31, 'PED-H23', 'Americanas', 'Av. Paulista, 1500', 110.00, 1.80, 'Normal', '2026-10-14 13:00:00', '2026-10-14 18:00:00', 'Entregue', '2026-10-13 14:00:00', NULL, NULL, -23.56381000, -46.65710000, '2026-10-14 16:20:00', NULL),
(32, 'PED-H24', 'Samsung', 'Faria Lima, 3000', 50.00, 0.80, 'Alta', '2026-10-15 10:00:00', '2026-10-15 12:00:00', 'Entregue', '2026-10-14 19:00:00', NULL, NULL, -23.57988000, -46.68967000, '2026-10-15 11:00:00', NULL),
(33, 'PED-H25', 'Apple Store', 'Shopping Morumbi', 5.00, 0.05, 'Normal', '2026-10-16 14:00:00', '2026-10-16 18:00:00', 'Entregue', '2026-10-15 13:00:00', NULL, NULL, -23.62352000, -46.69969000, '2026-10-16 15:45:00', NULL),
(34, 'PED-H26', 'Nike', 'Shopping Eldorado', 35.00, 0.50, 'Baixa', '2026-10-17 08:00:00', '2026-10-17 12:00:00', 'Entregue', '2026-10-16 12:00:00', NULL, NULL, -23.57205000, -46.69632000, '2026-10-17 09:30:00', NULL),
(35, 'PED-H27', 'Adidas', 'Av. Ibirapuera, 2500', 45.00, 0.70, 'Normal', '2026-10-18 10:00:00', '2026-10-18 15:00:00', 'Entregue', '2026-10-17 16:00:00', NULL, NULL, -23.60823000, -46.66578000, '2026-10-18 13:15:00', NULL),
(36, 'PED-H28', 'Zara', 'Shopping Iguatemi', 60.00, 1.10, 'Alta', '2026-10-19 09:00:00', '2026-10-19 11:00:00', 'Entregue', '2026-10-18 18:00:00', NULL, NULL, -23.57555900, -46.68682800, '2026-10-19 10:20:00', NULL),
(37, 'PED-H29', 'Renner', 'Shopping Morumbi', 80.00, 1.50, 'Normal', '2026-10-20 11:00:00', '2026-10-20 16:00:00', 'Entregue', '2026-10-19 12:00:00', NULL, NULL, -23.62352000, -46.69969000, '2026-10-20 14:10:00', NULL),
(38, 'PED-H30', 'C&A', 'Av. Paulista, 900', 90.00, 1.60, 'Baixa', '2026-10-21 08:00:00', '2026-10-21 12:00:00', 'Entregue', '2026-10-20 13:00:00', NULL, NULL, -23.56168400, -46.65598100, '2026-10-21 09:45:00', NULL),
(39, 'PED-9001', 'Amazon Logística', 'Av. Sumaré, 500', 250.00, 3.00, 'Alta', '2026-10-22 08:00:00', '2026-10-22 12:00:00', 'Pendente', '2026-10-21 18:01:00', NULL, NULL, -23.53851000, -46.67993000, NULL, NULL),
(40, 'PED-9002', 'Mercado Carrefour', 'Av. Rebouças, 1500', 400.00, 6.00, 'Alta', '2026-10-22 09:00:00', '2026-10-22 11:00:00', 'Pendente', '2026-10-21 18:01:00', NULL, NULL, -23.56615000, -46.67702000, NULL, NULL),
(41, 'PED-9003', 'Farmácia Onofre', 'Rua Vergueiro, 2000', 15.00, 0.10, 'Normal', '2026-10-22 10:00:00', '2026-10-22 15:00:00', 'Pendente', '2026-10-21 18:01:00', NULL, NULL, -23.58921000, -46.63458000, NULL, NULL),
(42, 'PED-9004', 'Fast Shop', 'Shopping Eldorado', 50.00, 0.80, 'Normal', '2026-10-22 13:00:00', '2026-10-22 18:00:00', 'Pendente', '2026-10-21 18:01:00', NULL, NULL, -23.57205000, -46.69632000, NULL, NULL),
(43, 'PED-9005', 'Restaurante Madero', 'Av. Paulista, 1500', 30.00, 0.20, 'Baixa', '2026-10-22 11:00:00', '2026-10-22 14:00:00', 'Pendente', '2026-10-21 18:01:00', NULL, NULL, -23.56381000, -46.65710000, NULL, NULL),
(44, 'PED-9006', 'Livraria Saraiva', 'Shopping Ibirapuera', 45.00, 0.60, 'Normal', '2026-10-22 14:00:00', '2026-10-22 20:00:00', 'Pendente', '2026-10-21 18:01:00', NULL, NULL, -23.61040900, -46.66645200, NULL, NULL),
(45, 'PED-9007', 'Óticas Carol', 'Rua Oscar Freire, 300', 5.00, 0.05, 'Alta', '2026-10-22 08:00:00', '2026-10-22 10:00:00', 'Pendente', '2026-10-21 18:01:00', NULL, NULL, -23.56350000, -46.66850000, NULL, NULL),
(46, 'PED-9008', 'Editora Globo', 'Av. das Nações Unidas, 7000', 800.00, 15.00, 'Alta', '2026-10-21 08:00:00', '2026-10-21 10:00:00', 'Pendente', '2026-10-21 18:01:00', NULL, NULL, -23.62258800, -46.69867100, NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `rastreamento`
--

CREATE TABLE `rastreamento` (
  `id` int(11) NOT NULL,
  `pedido_id` int(11) NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `status` varchar(50) NOT NULL,
  `registrado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `perfil` enum('admin','gestor','despachante','motorista') NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Ativo','Inativo') DEFAULT 'Ativo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `perfil`, `criado_em`, `status`) VALUES
(1, 'Fernanda Lima', 'gestor@senn.com', '$2y$10$rgnYAXLNPEVbQ9fjOjFXUe8y2ww9KMAldxv45X83WzNBLMS3Ll4Ca', 'gestor', '2026-10-21 17:53:15', 'Ativo'),
(2, 'Roberto Alves', 'despachante@senn.com', '$2y$10$K5DTJiOsTI1HLNrDv5FUiuPD8sdNZ110ZyFAgmr8kpm3EguWYxLra', 'despachante', '2026-10-21 17:53:45', 'Ativo'),
(3, 'João Silva', 'joao@senn.com', '$2y$10$QpyP0vGhQSIZEcXtrQLacufmdqQDw5TFCQUgpoaXFRJUVcYN/FXWO', 'motorista', '2026-10-21 17:55:49', 'Ativo'),
(4, 'Carlos Andrade', 'admin@senn.com', '$2y$10$CUCSbmsIO83BGDXL9orWzubCqXP8JTsDQ3dTpvdj3TMaOAOyZB8ue', 'admin', '2026-10-21 17:57:52', 'Ativo');

-- --------------------------------------------------------

--
-- Estrutura para tabela `veiculos`
--

CREATE TABLE `veiculos` (
  `id` int(11) NOT NULL,
  `placa` varchar(10) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `capacidade_kg` decimal(10,2) NOT NULL,
  `volume_m3` decimal(10,2) NOT NULL,
  `autonomia_km` int(11) NOT NULL,
  `status` enum('Disponível','Em Rota','Em Manutenção') NOT NULL DEFAULT 'Disponível',
  `proxima_manutencao` date NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `latitude` decimal(10,8) DEFAULT -23.55052000,
  `longitude` decimal(11,8) DEFAULT -46.63330800
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `veiculos`
--

INSERT INTO `veiculos` (`id`, `placa`, `tipo`, `capacidade_kg`, `volume_m3`, `autonomia_km`, `status`, `proxima_manutencao`, `criado_em`, `latitude`, `longitude`) VALUES
(1, 'ABC-1234', 'Caminhão Baú', 5000.00, 30.00, 450, 'Disponível', '2026-11-15', '2026-10-21 17:51:49', -23.55052000, -46.63330800),
(2, 'XYZ-9876', 'Van de Entrega', 1500.00, 10.00, 350, 'Disponível', '2026-12-01', '2026-10-21 17:51:49', -23.55052000, -46.63330800);

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `logs_notificacoes`
--
ALTER TABLE `logs_notificacoes`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `motoristas`
--
ALTER TABLE `motoristas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `veiculo_id` (`veiculo_id`),
  ADD KEY `fk_motorista_usuario` (`usuario_id`);

--
-- Índices de tabela `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`);

--
-- Índices de tabela `rastreamento`
--
ALTER TABLE `rastreamento`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pedido_id` (`pedido_id`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Índices de tabela `veiculos`
--
ALTER TABLE `veiculos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `placa` (`placa`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `logs_notificacoes`
--
ALTER TABLE `logs_notificacoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `motoristas`
--
ALTER TABLE `motoristas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT de tabela `rastreamento`
--
ALTER TABLE `rastreamento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `veiculos`
--
ALTER TABLE `veiculos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `motoristas`
--
ALTER TABLE `motoristas`
  ADD CONSTRAINT `fk_motorista_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `motoristas_ibfk_1` FOREIGN KEY (`veiculo_id`) REFERENCES `veiculos` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `rastreamento`
--
ALTER TABLE `rastreamento`
  ADD CONSTRAINT `rastreamento_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
