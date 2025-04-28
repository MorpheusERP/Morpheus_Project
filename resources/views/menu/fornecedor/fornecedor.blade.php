<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MorpheusERP - Cadastro de Fornecedor</title>
    <link rel="shortcut icon" href="{{ asset('images/logo.png') }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @include('layouts.nav_bottom')
    @include('layouts.background')
    
    @vite(['resources/css/menu/fornecedor/fornecedor.css'])

    <script>
        //Função para verificar se o usuário está logado
        function verificarLogin() {
            fetch('../../../Backend/verificalogin.php')
                .then(response => response.json())
                .then(data => {
                    if (!data.logado) {
                        // Redireciona para a página de login se não estiver logado
                        window.location.href = '../../../index.html';
                    }
                })
                .catch(error => {
                    console.error("Erro ao verificar autenticação:", error);
                });
        }

        // Executa a função quando a página é carregada
        document.addEventListener("DOMContentLoaded", verificarLogin);
    </script>
</head>
<body>
    <div class="header">
        <h1>Cadastro de Fornecedor</h1>
    </div>
    
    <div class="container">
        <div class="form">
            <div class="buttons" id="default-buttons">
                <button class="new" onclick="novo()">
                    <i class="fas fa-plus-circle"></i> Novo
                </button>
                <button class="search" onclick="window.location.href='{{ route('menu.fornecedor.fornecedor-buscar') }}'">
                    <i class="fas fa-search"></i> Buscar
                </button>
            </div>
            
            <div class="Conteudo">
                <div class="buttons" id="new-button" style="display: none;">
                    <button class="back" onclick="voltar(); recarregarPagina()">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </button>
                </div>
                
                <form id="cadastroForm" autocomplete="off">
                    <input type="text" id="razao" class="input-field" maxlength="150" placeholder="* Razão Social:" required disabled>
                    <input type="text" id="fantasia" class="input-field" maxlength="50" placeholder="Nome Fantasia" disabled>
                    <input type="text" id="apelido" class="input-field" maxlength="50" placeholder="Apelido" disabled>
                    <input type="text" id="grupo" class="input-field" maxlength="50" placeholder="* Grupo" required disabled>
                    <input type="text" id="subgrupo" class="input-field" maxlength="50" placeholder="Sub.Grupo" disabled>
                    <input type="text" id="observacao" class="input-field" maxlength="150" placeholder="Observações" disabled>

                    <div class="buttons-edit" id="edit-buttons" style="display: none;">
                        <button class="edit" type="submit">
                            <i class="fas fa-save"></i> Salvar
                        </button>
                    </div>
                </form>
                
                <div id="mensagemSucesso" style="display: none;"></div>
                <div id="mensagemErro" style="display: none;"></div>
            </div>
        </div>
    </div>

    <footer>
        <div class="BotoesFooter">
            <div class="buttons-search">
                <a href="{{ route('home') }}">
                   <button class="search">
                        <i class="fas fa-home"></i> Voltar para Home
                   </button>
                </a>
            </div>
        </div>
    </footer>
    
    <div class="logo">
        <img src="{{ asset('images/Emporio maxx s-fundo.png') }}" alt="Empório Maxx Logo">
    </div>

    <!-- Scripts -->
    <script> 
        function novo() {
            document.getElementById('default-buttons').style.display = 'none';
            document.getElementById('new-button').style.display = 'flex';
            document.getElementById('edit-buttons').style.display = 'flex';
            habilitarCampos();
        }

        function voltar() { 
            document.getElementById('new-button').style.display = 'none';
            document.getElementById('default-buttons').style.display = 'flex';
            document.getElementById('edit-buttons').style.display = 'none';
            desabilitarCampos();
            
            // Limpar formulário
            document.getElementById('cadastroForm').reset();
            
            // Esconder mensagens
            document.getElementById('mensagemSucesso').style.display = 'none';
            document.getElementById('mensagemErro').style.display = 'none';
        }
        
        function habilitarCampos() {
            document.getElementById('razao').disabled = false;
            document.getElementById('fantasia').disabled = false;
            document.getElementById('apelido').disabled = false;
            document.getElementById('grupo').disabled = false;
            document.getElementById('subgrupo').disabled = false;
            document.getElementById('observacao').disabled = false;
        }

        function desabilitarCampos() {
            document.getElementById('razao').disabled = true;
            document.getElementById('fantasia').disabled = true;
            document.getElementById('apelido').disabled = true;
            document.getElementById('grupo').disabled = true;
            document.getElementById('subgrupo').disabled = true;
            document.getElementById('observacao').disabled = true;
        }
        
        function recarregarPagina() {
            location.reload();
        }
        
        function mostrarSucesso(mensagem) {
            const mensagemSucesso = document.getElementById('mensagemSucesso');
            mensagemSucesso.innerText = mensagem;
            mensagemSucesso.style.display = 'block';
            mensagemSucesso.style.backgroundColor = 'rgba(40, 167, 69, 0.8)';
            mensagemSucesso.style.color = 'white';
            
            // Esconder mensagem de erro se estiver visível
            document.getElementById('mensagemErro').style.display = 'none';
        }
        
        function mostrarErro(mensagem) {
            const mensagemErro = document.getElementById('mensagemErro');
            mensagemErro.innerText = mensagem;
            mensagemErro.style.display = 'block';
            mensagemErro.style.backgroundColor = 'rgba(220, 53, 69, 0.8)';
            mensagemErro.style.color = 'white';
            
            // Esconder mensagem de sucesso se estiver visível
            document.getElementById('mensagemSucesso').style.display = 'none';
        }
        
        // Script para enviar os dados para o servidor e exibir a resposta
        document.getElementById('cadastroForm').addEventListener('submit', function(event) {
            event.preventDefault(); // Impede o envio padrão do formulário

            // Pega os valores dos campos do formulário
            const razao = document.getElementById('razao').value.trim();
            const fantasia = document.getElementById('fantasia').value.trim();
            const apelido = document.getElementById('apelido').value.trim();
            const grupo = document.getElementById('grupo').value.trim();
            const subgrupo = document.getElementById('subgrupo').value.trim();
            const observacao = document.getElementById('observacao').value.trim();

            // Verifica se todos os campos obrigatórios estão preenchidos
            if (!razao || !grupo) {
                mostrarErro('Por favor, preencha todos os campos com * antes de adicionar um fornecedor.');
                return;
            }
    
            // Exibir indicador de carregamento
            const btnSalvar = document.querySelector('.edit');
            const originalBtnText = btnSalvar.innerHTML;
            btnSalvar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';
            btnSalvar.disabled = true;
            
            // Obtém o token CSRF
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            // Cria um objeto com os dados do Fornecedor
            const formData = new FormData();
            formData.append('razao_Social', razao);
            formData.append('nome_Fantasia', fantasia);
            formData.append('apelido', apelido);
            formData.append('grupo', grupo);
            formData.append('sub_Grupo', subgrupo);
            formData.append('observacao', observacao);
    
            // Envia os dados para o Laravel
            fetch('{{ route("menu.fornecedor.store") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.mensagem || 'Erro ao processar requisição');
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'sucesso') {
                    mostrarSucesso(data.mensagem);
                    setTimeout(() => {
                        voltar();
                    }, 3000); // Delay de 3 segundos
                } else if (data.status === 'erro') {
                    mostrarErro(data.mensagem);
                }
            })
            .catch(error => {
                console.error("Erro:", error);
                mostrarErro("Ocorreu um erro ao processar a solicitação");
            })
            .finally(() => {
                // Restaurar o botão
                btnSalvar.innerHTML = originalBtnText;
                btnSalvar.disabled = false;
            });
        });
    </script>
</body>
</html>