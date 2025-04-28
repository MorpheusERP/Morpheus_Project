<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MorpheusERP - Editar Saída de Produtos</title>
    <link rel="shortcut icon" href="{{ asset('images/logo.png') }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @include('layouts.nav_bottom')
    @include('layouts.background')
    
    @vite(['resources/css/menu/saida-produtos/saida-produtos.css'])

    <style>
        /* Fix for edit button */
        .buttons-edit {
            width: 100%; 
            display: flex;
            justify-content: center;
            margin-top: 15px;
        }
        
        .buttons-edit button {
            width: 100%;
            max-width: 300px;
            height: 50px;
            background-color: var(--accent-color);
            color: var(--text-dark);
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Editar Saída de Produtos</h1>
    </div>
    
    <div class="container">
        <div class="form">
            <div class="buttons">
                <button class="back" onclick="window.location.href='{{ route('menu.saida-produtos.saida-produtos-buscar') }}'">
                    <i class="fas fa-arrow-left"></i> Voltar
                </button>
            </div>
            
            <div class="Conteudo">
                <form id="saidaForm" autocomplete="off">
                    <input type="hidden" id="id_Saida">
                    <input type="hidden" id="cod_Produto">
                    
                    <div class="image-placeholder">
                        <img id="preview" src="{{ asset('images/defaultimg.png') }}" class="image-enabled">
                    </div>
                    
                    <input type="text" id="produto" class="input-field" placeholder="Produto" disabled>
                    
                    <div class="container2">
                        <div class="coluna1">
                            <div class="search-container">
                                <input type="text" id="localDestinoText" class="input-field" placeholder="* Local de Destino" required>
                                <input type="hidden" id="localDestino" required>
                                <button type="button" class="search-button" id="search-local-button">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                            
                            <input type="text" id="tipoLocal" class="input-field" placeholder="Tipo de Local" disabled>
                        </div>
                        
                        <div class="coluna2">
                            <input type="number" id="quantidade" step="0.01" min="0.01" class="input-field" placeholder="* Quantidade" required>
                            <input type="date" id="dataSaida" class="input-field" required>
                        </div>
                    </div>
                    
                    <textarea id="observacao" maxlength="150" placeholder="Observações"></textarea>

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
    </div>
    
    <footer>
        <div class="BotoesFooter">
            <div class="buttons-search">
                <button class="exit" onclick="window.location.href='{{ route('home') }}'">
                    <i class="fas fa-home"></i> Voltar para Home
                </button>
            </div>
        </div>
    </footer>
    
    <div class="logo">
        <img src="{{ asset('images/Emporio maxx s-fundo.png') }}" alt="Empório Maxx Logo">
    </div>
    
    <!-- Modal Busca de Locais -->
    <div id="searchLocalModal" class="modal-Produto">
        <div class="modal-content-Produto">
            <span class="close" onclick="fecharModalBuscaLocal()">&times;</span>
            <h2>Buscar Local</h2>
            
            <div class="search-container" style="margin-bottom: 20px;">
                <input type="text" id="localSearch" class="input-field" placeholder="Digite para buscar...">
                <button type="button" class="search-button" id="btn-search-local">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            
            <div id="localSearchResults" style="max-height: 300px; overflow-y: auto;">
                <table id="locaisTable" style="width: 100%; margin-top: 15px;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Local</th>
                            <th>Tipo</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            
            <div id="searchLocalMessage" style="margin-top: 15px; text-align: center; display: none;">
                Nenhum local encontrado.
            </div>
        </div>
    </div>
    
    <div id="loadingOverlay" style="display: none;">
        <div id="loadingSpinner"></div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Carregar dados da saída pelo ID na URL
            carregarSaida();
            
            // Configurar eventos
            document.getElementById('saidaForm').addEventListener('submit', atualizarSaida);
            document.getElementById('search-local-button').addEventListener('click', abrirModalBuscaLocal);
            document.getElementById('btn-search-local').addEventListener('click', buscarLocais);
            document.getElementById('localSearch').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    buscarLocais();
                }
            });
        });

        function carregarSaida() {
            const urlParams = new URLSearchParams(window.location.search);
            const id = urlParams.get('id');
            
            if (!id) {
                mostrarErro('ID da saída não fornecido.');
                return;
            }
            
            document.getElementById('loadingOverlay').style.display = 'flex';
            
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            fetch('/home/saida-produtos/find', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id_Saida: id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'sucesso') {
                    preencherFormulario(data.saida);
                } else {
                    mostrarErro(data.mensagem || 'Erro ao carregar informações da saída.');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                mostrarErro('Erro ao comunicar com o servidor.');
            })
            .finally(() => {
                document.getElementById('loadingOverlay').style.display = 'none';
            });
        }
        
        function preencherFormulario(saida) {
            document.getElementById('id_Saida').value = saida.id_Saida;
            document.getElementById('cod_Produto').value = saida.cod_Produto;
            document.getElementById('produto').value = saida.nome_Produto;
            document.getElementById('localDestinoText').value = saida.nome_Local;
            document.getElementById('localDestino').value = saida.id_Local;
            document.getElementById('tipoLocal').value = saida.tipo_Local || '';
            document.getElementById('quantidade').value = saida.qtd_Saida;
            document.getElementById('observacao').value = saida.observacao || '';
            
            // Formatar a data para o formato esperado pelo input date
            if (saida.data_Saida) {
                const data = new Date(saida.data_Saida);
                const ano = data.getFullYear();
                const mes = String(data.getMonth() + 1).padStart(2, '0');
                const dia = String(data.getDate()).padStart(2, '0');
                document.getElementById('dataSaida').value = `${ano}-${mes}-${dia}`;
            }
            
            // Exibir imagem
            if (saida.imagem) {
                document.getElementById('preview').src = `data:image/jpeg;base64,${saida.imagem}`;
            } else {
                document.getElementById('preview').src = "{{ asset('images/defaultimg.png') }}";
            }
        }
        
        function atualizarSaida(event) {
            event.preventDefault();
            
            const idSaida = document.getElementById('id_Saida').value;
            const idLocal = document.getElementById('localDestino').value;
            const quantidade = document.getElementById('quantidade').value;
            const observacao = document.getElementById('observacao').value;
            const dataSaida = document.getElementById('dataSaida').value;
            
            if (!idSaida || !idLocal || !quantidade || !dataSaida) {
                mostrarErro('Preencha todos os campos obrigatórios.');
                return;
            }
            
            document.getElementById('loadingOverlay').style.display = 'flex';
            
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            fetch('/home/saida-produtos/update', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id_Saida: idSaida,
                    id_Local: idLocal,
                    qtd_Saida: quantidade,
                    observacao: observacao,
                    data_Saida: dataSaida
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
                    mostrarSucesso(data.mensagem || 'Saída atualizada com sucesso!');
                    // Redirecionar após 2 segundos
                    setTimeout(() => {
                        window.location.href = '{{ route("menu.saida-produtos.saida-produtos-buscar") }}';
                    }, 2000);
                } else {
                    mostrarErro(data.mensagem || 'Erro ao atualizar saída.');
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
        
        function abrirModalBuscaLocal() {
            document.getElementById('searchLocalModal').style.display = 'block';
            document.getElementById('localSearch').focus();
            
            // Carregar todos os locais automaticamente ao abrir o modal
            buscarLocais();
        }
        
        function buscarLocais() {
            const termo = document.getElementById('localSearch').value.trim();
            document.getElementById('loadingOverlay').style.display = 'flex';
            document.getElementById('searchLocalMessage').style.display = 'none';
            
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            console.log('Enviando busca de locais com termo:', termo);
            
            fetch('{{ route("menu.local-destino.search") }}', {
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
                console.log('Resposta da API de locais:', data);
                
                if (data.status === 'sucesso') {
                    if (Array.isArray(data.locais) && data.locais.length > 0) {
                        exibirLocaisTabela(data.locais);
                        document.getElementById('searchLocalMessage').style.display = 'none';
                    } else {
                        document.querySelector('#locaisTable tbody').innerHTML = '';
                        document.getElementById('searchLocalMessage').style.display = 'block';
                        document.getElementById('searchLocalMessage').textContent = 'Nenhum local encontrado.';
                    }
                } else {
                    document.querySelector('#locaisTable tbody').innerHTML = '';
                    document.getElementById('searchLocalMessage').style.display = 'block';
                    document.getElementById('searchLocalMessage').textContent = data.mensagem || 'Erro ao buscar locais.';
                }
            })
            .catch(error => {
                console.error('Erro ao buscar locais:', error);
                document.getElementById('searchLocalMessage').style.display = 'block';
                document.getElementById('searchLocalMessage').textContent = 'Erro ao buscar locais. Tente novamente mais tarde.';
            })
            .finally(() => {
                document.getElementById('loadingOverlay').style.display = 'none';
            });
        }
        
        function exibirLocaisTabela(locais) {
            const tbody = document.querySelector('#locaisTable tbody');
            tbody.innerHTML = '';
            
            locais.forEach(local => {
                const row = tbody.insertRow();
                
                row.insertCell(0).textContent = local.id_Local;
                row.insertCell(1).textContent = local.nome_Local;
                row.insertCell(2).textContent = local.tipo_Local;
                
                row.style.cursor = 'pointer';
                row.addEventListener('click', () => {
                    selecionarLocal(local);
                });
            });
        }
        
        function selecionarLocal(local) {
            document.getElementById('localDestinoText').value = local.nome_Local;
            document.getElementById('localDestino').value = local.id_Local;
            document.getElementById('tipoLocal').value = local.tipo_Local;
            
            fecharModalBuscaLocal();
        }
        
        function fecharModalBuscaLocal() {
            document.getElementById('searchLocalModal').style.display = 'none';
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
            if (event.target == document.getElementById('searchLocalModal')) {
                fecharModalBuscaLocal();
            }
        }
    </script>
</body>
</html>