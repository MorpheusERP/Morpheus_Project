@extends('layouts.app')

@section('title', 'Relatório de Usuários')
@section('header-title', 'Relatório de Usuários')

@push('styles')
    @vite(['resources/css/menu/relatorio/usuario-relatorio.css'])
@endpush

@section('content')
    <div class="form">
        <div class="Conteudo">
            <form id="consultaForm" autocomplete="off">
                <div class="search-fields">
                    <div class="input-containe search-container">
                        <input type="text" id="nome" class="input-field" placeholder="Nome do usuário">
                        <button type="button" class="search-button" onclick="buscarUsuarios()">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <div class="input-containe search-container">
                        <select id="tipoUsuario" class="input-field">
                            <option value="" selected>Todos</option>
                            <option value="admin">Administrador</option>
                            <option value="padrao">Padrão</option>
                        </select>
                        <button type="button" class="search-button" onclick="buscarUsuarios()">
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
                <div class="table-responsive">
                    <table id="tabelaUsuarios">
                        <thead>
                            <tr id="cabecalhoTabela"></tr>
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
            let resultadosUsuarios = [];

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
                document.getElementById('nome').addEventListener('keypress', function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        buscarUsuarios();
                    }
                });

                document.getElementById('tipoUsuario').addEventListener('change', function () {
                    buscarUsuarios();
                });

                document.getElementById('btnGerarPDF').addEventListener('click', function () {
                    if (resultadosUsuarios.length === 0) {
                        return;
                    }
                    try {
                        const btnGerarPDF = document.getElementById('btnGerarPDF');
                        const originalBtnText = btnGerarPDF.innerHTML;
                        btnGerarPDF.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Gerando...';
                        btnGerarPDF.disabled = true;

                        const { jsPDF } = window.jspdf;
                        if (!jsPDF) {
                            throw new Error('jsPDF não está disponível');
                        }

                        const doc = new jsPDF();

                        doc.setFontSize(18);
                        doc.setTextColor(66, 0, 255);
                        doc.text('Relatório de Usuários', 14, 22);

                        const dataAtual = new Date().toLocaleDateString('pt-BR');
                        doc.setFontSize(10);
                        doc.setTextColor(100, 100, 100);
                        doc.text(`Data de geração: ${dataAtual}`, 14, 30);

                        const headers = [['ID', 'Nome', 'Email', 'Tipo']];
                        const data = resultadosUsuarios.map(usuario => [
                            usuario.id_Usuario || '---',
                            usuario.nome_Usuario || '---',
                            usuario.email || '---',
                            formatarTipoUsuario(usuario.tipo_Usuario) || '---'
                        ]);

                        doc.autoTable({
                            head: headers,
                            body: data,
                            startY: 35,
                            theme: 'grid',
                            styles: {
                                fontSize: 9,
                                cellPadding: 3,
                            },
                            headStyles: {
                                fillColor: [66, 0, 255],
                                textColor: [255, 255, 255],
                                fontStyle: 'bold'
                            },
                            alternateRowStyles: {
                                fillColor: [240, 240, 240]
                            }
                        });

                        const totalPaginas = doc.internal.getNumberOfPages();
                        for (let i = 1; i <= totalPaginas; i++) {
                            doc.setPage(i);
                            doc.setFontSize(8);
                            doc.setTextColor(100, 100, 100);
                            doc.text(`Página ${i} de ${totalPaginas}`, doc.internal.pageSize.width - 30, doc.internal.pageSize.height - 10);
                            doc.text('Empório Maxx - Sistema de Gestão', 14, doc.internal.pageSize.height - 10);
                        }

                        const filename = `Relatorio_Usuarios_${dataAtual.replace(/\//g, '-')}.pdf`;
                        doc.save(filename);

                    } catch (error) {
                        console.error('Erro ao gerar PDF:', error);
                        mostrarErro('Erro ao gerar PDF: ' + error.message);
                    } finally {
                        const btnGerarPDF = document.getElementById('btnGerarPDF');
                        btnGerarPDF.innerHTML = '<i class="fas fa-file-pdf"></i> Gerar PDF';
                        btnGerarPDF.disabled = false;
                    }
                });
            });

            function buscarUsuarios() {
                document.getElementById('mensagemErro').style.display = 'none';

                const searchButtons = document.querySelectorAll('.search-button');
                searchButtons.forEach(button => {
                    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                    button.disabled = true;
                });

                const nome = document.getElementById('nome').value.trim();
                const tipo = document.getElementById('tipoUsuario').value;

                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                const requestData = {
                    nome: nome,
                    tipo: tipo
                };

                fetch('{{ route("menu.relatorio.usuarios.search") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                         'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify(requestData)
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
                            resultadosUsuarios = data.usuarios || [];
                            exibirResultados(resultadosUsuarios);

                            document.getElementById('btnGerarPDF').disabled = resultadosUsuarios.length === 0;
                        } else {
                            throw new Error(data.message || 'Resposta inválida do servidor');
                        }

                        searchButtons.forEach(button => {
                            button.innerHTML = '<i class="fas fa-search"></i>';
                            button.disabled = false;
                        });
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        mostrarErro(error.message || 'Ocorreu um erro ao buscar usuários');

                        searchButtons.forEach(button => {
                            button.innerHTML = '<i class="fas fa-search"></i>';
                            button.disabled = false;
                        });

                        document.getElementById('btnGerarPDF').disabled = true;
                    });
            }

            function exibirResultados(usuarios) {
                const corpoTabela = document.getElementById('corpoTabela');
                const cabecalhoTabela = document.getElementById('cabecalhoTabela');
                const resultadoContainer = document.getElementById('resultado-container');

                corpoTabela.innerHTML = '';
                cabecalhoTabela.innerHTML = '';

                if (!usuarios || usuarios.length === 0) {
                    corpoTabela.innerHTML = '<tr><td colspan="4" class="sem-resultados">Nenhum usuário encontrado.</td></tr>';
                    resultadoContainer.style.display = 'block';
                    return;
                }

                cabecalhoTabela.innerHTML = '<th>Nome</th><th>Email</th><th>Tipo</th>';
                usuarios.forEach(usuario => {
                    const linha = document.createElement('tr');

                    linha.innerHTML = `<td>${usuario.nome_Usuario || '---'}</td><td>${usuario.email || '---'}</td><td>${formatarTipoUsuario(usuario.tipo_Usuario) || '---'}</td>`;

                    corpoTabela.appendChild(linha);
                });

                resultadoContainer.style.opacity = '0';
                resultadoContainer.style.display = 'block';
                setTimeout(() => {
                    resultadoContainer.style.opacity = '1';
                    resultadoContainer.style.transition = 'opacity 0.3s ease';
                }, 10);
            }

            function formatarTipoUsuario(tipo) {
                if (!tipo) return '---';
                if (tipo === 'admin') return 'Administrador';
                if (tipo === 'padrao') return 'Padrão';
                return tipo;
            }
        </script>
@endpush
