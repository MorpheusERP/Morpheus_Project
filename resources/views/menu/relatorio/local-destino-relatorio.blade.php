@extends('layouts.app')

@section('title', 'Relatório de Locais de Destino')
@section('header-title', 'Relatório de Locais de Destino')

@push('styles')
    @vite(['resources/css/menu/relatorio/local-destino-relatorio.css'])
@endpush

@section('content')
    <div class="form">
        <div class="Conteudo">
            <form id="consultaForm" autocomplete="off">
                <div class="search-fields">
                    <div class="input-containe search-container">
                        <input type="text" id="nomeLocal" class="input-field" placeholder="Nome do Local">
                        <button type="button" class="search-button" onclick="buscarLocais()">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <div class="input-containe search-container">
                        <input type="text" id="tipoLocal" class="input-field" placeholder="Tipo do Local">
                        <button type="button" class="search-button" onclick="buscarLocais()">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
            <div id="mensagemErro" style="display: none;"></div>
            <div class="buttons-container" style="margin: 15px 0; display: flex; justify-content: flex-end;">
                <button id="btnGerarPDF" class="new" disabled>
                    <i class="fas fa-file-pdf"></i> Gerar PDF
                </button>
            </div>
            <div id="resultado-container" class="resultado-container" style="display: none;">
                <div class="resultado-titulo">Resultados da Busca</div>
                <table id="tabelaLocais">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome do Local</th>
                            <th>Tipo do Local</th>
                            <th>Observação</th>
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
        let resultadosLocais = [];

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

        document.addEventListener('DOMContentLoaded', function () {
            ['nomeLocal', 'tipoLocal'].forEach(function (id) {
                document.getElementById(id).addEventListener('keypress', function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        buscarLocais();
                    }
                });
            });
            document.getElementById('btnGerarPDF').addEventListener('click', function () {
                if (resultadosLocais.length === 0) {
                    mostrarErro('Não há dados para gerar o relatório');
                    return;
                }
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF();
                doc.setFontSize(18);
                doc.setTextColor(66, 0, 255);
                doc.text('Relatório de Locais de Destino', 14, 22);
                const dataAtual = new Date().toLocaleDateString('pt-BR');
                doc.setFontSize(10);
                doc.setTextColor(100, 100, 100);
                doc.text(`Data de geração: ${dataAtual}`, 14, 30);
                const headers = [['ID', 'Nome do Local', 'Tipo do Local', 'Observação']];
                const data = resultadosLocais.map(local => [
                    local.id_Local || '',
                    local.nome_Local || '',
                    local.tipo_Local || '',
                    local.observacao || ''
                ]);
                doc.autoTable({
                    head: headers,
                    body: data,
                    startY: 35,
                    theme: 'grid',
                    styles: { fontSize: 9, cellPadding: 3 },
                    headStyles: { fillColor: [66, 0, 255], textColor: [255, 255, 255], fontStyle: 'bold' },
                    alternateRowStyles: { fillColor: [240, 240, 240] }
                });
                const totalPaginas = doc.internal.getNumberOfPages();
                for (let i = 1; i <= totalPaginas; i++) {
                    doc.setPage(i);
                    doc.setFontSize(8);
                    doc.setTextColor(100, 100, 100);
                    doc.text(`Página ${i} de ${totalPaginas}`, doc.internal.pageSize.width - 30, doc.internal.pageSize.height - 10);
                    doc.text('Empório Maxx - Sistema de Gestão', 14, doc.internal.pageSize.height - 10);
                }
                doc.save(`Relatorio_Locais_Destino_${dataAtual.replace(/\//g, '-')}.pdf`);
            });
        });

        function buscarLocais() {
            document.getElementById('mensagemErro').style.display = 'none';
            const searchButtons = document.querySelectorAll('.search-button');
            searchButtons.forEach(button => {
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                button.disabled = true;
            });
            const nomeLocal = document.getElementById('nomeLocal').value.trim();
            const tipoLocal = document.getElementById('tipoLocal').value.trim();
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            fetch('{{ route("menu.relatorio.locais.search") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    nome_Local: nomeLocal,
                    tipo_Local: tipoLocal
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
                        resultadosLocais = data.locais || [];
                        exibirResultados(resultadosLocais);
                        document.getElementById('btnGerarPDF').disabled = resultadosLocais.length === 0;
                    } else {
                        throw new Error(data.message || 'Resposta inválida do servidor');
                    }
                    searchButtons.forEach(button => {
                        button.innerHTML = '<i class="fas fa-search"></i>';
                        button.disabled = false;
                    });
                })
                .catch(error => {
                    mostrarErro(error.message || 'Ocorreu um erro ao buscar locais');
                    searchButtons.forEach(button => {
                        button.innerHTML = '<i class="fas fa-search"></i>';
                        button.disabled = false;
                    });
                    document.getElementById('btnGerarPDF').disabled = true;
                });
        }

        function exibirResultados(locais) {
            const corpoTabela = document.getElementById('corpoTabela');
            const resultadoContainer = document.getElementById('resultado-container');
            corpoTabela.innerHTML = '';
            locais.forEach(local => {
                const linha = document.createElement('tr');
                const nomeLocal = local.nome_Local || '---';
                const tipoLocal = local.tipo_Local || '---';
                const observacao = local.observacao || '---';
                linha.innerHTML = `
                    <td>${local.id_Local || ''}</td>
                    <td ${nomeLocal.length > 20 ? `data-content="${nomeLocal}"` : ''}>${nomeLocal}</td>
                    <td ${tipoLocal.length > 15 ? `data-content="${tipoLocal}"` : ''}>${tipoLocal}</td>
                    <td ${observacao.length > 30 ? `data-content="${observacao}"` : ''}>${observacao}</td>
                `;
                corpoTabela.appendChild(linha);
            });
            resultadoContainer.style.opacity = '0';
            resultadoContainer.style.display = 'block';
            setTimeout(() => {
                resultadoContainer.style.opacity = '1';
                resultadoContainer.style.transition = 'opacity 0.3s ease';
            }, 10);
        }
    </script>
@endpush