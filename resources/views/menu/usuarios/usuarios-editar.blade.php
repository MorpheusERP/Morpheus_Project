@extends('layouts.app')

@section('title', 'Editar Usuário')

@section('header-title', 'Editar Usuário')

@push('styles')
    @vite(['resources/css/menu/usuarios/usuarios.css'])
@endpush

@section('content')
    <div class="form">
        <div class="Conteudo">
            <form id="cadastroForm" autocomplete="off">
                <select id="nivel" required>
                    <option value="" disabled>* Nível de Usuário</option>
                    <option value="padrao" {{ $usuario->tipo_Usuario == 'padrao' ? 'selected' : '' }}>Padrão</option>
                    <option value="admin" {{ $usuario->tipo_Usuario == 'admin' ? 'selected' : '' }}>Administrador</option>
                </select>
                <input type="text" id="nome" class="input-field" value="{{ $usuario->nome_Usuario }}" maxlength="20" placeholder="* Nome" required autocomplete="off">
                <input type="email" id="email" class="input-field" value="{{ $usuario->email }}" placeholder="* Email" required autocomplete="off">
                <div class="input-containe" id="show-password">
                    <input type="password" id="senha" class="input-field" maxlength="4" pattern="[0-9]*" inputmode="numeric" placeholder="Nova senha (deixe em branco para manter a atual)">
                    <button type="button" class="toggle-visibility" onclick="togglePasswordVisibility('senha')">
                        <i class="fa fa-eye-slash"></i>
                    </button>
                </div>
                <div class="buttons-edit">
                    <button type="button" class="back" onclick="window.location.href='{{ route('menu.usuarios.usuarios-buscar') }}'">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </button>
                    <button type="submit" class="edit">
                        <i class="fas fa-save"></i> Salvar
                    </button>
                </div>
            </form>
            <div id="mensagemSucesso" style="display: none;"></div>
            <div id="mensagemErro" style="display: none;"></div>
        </div>
    </div>
@endsection
@section('footer')
    <div class="BotoesFooter">
        <div class="buttons-search">
            <a href="{{ route('home') }}">
                <button class="search">
                    <i class="fas fa-home"></i> Voltar para Home
                </button>
            </a>
        </div>
    </div>
@endsection
@push('scripts')
<script>
    function togglePasswordVisibility(id) {
        const passwordField = document.getElementById(id);
        const icon = document.querySelector('.toggle-visibility i');
        
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
    
    // Ajuste para input numérico na senha
    document.getElementById('senha').addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });
    
    // Script para enviar os dados para o servidor e exibir a resposta
    document.getElementById('cadastroForm').addEventListener('submit', function(event) {
        event.preventDefault(); // Impede o envio padrão do formulário
        
        // Pega os valores dos campos do formulário
        const nivel = document.getElementById('nivel').value.trim();
        const nome = document.getElementById('nome').value.trim();
        const email = document.getElementById('email').value.trim();
        const senha = document.getElementById('senha').value.trim();
        
        // Validações
        if (!nivel || !nome || !email) {
            mostrarErro('Por favor, preencha todos os campos obrigatórios (*)');
            return;
        }
        
        if (senha !== '' && senha.length !== 4) {
            mostrarErro('A senha deve ter exatamente 4 dígitos');
            return;
        }
        
        if (senha !== '' && !/^\d{4}$/.test(senha)) {
            mostrarErro('A senha deve conter apenas números');
            return;
        }

        // Exibir indicador de carregamento
        const btnSalvar = document.querySelector('.edit[type="submit"]');
        const originalBtnText = btnSalvar.innerHTML;
        btnSalvar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';
        btnSalvar.disabled = true;
        
        // Obtém o token CSRF
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // Cria o FormData para enviar
        const formData = new FormData();
        formData.append('id', '{{ $usuario->id_Usuario }}');
        formData.append('nivel', nivel);
        formData.append('nome', nome);
        formData.append('email', email);
        if (senha) {
            formData.append('senha', senha);
        }
        
        // Envia os dados para o Laravel usando fetch com FormData
        fetch('{{ route("menu.usuarios.usuarios-update") }}', {
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
                // Espera 2 segundos antes de redirecionar
                setTimeout(() => {
                    window.location.href = '{{ route("menu.usuarios.usuarios-buscar") }}';
                }, 2000);
            } else {
                mostrarErro(data.mensagem || 'Ocorreu um erro ao processar a solicitação');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            mostrarErro(error.message || 'Ocorreu um erro ao processar a solicitação');
        })
        .finally(() => {
            // Restaurar o botão
            btnSalvar.innerHTML = originalBtnText;
            btnSalvar.disabled = false;
        });
    });
</script>
@endpush