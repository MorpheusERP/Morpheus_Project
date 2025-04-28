-- MySQL dump 10.13  Distrib 8.0.40, for Win64 (x86_64)
--
-- Host: localhost    Database: 
-- ------------------------------------------------------
-- Server version	8.0.40
--
-- Current Database: `projeto_gestao`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `projeto_gestao` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;

USE `projeto_gestao`;

--
-- Table structure for table `entrada_produtos`
--

DROP TABLE IF EXISTS `entrada_produtos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `entrada_produtos` (
  `id_Entrada` int NOT NULL AUTO_INCREMENT,
  `id_Fornecedor` int NOT NULL,
  `razao_Social` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `cod_Produto` int NOT NULL,
  `nome_Produto` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `qtd_Entrada` float NOT NULL,
  `preco_Custo` double NOT NULL,
  `id_Lote` int NOT NULL,
  PRIMARY KEY (`id_Entrada`),
  KEY `razao_Social_idx` (`razao_Social`),
  KEY `id_Fornecedor_idx` (`id_Fornecedor`),
  KEY `nome_Produto_idx` (`nome_Produto`),
  KEY `cod_Produto_fkep_idx` (`cod_Produto`),
  KEY `id_Lote_fke_idx` (`id_Lote`),
  CONSTRAINT `cod_Produto_fkep` FOREIGN KEY (`cod_Produto`) REFERENCES `produto` (`cod_Produto`),
  CONSTRAINT `id_Fornecedor_fkep` FOREIGN KEY (`id_Fornecedor`) REFERENCES `fornecedor` (`id_Fornecedor`),
  CONSTRAINT `id_Lote_fke` FOREIGN KEY (`id_Lote`) REFERENCES `lote_entrada` (`id_Lote`),
  CONSTRAINT `nome_Produto_fkep` FOREIGN KEY (`nome_Produto`) REFERENCES `produto` (`nome_Produto`),
  CONSTRAINT `razao_Social_fkep` FOREIGN KEY (`razao_Social`) REFERENCES `fornecedor` (`razao_Social`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `entrada_produtos`
--

LOCK TABLES `entrada_produtos` WRITE;
/*!40000 ALTER TABLE `entrada_produtos` DISABLE KEYS */;
/*!40000 ALTER TABLE `entrada_produtos` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `Update_Estoque` AFTER INSERT ON `entrada_produtos` FOR EACH ROW BEGIN
	-- Verifica se o produto já existe no estoque
    IF EXISTS (SELECT * FROM estoque WHERE cod_Produto = NEW.cod_Produto) THEN
        -- Se o produto existe, atualiza a quantidade somando com a nova entrada
        UPDATE estoque
        SET qtd_Estoque = qtd_Estoque + NEW.qtd_Entrada,
			id_Entrada = NEW.id_Entrada
        WHERE cod_Produto = NEW.cod_Produto;
    ELSE
        -- Se o produto não existe, insere um novo registro no estoque
        INSERT INTO estoque (cod_Produto, qtd_Estoque, id_Entrada)
        VALUES (NEW.cod_Produto, NEW.qtd_Entrada, NEW.id_Entrada);
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `estoque`
--

DROP TABLE IF EXISTS `estoque`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `estoque` (
  `id_Estoque` int NOT NULL AUTO_INCREMENT,
  `cod_Produto` int NOT NULL,
  `qtd_Estoque` float DEFAULT NULL,
  `id_Entrada` int DEFAULT NULL,
  `id_Saida` int DEFAULT NULL,
  PRIMARY KEY (`id_Estoque`),
  UNIQUE KEY `id_Estoque` (`id_Estoque`),
  KEY `cod_Produto_fke_idx` (`cod_Produto`),
  KEY `id_Saida_fke_idx` (`id_Saida`),
  KEY `id_Entrada_fke_idx` (`id_Entrada`),
  CONSTRAINT `cod_Produto_fke` FOREIGN KEY (`cod_Produto`) REFERENCES `produto` (`cod_Produto`),
  CONSTRAINT `id_Entrada_fke` FOREIGN KEY (`id_Entrada`) REFERENCES `entrada_produtos` (`id_Entrada`),
  CONSTRAINT `id_Saida_fke` FOREIGN KEY (`id_Saida`) REFERENCES `saida_produtos` (`id_Saida`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `estoque`
--

LOCK TABLES `estoque` WRITE;
/*!40000 ALTER TABLE `estoque` DISABLE KEYS */;
/*!40000 ALTER TABLE `estoque` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fornecedor`
--

