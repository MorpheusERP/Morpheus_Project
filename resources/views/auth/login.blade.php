<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>MorpheusERP - Login</title>
    <link rel="shortcut icon" href="Frontend/Imagens/logo.png" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/auth/login.css'])

    
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <meta name="theme-color" content="#4CAF50">
    <link rel="icon" href="{{ asset('icons/icon-192.png') }}" type="image/png">
</head>
<body>
    <!-- Decorative elements -->
    <div class="floating-shape shape1"></div>
    <div class="floating-shape shape2"></div>
    <div class="floating-shape shape3"></div>

    <div class="FundoLogin">
        <div class="Conteudo">
            <div class="perfil">
                <img src="{{ asset('images/Usuario.png') }}" alt="perfil">
            </div>
            
            @if (session('status'))
                <div style="background-color: rgba(40, 167, 69, 0.8); color: white; padding: 12px 20px; border-radius: 12px; margin-bottom: 15px; font-size: 14px; width: 100%; text-align: center; animation: slideIn 0.3s ease forwards; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);">
                    {{ session('status') }}
                </div>
            @endif
            
            <form id="loginForm" autocomplete="off">
                @csrf
                <div class="form-group">
                    <label class="label" for="login">Usuário</label>
                    <div class="input-with-icon">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" id="login" name="login" placeholder="Digite seu login" class="inputField" required autocomplete="off">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="label" for="senha">Senha</label>
                    <div class="input-container">
                        <div class="input-with-icon">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" maxlength="4" pattern="[0-9]*" inputmode="numeric" id="senha" name="senha" placeholder="Digite sua senha" class="inputField" required autocomplete="new-password">
                        </div>
                        <button type="button" class="toggle-visibility" id="togglePassword" aria-label="Mostrar/esconder senha">
                            <i class="fa fa-eye-slash"></i>
                        </button>
                    </div>
                </div>
                
                <div class="EsqueciSenha">
                    <a href="{{ route('auth.redefinir') }}" class="link">Esqueci minha senha</a>
                </div>

                <div class="botaoDiv">
                    <button type="submit" class="botao">Entrar <i class="fas fa-arrow-right"></i></button>
                </div>
            </form>
            
            <div id="mensagemErro"></div>
            
            <div class="Emporio">
                <img src="{{ asset('images/Emporio maxx s-fundo.png') }}" alt="Emporio">
            </div>
        </div>
    </div>

    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Cache de elementos DOM
            const togglePassword = document.getElementById('togglePassword');
            const senha = document.getElementById('senha');
            const form = document.getElementById('loginForm');
            const loadingOverlay = document.getElementById('loadingOverlay');
            
            // Toggle de visibilidade da senha
            togglePassword.addEventListener('click', function() {
                const type = senha.getAttribute('type') === 'password' ? 'text' : 'password';
                senha.setAttribute('type', type);
                
                // Alterna o ícone
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });
            
            // Ajuste para input numérico
            senha.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
            
            // Efeitos nos inputs
            const inputs = document.querySelectorAll('.inputField');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.closest('.input-with-icon, .input-container').classList.add('focused');
                });
                
                input.addEventListener('blur', function() {
                    if (!this.value) {
                        this.closest('.input-with-icon, .input-container').classList.remove('focused');
                    }
                });
                
                // Se o campo já tem valor
                if (input.value) {
                    input.closest('.input-with-icon, .input-container').classList.add('focused');
                }
            });
            
            // Formulário de login
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Valores dos campos
                const login = document.getElementById('login').value.trim();
                const password = document.getElementById('senha').value.trim();
                
                if (!login || !password) {
                    showError('Por favor, preencha todos os campos');
                    return;
                }
                
                // Mostrar carregamento
                loadingOverlay.classList.add('active');
                
                // CSRF token
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                
                // Enviar requisição AJAX - Usando FormData para manter consistência com o backend
                const formData = new FormData();
                formData.append('login', login);
                formData.append('senha', password);
                formData.append('_token', csrfToken);
                
                fetch('{{ route("login.post") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    loadingOverlay.classList.remove('active');
                    
                    if (data.status === 'success') {
                        // Redirecionar com base no tipo de usuário
                        window.location.href = '{{ route("home") }}';
                    } else {
                        // Usar a mensagem exata que vem do servidor
                        showError(data.message || 'Erro ao fazer login');
                    }
                })
                .catch(error => {
                    loadingOverlay.classList.remove('active');
                    console.error('Erro:', error);
                    showError('Erro ao conectar-se ao servidor');
                });
            });
            
            function showError(message) {
                const errorElement = document.getElementById('mensagemErro');
                errorElement.textContent = message;
                errorElement.style.display = 'block';
                
                // Animar entrada da mensagem
                errorElement.style.animation = 'none';
                errorElement.offsetHeight; // Força reflow
                errorElement.style.animation = 'slideIn 0.3s forwards';
                
                // Oculta a mensagem após 5 segundos
                setTimeout(() => {
                    errorElement.style.display = 'none';
                }, 5000);
            }
            
            // Adicionar efeito nos elementos flutuantes
            const shapes = document.querySelectorAll('.floating-shape');
            shapes.forEach(shape => {
                const randomX = Math.random() * 20 - 10;
                const randomY = Math.random() * 20 - 10;
                shape.style.transform = `translate(${randomX}px, ${randomY}px)`;
            });
        });
    </script>

     <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('{{ asset('sw.js') }}')
                .then(function(registration) {
                    console.log('Service Worker registrado com sucesso:', registration);
                })
                .catch(function(error) {
                    console.log('Falha ao registrar o Service Worker:', error);
                });
        }
    </script>
</body>
</html>