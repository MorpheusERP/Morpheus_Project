@extends('layouts.app')

@section('title', 'Editar Entrada de Produtos')

@section('header-title', 'Editar Entrada de Produtos')

@push('styles')
    @vite(['resources/css/menu/entrada-produtos/entrada-produtos.css'])
@endpush

@section('content')
    <div class="form">
        <div class="buttons">
            <button class="back" onclick="window.location.href='{{ route('menu.entrada-produtos.entrada-produtos-buscar') }}'">
                <i class="fas fa-arrow-left"></i> Voltar
            </button>
        </div>
        <div class="Conteudo">
            <form id="entradaForm" autocomplete="off">
                <input type="hidden" id="id_Entrada">
                <input type="hidden" id="cod_Produto">
                <div class="image-placeholder">
                    <img id="preview" src="{{ asset('images/defaultimg.png') }}" class="image-enabled">
                </div>
                <input type="text" id="produto" class="input-field" placeholder="Produto" disabled>
                <div class="container2">
                    <div class="search-container">
                        <input type="text" id="fornecedorText" class="input-field" placeholder="* Fornecedor" required>
                        <input type="hidden" id="fornecedor" required>
                        <button type="button" class="search-button" id="search-fornecedor-button">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <input type="text" id="razaoSocial" class="input-field" placeholder="Razão Social" disabled>
                    <input type="number" id="quantidade" step="0.01" min="0" class="input-field" placeholder="* Quantidade" required>
                    <input type="number" id="precoCusto" step="0.01" min="0.01" class="input-field" placeholder="* Preço de Custo" required>
                    <input type="number" id="precoVenda" step="0.01" min="0" class="input-field" placeholder="Preço de Venda">
                    <input type="date" id="dataEntrada" class="input-field" required>
                </div>
                <div class="buttons-edit">
                    <button type="submit" class="edit">
                        <i class="fas fa-save"></i> Salvar Alterações
                    </button>
                </div>
            </form>
            <div id="mensagemSucesso" style="display: none;"></div>
            <div id="mensagemErro" style="display: none;"></div>
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
@endsection

