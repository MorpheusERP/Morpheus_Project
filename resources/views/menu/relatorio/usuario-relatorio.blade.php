<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Relatório de Usuários</title>
    <link rel="shortcut icon" href="{{ asset('images/logo.png') }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>

    @include('layouts.nav_bottom')
    @include('layouts.background')
    
    @vite(['resources/css/menu/usuarios/usuarios.css'])
    <style>
        /* Estilos específicos para a tabela de relatório */
        .resultado-container {
            width: 100%;
            margin-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            padding-top: 15px;
            transition: opacity 0.3s ease;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 10px;
            overflow: hidden;
            animation: fadeIn 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        thead {
            background: rgba(66, 0, 255, 0.3);
        }

        th {
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
            color: var(--accent-color);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        td {
            padding: 10px 15px;
            font-size: 14px;
            color: var(--text-light);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        tbody tr:hover {
            background: rgba(255, 255, 255, 0.12);
        }

        /* Botão para gerar PDF */
        .new {
            background-color: var(--accent-color);
            color: var(--text-dark);
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        /* Layout de dois campos de busca */
        .search-fields {
            display: flex;
            gap: 10px;
            width: 100%;
            margin-bottom: 15px;
        }
        
        .search-fields .input-containe {
            flex: 1;
        }
        
        @media (max-width: 480px) {
            .search-fields {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Relatório de Usuários</h1>
    </div>
    <div class="container">
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
                    <button id="btnGerarPDF" class="new" disabled>
                        <i class="fas fa-file-pdf"></i> Gerar PDF
                    </button>
                </div>
                
                <div id="resultado-container" class="resultado-container" style="display: none;">
                    <div class="resultado-titulo">Resultados da Busca</div>
                    <table id="tabelaUsuarios">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Tipo</th>
                            </tr>
                        </thead>
                        <tbody id="corpoTabela"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <div class="BotoesFooter">
            <div class="buttons-footer">
                <a href="{{ route('menu.relatorio.relatorio') }}" class="back-link">
                    <button class="search">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </button>
                </a>
            </div>
        </div>
    </footer>

    <div class="logo">
        <img src="{{ asset('images/Emporio maxx s-fundo.png') }}" alt="Empório Maxx Logo">
    </div>

    <script>
        // Armazenar os resultados para uso posterior ao gerar PDF
        let resultadosUsuarios = [];
        
        // Função para mostrar mensagem de erro
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
        
        // Adicionar evento para submissão do formulário quando pressionar Enter
        document.getElementById('nome').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                buscarUsuarios();
            }
        });
        
        // Adicionar evento para o campo de tipo ao mudar
        document.getElementById('tipoUsuario').addEventListener('change', function() {
            console.log('Tipo selecionado:', this.value);
        });
        
        // Função para buscar usuários
        function buscarUsuarios() {
            // Limpar mensagens de erro anteriores
            document.getElementById('mensagemErro').style.display = 'none';
            
            // Mostrar indicador de carregamento
            const searchButtons = document.querySelectorAll('.search-button');
            searchButtons.forEach(button => {
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                button.disabled = true;
            });
            
            // Obter os termos de busca
            const nome = document.getElementById('nome').value.trim();
            const tipo = document.getElementById('tipoUsuario').value;
            
            console.log('Buscando usuários com filtros:', { nome, tipo });
            console.log('Tipo do valor "tipo":', typeof tipo, 'Valor exato:', JSON.stringify(tipo));
            
            // Obter o token CSRF
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            console.log('URL da rota:', '{{ route("menu.relatorio.usuarios.search") }}');
            
            // Fazer a requisição para o servidor
            const requestData = { 
                nome: nome,
                tipo: tipo
            };
            
            console.log('Dados enviados ao servidor:', JSON.stringify(requestData));
            
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
                console.log('Response data:', data); // Debug log
                
                if (data.status === 'success') {
                    resultadosUsuarios = data.usuarios || [];
                    exibirResultados(resultadosUsuarios);
                    
                    // Habilitar/desabilitar botão de PDF
                    document.getElementById('btnGerarPDF').disabled = resultadosUsuarios.length === 0;
                } else {
                    throw new Error(data.message || 'Resposta inválida do servidor');
                }
                
                // Restaurar botões de busca
                searchButtons.forEach(button => {
                    button.innerHTML = '<i class="fas fa-search"></i>';
                    button.disabled = false;
                });
            })
            .catch(error => {
                console.error('Erro:', error);
                mostrarErro(error.message || 'Ocorreu um erro ao buscar usuários');
                
                // Restaurar botões de busca
                searchButtons.forEach(button => {
                    button.innerHTML = '<i class="fas fa-search"></i>';
                    button.disabled = false;
                });
                
                // Desabilitar botão de PDF em caso de erro
                document.getElementById('btnGerarPDF').disabled = true;
            });
        }
        
        // Função para exibir os resultados na tabela
        function exibirResultados(usuarios) {
            const corpoTabela = document.getElementById('corpoTabela');
            const resultadoContainer = document.getElementById('resultado-container');
            
            // Limpar tabela anterior
            corpoTabela.innerHTML = '';
            console.log('Exibindo resultados:', usuarios, 'Quantidade:', usuarios.length);
            
            if (!usuarios || usuarios.length === 0) {
                resultadoContainer.style.display = 'none';
                mostrarErro('Nenhum usuário encontrado com os critérios informados');
                return;
            }
            
            // Adicionar cada usuário na tabela
            usuarios.forEach(usuario => {
                const linha = document.createElement('tr');
                
                linha.innerHTML = `
                    <td>${usuario.id_Usuario || ''}</td>
                    <td>${usuario.nome_Usuario || ''}</td>
                    <td>${usuario.email || ''}</td>
                    <td>${formatarTipoUsuario(usuario.tipo_Usuario) || ''}</td>
                `;
                
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
        
        // Função para formatar o tipo de usuário de forma mais amigável
        function formatarTipoUsuario(tipo) {
            if (tipo === 'admin') return 'Administrador';
            if (tipo === 'padrao') return 'Padrão';
            return tipo; // Retorna o valor original caso não seja nenhum dos tipos conhecidos
        }
        
        // Função para gerar PDF com os resultados
        document.getElementById('btnGerarPDF').addEventListener('click', function() {
            if (resultadosUsuarios.length === 0) {
                mostrarErro('Não há dados para gerar o relatório');
                return;
            }
            
            try {
                // Mostrar indicador de carregamento no botão
                const btnGerarPDF = document.getElementById('btnGerarPDF');
                const originalBtnText = btnGerarPDF.innerHTML;
                btnGerarPDF.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Gerando...';
                btnGerarPDF.disabled = true;
                
                console.log('Gerando PDF...');
                
                // Usar window.jspdf para acessar a biblioteca carregada via CDN
                const { jsPDF } = window.jspdf;
                if (!jsPDF) {
                    throw new Error('jsPDF não está disponível');
                }
                
                console.log('jsPDF disponível:', !!jsPDF);
                
                // Criar nova instância do jsPDF
                const doc = new jsPDF();
                
                // Adicionar cabeçalho
                doc.setFontSize(18);
                doc.setTextColor(66, 0, 255);
                doc.text('Relatório de Usuários', 14, 22);
                
                // Adicionar data de geração
                const dataAtual = new Date().toLocaleDateString('pt-BR');
                doc.setFontSize(10);
                doc.setTextColor(100, 100, 100);
                doc.text(`Data de geração: ${dataAtual}`, 14, 30);
                
                // Preparar dados para a tabela
                const headers = [['ID', 'Nome', 'Email', 'Tipo']];
                const data = resultadosUsuarios.map(usuario => [
                    usuario.id_Usuario || '',
                    usuario.nome_Usuario || '',
                    usuario.email || '',
                    formatarTipoUsuario(usuario.tipo_Usuario) || ''
                ]);
                
                // Criar tabela no PDF
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
                const filename = `Relatorio_Usuarios_${dataAtual.replace(/\//g, '-')}.pdf`;
                console.log('Salvando PDF com nome:', filename);
                doc.save(filename);
                
                console.log('PDF gerado com sucesso!');
            } catch (error) {
                console.error('Erro ao gerar PDF:', error);
                mostrarErro('Erro ao gerar PDF: ' + error.message);
            } finally {
                // Restaurar o botão
                const btnGerarPDF = document.getElementById('btnGerarPDF');
                btnGerarPDF.innerHTML = '<i class="fas fa-file-pdf"></i> Gerar PDF';
                btnGerarPDF.disabled = false;
            }
        });
        
        // Remover a busca automática ao carregar a página
        /*window.addEventListener('DOMContentLoaded', function() {
            buscarUsuarios();
        });*/
    </script>
</body>
</html>