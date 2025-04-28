<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MorpheusERP - Busca de Fornecedor</title>
    <link rel="shortcut icon" href="{{ asset('images/logo.png') }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @include('layouts.nav_bottom')
    @include('layouts.background')
    
    @vite(['resources/css/menu/fornecedor/fornecedor.css'])

    <script>
        //Função para verificar se o usuário está logado
        function verificarLogin() {
            fetch('../../../Backend/verificalogin.php')
                .then(response => response.json())
                .then(data => {
                    if (!data.logado) {
                        // Redireciona para a página de login se não estiver logado
                        window.location.href = '../../../index.html';
                    }
                })
                .catch(error => {
                    console.error("Erro ao verificar autenticação:", error);
                });
        }

        // Executa a função quando a página é carregada
        document.addEventListener("DOMContentLoaded", verificarLogin);
    </script>
</head>
<body>
    <div class="header">
        <h1>Busca de Fornecedor</h1>
    </div>
    
    <div class="container">
        <div class="form">
            <div class="Conteudo">
                <form id="consultaForm" autocomplete="off">
                    <div class="input-containe search-container">
                        <input type="text" id="razao" class="input-field" maxlength="150" placeholder="Razão Social">
                        <button type="button" class="search-button" onclick="consultarFornecedor()">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <input type="text" id="fantasia" class="input-field" maxlength="50" placeholder="Nome Fantasia">
                    <input type="text" id="apelido" class="input-field" maxlength="50" placeholder="Apelido">
                    <input type="text" id="grupo" class="input-field" maxlength="50" placeholder="Grupo">
                    <input type="text" id="subgrupo" class="input-field" maxlength="50" placeholder="Sub.Grupo">
                </form>
                
                <div id="mensagemErro" style="display: none;"></div>
                
                <!-- Resultados da busca -->
                <div class="resultado-container" id="resultadoContainer" style="display: none;">
                    <div class="resultado-titulo">Resultados da Busca</div>
                    <div class="lista-usuarios" id="listaFornecedores"></div>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <div class="BotoesFooter">
            <div class="buttons-search">
                <a href="{{ route('menu.fornecedor.fornecedor') }}">
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
        // Função para consultar o Fornecedor
        function consultarFornecedor() {
            const resultadoContainer = document.getElementById('resultadoContainer');
            const listaFornecedores = document.getElementById('listaFornecedores');
            
            // Mostrar indicador de carregamento
            const searchButton = document.querySelector('.search-button');
            searchButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            searchButton.disabled = true;
            
            // Obter valores dos campos
            const razao = document.getElementById('razao').value.trim();
            const fantasia = document.getElementById('fantasia').value.trim();
            const apelido = document.getElementById('apelido').value.trim();
            const grupo = document.getElementById('grupo').value.trim();
            const subgrupo = document.getElementById('subgrupo').value.trim();
            
            // Verificar se pelo menos um campo foi preenchido
            if (!razao && !fantasia && !apelido && !grupo && !subgrupo) {
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

            // Cria um objeto com os dados do fornecedor
            const formData = new FormData();
            formData.append('razao_Social', razao);
            formData.append('nome_Fantasia', fantasia);
            formData.append('apelido', apelido);
            formData.append('grupo', grupo);
            formData.append('sub_Grupo', subgrupo);

            // Envia os dados para o Laravel
            fetch('{{ route("menu.fornecedor.search") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: formData
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
                if (data.status === "sucesso") {
                    exibirResultados(data.resultados);
                } else if (data.status === 'erro') {
                    listaFornecedores.innerHTML = '<div class="sem-resultados">Nenhum resultado encontrado</div>';
                    mostrarErro(data.mensagem);
                }
                // Restaurar botão de busca
                searchButton.innerHTML = '<i class="fas fa-search"></i>';
                searchButton.disabled = false;
            })
            .catch(error => {
                console.error("Erro:", error);
                listaFornecedores.innerHTML = '<div class="sem-resultados">Erro ao processar a solicitação</div>';
                mostrarErro("Ocorreu um erro ao processar a solicitação");
                searchButton.innerHTML = '<i class="fas fa-search"></i>';
                searchButton.disabled = false;
            });
        }
        
        // Função para exibir os resultados da busca
        function exibirResultados(resultados) {
            const listaFornecedores = document.getElementById('listaFornecedores');
            const resultadoContainer = document.getElementById('resultadoContainer');
            
            // Limpar lista anterior
            listaFornecedores.innerHTML = '';
            
            if (resultados.length === 0) {
                listaFornecedores.innerHTML = '<div class="sem-resultados">Nenhum fornecedor encontrado</div>';
                
                // Adicionar animação de aparecimento suave
                resultadoContainer.style.opacity = '0';
                resultadoContainer.style.display = 'block';
                setTimeout(() => {
                    resultadoContainer.style.opacity = '1';
                    resultadoContainer.style.transition = 'opacity 0.3s ease';
                }, 10);
                
                return;
            }
            
            resultados.forEach((fornecedor, index) => {
                const itemFornecedor = document.createElement('div');
                itemFornecedor.className = 'usuario-item';
                
                itemFornecedor.innerHTML = `
                    <div class="usuario-info">
                        <div class="usuario-nome">${fornecedor.razao_Social}</div>
                        <div class="usuario-email">${fornecedor.nome_Fantasia || 'Sem nome fantasia'}</div>
                        <div class="usuario-tipo">Grupo: ${fornecedor.grupo || 'N/A'}</div>
                    </div>
                    <div class="usuario-acoes">
                        <a href="{{ route('menu.fornecedor.fornecedor-editar') }}?id_Fornecedor=${fornecedor.id_Fornecedor}" class="btn-editar" title="Editar">
                            <i class="fas fa-pencil-alt"></i>
                        </a>
                    </div>
                `;
                
                listaFornecedores.appendChild(itemFornecedor);
            });
            
            // Adicionar animação de aparecimento suave
            resultadoContainer.style.opacity = '0';
            resultadoContainer.style.display = 'block';
            setTimeout(() => {
                resultadoContainer.style.opacity = '1';
                resultadoContainer.style.transition = 'opacity 0.3s ease';
            }, 10);
        }
        
        // Função para editar um fornecedor
        function editarFornecedor(id) {
            window.location.href = `{{ route('menu.fornecedor.fornecedor-editar') }}?id_Fornecedor=${id}`;
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
                consultarFornecedor();
            }
        });
    </script>
</body>
</html>