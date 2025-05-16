@extends('layouts.app')

@section('title', 'Buscar Saídas de Produtos')

@section('header-title', 'Buscar Saídas de Produtos')

@push('styles')
    @vite(['resources/css/menu/saida-produtos/saida-produtos.css'])
@endpush

@section('content')
    <div class="form">
        <div class="buttons" id="default-buttons">
            <button class="back" onclick="window.location.href='{{ route('menu.saida-produtos.saida-produtos') }}'">
                <i class="fas fa-arrow-left"></i> Voltar
            </button>
        </div>
        <div class="Conteudo">
            <form id="consultaForm" autocomplete="off">
                <div class="search-container">
                    <input type="text" id="termoBusca" class="input-field" placeholder="Código, produto ou local...">
                    <button type="button" class="search-button" id="search-button">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
            <div id="mensagemSucesso" style="display: none;"></div>
            <div id="mensagemErro" style="display: none;"></div>
        </div>
    </div>
    <div class="table-responsive">
        <table id="resultadoTabela" style="display: none;">
            <thead>
                <tr>
                    <th>Foto</th>
                    <th>Produto</th>
                    <th>Local</th>
                    <th>QTD</th>
                    <th>Data</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
    <div class="buttons-search" id="clear-button" style="display: none; margin-top: 15px;">
        <button class="clear" onclick="limparTabela()">
            <i class="fas fa-trash-alt"></i> Limpar Resultados
        </button>
    </div>
    <!-- Modal Detalhes do Produto -->
    <div id="produtoModal" class="modal-Produto">
        <div class="modal-content-Produto">
            <span class="close" onclick="fecharModal()">&times;</span>
            <h2>Detalhes da Saída</h2>
            <div>
                <img id="modalImagem" src="" alt="Imagem do Produto" style="max-width: 150px; margin: 0 auto 20px; display: block; border-radius: 10px;">
                <label for="modalCodProduto">Código do Produto:</label>
                <input id="modalCodProduto" disabled>
                <label for="modalProduto">Produto:</label>
                <input id="modalProduto" disabled>
                <label for="modalLocalDestino">Local de Destino:</label>
                <input id="modalLocalDestino" disabled>
                <label for="modalTipoLocal">Tipo do Local:</label>
                <input id="modalTipoLocal" disabled>
                <label for="modalQuantidade">Quantidade:</label>
                <input id="modalQuantidade" disabled>
                <label for="modalData">Data de Saída:</label>
                <input id="modalData" disabled>
                <label for="modalObservacao">Observações:</label>
                <input id="modalObservacao" disabled>
                <div style="display: flex; justify-content: center; margin-top: 20px;">
                    <button class="edit" id="btnEditar" style="margin-right: 10px;">
                        <i class="fas fa-edit"></i> Editar
                    </button>
                    <button class="clear" id="btnExcluir">
                        <i class="fas fa-trash-alt"></i> Excluir
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div id="loadingOverlay" style="display: none;">
        <div id="loadingSpinner"></div>
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
            // Evento de busca ao clicar no botão
            document.getElementById('search-button').addEventListener('click', function() {
                buscarSaidas();
            });
            
            // Evento de busca ao pressionar Enter no campo de busca
            document.getElementById('termoBusca').addEventListener('keypress', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    buscarSaidas();
                }
            });
            
            // Excluir saída
            document.getElementById('btnExcluir').addEventListener('click', function() {
                confirmarExclusao(document.getElementById('modalCodProduto').getAttribute('data-id'));
            });
            
            // Editar saída
            document.getElementById('btnEditar').addEventListener('click', function() {
                const idSaida = document.getElementById('modalCodProduto').getAttribute('data-id');
                window.location.href = "{{ route('menu.saida-produtos.saida-produtos-editar') }}?id=" + idSaida;
            });

            // Adicionar funcionalidade de busca de locais
            document.getElementById('btn-search-local').addEventListener('click', function() {
                buscarLocais();
            });
            
            document.getElementById('localSearch').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    buscarLocais();
                }
            });
        });
        
        function buscarSaidas() {
            const termo = document.getElementById('termoBusca').value.trim();
            
            if (termo === '') {
                mostrarErro('Digite algo para buscar!');
                return;
            }
            
            document.getElementById('loadingOverlay').style.display = 'flex';
            
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            fetch('/saida-produtos/search', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Content-Type': 'application/json'
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
                if (data.status === 'sucesso' && Array.isArray(data.saidas) && data.saidas.length > 0) {
                    exibirResultados(data.saidas);
                    mostrarSucesso('Encontradas ' + data.saidas.length + ' saídas!');
                } else {
                    mostrarErro('Nenhuma saída encontrada com este termo.');
                    limparTabela();
                }
            })
            .catch(error => {
                if (error && error.message) {
                    mostrarErro("Erro ao buscar saídas: " + error.message);
                } else {
                    mostrarErro("Ocorreu um erro na busca. Tente novamente mais tarde.");
                }
                console.error("Erro ao buscar saídas:", error);
            })
            .finally(() => {
                document.getElementById('loadingOverlay').style.display = 'none';
            });
        }
        
        function exibirResultados(saidas) {
            document.getElementById("clear-button").style.display = "flex";
            const tabela = document.getElementById("resultadoTabela");
            const tbody = tabela.querySelector("tbody");
            tbody.innerHTML = ''; 
            tabela.style.display = 'table'; 
            
            saidas.forEach((saida) => {
                const row = tbody.insertRow();
                
                const cellImagem = row.insertCell(0);
                const imgElement = document.createElement("img");
                imgElement.src = saida.imagem ? `data:image/jpeg;base64,${saida.imagem}` : '{{ asset("images/defaultimg.png") }}';
                imgElement.alt = saida.nome_Produto || 'Imagem não disponível';
                imgElement.style.width = "50px";
                imgElement.style.height = "50px";
                imgElement.style.borderRadius = "6px";
                imgElement.style.objectFit = "cover";
                cellImagem.appendChild(imgElement);
                
                row.insertCell(1).textContent = saida.nome_Produto || 'Não informado';
                row.insertCell(2).textContent = saida.nome_Local || 'Não informado';
                row.insertCell(3).textContent = saida.qtd_saida || '0';
                
                const dataCell = row.insertCell(4);
                const dataFormatada = formatarData(saida.data_Saida);
                dataCell.textContent = dataFormatada;
                
                row.addEventListener('click', () => abrirModal(saida));
            });
        }
        
        function formatarData(dataString) {
            if (!dataString) return 'Data não informada';
            
            const data = new Date(dataString);
            return data.toLocaleDateString('pt-BR');
        }
        
        function abrirModal(saida) {
            document.getElementById("modalImagem").src = saida.imagem ? `data:image/jpeg;base64,${saida.imagem}` : '{{ asset("images/defaultimg.png") }}';
            
            const codProduto = document.getElementById("modalCodProduto");
            codProduto.value = saida.cod_Produto || '';
            codProduto.setAttribute('data-id', saida.id_Saida || '');
            
            document.getElementById("modalProduto").value = saida.nome_Produto || '';
            document.getElementById("modalLocalDestino").value = saida.nome_Local || '';
            document.getElementById("modalTipoLocal").value = saida.tipo_Local || '';
            document.getElementById("modalQuantidade").value = saida.qtd_saida || '';
            document.getElementById("modalData").value = formatarData(saida.data_Saida);
            document.getElementById("modalObservacao").value = saida.observacao || '';
            
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
        
        function confirmarExclusao(idSaida) {
            if (confirm('Tem certeza que deseja excluir esta saída?')) {
                excluirSaida(idSaida);
            }
        }
        
        function excluirSaida(idSaida) {
            document.getElementById('loadingOverlay').style.display = 'flex';
            
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            fetch('/home/saida-produtos/destroy', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id_Saida: idSaida })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro na requisição: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'sucesso') {
                    mostrarSucesso(data.mensagem);
                    fecharModal();
                    
                    // Refazer a busca para atualizar a tabela
                    setTimeout(() => {
                        buscarSaidas();
                    }, 1000);
                } else {
                    mostrarErro(data.mensagem || 'Erro ao excluir a saída.');
                }
            })
            .catch(error => {
                console.error("Erro:", error);
                mostrarErro("Ocorreu um erro ao excluir. Tente novamente mais tarde.");
            })
            .finally(() => {
                document.getElementById('loadingOverlay').style.display = 'none';
            });
        }
        
        function buscarLocais() {
            const termo = document.getElementById('localSearch').value.trim();
            document.getElementById('loadingOverlay').style.display = 'flex';
            document.getElementById('searchLocalMessage').style.display = 'none';
            
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            console.log('Enviando busca de locais com termo:', termo);
            
            // Criando FormData para enviar no formato esperado pelo controller
            const formData = new FormData();
            formData.append('nome_Local', termo);
            
            fetch('{{ route("menu.local-destino.search") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro na requisição: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log('Resposta da API de locais:', data);
                
                if (data.status === 'sucesso' || (data.resultados && data.resultados.length > 0)) {
                    exibirLocaisTabela(data.resultados);
                } else {
                    document.querySelector('#locaisTable tbody').innerHTML = '';
                    document.getElementById('searchLocalMessage').style.display = 'block';
                    document.getElementById('searchLocalMessage').textContent = data.mensagem || 'Nenhum local encontrado.';
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
            
            if (!locais.length) {
                document.getElementById('searchLocalMessage').style.display = 'block';
                document.getElementById('searchLocalMessage').textContent = 'Nenhum local encontrado.';
                return;
            }
            
            document.getElementById('searchLocalMessage').style.display = 'none';
            
            locais.forEach(local => {
                const row = tbody.insertRow();
                
                const cellId = row.insertCell(0);
                cellId.textContent = local.id_Local || '';
                
                const cellNome = row.insertCell(1);
                cellNome.textContent = local.nome_Local || '';
                
                const cellTipo = row.insertCell(2);
                cellTipo.textContent = local.tipo_Local || '';
                
                row.style.cursor = 'pointer';
                row.addEventListener('click', () => {
                    selecionarLocal(local);
                });
            });
        }
        
        function selecionarLocal(local) {
            // Aqui você pode implementar o que deve acontecer quando um local é selecionado
            console.log('Local selecionado:', local);
            fecharModalBuscaLocal();
        }
        
        function abrirModalBuscaLocal() {
            document.getElementById('searchLocalModal').style.display = 'block';
            document.getElementById('localSearch').focus();
            
            // Carregar todos os locais automaticamente ao abrir o modal
            buscarLocais();
        }
        
        function fecharModalBuscaLocal() {
            document.getElementById('searchLocalModal').style.display = 'none';
            document.getElementById('localSearch').value = '';
        }
        
        // Fechar modal ao clicar fora
        window.onclick = function(event) {
            if (event.target == document.getElementById('produtoModal')) {
                fecharModal();
            }
            if (event.target == document.getElementById('searchLocalModal')) {
                fecharModalBuscaLocal();
            }
        }
    </script>
@endpush