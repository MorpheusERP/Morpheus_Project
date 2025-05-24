@extends('layouts.app')

@section('title', 'Relatório de Saída de Produtos')
@section('header-title', 'Relatório de Saída de Produtos')

@push('styles')
    @vite(['resources/css/menu/relatorio/saida-produtos-relatorio.css'])
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
                        <button type="button" class="search-button" onclick="buscarSaidas()">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <div class="input-containe search-container">
                        <input type="text" id="nomeLocal" class="input-field" placeholder="Local de Destino">
                        <button type="button" class="search-button" onclick="buscarSaidas()">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <div class="search-fields">
                    <div class="input-containe search-container">
                        <input type="text" id="grupo" class="input-field" placeholder="Grupo">
                        <button type="button" class="search-button" onclick="buscarSaidas()">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <div class="input-containe search-container">
                        <input type="text" id="subgrupo" class="input-field" placeholder="Subgrupo">
                        <button type="button" class="search-button" onclick="buscarSaidas()">
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
                <table id="tabelaSaidas">
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
    <div id="saidaModal" class="saida-modal-bg" role="dialog" aria-modal="true" aria-labelledby="saidaModalTitle" tabindex="-1">
        <div class="saida-modal-content">
            <button type="button" class="saida-modal-close" aria-label="Fechar detalhes" onclick="fecharModal()">&times;</button>
            <h2 id="saidaModalTitle">Detalhes da Saída</h2>
            <div>
                <div class="saida-modal-field">
                    <label for="modalCodigo" class="saida-modal-label">Código:</label>
                    <input id="modalCodigo" class="saida-modal-input" disabled>
                </div>
                <div class="saida-modal-field">
                    <label for="modalData" class="saida-modal-label">Data:</label>
                    <input id="modalData" class="saida-modal-input" disabled>
                </div>
                <div class="saida-modal-field">
                    <label for="modalValor" class="saida-modal-label">Valor Total:</label>
                    <input id="modalValor" class="saida-modal-input" disabled>
                </div>
                <button id="btnDetalhesPDF" class="saida-modal-pdf-btn">
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
        let todasSaidas = [];
        let produtosSaida = [];
        let saidaSelecionada = null;

        document.addEventListener('DOMContentLoaded', function () {
            const hoje = new Date();
            const dataHoje = hoje.toISOString().split('T')[0];
            document.getElementById('end_date').value = dataHoje;

            const dataInicio = new Date();
            dataInicio.setDate(dataInicio.getDate() - 30);
            document.getElementById('start_date').value = dataInicio.toISOString().split('T')[0];

            // Adicionar eventos de tecla Enter para os campos
            document.getElementById('nomeProduto').addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    buscarSaidas();
                }
            });

            document.getElementById('nomeLocal').addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    buscarSaidas();
                }
            });

            document.getElementById('grupo').addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    buscarSaidas();
                }
            });

            document.getElementById('subgrupo').addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    buscarSaidas();
                }
            });
        });

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

        function buscarSaidas() {
            document.getElementById('mensagemErro').style.display = 'none';

            // Obter valores dos campos
            const start_date = document.getElementById('start_date').value;
            const end_date = document.getElementById('end_date').value;
            const nome_Produto = document.getElementById('nomeProduto').value.trim();
            const nome_Local = document.getElementById('nomeLocal').value.trim();
            const grupo = document.getElementById('grupo').value.trim();
            const sub_Grupo = document.getElementById('subgrupo').value.trim();

            if (!start_date || !end_date) {
                mostrarErro('Por favor, informe o período de consulta');
                return;
            }

            if (new Date(start_date) > new Date(end_date)) {
                mostrarErro('A data inicial deve ser menor ou igual à data final');
                return;
            }

            const searchButtons = document.querySelectorAll('.search-button');
            searchButtons.forEach(button => {
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                button.style.pointerEvents = 'none';
            });

            // Obter o token CSRF
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const dados = {
                start_date: start_date,
                end_date: end_date,
                nome_Produto: nome_Produto,
                nome_Local: nome_Local,
                grupo: grupo,
                sub_Grupo: sub_Grupo
            };

            // Fazer a requisição para o servidor
            fetch('{{ route("menu.relatorio.saidas.search") }}', {
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
                    // Restaurar botões
                    searchButtons.forEach(button => {
                        button.innerHTML = '<i class="fas fa-search"></i>';
                        button.style.pointerEvents = 'auto';
                    });

                    if (data.status === 'success') {
                        todasSaidas = data.saidas || [];
                        exibirResultados(todasSaidas);
                        document.getElementById('btnGerarPDF').style.display = todasSaidas.length > 0 ? 'inline-block' : 'none';
                        if (todasSaidas.length > 0) {
                            document.getElementById('btnGerarPDF').onclick = function (e) {
                                e.preventDefault();
                                gerarPDFResumo(todasSaidas);
                            };
                        }
                    } else {
                        mostrarErro(data.message || 'Nenhuma saída encontrada');
                    }
                })
                .catch(error => {
                    searchButtons.forEach(button => {
                        button.innerHTML = '<i class="fas fa-search"></i>';
                        button.style.pointerEvents = 'auto';
                    });
                    mostrarErro('Erro ao consultar saídas: ' + error.message);
                });
        }

        // Função para exibir os resultados na tabela
        function exibirResultados(saidas) {
            const resultadoContainer = document.getElementById('resultado-container');
            const corpoTabela = document.getElementById('corpoTabela');
            corpoTabela.innerHTML = '';
            saidas.forEach(saida => {
                const linha = document.createElement('tr');
                linha.innerHTML = `
                        <td>${saida.id_Saida || '---'}</td>
                        <td>${formatarData(saida.data_Saida) || '---'}</td>
                        <td>${formatarMoeda(saida.valor_Total) || '---'}</td>
                    `;
                linha.style.cursor = 'pointer';
                linha.addEventListener('click', () => abrirDetalhes(saida));
                corpoTabela.appendChild(linha);
            });
            resultadoContainer.style.opacity = '0';
            resultadoContainer.style.display = 'block';
            setTimeout(() => {
                resultadoContainer.style.opacity = '1';
                resultadoContainer.style.transition = 'opacity 0.3s ease';
            }, 10);
        }

        // Função para abrir modal com detalhes da saída
        function abrirDetalhes(saida) {
            saidaSelecionada = saida;
            document.getElementById('modalCodigo').value = saida.id_Saida || '---';
            document.getElementById('modalData').value = formatarData(saida.data_Saida) || '---';
            document.getElementById('modalValor').value = formatarMoeda(saida.valor_Total) || '---';
            document.getElementById('btnDetalhesPDF').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Carregando...';
            document.getElementById('btnDetalhesPDF').disabled = true;
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            fetch('{{ route("menu.relatorio.saidas.detalhes") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    id_Saida: saida.id_Saida
                })
            })
                .then(async response => {
                    let data;
                    try {
                        data = await response.json();
                    } catch (e) {
                        throw new Error('Resposta do servidor não é um JSON válido.');
                    }
                    return data;
                })
                .then(data => {
                    document.getElementById('btnDetalhesPDF').innerHTML = '<i class="fas fa-file-pdf"></i> Gerar PDF Detalhado';
                    document.getElementById('btnDetalhesPDF').disabled = false;
                    if (data.status === 'success') {
                        produtosSaida = data.produtos || [];
                        document.getElementById('btnDetalhesPDF').onclick = function () {
                            gerarPDFDetalhado(produtosSaida, saidaSelecionada);
                        };
                        const modal = document.getElementById('saidaModal');
                        modal.classList.add('active');
                        modal.style.display = 'flex';
                        setTimeout(() => {
                            modal.querySelector('.saida-modal-close').focus();
                        }, 100);
                    } else {
                        mostrarErro(data.message || 'Erro ao buscar detalhes da saída');
                    }
                })
                .catch(error => {
                    document.getElementById('btnDetalhesPDF').innerHTML = '<i class="fas fa-file-pdf"></i> Gerar PDF Detalhado';
                    document.getElementById('btnDetalhesPDF').disabled = false;
                    mostrarErro('Erro ao buscar detalhes: ' + error.message);
                });
        }

        // Função para fechar o modal
        function fecharModal() {
            const modal = document.getElementById('saidaModal');
            modal.classList.remove('active');
            modal.style.display = 'none';
        }

        // Fechar o modal ao clicar fora dele ou pressionar ESC
        window.onclick = function (event) {
            const modal = document.getElementById('saidaModal');
            if (event.target === modal) {
                fecharModal();
            }
        };
        document.addEventListener('keydown', function(e) {
            const modal = document.getElementById('saidaModal');
            if (modal.classList.contains('active') && (e.key === 'Escape' || e.key === 'Esc')) {
                fecharModal();
            }
        });

        // Função para gerar PDF com resumo das saídas
        function gerarPDFResumo(saidas) {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();

            // Adicionar cabeçalho
            doc.setFontSize(18);
            doc.setTextColor(66, 0, 255);
            doc.text('Relatório de Saídas de Produtos', 14, 15);

            // Adicionar informações do período
            doc.setFontSize(12);
            doc.setTextColor(0, 0, 0);

            const start_date = formatarData(document.getElementById('start_date').value);
            const end_date = formatarData(document.getElementById('end_date').value);

            doc.text(`Período: ${start_date} a ${end_date}`, 14, 25);

            // Critérios de busca
            let linhaY = 30;
            const nome_Produto = document.getElementById('nomeProduto').value.trim();
            const nome_Local = document.getElementById('nomeLocal').value.trim();
            const grupo = document.getElementById('grupo').value.trim();
            const sub_Grupo = document.getElementById('subgrupo').value.trim();

            if (nome_Produto) {
                doc.text(`Produto: ${nome_Produto}`, 14, linhaY);
                linhaY += 5;
            }

            if (nome_Local) {
                doc.text(`Local de Destino: ${nome_Local}`, 14, linhaY);
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
                "Data de Saída",
                "Valor Total"
            ];

            // Preparar dados para a tabela
            const linhas = saidas.map(saida => [
                saida.id_Saida || '---',
                formatarData(saida.data_Saida) || '---',
                formatarMoeda(Number(saida.valor_Total) || 0)
            ]);

            // Adicionar a tabela ao PDF
            doc.autoTable({
                startY: linhaY + 5,
                head: [colunas],
                body: linhas,
                theme: 'grid',
                headStyles: { fillColor: [66, 0, 255], textColor: 255 }
            });

            // Calcular e adicionar valor total
            let valorTotal = 0;
            saidas.forEach(saida => {
                valorTotal += Number(saida.valor_Total) || 0;
            });

            const finalY = doc.previousAutoTable.finalY || linhaY + 5;

            doc.setFontSize(12);
            doc.setTextColor(0, 0, 0);
            doc.text(`Total de saídas: ${saidas.length}`, 14, finalY + 10);
            doc.text(`Valor total: ${formatarMoeda(valorTotal)}`, 14, finalY + 18);

            // Adicionar rodapé
            const totalPaginas = doc.internal.getNumberOfPages();
            for (let i = 1; i <= totalPaginas; i++) {
                doc.setPage(i);
                doc.setFontSize(10);
                doc.text(`Página ${i} de ${totalPaginas}`, 180, 290);
            }

            doc.save('relatorio-saidas.pdf');
        }

        // Função para gerar PDF detalhado de uma saída específica
        function gerarPDFDetalhado(produtos, lote) {
            if (!produtos || produtos.length === 0 || !lote) {
                mostrarErro('Não há dados suficientes para gerar o relatório');
                return;
            }

            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();

            // Adicionar cabeçalho
            doc.setFontSize(18);
            doc.setTextColor(66, 0, 255);
            doc.text('Relatório Detalhado de Saída', 14, 15);

            // Adicionar informações da saída
            doc.setFontSize(12);
            doc.setTextColor(0, 0, 0);
            doc.text(`Código da Saída: ${lote.id_Saida || '---'}`, 14, 25);
            doc.text(`Data de Saída: ${formatarData(lote.data_Saida) || '---'}`, 14, 30);
            doc.text(`Valor Total: ${formatarMoeda(lote.valor_Total || 0)}`, 14, 35);
            doc.text(`Usuário: ${lote.nome_Usuario || 'Não informado'}`, 14, 40);

            // Configuração da tabela
            const colunas = [
                "Código",
                "Produto",
                "Local Destino",
                "Tipo Local",
                "Quantidade",
                "Preço Custo",
                "Grupo",
                "Subgrupo"
            ];

            // Preparar dados para a tabela
            const linhas = produtos.map(produto => [
                produto.cod_Produto || '---',
                produto.nome_Produto || '---',
                produto.nome_Local || '---',
                produto.tipo_Local || '---',
                produto.qtd_saida || '---',
                produto.preco_Custo || '---',
                produto.grupo || '---',
                produto.sub_Grupo || '---'
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
            doc.save(`Saida_${lote.id_Saida}_${dataAtual}.pdf`);
        }
    </script>
@endpush