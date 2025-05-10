<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MorpheusERP - Meu Perfil</title>
    <link rel="shortcut icon" href="{{ asset('images/logo.png') }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @include('layouts.nav_bottom')
    @include('layouts.background')
    @vite(['resources/css/home/perfil.css'])
</head>
<body>
    <div class="header">
        <h1>Meu Perfil</h1>
    </div>

    <div class="container">
        <button class="edit-button" onclick="alterar()">
            <i class="fas fa-pencil-alt"></i> Alterar
        </button>

        <div class="avatar-container">
            <div class="avatar">
                <img src="{{ asset('images/Usuario.png') }}" alt="Perfil">
            </div>
        </div>

        <div class="form-grid">
            <div class="input-group">
                <label for="nome" class="label">Nome:</label>
                <input type="text" id="nome" class="input-field" value="{{ $user->nome_Usuario ?? '' }}" disabled>
            </div>

            <div class="input-group">
                <label for="sobrenome" class="label">Sobrenome:</label>
                <input type="text" id="sobrenome" class="input-field" value="{{ $user->sobrenome ?? '' }}" disabled>
            </div>

            <div class="input-group">
                <label for="funcao" class="label">Função:</label>
                <input type="text" id="funcao" class="input-field" value="{{ $user->funcao ?? '' }}" disabled>
            </div>

            <div class="input-group">
                <label for="login" class="label">Login (Email):</label>
                <input type="email" id="login" class="input-field" value="{{ $user->email ?? '' }}" disabled>
            </div>

            <div class="input-group">
                <label for="senha" class="label">Senha:</label>
                <div class="password-container">
                    <input type="password" id="senha" class="input-field" maxlength="60" pattern="[0-9]*" inputmode="numeric" value="" disabled>
                    <button type="button" class="toggle-visibility" onclick="togglePasswordVisibility('senha')">
                        <i class="fa fa-eye-slash"></i>
                    </button>
                </div>
            </div>
        </div>

        <div id="mensagemSucesso" class="message message-success"></div>
        <div id="mensagemErro" class="message message-error"></div>
    </div>

    <footer>
        <div class="footer-container">
            <div class="footer-actions">
                <button class="btn btn-secondary" id="voltar" onclick="window.location.href='{{ route('home') }}'">
                    <i class="fas fa-arrow-left"></i> Voltar
                </button>
                
                <button class="btn btn-primary" id="save" style="display: none;" onclick="updateUsuario()">
                    <i class="fas fa-save"></i> Salvar
                </button>
            </div>
        </div>
    </footer>
    
    <div class="logo">
        <img src="{{ asset('images/Emporio maxx s-fundo.png') }}" alt="Empório Maxx Logo">
    </div>

    <script>
        function togglePasswordVisibility(id) {
            var passwordField = document.getElementById(id);
            var icon = document.querySelector('.toggle-visibility i');
            
            if (passwordField.type === "password") {
                passwordField.type = "text";
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                passwordField.type = "password";
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        }

        function alterar() {
            document.getElementById('nome').disabled = false;
            document.getElementById('sobrenome').disabled = false;
            document.getElementById('login').disabled = false;
            document.getElementById('senha').disabled = false;
            document.getElementById('save').style.display = 'flex';
        }

        async function updateUsuario() {
            // Get the CSRF token
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            // Obtém os valores dos campos do formulário
            const nome = document.getElementById('nome').value.trim();
            const sobrenome = document.getElementById('sobrenome').value.trim();
            const login = document.getElementById('login').value.trim();
            const senha = document.getElementById('senha').value.trim();
            
            // Validações básicas
            if (!nome) {
                mostrarErro('O nome é obrigatório');
                return;
            }
            
            if (!login) {
                mostrarErro('O login (email) é obrigatório');
                return;
            }
            
            // Validar formato de email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(login)) {
                mostrarErro('Por favor, insira um email válido');
                return;
            }
            
            // Exibir indicador de carregamento
            const btnSalvar = document.getElementById('save');
            const originalBtnText = btnSalvar.innerHTML;
            btnSalvar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';
            btnSalvar.disabled = true;
        
            try {
                // Envia uma requisição fetch para atualizar o perfil
                const response = await fetch('/atualizar-perfil', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify({ 
                        nome: nome, 
                        sobrenome: sobrenome, 
                        login: login, 
                        senha: senha 
                    })
                });
        
                const data = await response.json();
        
                if (data.status === 'success') {
                    mostrarSucesso(data.mensagem || 'Perfil atualizado com sucesso!');
                    // Desabilitar campos após salvamento
                    document.getElementById('nome').disabled = true;
                    document.getElementById('sobrenome').disabled = true;
                    document.getElementById('login').disabled = true;
                    document.getElementById('senha').disabled = true;
                    document.getElementById('save').style.display = 'none';
                } else {
                    mostrarErro(data.mensagem || 'Erro ao atualizar perfil');
                }
            } catch (error) {
                console.error("Erro de comunicação com o servidor:", error);
                mostrarErro("Erro de comunicação com o servidor");
            } finally {
                // Restaurar o botão
                btnSalvar.innerHTML = originalBtnText;
                btnSalvar.disabled = false;
            }
        }

        function mostrarSucesso(mensagem) {
            const mensagemSucesso = document.getElementById('mensagemSucesso');
            mensagemSucesso.textContent = mensagem;
            mensagemSucesso.style.display = 'block';
            document.getElementById('mensagemErro').style.display = 'none';
            
            setTimeout(() => {
                mensagemSucesso.style.display = 'none';
            }, 4000);
        }
        
        function mostrarErro(mensagem) {
            const mensagemErro = document.getElementById('mensagemErro');
            mensagemErro.textContent = mensagem;
            mensagemErro.style.display = 'block';
            document.getElementById('mensagemSucesso').style.display = 'none';
            
            setTimeout(() => {
                mensagemErro.style.display = 'none';
            }, 4000);
        }
    </script>
</body>
</html>