DROP TABLE IF EXISTS `fornecedor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fornecedor` (
  `id_Fornecedor` int NOT NULL AUTO_INCREMENT,
  `razao_Social` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `nome_Fantasia` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `apelido` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `grupo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `sub_Grupo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `observacao` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  PRIMARY KEY (`id_Fornecedor`,`razao_Social`),
  UNIQUE KEY `razao_Social` (`razao_Social`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fornecedor`
--

LOCK TABLES `fornecedor` WRITE;
/*!40000 ALTER TABLE `fornecedor` DISABLE KEYS */;
INSERT INTO `fornecedor` VALUES (5,'Emporio Max LTDA','Emporio MaxX','','Frutas',NULL,''),(6,'Perboni LTDA','Perboni','','Frutas',NULL,''),(7,'AP Frutas','AP','','Frutas',NULL,''),(8,'Ceasa Goiania','Ceasa','','Frutas',NULL,''),(9,'Sorriso Folhagem','','','Folhagens',NULL,''),(10,'Marcelo Pequi','','','Verduras',NULL,''),(11,'Atacadão Condimentos','','','Condimentos',NULL,''),(12,'Ione Verduras','','','Verduras',NULL,''),(13,'Emporio MaxX','','','',NULL,'');
/*!40000 ALTER TABLE `fornecedor` ENABLE KEYS */;
UNLOCK TABLES;

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
  `observacao` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  PRIMARY KEY (`id_Local`,`nome_Local`),
  UNIQUE KEY `nome_Local` (`nome_Local`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `local_destino`
--

LOCK TABLES `local_destino` WRITE;
/*!40000 ALTER TABLE `local_destino` DISABLE KEYS */;
INSERT INTO `local_destino` VALUES (18,'Pablo','descarte','Lixo');
/*!40000 ALTER TABLE `local_destino` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lote_entrada`
--

DROP TABLE IF EXISTS `lote_entrada`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lote_entrada` (
  `id_Lote` int NOT NULL AUTO_INCREMENT,
  `id_Usuario` int NOT NULL,
  `nome_Usuario` varchar(120) NOT NULL,
  `data_Entrada` datetime NOT NULL,
  `valor_Total` double NOT NULL,
  PRIMARY KEY (`id_Lote`),
  KEY `id_Usuario_fke_idx` (`id_Usuario`),
  CONSTRAINT `id_Usuario_fke` FOREIGN KEY (`id_Usuario`) REFERENCES `usuario` (`id_Usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lote_entrada`
--

LOCK TABLES `lote_entrada` WRITE;
/*!40000 ALTER TABLE `lote_entrada` DISABLE KEYS */;
/*!40000 ALTER TABLE `lote_entrada` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `Get_Date` BEFORE INSERT ON `lote_entrada` FOR EACH ROW BEGIN
	-- Atribui a data e hora atuais à coluna `data_Entrada` antes da inserção
		SET NEW.data_Entrada = NOW();
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `produto`
--

DROP TABLE IF EXISTS `produto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `produto` (
  `cod_Produto` int NOT NULL,
  `imagem` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `preco_Venda` double DEFAULT NULL,
  `nome_Produto` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `tipo_Produto` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `cod_Barras` int DEFAULT NULL,
  `preco_Custo` double NOT NULL,
  `grupo` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `sub_Grupo` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `observacao` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  PRIMARY KEY (`cod_Produto`,`nome_Produto`),
  UNIQUE KEY `nome_Produto` (`nome_Produto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `produto`
--

LOCK TABLES `produto` WRITE;
/*!40000 ALTER TABLE `produto` DISABLE KEYS */;
INSERT INTO `produto` VALUES (2,'imagens/672b2af603291_1000097238.jpg',0,'Morango','Unidade',0,7.99,'Frutas',NULL,'Frutas do freezer '),(364,'imagens/672b966792185_1000170846.jpg',0,'Abacaxi Perola GG','Quilo',0,10.99,'Frutas','',''),(366,'imagens/672b1d3eb9ca8_Imagem do WhatsApp de 2024-11-06 à(s) 03.45.26_88b602e3.jpg',0,'Abacaxi Perola','Unidade',0,4.99,'Frutas',NULL,'');
/*!40000 ALTER TABLE `produto` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `saida_produtos`
--

DROP TABLE IF EXISTS `saida_produtos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `saida_produtos` (
  `id_Saida` int NOT NULL AUTO_INCREMENT,
  `imagem` longblob,
  `id_Usuario` int NOT NULL,
  `cod_Produto` int NOT NULL,
  `nome_Produto` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `preco_Custo` double NOT NULL,
  `id_Local` int NOT NULL,
  `nome_Local` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `qtd_saida` double NOT NULL,
  `valor_Total` double DEFAULT NULL,
  `observacao` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `data_Saida` datetime DEFAULT NULL,
  PRIMARY KEY (`id_Saida`),
  KEY `id_Local_idx` (`id_Local`) /*!80000 INVISIBLE */,
  KEY `nome_Local_idx` (`nome_Local`),
  KEY `nome_Produto_idx` (`nome_Produto`),
  KEY `id_Usuario_fksp_idx` (`id_Usuario`),
  KEY `cod_Produto_idx` (`cod_Produto`),
  CONSTRAINT `cod_Produto` FOREIGN KEY (`cod_Produto`) REFERENCES `produto` (`cod_Produto`),
  CONSTRAINT `id_Local_fksp` FOREIGN KEY (`id_Local`) REFERENCES `local_destino` (`id_Local`),
  CONSTRAINT `id_Usuario_fksp` FOREIGN KEY (`id_Usuario`) REFERENCES `usuario` (`id_Usuario`),
  CONSTRAINT `nome_Local_fksp` FOREIGN KEY (`nome_Local`) REFERENCES `local_destino` (`nome_Local`),
  CONSTRAINT `nome_Produto_fksp` FOREIGN KEY (`nome_Produto`) REFERENCES `produto` (`nome_Produto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `saida_produtos`
--

LOCK TABLES `saida_produtos` WRITE;
/*!40000 ALTER TABLE `saida_produtos` DISABLE KEYS */;
/*!40000 ALTER TABLE `saida_produtos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuario`
--

DROP TABLE IF EXISTS `usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuario` (
  `id_Usuario` int NOT NULL AUTO_INCREMENT,
  `nivel_Usuario` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nome_Usuario` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `sobrenome` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `funcao` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `login` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `senha` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`id_Usuario`),
  KEY `nome_Usuario` (`nome_Usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=3036 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario`
--

LOCK TABLES `usuario` WRITE;
/*!40000 ALTER TABLE `usuario` DISABLE KEYS */;
INSERT INTO `usuario` VALUES (3033,'admin','Vitor','Marques','Full','MH','$2y$10$bhuo5seuqeK0CttH0xaz3usGMqOW2IUcOCjenSh8Y4cuExPR6SVFG'),(3035,'padrao','Luighy','Silva lopes','Frontend','LG','$2y$10$4zEdDEnYeiHOJy3LRlz.dOZp8l0c4J/3X0RXqxgH0qfpToFbvUE6e');
/*!40000 ALTER TABLE `usuario` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!50606 SET GLOBAL INNODB_STATS_AUTO_RECALC=@OLD_INNODB_STATS_AUTO_RECALC */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-11-10  1:52:30
