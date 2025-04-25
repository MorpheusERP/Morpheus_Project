<?php
//Este arquivo realiza a conexão com o banco de dados, sendo referenciado em todos os outros arquivos PHP
    $host = "127.0.0.1";
    $user = "root";
    $password = "#$61@0Aljk";
    $database = "morpheusbd";

// Cria uma nova conexão com o MySQL
$mysqli = new mysqli($host, $user, $password, $database);

if ($mysqli->connect_error) {
    echo json_encode(["status" => "erro", "mensagem" => "Erro ao conectar ao banco de dados."]);
    exit(); // Encerra o script
}

// Defina o charset para evitar problemas com caracteres especiais
$mysqli->set_charset("utf8");
?>