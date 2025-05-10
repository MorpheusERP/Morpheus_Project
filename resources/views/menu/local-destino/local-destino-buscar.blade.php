<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MorpheusERP - Busca de Local de Destino</title>
    <link rel="shortcut icon" href="{{ asset('images/logo.png') }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @include('layouts.nav_bottom')
    @include('layouts.background')
    
    @vite(['resources/css/menu/local-destino/local-destino.css'])

</head>
<body>
    <div class="header">
        <h1>Busca de Local de Destino</h1>
    </div>
    
    <div class="container">
        <div class="form">
            <div class="Conteudo">
                <form id="consultaForm" autocomplete="off">
                    <div class="input-containe search-container">
                        <input type="text" id="nome" class="input-field" maxlength="34" placeholder="Nome do Local">
                        <button type="button" class="search-button" onclick="consultarLocal()">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <select id="tipo" class="input-field">
                        <option value="" selected>Todos os Tipos</option>
                        <option value="Descarte">Descarte</option>
                        <option value="Reaproveitamento">Reaproveitamento</option>
                    </select>
                </form>
                
                <div id="mensagemErro" style="display: none;"></div>
                
                <!-- Resultados da busca -->
                <div class="resultado-container" id="resultadoContainer" style="display: none;">
                    <div class="resultado-titulo">Resultados da Busca</div>
                    <div class="lista-usuarios" id="listaLocais"></div>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <div class="BotoesFooter">
            <div class="buttons-search">
                <a href="{{ route('menu.local-destino.local-destino') }}">
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

    <!-- Scripts -->
    <script>
        // Função para consultar o Local de Destino
        function consultarLocal() {
            const resultadoContainer = document.getElementById('resultadoContainer');
            const listaLocais = document.getElementById('listaLocais');
            
            // Mostrar indicador de carregamento
            const searchButton = document.querySelector('.search-button');
            searchButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            searchButton.disabled = true;
            
            // Obter valores dos campos
            const nome = document.getElementById('nome').value.trim();
            const tipo = document.getElementById('tipo').value.trim();
            
            // Verificar se pelo menos um campo foi preenchido
            if (!nome && !tipo) {
                mostrarErro('Preencha pelo menos um campo para realizar a busca');
                searchButton.innerHTML = '<i class="fas fa-search"></i>';
                searchButton.disabled = false;
                resultadoContainer.style.display = 'none';
                return;
            }

            // Esconder mensagem de erro se estiver visível
            document.getElementById('mensagemErro').style.display = 'none';

            // Obtém o token CSRF
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // Cria um objeto FormData para enviar
            const formData = new FormData();
            formData.append('nome_Local', nome);
            formData.append('tipo_Local', tipo);

            // Envia a requisição com fetch API
            fetch('{{ route("menu.local-destino.search") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro na resposta do servidor: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                // Verificar se temos resultados e se são um array
                if (data.status === "sucesso" && Array.isArray(data.resultados)) {
                    exibirResultados(data.resultados);
                } else if (data.status === "sucesso" && data.resultados) {
                    // Se não for um array mas existir, converta para array
                    exibirResultados(Object.values(data.resultados));
                } else {
                    listaLocais.innerHTML = '<div class="sem-resultados">Nenhum resultado encontrado</div>';
                    mostrarErro(data.mensagem || 'Nenhum resultado encontrado');
                }
                resultadoContainer.style.display = 'block';
                // Restaurar botão de busca
                searchButton.innerHTML = '<i class="fas fa-search"></i>';
                searchButton.disabled = false;
            })
            .catch(error => {
                console.error("Erro:", error);
                listaLocais.innerHTML = '<div class="sem-resultados">Erro ao processar a solicitação</div>';
                mostrarErro("Ocorreu um erro ao processar a solicitação: " + error.message);
                resultadoContainer.style.display = 'block';
                // Restaurar botão de busca
                searchButton.innerHTML = '<i class="fas fa-search"></i>';
                searchButton.disabled = false;
            });
        }
        
        // Função para exibir os resultados da busca
        function exibirResultados(resultados) {
            const listaLocais = document.getElementById('listaLocais');
            const resultadoContainer = document.getElementById('resultadoContainer');
            
            // Limpar lista anterior
            listaLocais.innerHTML = '';
            
            if (resultados.length === 0) {
                listaLocais.innerHTML = '<div class="sem-resultados">Nenhum local de destino encontrado</div>';
                
                // Adicionar animação de aparecimento suave
                resultadoContainer.style.opacity = '0';
                resultadoContainer.style.display = 'block';
                setTimeout(() => {
                    resultadoContainer.style.opacity = '1';
                    resultadoContainer.style.transition = 'opacity 0.3s ease';
                }, 10);
                
                return;
            }
            
            resultados.forEach((local, index) => {
                const itemLocal = document.createElement('div');
                itemLocal.className = 'usuario-item';
                
                itemLocal.innerHTML = `
                    <div class="usuario-info">
                        <div class="usuario-nome">${local.nome_Local}</div>
                        <div class="usuario-tipo">Tipo: ${local.tipo_Local || 'N/A'}</div>
                    </div>
                    <div class="usuario-acoes">
                        <a href="{{ route('menu.local-destino.local-destino-editar') }}?id_Local=${local.id_Local}" class="btn-editar" title="Editar">
                            <i class="fas fa-pencil-alt"></i>
                        </a>
                    </div>
                `;
                
                listaLocais.appendChild(itemLocal);
            });
            
            // Adicionar animação de aparecimento suave
            resultadoContainer.style.opacity = '0';
            resultadoContainer.style.display = 'block';
            setTimeout(() => {
                resultadoContainer.style.opacity = '1';
                resultadoContainer.style.transition = 'opacity 0.3s ease';
            }, 10);
        }
        
        function mostrarSucesso(mensagem) {
            const mensagemSucesso = document.createElement('div');
            mensagemSucesso.id = 'mensagemSucesso';
            mensagemSucesso.innerText = mensagem;
            mensagemSucesso.style.display = 'block';
            mensagemSucesso.style.backgroundColor = 'rgba(40, 167, 69, 0.8)';
            mensagemSucesso.style.color = 'white';
            mensagemSucesso.style.padding = '12px 20px';
            mensagemSucesso.style.borderRadius = '12px';
            mensagemSucesso.style.margin = '15px 0';
            mensagemSucesso.style.width = '100%';
            mensagemSucesso.style.textAlign = 'center';
            
            // Remover mensagem existente se houver
            const existente = document.getElementById('mensagemSucesso');
            if (existente) {
                existente.remove();
            }
            
            // Esconder mensagem de erro se estiver visível
            document.getElementById('mensagemErro').style.display = 'none';
            
            // Adicionar a mensagem ao DOM
            const conteudo = document.querySelector('.Conteudo');
            conteudo.insertBefore(mensagemSucesso, document.getElementById('resultadoContainer'));
            
            // Remover após alguns segundos
            setTimeout(() => {
                mensagemSucesso.remove();
            }, 3000);
        }
        
        function mostrarErro(mensagem) {
            const mensagemErro = document.getElementById('mensagemErro');
            mensagemErro.innerText = mensagem;
            mensagemErro.style.display = 'block';
            mensagemErro.style.backgroundColor = 'rgba(220, 53, 69, 0.8)';
            mensagemErro.style.color = 'white';
            mensagemErro.style.padding = '12px 20px';
            mensagemErro.style.borderRadius = '12px';
            mensagemErro.style.marginTop = '15px';
            
            // Remover mensagem de sucesso se existir
            const mensagemSucesso = document.getElementById('mensagemSucesso');
            if (mensagemSucesso) {
                mensagemSucesso.remove();
            }
            
            // Esconder após alguns segundos
            setTimeout(() => {
                mensagemErro.style.display = 'none';
            }, 4000);
        }
        
        // Permitir submissão do formulário ao pressionar Enter
        document.getElementById('consultaForm').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                consultarLocal();
            }
        });
    </script>
</body>
</html>