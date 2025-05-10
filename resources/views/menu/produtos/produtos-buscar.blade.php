<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MorpheusERP - Busca de Produtos</title>
    <link rel="shortcut icon" href="{{ asset('images/logo.png') }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @include('layouts.nav_bottom')
    @include('layouts.background')
    
    @vite(['resources/css/menu/produtos/produtos.css'])

</head>
<body>
    <div class="header">
        <h1>Busca de Produtos</h1>
    </div>
    
    <div class="container">
        <div class="form">
            <div class="Conteudo">
                <form id="consultaForm" autocomplete="off">
                    <div class="input-container search-container">
                        <input type="number" id="codigo" class="input-field" placeholder="Código do Produto">
                        <button type="button" class="search-button" onclick="consultarProdutos()">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <input type="text" id="produto" class="input-field" placeholder="Nome do Produto">
                    <input type="text" id="grupo" class="input-field" placeholder="Grupo">
                    <input type="text" id="subgrupo" class="input-field" placeholder="Sub. Grupo">
                </form>
                
                <div id="mensagemErro" style="display: none;"></div>
                
                <!-- Resultados da busca -->
                <div class="resultado-container" id="resultadoContainer" style="display: none;">
                    <div class="resultado-titulo">Resultados da Busca</div>
                    <table id="resultadoTabela" style="display:none;">
                        <thead>
                            <tr>
                                <th>COD</th>
                                <th>Nome</th>
                                <th>P.Custo</th>
                                <th>Grupo</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody id="resultadoCorpo"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <div class="BotoesFooter">
            <div class="buttons-search">
                <a href="{{ route('menu.produtos.produtos') }}">
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
        // Função para consultar o Produto
        function consultarProdutos() {
            const resultadoContainer = document.getElementById('resultadoContainer');
            const resultadoTabela = document.getElementById('resultadoTabela');
            const corpoTabela = document.getElementById('resultadoCorpo');
            
            // Mostrar indicador de carregamento
            const searchButton = document.querySelector('.search-button');
            searchButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            searchButton.disabled = true;
            
            // Obter valores dos campos
            const codigo = document.getElementById('codigo').value.trim();
            const produto = document.getElementById('produto').value.trim();
            const grupo = document.getElementById('grupo').value.trim();
            const subgrupo = document.getElementById('subgrupo').value.trim();
            
            // Verificar se pelo menos um campo foi preenchido
            if (!codigo && !produto && !grupo && !subgrupo) {
                mostrarErro('Preencha pelo menos um campo para realizar a busca');
                searchButton.innerHTML = '<i class="fas fa-search"></i>';
                searchButton.disabled = false;
                resultadoContainer.style.display = 'none';
                return;
            }

            // Esconder mensagem de erro se estiver visível
            document.getElementById('mensagemErro').style.display = 'none';
            
            // Pegar o token CSRF
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            // Criação da URL com parâmetros de consulta
            let url = '{{ route("produto.search") }}?';
            const params = new URLSearchParams();
            
            if (codigo) params.append('cod_Produto', codigo);
            if (produto) params.append('nome_Produto', produto);
            if (grupo) params.append('grupo', grupo);
            if (subgrupo) params.append('sub_Grupo', subgrupo);
            
            url += params.toString();

            // Envia a requisição para a rota do Laravel
            fetch(url, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Erro na requisição: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.status === "sucesso") {
                    exibirResultados(data.produtos);
                } else if (data.status === 'erro'){
                    mostrarErro(data.mensagem || 'Nenhum resultado encontrado');
                    resultadoContainer.style.display = 'none';
                }
                // Restaurar botão de busca
                searchButton.innerHTML = '<i class="fas fa-search"></i>';
                searchButton.disabled = false;
            })
            .catch(error => {
                console.error("Erro:", error);
                mostrarErro("Ocorreu um erro ao processar a solicitação: " + error.message);
                searchButton.innerHTML = '<i class="fas fa-search"></i>';
                searchButton.disabled = false;
                resultadoContainer.style.display = 'none';
            });
        }
        
        // Função para exibir os resultados da busca
        function exibirResultados(resultados) {
            const resultadoCorpo = document.getElementById('resultadoCorpo');
            const resultadoTabela = document.getElementById('resultadoTabela');
            const resultadoContainer = document.getElementById('resultadoContainer');
            
            // Limpar resultados anteriores
            resultadoCorpo.innerHTML = '';
            
            if (resultados.length === 0) {
                resultadoContainer.style.display = 'block';
                resultadoCorpo.innerHTML = '<tr><td colspan="5" class="sem-resultados">Nenhum produto encontrado</td></tr>';
                resultadoTabela.style.display = 'table';
                return;
            }
            
            // Adiciona as novas linhas com os resultados
            resultados.forEach(produto => {
                const linha = document.createElement('tr');
                
                linha.innerHTML = `
                    <td>${produto.cod_Produto}</td>
                    <td>${produto.nome_Produto}</td>
                    <td>R$ ${Number(produto.preco_Custo).toFixed(2)}</td>
                    <td>${produto.grupo}</td>
                    <td>
                        <a href="{{ route('menu.produtos.produtos-editar') }}?cod_Produto=${produto.cod_Produto}&editar=true" class="btn-editar" title="Editar">
                            <i class="fas fa-pencil-alt"></i>
                        </a>
                    </td>
                `;
                
                resultadoCorpo.appendChild(linha);
            });
            
            // Adicionar animação de aparecimento suave
            resultadoContainer.style.opacity = '0';
            resultadoContainer.style.display = 'block';
            resultadoTabela.style.display = 'table';
            
            setTimeout(() => {
                resultadoContainer.style.opacity = '1';
                resultadoContainer.style.transition = 'opacity 0.3s ease';
            }, 10);
        }
        
        function mostrarErro(mensagem) {
            const mensagemErro = document.getElementById('mensagemErro');
            mensagemErro.innerText = mensagem;
            mensagemErro.style.display = 'block';
            mensagemErro.style.backgroundColor = 'rgba(220, 53, 69, 0.8)';
            mensagemErro.style.color = 'white';
            
            // Esconder após alguns segundos
            setTimeout(() => {
                mensagemErro.style.opacity = '0.7';
            }, 3000);
            
            setTimeout(() => {
                mensagemErro.style.display = 'none';
                mensagemErro.style.opacity = '1';
            }, 4000);
        }
        
        // Permitir submissão do formulário ao pressionar Enter
        document.getElementById('consultaForm').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                consultarProdutos();
            }
        });
    </script>
</body>
</html>