@section('footer')
    <div class="BotoesFooter">
        <div class="buttons-search">
            <button class="exit" onclick="window.location.href='{{ route('home') }}'">
                <i class="fas fa-home"></i> Voltar para Home
            </button>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Carregar dados da entrada pelo ID na URL
            carregarEntrada();
            
            // Configurar eventos
            document.getElementById('entradaForm').addEventListener('submit', atualizarEntrada);
            document.getElementById('search-fornecedor-button').addEventListener('click', abrirModalBuscaFornecedor);
            document.getElementById('btn-search-fornecedor').addEventListener('click', buscarFornecedores);
            document.getElementById('fornecedorSearch').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    buscarFornecedores();
                }
            });
        });

        function carregarEntrada() {
            const urlParams = new URLSearchParams(window.location.search);
            const id = urlParams.get('id');
            
            if (!id) {
                mostrarErro('ID da entrada não fornecido.');
                return;
            }
            
            document.getElementById('loadingOverlay').style.display = 'flex';
            
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            fetch('/entrada-produtos/find', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ id_Entrada: id })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro na requisição: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'sucesso') {
                    preencherFormulario(data.entrada);
                } else {
                    mostrarErro(data.mensagem || 'Erro ao carregar informações da entrada.');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                mostrarErro('Erro ao comunicar com o servidor. Verifique se os endpoints estão corretos.');
            })
            .finally(() => {
                document.getElementById('loadingOverlay').style.display = 'none';
            });
        }
        
        function preencherFormulario(entrada) {
            document.getElementById('id_Entrada').value = entrada.id_Entrada;
            document.getElementById('cod_Produto').value = entrada.cod_Produto;
            document.getElementById('produto').value = entrada.nome_Produto;
            document.getElementById('fornecedorText').value = entrada.nome_Fantasia || entrada.razao_Social;
            document.getElementById('fornecedor').value = entrada.id_Fornecedor;
            document.getElementById('razaoSocial').value = entrada.razao_Social || '';
            
            // Certifique-se de que os valores numéricos são preenchidos corretamente
            document.getElementById('quantidade').value = parseFloat(entrada.qtd_Entrada) || '';
            
            // Tratar preços de custo e venda - removendo formatação e convertendo para números
            if (typeof entrada.preco_Custo === 'string') {
                document.getElementById('precoCusto').value = entrada.preco_Custo.replace(/[^\d,.-]/g, '').replace(',', '.');
            } else {
                document.getElementById('precoCusto').value = entrada.preco_Custo || '';
            }
            
            if (entrada.preco_Venda) {
                if (typeof entrada.preco_Venda === 'string') {
                    document.getElementById('precoVenda').value = entrada.preco_Venda.replace(/[^\d,.-]/g, '').replace(',', '.');
                } else {
                    document.getElementById('precoVenda').value = entrada.preco_Venda;
                }
            } else {
                document.getElementById('precoVenda').value = '';
            }
            
            // Formatar a data para o formato esperado pelo input date
            if (entrada.data_Entrada) {
                const data = new Date(entrada.data_Entrada);
                const ano = data.getFullYear();
                const mes = String(data.getMonth() + 1).padStart(2, '0');
                const dia = String(data.getDate()).padStart(2, '0');
                document.getElementById('dataEntrada').value = `${ano}-${mes}-${dia}`;
            }
            
            // Exibir imagem
            if (entrada.imagem) {
                document.getElementById('preview').src = `data:image/jpeg;base64,${entrada.imagem}`;
            } else {
                document.getElementById('preview').src = "{{ asset('images/defaultimg.png') }}";
            }
        }
        
        function atualizarEntrada(event) {
            event.preventDefault();
            
            const idEntrada = document.getElementById('id_Entrada').value;
            const idFornecedor = document.getElementById('fornecedor').value;
            const quantidade = document.getElementById('quantidade').value;
            const precoCusto = document.getElementById('precoCusto').value;
            const precoVenda = document.getElementById('precoVenda').value;
            const dataEntrada = document.getElementById('dataEntrada').value;
            
            if (!idEntrada || !idFornecedor || !quantidade || !precoCusto || !dataEntrada) {
                mostrarErro('Preencha todos os campos obrigatórios.');
                return;
            }
            
            document.getElementById('loadingOverlay').style.display = 'flex';
            
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            fetch('/entrada-produtos/update', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id_Entrada: idEntrada,
                    id_Fornecedor: idFornecedor,
                    qtd_Entrada: quantidade,
                    preco_Custo: precoCusto,
                    preco_Venda: precoVenda,
                    data_Entrada: dataEntrada
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro na requisição: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'sucesso') {
                    mostrarSucesso(data.mensagem || 'Entrada atualizada com sucesso!');
                    // Redirecionar após 2 segundos
                    setTimeout(() => {
                        window.location.href = '{{ route("menu.entrada-produtos.entrada-produtos-buscar") }}';
                    }, 2000);
                } else {
                    mostrarErro(data.mensagem || 'Erro ao atualizar entrada.');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                mostrarErro('Erro ao comunicar com o servidor. Tente novamente.');
            })
            .finally(() => {
                document.getElementById('loadingOverlay').style.display = 'none';
            });
        }
        
        function abrirModalBuscaFornecedor() {
            document.getElementById('searchFornecedorModal').style.display = 'block';
            document.getElementById('fornecedorSearch').focus();
            
            // Carregar todos os fornecedores automaticamente ao abrir o modal
            buscarFornecedores();
        }
        
        function buscarFornecedores() {
            const termo = document.getElementById('fornecedorSearch').value.trim();
            document.getElementById('loadingOverlay').style.display = 'flex';
            document.getElementById('searchFornecedorMessage').style.display = 'none';
            
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            fetch('{{ route("entrada-produtos.fornecedores") }}', {
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
                if (data.status === 'sucesso' && data.fornecedores && data.fornecedores.length > 0) {
                    exibirFornecedoresTabela(data.fornecedores);
                } else {
                    document.querySelector('#fornecedoresTable tbody').innerHTML = '';
                    document.getElementById('searchFornecedorMessage').style.display = 'block';
                    document.getElementById('searchFornecedorMessage').textContent = data.mensagem || 'Nenhum fornecedor encontrado.';
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                document.getElementById('searchFornecedorMessage').style.display = 'block';
                document.getElementById('searchFornecedorMessage').textContent = 'Erro ao buscar fornecedores. Tente novamente.';
            })
            .finally(() => {
                document.getElementById('loadingOverlay').style.display = 'none';
            });
        }
        
        function exibirFornecedoresTabela(fornecedores) {
            const tbody = document.querySelector('#fornecedoresTable tbody');
            tbody.innerHTML = '';
            
            fornecedores.forEach(fornecedor => {
                const row = tbody.insertRow();
                
                row.insertCell(0).textContent = fornecedor.id_Fornecedor;
                row.insertCell(1).textContent = fornecedor.nome_Fantasia || fornecedor.razao_Social;
                row.insertCell(2).textContent = fornecedor.grupo || 'N/A';
                
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
        
        // Fechar modal ao clicar fora
        window.onclick = function(event) {
            if (event.target == document.getElementById('searchFornecedorModal')) {
                fecharModalBuscaFornecedor();
            }
        }
    </script>
@endpush