@extends('layouts.app')

@section('title', 'Buscar Entradas de Produtos')

@section('header-title', 'Buscar Entradas de Produtos')

@push('styles')
    @vite(['resources/css/menu/entrada-produtos/entrada-produtos.css'])
@endpush

@section('content')
    <div class="form">
        <div class="buttons" id="default-buttons">
            <button class="back" onclick="window.location.href='{{ route('menu.entrada-produtos.entrada-produtos') }}'">
                <i class="fas fa-arrow-left"></i> Voltar
            </button>
        </div>
        <div class="Conteudo">
            <form id="consultaForm" autocomplete="off">
                <div class="search-container">
                    <input type="text" id="termoBusca" class="input-field" placeholder="Código, produto ou fornecedor...">
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
                    <th>Fornecedor</th>
                    <th>QTD</th>
                    <th>Data</th>
                    <th>Valor Total</th>
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
            <h2>Detalhes da Entrada</h2>
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
                <label for="modalData">Data de Entrada:</label>
                <input id="modalData" disabled>
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
                buscarEntradas();
            });

            // Evento de busca ao pressionar Enter no campo de busca
            document.getElementById('termoBusca').addEventListener('keypress', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    buscarEntradas();
                }
            });

            // Excluir entrada
            document.getElementById('btnExcluir').addEventListener('click', function() {
                confirmarExclusao(document.getElementById('modalCodProduto').getAttribute('data-id'));
            });

            // Editar entrada
            document.getElementById('btnEditar').addEventListener('click', function() {
                const idEntrada = document.getElementById('modalCodProduto').getAttribute('data-id');
                window.location.href = "{{ route('menu.entrada-produtos.entrada-produtos-editar') }}?id=" + idEntrada;
            });
        });

        function buscarEntradas() {
            const termo = document.getElementById('termoBusca').value.trim();

            if (termo === '') {
                mostrarErro('Digite algo para buscar!');
                return;
            }

            document.getElementById('loadingOverlay').style.display = 'flex';

            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            fetch('/entrada-produtos/search', {
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
                if (data.status === 'sucesso' && Array.isArray(data.entradas) && data.entradas.length > 0) {
                    exibirResultados(data.entradas);
                    mostrarSucesso('Encontradas ' + data.entradas.length + ' entradas!');
                } else {
                    mostrarErro('Nenhuma entrada encontrada com este termo.');
                    limparTabela();
                }
            })
            .catch(error => {
                console.error("Erro:", error);
                mostrarErro("Ocorreu um erro na busca. Tente novamente mais tarde.");
            })
            .finally(() => {
                document.getElementById('loadingOverlay').style.display = 'none';
            });
        }

        function exibirResultados(entradas) {
            document.getElementById("clear-button").style.display = "flex";
            const tabela = document.getElementById("resultadoTabela");
            const tbody = tabela.querySelector("tbody");
            tbody.innerHTML = '';
            tabela.style.display = 'table';

            entradas.forEach((entrada) => {
                const row = tbody.insertRow();

                const cellImagem = row.insertCell(0);
                const imgElement = document.createElement("img");
                imgElement.src = entrada.imagem ? `data:image/jpeg;base64,${entrada.imagem}` : '{{ asset("images/defaultimg.png") }}';
                imgElement.alt = entrada.nome_Produto || 'Imagem não disponível';
                imgElement.style.width = "50px";
                imgElement.style.height = "50px";
                imgElement.style.borderRadius = "6px";
                imgElement.style.objectFit = "cover";
                cellImagem.appendChild(imgElement);

                row.insertCell(1).textContent = entrada.nome_Produto || 'Não informado';
                row.insertCell(2).textContent = entrada.razao_Social || 'Não informado';
                row.insertCell(3).textContent = entrada.qtd_Entrada || '0';

                const dataCell = row.insertCell(4);
                const dataFormatada = formatarData(entrada.data_Entrada);
                dataCell.textContent = dataFormatada;

                const valorTotalCell = row.insertCell(5);
                const valorTotal = entrada.qtd_Entrada * entrada.preco_Custo;
                valorTotalCell.textContent = formatarDinheiro(valorTotal);

                row.addEventListener('click', () => abrirModal(entrada));
            });
        }

        function formatarData(dataString) {
            if (!dataString) return 'Data não informada';
            const data = dataString.substring(0, 10).split('-');
            if (data.length === 3) {
                return `${data[2]}/${data[1]}/${data[0]}`;
            }
            return dataString;
        }

        function formatarDinheiro(valor) {
            return parseFloat(valor).toLocaleString('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            });
        }

        function abrirModal(entrada) {
            document.getElementById("modalImagem").src = entrada.imagem ? `data:image/jpeg;base64,${entrada.imagem}` : '{{ asset("images/defaultimg.png") }}';

            const codProduto = document.getElementById("modalCodProduto");
            codProduto.value = entrada.cod_Produto || '';
            codProduto.setAttribute('data-id', entrada.id_Entrada || '');

            document.getElementById("modalProduto").value = entrada.nome_Produto || '';
            document.getElementById("modalFornecedor").value = entrada.razao_Social || '';
            document.getElementById("modalQuantidade").value = entrada.qtd_Entrada || '';
            document.getElementById("modalPrecoCusto").value = entrada.preco_Custo ? formatarDinheiro(entrada.preco_Custo) : 'N/A';
            document.getElementById("modalPrecoVenda").value = entrada.preco_Venda ? formatarDinheiro(entrada.preco_Venda) : 'N/A';
            document.getElementById("modalData").value = formatarData(entrada.data_Entrada);

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

        function confirmarExclusao(idEntrada) {
            if (confirm('Tem certeza que deseja excluir esta entrada?')) {
                excluirEntrada(idEntrada);
            }
        }

        function excluirEntrada(idEntrada) {
            document.getElementById('loadingOverlay').style.display = 'flex';

            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            fetch('/home/entrada-produtos/destroy', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id_Entrada: idEntrada })
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
                        buscarEntradas();
                    }, 1000);
                } else {
                    mostrarErro(data.mensagem || 'Erro ao excluir a entrada.');
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

        // Fechar modal ao clicar fora
        window.onclick = function(event) {
            if (event.target == document.getElementById('produtoModal')) {
                fecharModal();
            }
        }
    </script>
@endpush
