@extends('layouts.app')

@section('title', 'Relatório de Produtos')
@section('header-title', 'Relatório de Produtos')

@push('styles')
    @vite(['resources/css/menu/relatorio/produtos-relatorio.css'])
@endpush

@section('content')
        <div class="form">
    <div class="Conteudo">
        <form id="consultaForm" autocomplete="off">
            <div class="search-fields">
                <div class="input-containe search-container">
                    <input type="number" id="codProduto" class="input-field" placeholder="Código do Produto">
                    <button type="button" class="search-button" onclick="buscarProdutos()">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                <div class="input-containe search-container">
                    <input type="text" id="nomeProduto" class="input-field" placeholder="Nome do Produto">
                    <button type="button" class="search-button" onclick="buscarProdutos()">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
            <div class="search-fields">
                <div class="input-containe search-container">
                    <input type="text" id="grupo" class="input-field" placeholder="Grupo">
                    <button type="button" class="search-button" onclick="buscarProdutos()">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                <div class="input-containe search-container">
                    <input type="text" id="subgrupo" class="input-field" placeholder="Subgrupo">
                    <button type="button" class="search-button" onclick="buscarProdutos()">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </form>
        <div id="mensagemErro" style="display: none;"></div>
        <div class="buttons-container" style="margin: 15px 0; display: flex; justify-content: flex-end;">
            <button id="btnGerarPDF" class="new">
                <i class="fas fa-file-pdf"></i> Gerar PDF
            </button>
        </div>
        <div id="resultado-container" class="resultado-container" style="display: none;">
            <div class="resultado-titulo">Resultados da Busca</div>
            <table id="tabelaProdutos">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nome</th>
                        <th>Preço Custo</th>
                        <th>Preço Venda</th>
                        <th>Grupo</th>
                    </tr>
                </thead>
                <tbody id="corpoTabela"></tbody>
            </table>
        </div>
    </div>
    </div>
@endsection

@section('footer')
        <div class="BotoesFooter">
    <div class="buttons-footer">
        <a href="{{ route('menu.relatorio.relatorio') }}" class="back-link">
            <button class="search"> <i class="fas fa-arrow-left"></i> Voltar
            </button>
        </a>
    </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
    <script>
        let resultadosProdutos = [];

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
        document.getElementById('codProduto').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                buscarProdutos();
            }
        });
        document.getElementById('nomeProduto').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                buscarProdutos();
            }
        });
        document.getElementById('grupo').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                buscarProdutos();
            }
        });
        document.getElementById('subgrupo').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                buscarProdutos();
            }
        });
        function buscarProdutos() {
            document.getElementById('mensagemErro').style.display = 'none';
            const searchButtons = document.querySelectorAll('.search-button');
            searchButtons.forEach(button => {
                button.disabled = true;
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            });
            const codProduto = document.getElementById('codProduto').value.trim();
            const nomeProduto = document.getElementById('nomeProduto').value.trim();
            const grupo = document.getElementById('grupo').value.trim();
            const subgrupo = document.getElementById('subgrupo').value.trim();
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            fetch('{{ route("menu.relatorio.produtos.search") }}', {
                method: 'POST',
                headers: {
                        'Content-Type': 'application/json; charset=utf-8',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        cod_Produto: codProduto,
                        nome_Produto: nomeProduto,
                        grupo: grupo,
                        sub_Grupo: subgrupo
                    })
                })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(data => {
                                throw new Error(data.message || 'Erro ao processar requisição');
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.status === 'success') {
                            resultadosProdutos = data.produtos || [];
                            exibirResultados(resultadosProdutos);
                            document.getElementById('btnGerarPDF').disabled = resultadosProdutos.length === 0;
                        } else {
                            throw new Error(data.message || 'Resposta inválida do servidor');
                        }
                        searchButtons.forEach(button => {
                            button.innerHTML = '<i class="fas fa-search"></i>';
                            button.disabled = false;
                        });
                    })
                    .catch(error => {
                        mostrarErro(error.message || 'Ocorreu um erro ao buscar produtos');
                        searchButtons.forEach(button => {
                            button.innerHTML = '<i class="fas fa-search"></i>';
                            button.disabled = false;
                        });
                        document.getElementById('btnGerarPDF').disabled = true;
                    });
        }
        function exibirResultados(produtos) {
            const corpoTabela = document.getElementById('corpoTabela');
            const resultadoContainer = document.getElementById('resultado-container');
            corpoTabela.innerHTML = '';
            resultadoContainer.style.display = 'block';
            produtos.forEach(produto => {
                const row = document.createElement('tr');
                row.innerHTML = `
                <td>${produto.cod_Produto || '---'}</td>
                <td>${produto.nome_Produto || '---'}</td>
                <td>${produto.preco_Custo || '---'}</td>
                <td>${produto.preco_Venda || '---'}</td>
                <td>${produto.grupo || '---'}</td>
            `;
                corpoTabela.appendChild(row);
            });
        }
        // Geração do PDF
        document.getElementById('btnGerarPDF').addEventListener('click', function(e) {
            e.preventDefault();
            if (!resultadosProdutos.length) {
                mostrarErro('Não há produtos para gerar o relatório');
                return;
            }
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            doc.setFontSize(18);
            doc.setTextColor(66, 0, 255);
            doc.text('Relatório de Produtos', 14, 15);
            const tableData = resultadosProdutos.map(produto => [
                produto.cod_Produto || '---',
                produto.nome_Produto || '---',
                produto.preco_Custo || '---',
                produto.preco_Venda || '---',
                produto.grupo || '---'
            ]);
            doc.autoTable({
                head: [[
                    'Código',
                    'Nome',
                    'Preço Custo',
                    'Preço Venda',
                    'Grupo'
                ]],
                body: tableData,
                startY: 20,
                styles: { fontSize: 10 }
            });
            doc.save('relatorio_produtos.pdf');
        });
    </script>
@endpush