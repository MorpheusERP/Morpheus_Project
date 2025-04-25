<?php
//Este arquivo serve para adicionar um Produto, com base na requisição do arquivo Cadastro.html

include '../conexao.php'; 

header('Content-Type: application/json');

error_reporting(E_ALL); // Relatar todos os erros
ini_set('display_errors', 1); // Exibir erros

try {
    // Recebe os dados do formulário
    $cod_Produto = $_POST['codigo'];
    $nome_Produto = $_POST['nome'];
    $tipo_Produto = $_POST['tipo'];
    $cod_Barras = $_POST['codigoBarras'] ?? null;
    $preco_Custo = $_POST['precoCusto'];
    $preco_Venda = $_POST['precoVenda'] ?? null;
    $grupo = $_POST['grupo'];
    $sub_Grupo = $_POST['subgrupo'] ?? null;
    $observacao = $_POST['observacoes'] ?? null;
// Processamento da imagem (verifica se a imagem foi enviada)
$imagem = $_FILES['imagem'] ?? null;
if ($imagem && $imagem['tmp_name']) {
    $imagemNome = $imagem['name'];
    $imagemTemp = $imagem['tmp_name'];
    $imagemDestino = "imagens/" . uniqid() . '_' . $imagemNome;
    
    // Move o arquivo para a pasta de imagens
    if (!move_uploaded_file($imagemTemp, $imagemDestino)) {
        throw new Exception("Erro ao salvar imagem.");
    }
} else {
    // Define um valor padrão para o campo imagem (pode ser NULL ou uma imagem padrão)
    $imagemDestino = 'imagens/defaultimg.png';
}
    // Verifica se o Produto já existe
    $sqlCheck = "SELECT * FROM produto WHERE cod_Produto = ?";
    $stmtCheck = $mysqli->prepare($sqlCheck);
    $stmtCheck->bind_param("i", $cod_Produto);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();

    if ($resultCheck->num_rows > 0) {
        echo json_encode(["status" => "erro", "mensagem" => "Erro: Produto já cadastrado."]);
        $stmtCheck->close();
        $mysqli->close();
        exit();
    }
    $stmtCheck->close(); // Fecha o statement do SELECT para liberar a próxima operação

    // Insere os dados no banco de dados
    $sql = "INSERT INTO produto (cod_Produto, imagem, preco_Venda, nome_Produto, tipo_Produto, cod_Barras, preco_Custo, grupo, sub_Grupo, observacao) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    // Prepara a declaração para evitar injeção de SQL
    $stmt = $mysqli->prepare($sql);

    if (!$stmt) {
        echo json_encode(["status" => "erro", "mensagem" => "Erro na preparação da consulta: ".$mysqli->error]);
    }

    $stmt->bind_param("isdssidsss", $cod_Produto, $imagemDestino, $preco_Venda, $nome_Produto, $tipo_Produto, $cod_Barras, $preco_Custo, $grupo, $sub_Grupo, $observacao);

    if ($stmt->execute()) {
        echo json_encode(["status" => "sucesso", "mensagem" => "Produto adicionado com sucesso!"]);
    } else {
        throw new Exception("Erro ao inserir no banco de dados: " . $stmt->error);
    }

    $stmt->close();
} catch (Exception $e) {
    echo json_encode(["status" => "erro", "mensagem" => $e->getMessage()]);
}
?>
