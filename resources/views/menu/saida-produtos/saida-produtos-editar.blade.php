@extends('layouts.app')

@section('title', 'Editar Saída de Produtos')

@section('header-title', 'Editar Saída de Produtos')

@push('styles')
    @vite(['resources/css/menu/saida-produtos/saida-produtos.css'])
@endpush

@section('content')
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
                            <input type="text" id="localDestinoText" class="input-field" placeholder="* Local de Destino" required disabled>
                            <input type="hidden" id="localDestino" required>
                            <button type="button" class="search-button" id="search-local-button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        <input type="text" id="tipoLocal" class="input-field" placeholder="Tipo de Local" disabled>
                    </div>
                    <div class="coluna2">
                        <input type="number" id="quantidade" step="0.01" min="0.01" class="input-field"
                            placeholder="* Quantidade" required>
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
        document.addEventListener('DOMContentLoaded', function () {
            // Carregar dados da saída pelo ID na URL
            carregarSaida();

            // Configurar eventos
            document.getElementById('saidaForm').addEventListener('submit', atualizarSaida);
            document.getElementById('search-local-button').addEventListener('click', abrirModalBuscaLocal);
            document.getElementById('btn-search-local').addEventListener('click', buscarLocais);
            document.getElementById('localSearch').addEventListener('keypress', function (e) {
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

            fetch('/saida-produtos/find', {
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
            document.getElementById('quantidade').value = saida.qtd_saida;
            document.getElementById('observacao').value = saida.observacao || '';

            if (saida.data_Saida) {
                document.getElementById('dataSaida').value = saida.data_Saida.split('T')[0];
            }

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

            fetch('/saida-produtos/update', {
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

            buscarLocais();
        }

        function buscarLocais() {
            const termo = document.getElementById('localSearch').value.trim();
            document.getElementById('loadingOverlay').style.display = 'flex';
            document.getElementById('searchLocalMessage').style.display = 'none';

            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // Usar FormData e nome_Local para padronizar igual ao cadastro
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
                    if (data.status === 'sucesso') {
                        if (Array.isArray(data.resultados) && data.resultados.length > 0) {
                            exibirLocaisTabela(data.resultados);
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
        window.onclick = function (event) {
            if (event.target == document.getElementById('searchLocalModal')) {
                fecharModalBuscaLocal();
            }
        }
    </script>
@endpush
