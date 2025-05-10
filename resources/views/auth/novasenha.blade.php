<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>MorpheusERP - Nova Senha</title>
    <link rel="shortcut icon" href="Frontend/Imagens/logo.png" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    @vite(['resources/css/auth/login.css'])
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
            
            <h2 style="text-align: center; margin-bottom: 20px;">Definir Nova Senha</h2>
            
            @if (session('status'))
                <div style="background-color: rgba(40, 167, 69, 0.8); color: white; padding: 12px 20px; border-radius: 12px; margin-bottom: 15px; font-size: 14px; width: 100%; text-align: center; animation: slideIn 0.3s ease forwards; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);">
                    {{ session('status') }}
                </div>
            @endif
            
            @if (session('error'))
                <div style="background-color: rgba(220, 53, 69, 0.8); color: white; padding: 12px 20px; border-radius: 12px; margin-bottom: 15px; font-size: 14px; width: 100%; text-align: center; animation: slideIn 0.3s ease forwards; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);">
                    {{ session('error') }}
                </div>
            @endif
            
            <form id="resetForm" method="POST" action="{{ route('auth.novasenha.post') }}">
                @csrf
                <div class="form-group">
                    <label class="label" for="login">Nome do Usuário</label>
                    <div class="input-with-icon">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" id="login" name="login" placeholder="Digite o nome de usuário" class="inputField" autocomplete="off" required>
                    </div>
                    @error('login')
                        <span style="color: #FFEF0D; display: block; margin-top: 5px; font-size: 14px;">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label class="label" for="senha">Nova Senha</label>
                    <div class="input-container">
                        <div class="input-with-icon">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" maxlength="4" pattern="[0-9]*" inputmode="numeric" id="senha" name="senha" placeholder="Digite a nova senha" class="inputField" required>
                        </div>
                        <button type="button" class="toggle-visibility" id="togglePassword" aria-label="Mostrar/esconder senha">
                            <i class="fa fa-eye-slash"></i>
                        </button>
                    </div>
                    @error('senha')
                        <span style="color: #FFEF0D; display: block; margin-top: 5px; font-size: 14px;">{{ $message }}</span>
                    @enderror
                </div>
                
                <div style="display: flex; gap: 10px;">
                    <div class="botaoDiv" style="flex: 1;">
                        <a href="{{ route('auth.redefinir') }}" style="text-decoration: none; color: inherit; display: block;">
                            <button type="button" class="botao">Voltar</button>
                        </a>
                    </div>
                    
                    <div class="botaoDiv" style="flex: 1;">
                        <button type="submit" class="botao">Confirmar <i class="fas fa-check"></i></button>
                    </div>
                </div>
            </form>
            
            <div id="mensagemErro"></div>
            
            <div class="Emporio">
                <img src="{{ asset('images/Emporio maxx s-fundo.png') }}" alt="Emporio">
            </div>
        </div>
    </div>

    <!-- Overlay de carregamento -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Cache de elementos DOM
            const form = document.getElementById('resetForm');
            const loadingOverlay = document.getElementById('loadingOverlay');
            const togglePassword = document.getElementById('togglePassword');
            const senha = document.getElementById('senha');
            
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
                
                // Se o campo já tem valor (por exemplo, depois de um erro de validação)
                if (input.value) {
                    input.closest('.input-with-icon, .input-container').classList.add('focused');
                }
            });
            
            // Formulário de redefinição
            form.addEventListener('submit', function() {
                // Mostrar carregamento
                loadingOverlay.classList.add('active');
            });
            
            // Adicionar efeito nos elementos flutuantes
            const shapes = document.querySelectorAll('.floating-shape');
            shapes.forEach(shape => {
                const randomX = Math.random() * 20 - 10;
                const randomY = Math.random() * 20 - 10;
                shape.style.transform = `translate(${randomX}px, ${randomY}px)`;
            });
        });
    </script>
</body>
</html>