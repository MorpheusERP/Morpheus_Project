<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MorpheusERP - Entrada de Produtos</title>
    <link rel="shortcut icon" href="{{ asset('images/logo.png') }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @include('layouts.nav_bottom')
    @include('layouts.background')
    
    @vite(['resources/css/menu/entrada-produtos/entrada-produtos.css'])

    <style>
        /* Fix for search button alignment */
        .search-button {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            padding: 0;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
            cursor: pointer;
        }
        
        .search-button:hover {
            transform: translateY(-50%) scale(1.05);
        }
        
        .search-button:active {
            transform: translateY(-50%) scale(0.95);
        }
        
        .search-button::before {
            display: none;
        }
        
        /* Improved layout for the form */
        .container2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            width: 100%;
        }
        
        @media (max-width: 480px) {
            .container2 {
                grid-template-columns: 1fr;
            }
        }
        
        /* Fix for product search results */
        #produtosTable, #fornecedoresTable {
            width: 100%;
            border-collapse: collapse;
            border-radius: 8px;
            overflow: hidden;
        }
        
        #produtosTable thead th, #fornecedoresTable thead th {
            background-color: rgba(255, 239, 13, 0.2);
            color: var(--accent-color);
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
        }
        
        #produtosTable tbody tr, #fornecedoresTable tbody tr {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            transition: background-color 0.2s;
        }
        
        #produtosTable tbody tr:hover, #fornecedoresTable tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        #produtosTable td, #fornecedoresTable td {
            padding: 12px 15px;
            color: var(--input-text);
        }
        
        #produtoSearchResults, #fornecedorSearchResults {
            max-height: 300px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Entrada de Produtos</h1>
    </div>
    
    <div class="container">
        <div class="form">
            <div class="buttons" id="default-buttons">
                <button class="new" onclick="novo()">
                    <i class="fas fa-plus-circle"></i> Novo
                </button>
                <button class="search" onclick="window.location.href='{{ route('menu.entrada-produtos.entrada-produtos-buscar') }}'">
                    <i class="fas fa-search"></i> Buscar
                </button>
            </div>
            
            <div class="Conteudo">
                <div class="buttons" id="new-button" style="display: none;">
                    <button class="back" onclick="voltar(); recarregarPagina()">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </button>
                </div>
                
                <form id="entradaForm" autocomplete="off" enctype="multipart/form-data">
                    <div class="image-placeholder">
                        <img id="preview" src="{{ asset('images/defaultimg.png') }}" class="image-disabled">
                    </div>

                    <div class="search-container">
                        <input type="text" id="codigo" class="input-field" placeholder="* Código do Produto" required disabled readonly>
                        <button type="button" class="search-button" id="search-product-button" disabled>
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    
                    <input type="text" id="produto" class="input-field" placeholder="* Produto" required disabled readonly>
                    
                    <div class="container2">
                        <div class="search-container">
                            <input type="text" id="fornecedorText" class="input-field" placeholder="* Fornecedor" required disabled readonly>
                            <input type="hidden" id="fornecedor" required>
                            <button type="button" class="search-button" id="search-fornecedor-button" disabled>
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        
                        <input type="text" id="razaoSocial" class="input-field" placeholder="Razão Social" disabled readonly>
                        
                        <input type="number" id="quantidade" step="1" class="input-field" placeholder="* Quantidade" required disabled>
                        
                        <input type="number" id="precoCusto" step="0.01" class="input-field" placeholder="* Preço de Custo" required disabled>
                        
                        <input type="number" id="precoVenda" step="0.01" class="input-field" placeholder="Preço de Venda" disabled>
                        
                        <input type="date" id="dataEntrada" class="input-field" disabled>
                    </div>
                    
                    <div class="buttons-edit" id="edit-buttons" style="display: none;">
                        <button class="edit" type="submit">
                            <i class="fas fa-save"></i> Salvar
                        </button>
                    </div>
                </form>
                
                <div id="mensagemSucesso" style="display: none;"></div>
                <div id="mensagemErro" style="display: none;"></div>
            </div>
        </div>
        
        <table id="resultadoTabela" style="display: none;">
            <thead>
                <tr>
                    <th>Foto</th>
                    <th>Produto</th>
                    <th>Fornecedor</th>
                    <th>QTD</th>
                    <th>Custo</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
        
        <div class="buttons-search" id="clear-button" style="display: none; margin-top: 15px;">
            <button class="clear" onclick="limparTabela()">
                <i class="fas fa-trash-alt"></i> Limpar Tabela
            </button>
        </div>
    </div>

    <footer>
        <div class="BotoesFooter">
            <div class="buttons-search">
                <button class="exit" onclick="back()">
                    <i class="fas fa-home"></i> Voltar para Home
                </button>
            </div>
        </div>
    </footer>
    
    <div class="logo">
        <img src="{{ asset('images/Emporio maxx s-fundo.png') }}" alt="Empório Maxx Logo">
    </div>
    
    <!-- Modal Detalhes do Produto -->
    <div id="produtoModal" class="modal-Produto">
        <div class="modal-content-Produto">
            <span class="close" onclick="fecharModal()">&times;</span>
            <h2>Detalhes do Produto</h2>
            <div>
                <img id="modalImagem" src="" alt="Imagem do Produto" style="max-width: 150px; margin: 0 auto 20px; display: block; border-radius: 10px;">
                
                <label for="modalCodProduto">Código do Produto:</label>
                <input id="modalCodProduto" disabled>
                
                <label for="modalProduto">Produto:</label>
                <input id="modalProduto" disabled>
                
                <label for="modalFornecedor">Fornecedor:</label>
                <input id="modalFornecedor" disabled>
                
                <label for="modalQuantidade">Quantidade:</label>
                <input id="modalQuantidade" disabled>
                
                <label for="modalPrecoCusto">Preço de Custo:</label>
                <input id="modalPrecoCusto" disabled>
                
                <label for="modalPrecoVenda">Preço de Venda:</label>
                <input id="modalPrecoVenda" disabled>
            </div>
        </div>
    </div>
    
    <!-- Modal Busca de Produtos -->
    <div id="searchProductModal" class="modal-Produto">
        <div class="modal-content-Produto">
            <span class="close" onclick="fecharModalBuscaProduto()">&times;</span>
            <h2>Buscar Produto</h2>
            
            <div class="search-container" style="margin-bottom: 20px;">
                <input type="text" id="produtoSearch" class="input-field" placeholder="Digite para buscar...">
                <button type="button" class="search-button" id="btn-search-product">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            
            <div id="produtoSearchResults" style="max-height: 300px; overflow-y: auto;">
                <table id="produtosTable" style="width: 100%; margin-top: 15px;">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Produto</th>
                            <th>Grupo</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            
            <div id="searchProductMessage" style="margin-top: 15px; text-align: center; display: none;">
                Nenhum produto encontrado.
            </div>
        </div>
    </div>
    
    <!-- Modal Busca de Fornecedores -->
    <div id="searchFornecedorModal" class="modal-Produto">
        <div class="modal-content-Produto">
            <span class="close" onclick="fecharModalBuscaFornecedor()">&times;</span>
            <h2>Buscar Fornecedor</h2>
            
            <div class="search-container" style="margin-bottom: 20px;">
                <input type="text" id="fornecedorSearch" class="input-field" placeholder="Digite para buscar...">
                <button type="button" class="search-button" id="btn-search-fornecedor">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            
            <div id="fornecedorSearchResults" style="max-height: 300px; overflow-y: auto;">
                <table id="fornecedoresTable" style="width: 100%; margin-top: 15px;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Grupo</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            
            <div id="searchFornecedorMessage" style="margin-top: 15px; text-align: center; display: none;">
                Nenhum fornecedor encontrado.
            </div>
        </div>
    </div>
    
    <div id="loadingOverlay" style="display: none;">
        <div id="loadingSpinner"></div>
    </div>

    <script>
        function novo() {
            document.getElementById('default-buttons').style.display = 'none';
            document.getElementById('new-button').style.display = 'flex';
            document.getElementById('edit-buttons').style.display = 'flex';
            habilitarCampos();
        }

        function voltar() { 
            document.getElementById('new-button').style.display = 'none';
            document.getElementById('default-buttons').style.display = 'flex';
            document.getElementById('edit-buttons').style.display = 'none';
            desabilitarCampos();
            
            document.getElementById('entradaForm').reset();
            document.getElementById('preview').src = "{{ asset('images/defaultimg.png') }}";
            document.getElementById('preview').classList.remove('image-enabled');
            document.getElementById('preview').classList.add('image-disabled');
            
            document.getElementById('mensagemSucesso').style.display = 'none';
            document.getElementById('mensagemErro').style.display = 'none';
        }
        
        function habilitarCampos() {
            document.getElementById('preview').classList.remove('image-disabled');
            document.getElementById('preview').classList.add('image-enabled');
            document.getElementById('search-product-button').disabled = false;
            document.getElementById('search-fornecedor-button').disabled = false;
            document.getElementById('quantidade').disabled = false;
            document.getElementById('precoCusto').disabled = false;
            document.getElementById('precoVenda').disabled = false;
            document.getElementById('dataEntrada').disabled = false;
            
            // Definir data padrão
            const hoje = new Date();
            const dataFormatada = hoje.toISOString().split('T')[0];
            document.getElementById('dataEntrada').value = dataFormatada;
            
            // Produto e fornecedor permanecem readonly, mas visualmente ativos
            document.getElementById('codigo').classList.remove('image-disabled');
            document.getElementById('produto').classList.remove('image-disabled');
            document.getElementById('fornecedorText').classList.remove('image-disabled');
            document.getElementById('razaoSocial').classList.remove('image-disabled');
        }

        function desabilitarCampos() {
            document.getElementById('preview').classList.remove('image-enabled');
            document.getElementById('preview').classList.add('image-disabled');
            document.getElementById('search-product-button').disabled = true;
            document.getElementById('search-fornecedor-button').disabled = true;
            document.getElementById('quantidade').disabled = true;
            document.getElementById('precoCusto').disabled = true;
            document.getElementById('precoVenda').disabled = true;
            document.getElementById('dataEntrada').disabled = true;
            
            // Adicionar visual de desabilitado
            document.getElementById('codigo').classList.add('image-disabled');
            document.getElementById('produto').classList.add('image-disabled');
            document.getElementById('fornecedorText').classList.add('image-disabled');
            document.getElementById('razaoSocial').classList.add('image-disabled');
        }
        
        function recarregarPagina() {
            location.reload();
        }
        
        function mostrarSucesso(mensagem) {
            const mensagemSucesso = document.getElementById('mensagemSucesso');
            mensagemSucesso.innerText = mensagem;
            mensagemSucesso.style.display = 'block';
            mensagemSucesso.style.backgroundColor = 'rgba(40, 167, 69, 0.8)';
            mensagemSucesso.style.color = 'white';
            
            document.getElementById('mensagemErro').style.display = 'none';
        }
        
        function mostrarErro(mensagem) {
            const mensagemErro = document.getElementById('mensagemErro');
            mensagemErro.innerText = mensagem;
            mensagemErro.style.display = 'block';
            mensagemErro.style.backgroundColor = 'rgba(220, 53, 69, 0.8)';
            mensagemErro.style.color = 'white';
            
            document.getElementById('mensagemSucesso').style.display = 'none';
        }
        
        // Busca de produtos
        document.getElementById('search-product-button').addEventListener('click', function() {
            document.getElementById('searchProductModal').style.display = 'block';
            document.getElementById('produtoSearch').focus();
            
            // Carregar todos os produtos automaticamente ao abrir o modal
            buscarProdutos('');
        });
        
        document.getElementById('btn-search-product').addEventListener('click', function() {
            const termo = document.getElementById('produtoSearch').value.trim();
            buscarProdutos(termo);
        });
        
        document.getElementById('produtoSearch').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const termo = this.value.trim();
                buscarProdutos(termo);
            }
        });
        
        function buscarProdutos(termo) {
            document.getElementById('loadingOverlay').style.display = 'flex';
            document.getElementById('searchProductMessage').style.display = 'none';
            
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            fetch(`/produtos/search?termo=${encodeURIComponent(termo)}`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro na requisição: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log('Resposta da API:', data); // Para debugging
                
                if (data.status === 'sucesso' && Array.isArray(data.produtos) && data.produtos.length > 0) {
                    exibirProdutosTabela(data.produtos);
                } else {
                    document.querySelector('#produtosTable tbody').innerHTML = '';
                    document.getElementById('searchProductMessage').style.display = 'block';
                    document.getElementById('searchProductMessage').textContent = 'Nenhum produto encontrado.';
                }
            })
            .catch(error => {
                console.error('Erro ao buscar produtos:', error);
                document.getElementById('searchProductMessage').style.display = 'block';
                document.getElementById('searchProductMessage').textContent = 'Erro ao buscar produtos. Tente novamente mais tarde.';
            })
            .finally(() => {
                document.getElementById('loadingOverlay').style.display = 'none';
            });
        }
        
        function exibirProdutosTabela(produtos) {
            const tbody = document.querySelector('#produtosTable tbody');
            tbody.innerHTML = '';
            
            if (!produtos.length) {
                document.getElementById('searchProductMessage').style.display = 'block';
                document.getElementById('searchProductMessage').textContent = 'Nenhum produto encontrado.';
                return;
            }
            
            produtos.forEach(produto => {
                const row = tbody.insertRow();
                
                const cellCodigo = row.insertCell(0);
                cellCodigo.textContent = produto.cod_Produto || 'N/D';
                
                const cellNome = row.insertCell(1);
                cellNome.textContent = produto.nome_Produto || 'N/D';
                
                const cellGrupo = row.insertCell(2);
                cellGrupo.textContent = produto.grupo || 'N/D';
                
                row.style.cursor = 'pointer';
                row.addEventListener('click', () => {
                    selecionarProduto(produto);
                });
            });
        }
        
        function selecionarProduto(produto) {
            document.getElementById('codigo').value = produto.cod_Produto;
            document.getElementById('produto').value = produto.nome_Produto;
            document.getElementById('precoCusto').value = produto.preco_Custo || '';
            document.getElementById('precoVenda').value = produto.preco_Venda || '';
            
            if (produto.imagem) {
                document.getElementById('preview').src = `data:image/jpeg;base64,${produto.imagem}`;
            } else {
                document.getElementById('preview').src = "{{ asset('images/defaultimg.png') }}";
            }
            
            fecharModalBuscaProduto();
        }
        
        function fecharModalBuscaProduto() {
            document.getElementById('searchProductModal').style.display = 'none';
            document.getElementById('produtoSearch').value = '';
        }
        
        // Busca de fornecedores
        document.getElementById('search-fornecedor-button').addEventListener('click', function() {
            document.getElementById('searchFornecedorModal').style.display = 'block';
            document.getElementById('fornecedorSearch').focus();
            
            // Carregar todos os fornecedores automaticamente ao abrir o modal
            buscarFornecedores('');
        });
        
        document.getElementById('btn-search-fornecedor').addEventListener('click', function() {
            const termo = document.getElementById('fornecedorSearch').value.trim();
            buscarFornecedores(termo);
        });
        
        document.getElementById('fornecedorSearch').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const termo = this.value.trim();
                buscarFornecedores(termo);
            }
        });
        
        function buscarFornecedores(termo) {
            document.getElementById('loadingOverlay').style.display = 'flex';
            document.getElementById('searchFornecedorMessage').style.display = 'none';
            
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            console.log('Enviando busca de fornecedores com termo:', termo);
            
            fetch(`/fornecedor/search`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ termo: termo })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro na requisição: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log('Resposta da API de fornecedores:', data);
                
                if (data.status === 'sucesso') {
                    if (Array.isArray(data.fornecedores) && data.fornecedores.length > 0) {
                        exibirFornecedoresTabela(data.fornecedores);
                        document.getElementById('searchFornecedorMessage').style.display = 'none';
                    } else {
                        document.querySelector('#fornecedoresTable tbody').innerHTML = '';
                        document.getElementById('searchFornecedorMessage').style.display = 'block';
                        document.getElementById('searchFornecedorMessage').textContent = 'Nenhum fornecedor encontrado.';
                    }
                } else {
                    document.querySelector('#fornecedoresTable tbody').innerHTML = '';
                    document.getElementById('searchFornecedorMessage').style.display = 'block';
                    document.getElementById('searchFornecedorMessage').textContent = data.mensagem || 'Erro ao buscar fornecedores.';
                }
            })
            .catch(error => {
                console.error('Erro ao buscar fornecedores:', error);
                document.getElementById('searchFornecedorMessage').style.display = 'block';
                document.getElementById('searchFornecedorMessage').textContent = 'Erro ao buscar fornecedores. Tente novamente mais tarde.';
            })
            .finally(() => {
                document.getElementById('loadingOverlay').style.display = 'none';
            });
        }
        
        function exibirFornecedoresTabela(fornecedores) {
            const tbody = document.querySelector('#fornecedoresTable tbody');
            tbody.innerHTML = '';
            
            console.log('Exibindo ' + fornecedores.length + ' fornecedores na tabela');
            
            fornecedores.forEach(fornecedor => {
                console.log('Fornecedor:', fornecedor);
                const row = tbody.insertRow();
                
                const cellId = row.insertCell(0);
                cellId.textContent = fornecedor.id_Fornecedor || 'N/D';
                
                const cellNome = row.insertCell(1);
                cellNome.textContent = fornecedor.nome_Fantasia || fornecedor.razao_Social || 'N/D';
                
                const cellGrupo = row.insertCell(2);
                cellGrupo.textContent = fornecedor.grupo || 'N/D';
                
                row.style.cursor = 'pointer';
                row.addEventListener('click', () => {
                    selecionarFornecedor(fornecedor);
                });
            });
        }
        
        function selecionarFornecedor(fornecedor) {
            document.getElementById('fornecedorText').value = fornecedor.nome_Fantasia || fornecedor.razao_Social;
            document.getElementById('fornecedor').value = fornecedor.id_Fornecedor;
            document.getElementById('razaoSocial').value = fornecedor.razao_Social;
            
            fecharModalBuscaFornecedor();
        }
        
        function fecharModalBuscaFornecedor() {
            document.getElementById('searchFornecedorModal').style.display = 'none';
            document.getElementById('fornecedorSearch').value = '';
        }
        
        document.getElementById('entradaForm').addEventListener('submit', function(event) {
            event.preventDefault(); 

            const codigo = document.getElementById('codigo').value.trim();
            const produto = document.getElementById('produto').value.trim();
            const idFornecedor = document.getElementById('fornecedor').value.trim();
            const quantidade = document.getElementById('quantidade').value.trim();
            const precoCusto = document.getElementById('precoCusto').value.trim();
            const precoVenda = document.getElementById('precoVenda').value.trim();
            const dataEntrada = document

            if (!codigo || !produto || !idFornecedor || !quantidade || !precoCusto) {
                mostrarErro('Por favor, preencha todos os campos obrigatórios.');
                return;
            }
    
            const btnSalvar = document.querySelector('.edit');
            const originalBtnText = btnSalvar.innerHTML;
            btnSalvar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';
            btnSalvar.disabled = true;
            
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            const formData = new FormData();
            formData.append('cod_Produto', codigo);
            formData.append('id_Fornecedor', idFornecedor);
            formData.append('qtd_Entrada', quantidade);
            formData.append('preco_Custo', precoCusto);
            formData.append('preco_Venda', precoVenda);
            formData.append('data_Entrada', dataEntrada || new Date().toISOString().split('T')[0]);
    
            fetch('/home/entrada-produtos/store', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    if (response.status === 422) {
                        return response.json().then(data => {
                            throw new Error(Object.values(data.errors).flat().join(' '));
                        });
                    }
                    throw new Error('Erro na requisição: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'sucesso') {
                    mostrarSucesso(data.mensagem);
                    setTimeout(() => {
                        voltar();
                    }, 3000); 
                } else if (data.status === 'erro') {
                    mostrarErro(data.mensagem);
                }
            })
            .catch(error => {
                console.error("Erro:", error);
                mostrarErro(error.message || "Ocorreu um erro ao processar a solicitação");
            })
            .finally(() => {
                btnSalvar.innerHTML = originalBtnText;
                btnSalvar.disabled = false;
            });
        });
        
        function buscarURL() {
            const urlParams = new URLSearchParams(window.location.search);
            const id_Lote = urlParams.get("id_Lote");
            
            if (id_Lote) {
                document.getElementById('loadingOverlay').style.display = 'flex';
                
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                fetch('/home/entrada-produtos/find', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id_Lote: id_Lote })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erro na requisição: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.status === 'sucesso' && data.produtos && Array.isArray(data.produtos)) {
                        exibirTabelaEntradas(data.produtos);
                    } else {
                        mostrarErro('Nenhuma entrada encontrada para o ID do Lote fornecido.');
                    }
                })
                .catch(error => {
                    console.error('Erro ao buscar entradas:', error);
                    mostrarErro('Erro ao buscar dados: ' + error.message);
                })
                .finally(() => {
                    document.getElementById('loadingOverlay').style.display = 'none';
                });
            }

            window.history.replaceState({}, document.title, window.location.pathname);
        }
        
        function exibirTabelaEntradas(produtos) {
            document.getElementById("clear-button").style.display = "flex";
            const tabela = document.getElementById("resultadoTabela");
            const tbody = tabela.querySelector("tbody");
            tbody.innerHTML = ''; 
            tabela.style.display = 'table'; 

            produtos.forEach((produto) => {
                const row = tbody.insertRow();

                const cellImagem = row.insertCell(0);
                const imgElement = document.createElement("img");
                imgElement.src = produto.imagem ? `data:image/jpeg;base64,${produto.imagem}` : '{{ asset("images/defaultimg.png") }}';
                imgElement.alt = produto.nome_Produto || 'Imagem não disponível';
                imgElement.style.width = "50px";
                imgElement.style.height = "50px";
                imgElement.style.borderRadius = "6px";
                imgElement.style.objectFit = "cover";
                cellImagem.appendChild(imgElement);

                row.insertCell(1).textContent = produto.nome_Produto || 'Produto não disponível';
                row.insertCell(2).textContent = produto.razao_Social || 'Fornecedor não informado';
                row.insertCell(3).textContent = produto.qtd_Entrada || '0';
                row.insertCell(4).textContent = produto.preco_Custo ? `R$ ${produto.preco_Custo}` : 'N/A';

                row.addEventListener('click', () => abrirModal(produto));
            });
        }
        
        function formatarDinheiro(valor) {
            return parseFloat(valor).toLocaleString('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            });
        }
        
        function abrirModal(produto) {
            document.getElementById("modalImagem").src = produto.imagem ? `data:image/jpeg;base64,${produto.imagem}` : '{{ asset("images/defaultimg.png") }}';
            document.getElementById("modalCodProduto").value = produto.cod_Produto || '';
            document.getElementById("modalProduto").value = produto.nome_Produto || '';
            document.getElementById("modalFornecedor").value = produto.razao_Social || '';
            document.getElementById("modalQuantidade").value = produto.qtd_Entrada || '';
            document.getElementById("modalPrecoCusto").value = produto.preco_Custo ? formatarDinheiro(produto.preco_Custo) : 'N/A';
            document.getElementById("modalPrecoVenda").value = produto.preco_Venda ? formatarDinheiro(produto.preco_Venda) : 'N/A';
            
            document.getElementById("produtoModal").style.display = "block";
        }

        function fecharModal() {
            document.getElementById("produtoModal").style.display = "none";
        }

        function limparTabela() {
            const tabela = document.getElementById("resultadoTabela");
            const tbody = tabela.querySelector("tbody");
            tbody.innerHTML = '';
            tabela.style.display = 'none';
            document.getElementById("clear-button").style.display = "none";
        }
        
        function back() {
            window.location.href = "{{ route('home') }}";
        }
        
        document.addEventListener("DOMContentLoaded", function() {
            buscarURL();
            
            // Fechar modais ao clicar fora
            window.onclick = function(event) {
                if (event.target == document.getElementById('produtoModal')) {
                    fecharModal();
                }
                if (event.target == document.getElementById('searchProductModal')) {
                    fecharModalBuscaProduto();
                }
                if (event.target == document.getElementById('searchFornecedorModal')) {
                    fecharModalBuscaFornecedor();
                }
            }
        });
    </script>
</body>
</html>