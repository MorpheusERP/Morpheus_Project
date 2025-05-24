@extends('layouts.app')

@section('title', 'Relatório de Entrada de Produtos')
@section('header-title', 'Relatório de Entrada de Produtos')

@push('styles')
    @vite(['resources/css/menu/relatorio/entrada-produtos-relatorio.css'])
@endpush

@section('content')
    <div class="form">
        <div class="Conteudo">
            <form id="consultaForm" autocomplete="off">
                <div class="search-fields">
                    <div class="input-containe search-container">
                        <input type="date" id="start_date" class="input-field" placeholder="Data Inicial" required>
                    </div>
                    <div class="input-containe search-container">
                        <input type="date" id="end_date" class="input-field" placeholder="Data Final" required>
                    </div>
                </div>
                <div class="search-fields">
                    <div class="input-containe search-container">
                        <input type="text" id="nomeProduto" class="input-field" placeholder="Nome do Produto">
                        <button type="button" class="search-button" onclick="buscarEntradas()">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <div class="input-containe search-container">
                        <input type="text" id="fornecedor" class="input-field" placeholder="Fornecedor">
                        <button type="button" class="search-button" onclick="buscarEntradas()">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <div class="search-fields">
                    <div class="input-containe search-container">
                        <input type="text" id="grupo" class="input-field" placeholder="Grupo">
                        <button type="button" class="search-button" onclick="buscarEntradas()">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <div class="input-containe search-container">
                        <input type="text" id="subgrupo" class="input-field" placeholder="Subgrupo">
                        <button type="button" class="search-button" onclick="buscarEntradas()">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
            <div id="mensagemErro" style="display: none;"></div>
            <div class="buttons-search">
                <button id="btnGerarPDF" class="new" style="margin-left: 10px;">
                    <i class="fas fa-file-pdf"></i> Gerar PDF
                </button>
            </div>
            <div id="resultado-container" class="resultado-container" style="display: none;">
                <div class="resultado-titulo">Resultados da Busca</div>
                <table id="tabelaEntradas">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Data</th>
                            <th>Valor Total</th>
                        </tr>
                    </thead>
                    <tbody id="corpoTabela"></tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- Modal para detalhes da entrada -->
    <div id="entradaModal" class="entrada-modal-bg" role="dialog" aria-modal="true" aria-labelledby="entradaModalTitle"
        tabindex="-1">
        <div class="entrada-modal-content">
            <button type="button" class="entrada-modal-close" aria-label="Fechar detalhes"
                onclick="fecharModal()">&times;</button>
            <h2 id="entradaModalTitle">Detalhes da Entrada</h2>
            <div>
                <div class="entrada-modal-field">
                    <label for="modalCodigo" class="entrada-modal-label">Código:</label>
                    <input id="modalCodigo" class="entrada-modal-input" disabled>
                </div>
                <div class="entrada-modal-field">
                    <label for="modalData" class="entrada-modal-label">Data:</label>
                    <input id="modalData" class="entrada-modal-input" disabled>
                </div>
                <div class="entrada-modal-field">
                    <label for="modalValor" class="entrada-modal-label">Valor Total:</label>
                    <input id="modalValor" class="entrada-modal-input" disabled>
                </div>
                <button id="btnDetalhesPDF">
                    <i class="fas fa-file-pdf"></i> Gerar PDF Detalhado
                </button>
            </div>
        </div>
    </div>
@endsection

