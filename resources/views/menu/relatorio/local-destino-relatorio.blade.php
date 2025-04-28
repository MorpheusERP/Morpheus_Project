<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Relatório de Locais de Destino</title>
    <link rel="shortcut icon" href="{{ asset('images/logo.png') }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>

    @include('layouts.nav_bottom')
    @include('layouts.background')
    
    @vite(['resources/css/menu/relatorio/local-destino-relatorio.css'])
</head>
<body>
    <div class="header">
        <h1>Relatório de Locais de Destino</h1>
    </div>
    <div class="container">
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
        let resultadosLocais = [];
        
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
        
        // Adicionar evento para submissão do formulário quando pressionar Enter nos campos
        document.getElementById('nomeLocal').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                buscarLocais();
            }
        });
        
        document.getElementById('tipoLocal').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                buscarLocais();
            }
        });
        
        // Função para buscar locais
        function buscarLocais() {
            // Limpar mensagens de erro anteriores
            document.getElementById('mensagemErro').style.display = 'none';
            
            // Mostrar indicador de carregamento
            const searchButtons = document.querySelectorAll('.search-button');
            searchButtons.forEach(button => {
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                button.disabled = true;
            });
            
            // Obter os termos de busca
            const nomeLocal = document.getElementById('nomeLocal').value.trim();
            const tipoLocal = document.getElementById('tipoLocal').value.trim();
            
            if (!nomeLocal && !tipoLocal) {
                mostrarErro('Por favor, preencha pelo menos um campo de pesquisa');
                searchButtons.forEach(button => {
                    button.innerHTML = '<i class="fas fa-search"></i>';
                    button.disabled = false;
                });
                return;
            }
            
            // Obter o token CSRF
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            // Fazer a requisição para o servidor
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
                console.log('Response data:', data); // Debug log
                
                if (data.status === 'success') {
                    resultadosLocais = data.locais || [];
                    exibirResultados(resultadosLocais);
                    
                    // Habilitar/desabilitar botão de PDF
                    document.getElementById('btnGerarPDF').disabled = resultadosLocais.length === 0;
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
                mostrarErro(error.message || 'Ocorreu um erro ao buscar locais');
                
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
        function exibirResultados(locais) {
            const corpoTabela = document.getElementById('corpoTabela');
            const resultadoContainer = document.getElementById('resultado-container');
            
            // Limpar tabela anterior
            corpoTabela.innerHTML = '';
            console.log('Exibindo resultados:', locais, 'Quantidade:', locais.length);
            
            if (!locais || locais.length === 0) {
                resultadoContainer.style.display = 'none';
                mostrarErro('Nenhum local encontrado com os critérios informados');
                return;
            }
            
            // Adicionar cada local na tabela
            locais.forEach(local => {
                const linha = document.createElement('tr');
                
                // Preservando o texto completo como data-content para os campos que podem transbordar
                const nomeLocal = local.nome_Local || '';
                const tipoLocal = local.tipo_Local || '';
                const observacao = local.observacao || '';
                
                linha.innerHTML = `
                    <td>${local.id_Local || ''}</td>
                    <td ${nomeLocal.length > 20 ? `data-content="${nomeLocal}"` : ''}>${nomeLocal}</td>
                    <td ${tipoLocal.length > 15 ? `data-content="${tipoLocal}"` : ''}>${tipoLocal}</td>
                    <td ${observacao.length > 30 ? `data-content="${observacao}"` : ''}>${observacao}</td>
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
        
        // Função para gerar PDF com os resultados
        document.getElementById('btnGerarPDF').addEventListener('click', function() {
            if (resultadosLocais.length === 0) {
                mostrarErro('Não há dados para gerar o relatório');
                return;
            }
            
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            
            // Adicionar cabeçalho
            doc.setFontSize(18);
            doc.setTextColor(66, 0, 255);
            doc.text('Relatório de Locais de Destino', 14, 22);
            
            // Adicionar data de geração
            const dataAtual = new Date().toLocaleDateString('pt-BR');
            doc.setFontSize(10);
            doc.setTextColor(100, 100, 100);
            doc.text(`Data de geração: ${dataAtual}`, 14, 30);
            
            // Preparar dados para a tabela
            const headers = [['ID', 'Nome do Local', 'Tipo do Local', 'Observação']];
            const data = resultadosLocais.map(local => [
                local.id_Local || '',
                local.nome_Local || '',
                local.tipo_Local || '',
                local.observacao || ''
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
            doc.save(`Relatorio_Locais_Destino_${dataAtual.replace(/\//g, '-')}.pdf`);
        });
    </script>
</body>
</html>