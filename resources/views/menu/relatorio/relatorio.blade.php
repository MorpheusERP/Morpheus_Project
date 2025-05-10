<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Relatórios</title>

    @include('layouts.background')
    @include('layouts.nav_bottom')

    @vite(['resources/css/menu/relatorio/relatorio.css'])
    
</head>

<body>
    <div class="Fundo">
        <div class="container">
            <header>
                <h1>Relatórios</h1>
            </header>
            <main class="main-container">
                <div class="grid-container">
                    <button id="entrada_produtos" onclick="window.location.href='{{ route('menu.relatorio.entradas-relatorio') }}'">
                        <img src="{{ asset('images/relatorio/relatorio-entrada-produto.png') }}" alt="Entrada Produtos">
                        <span>Entrada Produtos</span>
                    </button>
                    <button id="saida_produtos" onclick="window.location.href='{{ route('menu.relatorio.saidas-relatorio') }}'">
                        <img src="{{ asset('images/relatorio/relatorio-saida-produto.png') }}" alt="Saída Produtos">
                        <span>Saída Produtos</span>
                    </button>
                    @if ((Auth::check() && Auth::user()->tipo_Usuario == 'admin') || session('user_type') == 'admin')
                    <button id="local_destino" onclick="window.location.href='{{ route('menu.relatorio.locais-relatorio') }}'">
                       <img src="{{ asset('images/relatorio/relatorio-local-destino.png') }}" alt="Local Destino">
                       <span>Locais de destino</span>
                    </button>
                    <button id="produtos" onclick="window.location.href='{{ route('menu.relatorio.produtos-relatorio') }}'">
                        <img src="{{ asset('images/relatorio/relatorio-produto.png') }}" alt="Produtos">
                        <span>Produtos</span>
                    </button>
                    <button id="cadastro_usuario" onclick="window.location.href='{{ route('menu.relatorio.usuarios') }}'">
                        <img src="{{ asset('images/relatorio/relatorio-usuario.png') }}" alt="Usuários">
                        <span>Usuários</span>
                    </button>
                    @endif
                    <button id="fornecedor" onclick="window.location.href='{{ route('menu.relatorio.fornecedores-relatorio') }}'">
                        <img src="{{ asset('images/relatorio/relatorio-fornecedor.png') }}" alt="Fornecedores">
                        <span>Fornecedores</span>
                    </button>
                </div>
            </main>
            <footer>
                <button class="back-button" onclick="window.location.href='{{ route('home') }}'">
                    <img src="{{ asset('images/relatorio/seta.svg') }}" alt="Voltar">
                    <span>Sair</span>
                </button>
                <div class="logo">
                    <img src="{{ asset('images/Emporio maxx s-fundo.png') }}" alt="Logo Maxx">
                </div>
            </footer>
        </div>
    </div>
</body>
</html>