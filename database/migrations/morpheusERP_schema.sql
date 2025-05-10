-- MySQL dump for MorpheusERP
-- ------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `usuario`
--

DROP TABLE IF EXISTS `usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuario` (
  `id_Usuario` int NOT NULL AUTO_INCREMENT,
  `nivel_Usuario` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nome_Usuario` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `sobrenome` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `funcao` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `logi` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `senha` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id_Usuario`,`nome_Usuario`),
  UNIQUE KEY `nome_Usuario_2` (`nome_Usuario`),
  FULLTEXT KEY `nome_Usuario` (`nome_Usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=3031 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fornecedor`
--

DROP TABLE IF EXISTS `fornecedor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fornecedor` (
  `id_Fornecedor` int NOT NULL AUTO_INCREMENT,
  `razao_Social` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nome_Fantasia` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `apelido` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `grupo` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `sub_Grupo` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `observacao` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id_Fornecedor`,`razao_Social`),
  UNIQUE KEY `razao_Social` (`razao_Social`)
) ENGINE=InnoDB AUTO_INCREMENT=568 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `produto`
--

DROP TABLE IF EXISTS `produto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `produto` (
  `cod_Produto` int NOT NULL,
  `imagem` longblob,
  `nome_Produto` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `tipo_Produto` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `cod_Barras` int DEFAULT NULL,
  `preco_Custo` double NOT NULL,
  `preco_Venda` double DEFAULT NULL,
  `grupo` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `sub_Grupo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `observacao` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`cod_Produto`,`nome_Produto`,`preco_Custo`),
  UNIQUE KEY `nome_Produto` (`nome_Produto`),
  UNIQUE KEY `preco_Custo` (`preco_Custo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `local_destino`
--

DROP TABLE IF EXISTS `local_destino`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `local_destino` (
  `id_Local` int NOT NULL AUTO_INCREMENT,
  `nome_Local` varchar(34) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `tipo_Local` varchar(34) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `observacao` varchar(34) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id_Local`,`nome_Local`),
  UNIQUE KEY `nome_Local` (`nome_Local`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `estoque` (Primeira tabela com chave estrangeira)
--

DROP TABLE IF EXISTS `estoque`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `estoque` (
  `id_Estoque` int NOT NULL AUTO_INCREMENT,
  `cod_Produto` int NOT NULL,
  `qtd_Estoque` float DEFAULT NULL,
  PRIMARY KEY (`id_Estoque`),
  UNIQUE KEY `id_Estoque` (`id_Estoque`),
  KEY `cod_Produto` (`cod_Produto`),
  CONSTRAINT `estoque_ibfk_1` FOREIGN KEY (`cod_Produto`) REFERENCES `produto` (`cod_Produto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `entrada_produtos` (Segunda tabela com chave estrangeira)
--

DROP TABLE IF EXISTS `entrada_produtos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `entrada_produtos` (
  `id_Entrada` int NOT NULL AUTO_INCREMENT,
  `id_Usuario` int NOT NULL,
  `nome_Usuario` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `id_Fornecedor` int NOT NULL,
  `razao_Social` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `cod_Produto` int NOT NULL,
  `nome_Produto` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `id_Estoque` int NOT NULL,
  `qtd_Entrada` float NOT NULL,
  `preco_Custo` double NOT NULL,
  `preco_Venda` float DEFAULT NULL,
  `valor_Total` double DEFAULT NULL,
  `data_Entrada` date DEFAULT NULL,
  PRIMARY KEY (`id_Entrada`),
  KEY `cod_Produto` (`cod_Produto`),
  KEY `id_Usuario` (`id_Usuario`),
  KEY `nome_Usuario` (`nome_Usuario`),
  KEY `id_Fornecedor` (`id_Fornecedor`),
  KEY `razao_Social` (`razao_Social`),
  KEY `nome_Produto` (`nome_Produto`),
  KEY `id_Estoque` (`id_Estoque`),
  KEY `preco_Custo` (`preco_Custo`),
  CONSTRAINT `entrada_produtos_ibfk_1` FOREIGN KEY (`cod_Produto`) REFERENCES `produto` (`cod_Produto`),
  CONSTRAINT `entrada_produtos_ibfk_2` FOREIGN KEY (`id_Usuario`) REFERENCES `usuario` (`id_Usuario`),
  CONSTRAINT `entrada_produtos_ibfk_3` FOREIGN KEY (`nome_Usuario`) REFERENCES `usuario` (`nome_Usuario`),
  CONSTRAINT `entrada_produtos_ibfk_4` FOREIGN KEY (`id_Fornecedor`) REFERENCES `fornecedor` (`id_Fornecedor`),
  CONSTRAINT `entrada_produtos_ibfk_5` FOREIGN KEY (`razao_Social`) REFERENCES `fornecedor` (`razao_Social`),
  CONSTRAINT `entrada_produtos_ibfk_6` FOREIGN KEY (`nome_Produto`) REFERENCES `produto` (`nome_Produto`),
  CONSTRAINT `entrada_produtos_ibfk_7` FOREIGN KEY (`id_Estoque`) REFERENCES `estoque` (`id_Estoque`),
  CONSTRAINT `entrada_produtos_ibfk_8` FOREIGN KEY (`preco_Custo`) REFERENCES `produto` (`preco_Custo`)
) ENGINE=InnoDB AUTO_INCREMENT=501 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `saida_produtos` (Última tabela com chave estrangeira)
--

DROP TABLE IF EXISTS `saida_produtos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `saida_produtos` (
  `id_Saida` int NOT NULL AUTO_INCREMENT,
  `imagem` longblob,
  `id_usuario` int NOT NULL,
  `nome_Usuario` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `cod_Produto` int NOT NULL,
  `nome_Produto` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `preco_Custo` double NOT NULL,
  `id_Local` int NOT NULL,
  `nome_Local` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `id_Estoque` int NOT NULL,
  `qtd_saida` double NOT NULL,
  `valor_Total` double DEFAULT NULL,
  `observacao` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `data_Saida` date DEFAULT NULL,
  PRIMARY KEY (`id_Saida`),
  KEY `cod_Produto` (`cod_Produto`),
  KEY `id_usuario` (`id_usuario`),
  KEY `nome_Usuario` (`nome_Usuario`),
  KEY `nome_Produto` (`nome_Produto`),
  KEY `id_Estoque` (`id_Estoque`),
  KEY `preco_Custo` (`preco_Custo`),
  KEY `id_Local` (`id_Local`),
  KEY `nome_Local` (`nome_Local`),
  CONSTRAINT `saida_produtos_ibfk_1` FOREIGN KEY (`cod_Produto`) REFERENCES `produto` (`cod_Produto`),
  CONSTRAINT `saida_produtos_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_Usuario`),
  CONSTRAINT `saida_produtos_ibfk_3` FOREIGN KEY (`nome_Usuario`) REFERENCES `usuario` (`nome_Usuario`),
  CONSTRAINT `saida_produtos_ibfk_4` FOREIGN KEY (`nome_Produto`) REFERENCES `produto` (`nome_Produto`),
  CONSTRAINT `saida_produtos_ibfk_5` FOREIGN KEY (`id_Estoque`) REFERENCES `estoque` (`id_Estoque`),
  CONSTRAINT `saida_produtos_ibfk_6` FOREIGN KEY (`preco_Custo`) REFERENCES `produto` (`preco_Custo`),
  CONSTRAINT `saida_produtos_ibfk_7` FOREIGN KEY (`id_Local`) REFERENCES `local_destino` (`id_Local`),
  CONSTRAINT `saida_produtos_ibfk_8` FOREIGN KEY (`nome_Local`) REFERENCES `local_destino` (`nome_Local`)
) ENGINE=InnoDB AUTO_INCREMENT=8967 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;