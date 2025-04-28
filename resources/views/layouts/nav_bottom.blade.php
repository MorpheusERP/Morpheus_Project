<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Morpheus ERP</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/layouts/nav_bottom.css'])
    

</head>
<body>
    
    <div class="bottom-nav">
        <a href="{{ route('home') }}" class="active">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <a href="{{ route('menu.entrada-produtos.entrada-produtos') }}">
            <i class="fas fa-sign-in-alt"></i>
            <span>Entrada</span>
        </a>
        <a href="{{ route('menu.saida-produtos.saida-produtos') }}">
            <i class="fas fa-sign-out-alt"></i>
            <span>Saída</span>
        </a>
        <a href="{{ route('menu.relatorio.relatorio') }}">
            <i class="fas fa-chart-bar"></i>
            <span>Relatórios</span>
        </a>
        <a href="{{ route('menu.home.perfil') }}">
            <i class="fas fa-user"></i>
            <span>Perfil</span>
        </a>
    </div>

</body>
</html>