@section('footer')
    <div class="BotoesFooter">
        <div class="buttons-footer">
            <a href="{{ route('menu.relatorio.relatorio') }}" class="back-link">
                <button class="search">
                    <i class="fas fa-arrow-left"></i> Voltar
                </button>
            </a>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
    <script>
        // Variáveis para armazenar os resultados
        let todasEntradas = [];
        let produtosEntrada = [];
        let entradaSelecionada = null;

        // Definir datas padrão ao carregar a página
        document.addEventListener('DOMContentLoaded', function () {
            // Data atual para o campo "até"
            const hoje = new Date();
            const dataHoje = hoje.toISOString().split('T')[0];
            document.getElementById('end_date').value = dataHoje;

            // Data de 30 dias atrás para o campo "de"
            const dataInicio = new Date();
            dataInicio.setDate(dataInicio.getDate() - 30);
            document.getElementById('start_date').value = dataInicio.toISOString().split('T')[0];

            // Adicionar eventos de tecla Enter para os campos
            document.getElementById('nomeProduto').addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    buscarEntradas();
                }
            });

            document.getElementById('fornecedor').addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    buscarEntradas();
                }
            });

            document.getElementById('grupo').addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    buscarEntradas();
                }
            });

            document.getElementById('subgrupo').addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    buscarEntradas();
                }
            });
        });

        // Função para mostrar mensagem de erro
        function mostrarErro(mensagem) {
            const mensagemErro = document.getElementById('mensagemErro');
            mensagemErro.innerText = mensagem;
            mensagemErro.style.display = 'block';
            mensagemErro.style.backgroundColor = 'rgba(220, 53, 69, 0.8)';
            mensagemErro.style.color = 'white';
            mensagemErro.style.padding = '10px';
            mensagemErro.style.borderRadius = '8px';
            mensagemErro.style.textAlign = 'center';
            setTimeout(() => {
                mensagemErro.style.display = 'none';
            }, 3000);
        }

        // Função para formatar data para exibição
        function formatarData(data) {
            if (!data) return '---';
            const partes = data.split('-');
            if (partes.length === 3) {
                return `${partes[2]}/${partes[1]}/${partes[0]}`;
            }
            return data;
        }

        // Função para formatar valores monetários
        function formatarMoeda(valor) {
            return new Intl.NumberFormat('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            }).format(valor || 0);
        }

        // Função para buscar entradas de produtos
        function buscarEntradas() {
            // Limpar mensagens de erro anteriores
            document.getElementById('mensagemErro').style.display = 'none';

            // Obter valores dos campos
            const start_date = document.getElementById('start_date').value;
            const end_date = document.getElementById('end_date').value;
            const nome_Produto = document.getElementById('nomeProduto').value.trim();
            const razao_Social = document.getElementById('fornecedor').value.trim();
            const grupo = document.getElementById('grupo').value.trim();
            const sub_Grupo = document.getElementById('subgrupo').value.trim();

            // Validar datas
            if (!start_date || !end_date) {
                mostrarErro('Por favor, informe o período de consulta');
                return;
            }

            // Verificar se data inicial é menor que data final
            if (new Date(start_date) > new Date(end_date)) {
                mostrarErro('A data inicial deve ser menor ou igual à data final');
                return;
            }

            // Mostrar indicador de carregamento nos botões de busca
            const searchButtons = document.querySelectorAll('.search-button');
            searchButtons.forEach(button => {
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                button.style.pointerEvents = 'none';
                button.disabled = true;
            });

            // Obter o token CSRF
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // Preparar dados para envio
            const dados = {
                start_date: start_date,
                end_date: end_date,
                nome_Produto: nome_Produto,
                razao_Social: razao_Social,
                grupo: grupo,
                sub_Grupo: sub_Grupo
            };

            // Fazer a requisição para o servidor
            fetch('{{ route("menu.relatorio.entradas.search") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(dados)
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
                    console.log('Response data:', data);

                    // Restaurar botões
                    searchButtons.forEach(button => {
                        button.innerHTML = '<i class="fas fa-search"></i>';
                        button.style.pointerEvents = 'auto';
                        button.disabled = false;
                    });

                    if (data.status === 'success') {
                        todasEntradas = data.entradas || [];
                        exibirResultados(todasEntradas);

                        // Mostrar/ocultar botão de PDF
                        document.getElementById('btnGerarPDF').style.display = todasEntradas.length > 0 ? 'inline-block' : 'none';

                        // Configurar o botão de PDF
                        if (todasEntradas.length > 0) {
                            document.getElementById('btnGerarPDF').onclick = function () {
                                gerarPDFResumo(todasEntradas);
                            };
                        }
                    } else {
                        mostrarErro(data.message || 'Nenhuma entrada encontrada');
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);

                    // Restaurar botões
                    searchButtons.forEach(button => {
                        button.innerHTML = '<i class="fas fa-search"></i>';
                        button.style.pointerEvents = 'auto';
                        button.disabled = false;
                    });

                    mostrarErro('Erro ao consultar entradas: ' + error.message);
                });
        }

        // Função para exibir os resultados na tabela
        function exibirResultados(entradas) {
            const resultadoContainer = document.getElementById('resultado-container');
            const corpoTabela = document.getElementById('corpoTabela');

            // Limpar tabela atual
            corpoTabela.innerHTML = '';

            if (!entradas || entradas.length === 0) {
                resultadoContainer.style.display = 'none';
                mostrarErro('Nenhuma entrada encontrada para o período informado');
                return;
            }

            // Adicionar cada entrada na tabela
            entradas.forEach(entrada => {
                const linha = document.createElement('tr');

                linha.innerHTML = `
                        <td>${entrada.id_Entrada || '---'}</td>
                        <td>${formatarData(entrada.data_Entrada) || '---'}</td>
                        <td>${formatarMoeda(entrada.valor_Total) || '---'}</td>
                    `;

                // Adicionar evento de clique para mostrar detalhes
                linha.style.cursor = 'pointer';
                linha.addEventListener('click', () => abrirDetalhes(entrada));

                corpoTabela.appendChild(linha);
            });

            // Exibir a tabela com animação
            resultadoContainer.style.opacity = '0';
            resultadoContainer.style.display = 'block';
            setTimeout(() => {
                resultadoContainer.style.opacity = '1';
                resultadoContainer.style.transition = 'opacity 0.3s ease';
            }, 10);
        }

        // Função para abrir modal com detalhes da entrada
        function abrirDetalhes(entrada) {
            entradaSelecionada = entrada;
            // Preencher os campos do modal
            document.getElementById('modalCodigo').value = entrada.id_Entrada || '---';
            document.getElementById('modalData').value = formatarData(entrada.data_Entrada) || '---';
            document.getElementById('modalValor').value = formatarMoeda(entrada.valor_Total) || '---';
            // Mostrar indicador de carregamento
            document.getElementById('btnDetalhesPDF').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Carregando...';
            document.getElementById('btnDetalhesPDF').disabled = true;
            // Obter o token CSRF
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            // Buscar detalhes dos produtos dessa entrada
            fetch('{{ route("menu.relatorio.entradas.detalhes") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ id_Entrada: entrada.id_Entrada })
            })
                .then(response => response.json())
                .then(data => {
                    document.getElementById('btnDetalhesPDF').innerHTML = '<i class="fas fa-file-pdf"></i> Gerar PDF Detalhado';
                    document.getElementById('btnDetalhesPDF').disabled = false;
                    if (data.status === 'success') {
                        produtosEntrada = data.produtos || [];
                        document.getElementById('btnDetalhesPDF').onclick = function () {
                            gerarPDFDetalhado(produtosEntrada, entrada);
                        };
                        // Exibir o modal com classe ativa e foco
                        const modal = document.getElementById('entradaModal');
                        modal.classList.add('active');
                        modal.style.display = '';
                        setTimeout(() => {
                            modal.querySelector('.entrada-modal-close').focus();
                        }, 100);
                    } else {
                        mostrarErro(data.message || 'Erro ao buscar detalhes da entrada');
                    }
                })
                .catch(error => {
                    document.getElementById('btnDetalhesPDF').innerHTML = '<i class="fas fa-file-pdf"></i> Gerar PDF Detalhado';
                    document.getElementById('btnDetalhesPDF').disabled = false;
                    console.error('Erro:', error);
                    mostrarErro('Erro ao buscar detalhes: ' + error.message);
                });
        }

        // Função para fechar o modal
        function fecharModal() {
            const modal = document.getElementById('entradaModal');
            modal.classList.remove('active');
            modal.style.display = 'none';
        }

        // Fechar o modal ao clicar fora dele ou pressionar ESC
        window.onclick = function (event) {
            const modal = document.getElementById('entradaModal');
            if (event.target === modal) {
                fecharModal();
            }
        };
        document.addEventListener('keydown', function (e) {
            const modal = document.getElementById('entradaModal');
            if (modal.classList.contains('active') && (e.key === 'Escape' || e.key === 'Esc')) {
                fecharModal();
            }
        });

        // Função para gerar PDF com resumo das entradas
        function gerarPDFResumo(entradas) {
            if (!entradas || entradas.length === 0) {
                mostrarErro('Não há dados para gerar o relatório');
                return;
            }

            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();

            // Adicionar cabeçalho
            doc.setFontSize(18);
            doc.setTextColor(66, 0, 255);
            doc.text('Relatório de Entradas de Produtos', 14, 15);

            // Adicionar informações do período
            doc.setFontSize(12);
            doc.setTextColor(0, 0, 0);

            const start_date = formatarData(document.getElementById('start_date').value);
            const end_date = formatarData(document.getElementById('end_date').value);

            doc.text(`Período: ${start_date} a ${end_date}`, 14, 25);

            // Critérios de busca
            let linhaY = 30;
            const nome_Produto = document.getElementById('nomeProduto').value.trim();
            const razao_Social = document.getElementById('fornecedor').value.trim();
            const grupo = document.getElementById('grupo').value.trim();
            const sub_Grupo = document.getElementById('subgrupo').value.trim();

            if (nome_Produto) {
                doc.text(`Produto: ${nome_Produto}`, 14, linhaY);
                linhaY += 5;
            }

            if (razao_Social) {
                doc.text(`Fornecedor: ${razao_Social}`, 14, linhaY);
                linhaY += 5;
            }

            if (grupo) {
                doc.text(`Grupo: ${grupo}`, 14, linhaY);
                linhaY += 5;
            }

            if (sub_Grupo) {
                doc.text(`SubGrupo: ${sub_Grupo}`, 14, linhaY);
                linhaY += 5;
            }

            // Configuração da tabela
            const colunas = [
                "Código",
                "Data de Entrada",
                "Valor Total"
            ];

            // Preparar dados para a tabela
            const linhas = entradas.map(entrada => [
                entrada.id_Entrada || '---',
                formatarData(entrada.data_Entrada) || '---',
                formatarMoeda(entrada.valor_Total || 0)
            ]);

            // Adicionar a tabela ao PDF
            doc.autoTable({
                startY: linhaY + 5,
                head: [colunas],
                body: linhas,
                theme: 'grid',
                headStyles: {
                    fillColor: [66, 0, 255],
                    textColor: [255, 255, 255],
                    fontStyle: 'bold'
                }
            });

            // Calcular e adicionar valor total
            let valorTotal = 0;
            entradas.forEach(entrada => {
                valorTotal += parseFloat(entrada.valor_Total || 0);
            });

            const finalY = doc.previousAutoTable.finalY || linhaY + 5;

            doc.setFontSize(12);
            doc.setTextColor(0, 0, 0);
            doc.text(`Total de entradas: ${entradas.length}`, 14, finalY + 10);
            doc.text(`Valor total: ${formatarMoeda(valorTotal)}`, 14, finalY + 18);

            // Adicionar rodapé
            const totalPaginas = doc.internal.getNumberOfPages();
            for (let i = 1; i <= totalPaginas; i++) {
                doc.setPage(i);
                doc.setFontSize(8);
                doc.setTextColor(100, 100, 100);
                doc.text(`Página ${i} de ${totalPaginas}`, doc.internal.pageSize.width - 30, doc.internal.pageSize.height - 10);
                doc.text('Empório Maxx - Sistema de Gestão', 14, doc.internal.pageSize.height - 10);
            }

            // Salvar o PDF
            const dataAtual = new Date().toLocaleDateString('pt-BR').replace(/\//g, '-');
            doc.save(`Relatorio_Entradas_${dataAtual}.pdf`);
        }

        // Função para gerar PDF detalhado de uma entrada específica
        function gerarPDFDetalhado(produtos, entrada) {
            if (!produtos || produtos.length === 0 || !entrada) {
                mostrarErro('Não há dados suficientes para gerar o relatório');
                return;
            }

            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();

            // Adicionar cabeçalho
            doc.setFontSize(18);
            doc.setTextColor(66, 0, 255);
            doc.text('Relatório Detalhado de Entrada', 14, 15);

            // Adicionar informações da entrada
            doc.setFontSize(12);
            doc.setTextColor(0, 0, 0);
            doc.text(`Código da Entrada: ${entrada.id_Entrada || '---'}`, 14, 25);
            doc.text(`Data de Entrada: ${formatarData(entrada.data_Entrada) || '---'}`, 14, 30);
            doc.text(`Valor Total: ${formatarMoeda(entrada.valor_Total || 0)}`, 14, 35);
            doc.text(`Fornecedor: ${entrada.razao_Social || 'Não informado'}`, 14, 40);

            // Configuração da tabela
            const colunas = [
                "Código",
                "Produto",
                "Fornecedor",
                "Quantidade",
                "Preço Custo",
                "Valor Total"
            ];

            // Preparar dados para a tabela
            const linhas = produtos.map(produto => [
                produto.cod_Produto || '---',
                produto.nome_Produto || '---',
                produto.razao_Social || '---',
                produto.qtd_Entrada || '---',
                formatarMoeda(produto.preco_Custo || 0),
                formatarMoeda((produto.preco_Custo || 0) * (produto.qtd_Entrada || 0))
            ]);

            // Adicionar a tabela ao PDF
            doc.autoTable({
                startY: 45,
                head: [colunas],
                body: linhas,
                theme: 'grid',
                headStyles: {
                    fillColor: [66, 0, 255],
                    textColor: [255, 255, 255],
                    fontStyle: 'bold'
                }
            });

            // Adicionar rodapé
            const totalPaginas = doc.internal.getNumberOfPages();
            for (let i = 1; i <= totalPaginas; i++) {
                doc.setPage(i);
                doc.setFontSize(8);
                doc.setTextColor(100, 100, 100);
                doc.text(`Página ${i} de ${totalPaginas}`, doc.internal.pageSize.width - 30, doc.internal.pageSize.height - 10);
                doc.text('Empório Maxx - Sistema de Gestão', 14, doc.internal.pageSize.height - 10);
            }

            // Salvar o PDF
            const dataAtual = new Date().toLocaleDateString('pt-BR').replace(/\//g, '-');
            doc.save(`Entrada_${entrada.id_Entrada}_${dataAtual}.pdf`);
        }
    </script>
@endpush