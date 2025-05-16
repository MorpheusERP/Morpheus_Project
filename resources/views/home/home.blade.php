<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="shortcut icon" href="{{ asset('images/logo.png') }}" />
    <title>Morpheus ERP</title>

    @include('layouts.background')
    @include('layouts.nav_bottom')
    
    @vite(['resources/css/home/home.css'])

</head>
<body>
    <div class="Fundo">
        <div class="Conteudo">
            <div id="Usuario">
                <a href="{{ route("menu.home.perfil") }}" title="Perfil do Usuário">
                    <img src="{{ asset('images/Usuario.png') }}" alt="Perfil">
                </a>
            </div>
            
            <div class="date-selector">
                <input type="date" id="datePicker" aria-label="Selecione uma Data" disabled/>
            </div>
            
            <div class="container">
                <div class="button-grid">
                    <a href="{{ route("menu.relatorio.relatorio") }}" class="grid-button">
                        <img src="{{ asset('images/relatorios.png') }}" alt="Relatórios">
                        <span>Relatórios</span>
                    </a>
                    
                    <a href="{{ route("menu.entrada-produtos.entrada-produtos") }}" class="grid-button">
                        <img src="{{ asset('images/entrada_produtos.png') }}" alt="Entrada">
                        <span>Entrada</span>
                    </a>

                    <a href="{{ route("menu.saida-produtos.saida-produtos") }}" class="grid-button">
                        <img src="{{ asset('images/saida_produtos.png') }}" alt="Saída">
                        <span>Saída</span>
                    </a>

                    @if ((Auth::check() && Auth::user()->tipo_Usuario == 'admin') || session('user_type') == 'admin')
                    <a href="{{ route('menu.produtos.produtos') }}" class="grid-button">
                        <img src="{{ asset('images/produtos.png') }}" alt="Produtos">
                        <span>Produtos</span>
                    </a>
                    
                    <a href="{{ route("menu.local-destino.local-destino") }}" class="grid-button">
                        <img src="{{ asset('images/local_destino.png') }}" alt="Local">
                        <span>Local Destino</span>
                    </a>
                    
                    <a href="{{ route("menu.fornecedor.fornecedor") }}" class="grid-button">
                        <img src="{{ asset('images/fornecedor.png') }}" alt="Fornecedor">
                        <span>Fornecedor</span>
                    </a>
                    
                    <a href="{{ route("menu.usuarios.usuarios") }}" class="grid-button">
                        <img src="{{ asset('images/cadastro_usuarios.png') }}" alt="Usuários">
                        <span>Usuários</span>
                    </a>
                    @endif
                </div>
            </div>
            
            <div class="Emporio">
                <img src="{{ asset('images/Emporio maxx s-fundo.png') }}" alt="Emporio Maxx">
            </div>
        </div>
    </div>
    
    <script>
        const dateInput = document.getElementById('datePicker');
        const today = new Date();
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const dd = String(today.getDate()).padStart(2, '0');
        dateInput.value = `${yyyy}-${mm}-${dd}`;
        
        // Adicionar suporte a feedback tátil para botões em dispositivos móveis
        document.querySelectorAll('.grid-button').forEach(button => {
            button.addEventListener('touchstart', function() {
                this.style.transform = 'scale(0.98)';
                this.style.background = 'rgba(255, 255, 255, 0.2)';
            });
            button.addEventListener('touchend', function() {
                this.style.transform = '';
                this.style.background = '';
            });
        });
    </script>
</body>
</html>