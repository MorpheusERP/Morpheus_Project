<?php
session_start();
include '../conexao.php'; // Conexão com o banco de dados

// Recebe o id_Lote enviado pelo JavaScript
$data = json_decode(file_get_contents('php://input'), true);
$id_Lote = $data['id_Lote'] ?? null;

if ($id_Lote) {
    // Array para armazenar os dados do lote
    $lote = [];

    // 1. Consulta para obter dados do lote na tabela `lote_Entrada`
    $sqlLote = "SELECT id_Lote, id_Usuario, nome_Usuario, data_Entrada, valor_Lote
                FROM lote_entrada
                WHERE id_Lote = ?";
    $stmtLote = $mysqli->prepare($sqlLote);

    if ($stmtLote) {
        $stmtLote->bind_param("i", $id_Lote);
        $stmtLote->execute();
        $resultLote = $stmtLote->get_result();

        // Verifica se o lote foi encontrado
        if ($resultLote->num_rows > 0) {
            $dadosLote = $resultLote->fetch_assoc();

            // Adiciona os dados do lote ao array $lote
            $lote = [
                'id_Lote' => $dadosLote['id_Lote'],
                'id_Usuario' => $dadosLote['id_Usuario'],
                'nome_Usuario' => $dadosLote['nome_Usuario'],
                'data_Entrada' => (new DateTime($dadosLote['data_Entrada']))->format('d/m/Y'),
                'valor_Lote' => 'R$ ' . number_format($dadosLote['valor_Lote'], 2, ',', '.')
            ];
        } else {
            echo json_encode(['status' => 'erro', 'mensagem' => 'Lote não encontrado.']);
            exit();
        }

        $stmtLote->close();
    } else {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Erro na consulta do lote.']);
        exit();
    }

    // 2. Consulta para obter os produtos associados ao id_Lote na tabela `entrada_Produtos`
    $produtos = []; // Array para armazenar os produtos

    $sqlProdutos = "SELECT * FROM entrada_Produtos WHERE id_Lote = ?";
    $stmtProdutos = $mysqli->prepare($sqlProdutos);

    if ($stmtProdutos) {
        $stmtProdutos->bind_param("i", $id_Lote);
        $stmtProdutos->execute();
        $resultProdutos = $stmtProdutos->get_result();

        // Armazena os produtos associados ao lote
        while ($row = $resultProdutos->fetch_assoc()) {
            // Para cada produto, realiza a consulta para obter o tipo_Produto
            $id_Produto = $row['cod_Produto']; // Supondo que 'cod_Produto' seja o identificador do produto

            // Consulta para obter o tipo do produto
            $sqlTipoProduto = "SELECT tipo_Produto FROM produto WHERE cod_Produto = ?";
            $stmtTipoProduto = $mysqli->prepare($sqlTipoProduto);

            if ($stmtTipoProduto) {
                $stmtTipoProduto->bind_param("s", $id_Produto);
                $stmtTipoProduto->execute();
                $resultTipoProduto = $stmtTipoProduto->get_result();

                if ($resultTipoProduto->num_rows > 0) {
                    $dadosTipoProduto = $resultTipoProduto->fetch_assoc();
                    $row['tipo_Produto'] = $dadosTipoProduto['tipo_Produto']; // Adiciona o tipo do produto ao array
                } else {
                    $row['tipo_Produto'] = 'Desconhecido'; // Se não encontrar o tipo, define como 'Desconhecido'
                }

                $stmtTipoProduto->close();
            }

            // Adiciona o produto com o tipo ao array $produtos
            $produtos[] = $row;
        }

        $stmtProdutos->close();
    } else {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Erro na consulta dos produtos.']);
        exit();
    }

    // Retorna o array completo com os dados do lote e os produtos em dois arrays separados
    echo json_encode([
        'status' => 'sucesso',
        'lote' => $lote,
        'produtos' => $produtos
    ]);
} else {
    echo json_encode(['status' => 'erro', 'mensagem' => 'id_Lote não fornecido.']);
}

$mysqli->close();
?>
