<?php
session_start();

include '../conexao.php';// Conexão com o banco de dados

// Obtém as informações do usuário da sessão
$id_Usuario = $_SESSION['id_Usuario'];
$nome_Usuario = $_SESSION['usuario'];

// Obtém os dados enviados pelo JavaScript
$formData = json_decode(file_get_contents("php://input"), true);
if (!$formData) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Dados inválidos.']);
    exit;
}
$valor_Total = 0;

foreach ($formData as $produtos) {
    // Busca o preço de custo para cada produto
    $stmtBusca = $mysqli->prepare("SELECT preco_Custo FROM produto WHERE cod_Produto = ?");
    $stmtBusca->bind_param("i", $produtos['cod_Produto']);
    $stmtBusca->execute();
    $stmtBusca->bind_result($preco_Custo);
    $stmtBusca->fetch();
    $stmtBusca->close();

    // Calcula o valor total multiplicando a quantidade pelo preço de custo
    $valor_Total += $produtos['quantidade'] * $preco_Custo;
}

$valor_Lote = $valor_Total;

// Insere um novo registro na tabela `lote_saida` para gerar um `id_Lote`
$stmtLote = $mysqli->prepare("INSERT INTO lote_saida (id_Usuario, nome_Usuario, valor_Lote) VALUES (?, ?, ?)");
$stmtLote->bind_param("isd", $id_Usuario, $nome_Usuario, $valor_Lote);
$stmtLote->execute();
$id_Lote = $mysqli->insert_id;
$stmtLote->close();

if ($id_Lote) {
    // Insere cada produto na tabela `saida_produtos` usando o `id_Lote` gerado
    $stmtProdutos = $mysqli->prepare("INSERT INTO saida_produtos (imagem, cod_Produto, nome_Produto, preco_Custo, id_Local, nome_Local, tipo_Local, qtd_Saida, grupo, sub_Grupo, observacao, id_Lote) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    foreach ($formData as $produtos) {

        // Busca o preço de custo para cada produto
        $stmtBusca = $mysqli->prepare("SELECT preco_Custo, grupo, sub_Grupo FROM produto WHERE cod_Produto = ?");
        $stmtBusca->bind_param("i", $produtos['cod_Produto']);
        $stmtBusca->execute();
        $stmtBusca->bind_result($preco_Custo, $grupo, $sub_Grupo);
        $stmtBusca->fetch();
        $stmtBusca->close();

        // Verifica se a imagem foi enviada
        $caminhoImagem = null; // Caminho padrão se a imagem não for enviada
        if (!empty($produtos['imagem'])) {
            // Remove o prefixo 'data:image/jpeg;base64,' (ou qualquer outro formato)
            $imagemBase64 = preg_replace('/^data:image\/\w+;base64,/', '', $produtos['imagem']);
            
            // Decodifica a imagem
            $imagemBinaria = base64_decode($imagemBase64);

            // Define um nome único para a imagem e o caminho para salvá-la
            $nomeImagem = uniqid('img_', true) . '.jpg';
            $caminhoImagem = 'imagens/' . $nomeImagem;

            // Salva a imagem na pasta "imagens/"
            file_put_contents($caminhoImagem, $imagemBinaria);
        }
      
        $stmtProdutos->bind_param(
            "sisdissdsssi",
            $caminhoImagem,
            $produtos['cod_Produto'],
            $produtos['nome_Produto'],
            $preco_Custo,
            $produtos['id_Local'],
            $produtos['nome_Local'],
            $produtos['tipo_Local'],
            $produtos['quantidade'],
            $grupo,
            $sub_Grupo,
            $produtos['observacao'],
            $id_Lote
        );
        $stmtProdutos->execute();
    }

    $stmtProdutos->close();

    echo json_encode(['status' => 'sucesso', 'mensagem' => 'Saida adicionada com sucesso!', 'id_Lote' => $id_Lote]);
} else {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao inserir o lote.']);
}

$mysqli->close();
?>
