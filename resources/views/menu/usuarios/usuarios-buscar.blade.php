<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Busca de Usuários</title>
    <link rel="shortcut icon" href="{{ asset('images/logo.png') }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @include('layouts.nav_bottom')
    @include('layouts.background')
    
    @vite(['resources/css/menu/usuarios/usuarios.css'])
</head>
<body>
    <div class="header">
        <h1>Busca de Usuários</h1>
    </div>
    <div class="container">
        <div class="form">
            <div class="Conteudo">
                <form id="consultaForm" autocomplete="off">
                    <div class="input-containe search-container">
                        <input type="text" id="searchTerm" class="input-field" placeholder="Digite nome ou email do usuário">
                        <button type="button" class="search-button" onclick="buscarUsuarios()">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
                
                <div id="mensagemErro" style="display: none;"></div>
                
                <div id="resultado-container" class="resultado-container" style="display: none;">
                    <div class="resultado-titulo">Resultados da Busca</div>
                    <div id="lista-usuarios" class="lista-usuarios"></div>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <div class="BotoesFooter">
            <div class="buttons-search">
                <a href="{{ route('menu.usuarios.usuarios') }}">
                    <button class="search">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </button>
                </a>
            </div>
        </div>
    </footer>

    <div class="logo">
        <img src="{{ asset('images/Emporio maxx s-fundo.png') }}" alt="Empório Maxx Logo">
    </div>

    <script>
        function mostrarErro(mensagem) {
            const mensagemErro = document.getElementById('mensagemErro');
            mensagemErro.innerText = mensagem;
            mensagemErro.style.display = 'block';
            mensagemErro.style.backgroundColor = 'rgba(220, 53, 69, 0.8)';
            mensagemErro.style.color = 'white';
            setTimeout(() => {
                mensagemErro.style.display = 'none';
            }, 3000);
        }
        
        document.getElementById('searchTerm').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                buscarUsuarios();
            }
        });
        
        function buscarUsuarios() {
            // Limpar mensagens de erro anteriores
            document.getElementById('mensagemErro').style.display = 'none';
            
            // Mostrar indicador de carregamento
            const searchButton = document.querySelector('.search-button');
            searchButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            searchButton.disabled = true;
            
            // Obter o termo de busca
            const searchTerm = document.getElementById('searchTerm').value.trim();
            
            if (!searchTerm) {
                mostrarErro('Por favor, digite um termo para busca');
                searchButton.innerHTML = '<i class="fas fa-search"></i>';
                searchButton.disabled = false;
                return;
            }
            
            // Obter o token CSRF
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            // Fazer a requisição para o servidor
            fetch('{{ route("menu.usuarios.search") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ searchTerm: searchTerm })
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.mensagem || 'Erro ao processar requisição');
                    });
                }
                return response.json();
            })
            .then(data => {
                exibirResultados(data.usuarios);
                // Restaurar botão de busca
                searchButton.innerHTML = '<i class="fas fa-search"></i>';
                searchButton.disabled = false;
            })
            .catch(error => {
                console.error('Erro:', error);
                mostrarErro(error.message || 'Ocorreu um erro ao buscar usuários');
                searchButton.innerHTML = '<i class="fas fa-search"></i>';
                searchButton.disabled = false;
            });
        }
        
        function exibirResultados(usuarios) {
            const listaUsuarios = document.getElementById('lista-usuarios');
            const resultadoContainer = document.getElementById('resultado-container');
            
            // Limpar lista anterior
            listaUsuarios.innerHTML = '';
            
            if (usuarios.length === 0) {
                listaUsuarios.innerHTML = '<div class="sem-resultados">Nenhum usuário encontrado</div>';
                resultadoContainer.style.display = 'block';
                return;
            }
            
            // Adicionar cada usuário na lista
            usuarios.forEach(usuario => {
                const item = document.createElement('div');
                item.className = 'usuario-item';
                
                item.innerHTML = `
                    <div class="usuario-info">
                        <div class="usuario-nome">${usuario.nome_Usuario}</div>
                        <div class="usuario-email">${usuario.email}</div>
                        <div class="usuario-tipo">${usuario.tipo_Usuario === 'admin' ? 'Administrador' : 'Padrão'}</div>
                    </div>
                    <div class="usuario-acoes">
                        <a href="{{ route('menu.usuarios.usuarios-editar') }}?id=${usuario.id_Usuario}" class="btn-editar" title="Editar">
                            <i class="fas fa-pencil-alt"></i>
                        </a>
                    </div>
                `;
                
                listaUsuarios.appendChild(item);
            });
            
            // Adicionar animação de aparecimento suave
            resultadoContainer.style.opacity = '0';
            resultadoContainer.style.display = 'block';
            setTimeout(() => {
                resultadoContainer.style.opacity = '1';
                resultadoContainer.style.transition = 'opacity 0.3s ease';
            }, 10);
        }
    </script>
</body>
</html>