@extends('layouts.app')

@section('title', 'Relatório de Fornecedores')
@section('header-title', 'Relatório de Fornecedores')

@push('styles')
    @vite(['resources/css/menu/relatorio/fornecedor-relatorio.css'])
@endpush

@section('content')
    <div class="form">
        <div class="Conteudo">
            <form id="consultaForm" autocomplete="off">
                <div class="search-fields">
                    <div class="input-containe search-container">
                        <input type="text" id="razaoSocial" class="input-field" placeholder="Razão Social">
                        <button type="button" class="search-button" onclick="buscarFornecedores()">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <div class="input-containe search-container">
                        <input type="text" id="nomeFantasia" class="input-field" placeholder="Nome Fantasia">
                        <button type="button" class="search-button" onclick="buscarFornecedores()">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <div class="search-fields">
                    <div class="input-containe search-container">
                        <input type="text" id="grupo" class="input-field" placeholder="Grupo">
                        <button type="button" class="search-button" onclick="buscarFornecedores()">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <div class="input-containe search-container">
                        <input type="text" id="subgrupo" class="input-field" placeholder="Subgrupo">
                        <button type="button" class="search-button" onclick="buscarFornecedores()">
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
                <div class="table-responsive-scroll">
                    <table id="tabelaFornecedores">
                        <thead>
                            <tr>
                                <th class="col-id">ID</th>
                                <th class="col-razao">Razão Social</th>
                                <th class="col-nome">Nome Fantasia</th>
                                <th class="col-grupo">Grupo</th>
                                <th class="col-subgrupo">Subgrupo</th>
                            </tr>
                        </thead>
                        <tbody id="corpoTabela"></tbody>
                    </table>
                </div>
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
        let resultadosFornecedores = [];

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
            ['razaoSocial', 'nomeFantasia', 'grupo', 'subgrupo'].forEach(function (id) {
                document.getElementById(id).addEventListener('keypress', function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        buscarFornecedores();
                    }
                });
            });
            document.getElementById('btnGerarPDF').addEventListener('click', function () {
                if (resultadosFornecedores.length === 0) {
                    mostrarErro('Não há dados para gerar o relatório');
                    return;
                }
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF();
                doc.setFontSize(18);
                doc.setTextColor(66, 0, 255);
                doc.text('Relatório de Fornecedores', 14, 22);
                const dataAtual = new Date().toLocaleDateString('pt-BR');
                doc.setFontSize(10);
                doc.setTextColor(100, 100, 100);
                doc.text(`Data de geração: ${dataAtual}`, 14, 30);
                const headers = [['ID', 'Razão Social', 'Nome Fantasia', 'Grupo', 'Subgrupo']];
                const data = resultadosFornecedores.map(fornecedor => [
                    fornecedor.id_Fornecedor || '---',
                    fornecedor.razao_Social || '---',
                    fornecedor.nome_Fantasia || '---',
                    fornecedor.grupo || '---',
                    fornecedor.sub_Grupo || '---'
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
                doc.save(`Relatorio_Fornecedores_${dataAtual.replace(/\//g, '-')}.pdf`);
            });
        });

        function buscarFornecedores() {
            document.getElementById('mensagemErro').style.display = 'none';
            const searchButtons = document.querySelectorAll('.search-button');
            searchButtons.forEach(button => {
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                button.disabled = true;
            });
            const razaoSocial = document.getElementById('razaoSocial').value.trim();
            const nomeFantasia = document.getElementById('nomeFantasia').value.trim();
            const grupo = document.getElementById('grupo').value.trim();
            const subgrupo = document.getElementById('subgrupo').value.trim();

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            fetch('{{ route("menu.relatorio.fornecedores.search") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    razao_Social: razaoSocial,
                    nome_Fantasia: nomeFantasia,
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
                        resultadosFornecedores = data.fornecedores || [];
                        exibirResultados(resultadosFornecedores);
                        document.getElementById('btnGerarPDF').disabled = resultadosFornecedores.length === 0;
                    } else {
                        throw new Error(data.message || 'Resposta inválida do servidor');
                    }
                    searchButtons.forEach(button => {
                        button.innerHTML = '<i class="fas fa-search"></i>';
                        button.disabled = false;
                    });
                })
                .catch(error => {
                    mostrarErro(error.message || 'Ocorreu um erro ao buscar fornecedores');
                    searchButtons.forEach(button => {
                        button.innerHTML = '<i class="fas fa-search"></i>';
                        button.disabled = false;
                    });
                    document.getElementById('btnGerarPDF').disabled = true;
                });
        }

        function exibirResultados(fornecedores) {
            const corpoTabela = document.getElementById('corpoTabela');
            const resultadoContainer = document.getElementById('resultado-container');
            corpoTabela.innerHTML = '';
            fornecedores.forEach(fornecedor => {
                const linha = document.createElement('tr');
                linha.innerHTML = `
                    <td>${fornecedor.id_Fornecedor || '---'}</td>
                    <td>${fornecedor.razao_Social || '---'}</td>
                    <td>${fornecedor.nome_Fantasia || '---'}</td>
                    <td>${fornecedor.grupo || '---'}</td>
                    <td>${fornecedor.sub_Grupo || '---'}</td>
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