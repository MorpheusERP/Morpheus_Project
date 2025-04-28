<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Relatório de Produtos</title>
    <link rel="shortcut icon" href="{{ asset('images/logo.png') }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>

    @include('layouts.nav_bottom')
    @include('layouts.background')
    
    @vite(['resources/css/menu/relatorio/produtos-relatorio.css'])
</head>
<body>
    <div class="header">
        <h1>Relatório de Produtos</h1>
    </div>
    <div class="container">
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
                    <button id="btnGerarPDF" class="new" disabled>
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
        let resultadosProdutos = [];
        
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
        document.getElementById('codProduto').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                buscarProdutos();
            }
        });
        
        document.getElementById('nomeProduto').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                buscarProdutos();
            }
        });
        
        document.getElementById('grupo').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                buscarProdutos();
            }
        });
        
        document.getElementById('subgrupo').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                buscarProdutos();
            }
        });
        
        // Função para buscar produtos
        function buscarProdutos() {
            // Limpar mensagens de erro anteriores
            document.getElementById('mensagemErro').style.display = 'none';
            
            // Mostrar indicador de carregamento
            const searchButtons = document.querySelectorAll('.search-button');
            searchButtons.forEach(button => {
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                button.disabled = true;
            });
            
            // Obter os termos de busca
            const codProduto = document.getElementById('codProduto').value.trim();
            const nomeProduto = document.getElementById('nomeProduto').value.trim();
            const grupo = document.getElementById('grupo').value.trim();
            const subgrupo = document.getElementById('subgrupo').value.trim();
            
            if (!codProduto && !nomeProduto && !grupo && !subgrupo) {
                mostrarErro('Por favor, preencha pelo menos um campo de pesquisa');
                searchButtons.forEach(button => {
                    button.innerHTML = '<i class="fas fa-search"></i>';
                    button.disabled = false;
                });
                return;
            }
            
            // Obter o token CSRF
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            // Fazer a requisição para o servidor com os headers corretos
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
                console.log('Response data:', data); // Debug log
                
                if (data.status === 'success') {
                    resultadosProdutos = data.produtos || [];
                    exibirResultados(resultadosProdutos);
                    
                    // Habilitar/desabilitar botão de PDF
                    document.getElementById('btnGerarPDF').disabled = resultadosProdutos.length === 0;
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
                mostrarErro(error.message || 'Ocorreu um erro ao buscar produtos');
                
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
        function exibirResultados(produtos) {
            const corpoTabela = document.getElementById('corpoTabela');
            const resultadoContainer = document.getElementById('resultado-container');
            
            // Limpar tabela anterior
            corpoTabela.innerHTML = '';
            console.log('Exibindo resultados:', produtos, 'Quantidade:', produtos.length);
            
            if (!produtos || produtos.length === 0) {
                resultadoContainer.style.display = 'none';
                mostrarErro('Nenhum produto encontrado com os critérios informados');
                return;
            }
            
            // Adicionar cada produto na tabela
            produtos.forEach(produto => {
                const linha = document.createElement('tr');
                
                // Tratar possíveis valores nulos ou indefinidos
                const formatCurrency = (value) => {
                    if (value === null || value === undefined) return 'R$ 0,00';
                    return new Intl.NumberFormat('pt-BR', { 
                        style: 'currency', 
                        currency: 'BRL'
                    }).format(value);
                };
                
                linha.innerHTML = `
                    <td>${produto.cod_Produto || ''}</td>
                    <td>${produto.nome_Produto || ''}</td>
                    <td>${formatCurrency(produto.preco_Custo)}</td>
                    <td>${formatCurrency(produto.preco_Venda)}</td>
                    <td>${produto.grupo || ''}</td>
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
            if (resultadosProdutos.length === 0) {
                mostrarErro('Não há dados para gerar o relatório');
                return;
            }
            
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            
            // Adicionar cabeçalho
            doc.setFontSize(18);
            doc.setTextColor(66, 0, 255);
            doc.text('Relatório de Produtos', 14, 22);
            
            // Adicionar data de geração
            const dataAtual = new Date().toLocaleDateString('pt-BR');
            doc.setFontSize(10);
            doc.setTextColor(100, 100, 100);
            doc.text(`Data de geração: ${dataAtual}`, 14, 30);
            
            // Formatação para moeda brasileira
            const formatCurrency = (value) => {
                if (value === null || value === undefined) return 'R$ 0,00';
                return new Intl.NumberFormat('pt-BR', { 
                    style: 'currency', 
                    currency: 'BRL'
                }).format(value);
            };
            
            // Preparar dados para a tabela
            const headers = [['Código', 'Nome', 'Preço Custo', 'Preço Venda', 'Grupo', 'Subgrupo']];
            const data = resultadosProdutos.map(produto => [
                produto.cod_Produto || '',
                produto.nome_Produto || '',
                formatCurrency(produto.preco_Custo),
                formatCurrency(produto.preco_Venda),
                produto.grupo || '',
                produto.sub_Grupo || ''
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
            doc.save(`Relatorio_Produtos_${dataAtual.replace(/\//g, '-')}.pdf`);
        });
        
        // Carregar todos os produtos ao iniciar a página (opcional)
        // window.addEventListener('DOMContentLoaded', buscarProdutos);
    </script>
</body>
</html>