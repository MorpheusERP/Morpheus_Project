@extends('layouts.app')

@section('title', 'Cadastro de Usuários')

@section('header-title', 'Cadastro de Usuários')

@push('styles')
    @vite(['resources/css/menu/usuarios/usuarios.css'])
@endpush

@section('content')
    <div class="form">
        <div class="buttons" id="default-buttons">
            <button class="new" onclick="novo()">
                <i class="fas fa-plus-circle"></i> Novo
            </button>
            <button class="search" onclick="window.location.href='{{ route('menu.usuarios.usuarios-buscar') }}'">
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
                <select id="nivel" required disabled>
                    <option value="" disabled selected>* Nível de Usuário</option>
                    <option value="padrao">Padrão</option>
                    <option value="admin">Administrador</option>
                </select>
                
                <input type="text" id="nome" class="input-field" maxlength="20" placeholder="* Nome" required disabled autocomplete="off">
                <input type="text" id="sobrenome" class="input-field" maxlength="20" placeholder="Sobrenome" disabled autocomplete="off">
                <input type="text" id="funcao" class="input-field" maxlength="20" placeholder="* Função" required disabled autocomplete="off">
                <input type="text" id="login" class="input-field" placeholder="* Login" required disabled autocomplete="off">
                
                <div class="input-containe" id="show-password" style="display: none;">
                    <input type="password" id="senha" class="input-field" maxlength="4" pattern="[0-9]*" inputmode="numeric" placeholder="* Senha (4 dígitos)" required disabled>
                    <button type="button" class="toggle-visibility" onclick="togglePasswordVisibility('senha')">
                        <i class="fa fa-eye-slash"></i>
                    </button>
                </div>

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
    function novo() {
        document.getElementById('default-buttons').style.display = 'none';
        document.getElementById('new-button').style.display = 'flex';
        document.getElementById('edit-buttons').style.display = 'flex';
        document.getElementById('show-password').style.display = 'flex';
        habilitarCampos();
        
        // Garantir que o select de nível tenha uma aparência consistente quando estiver ativo
        document.getElementById('nivel').style.opacity = '1';
        document.getElementById('nivel').style.color = 'var(--input-text)';
    }

    function voltar() { 
        document.getElementById('new-button').style.display = 'none';
        document.getElementById('default-buttons').style.display = 'flex';
        document.getElementById('edit-buttons').style.display = 'none';
        document.getElementById('show-password').style.display = 'none';
        desabilitarCampos();
        
        // Limpar formulário
        document.getElementById('cadastroForm').reset();
        
        // Esconder mensagens
        document.getElementById('mensagemSucesso').style.display = 'none';
        document.getElementById('mensagemErro').style.display = 'none';
    }
    
    function habilitarCampos() {
        document.getElementById('nivel').disabled = false;
        document.getElementById('nome').disabled = false;
        document.getElementById('sobrenome').disabled = false;
        document.getElementById('funcao').disabled = false;
        document.getElementById('login').disabled = false;
        document.getElementById('senha').disabled = false;
    }

    function desabilitarCampos() {
        document.getElementById('nivel').disabled = true;
        document.getElementById('nome').disabled = true;
        document.getElementById('sobrenome').disabled = true;
        document.getElementById('funcao').disabled = true;
        document.getElementById('login').disabled = true;
        document.getElementById('senha').disabled = true;
    }
    
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
        const sobrenome = document.getElementById('sobrenome').value.trim();
        const funcao = document.getElementById('funcao').value.trim();
        const login = document.getElementById('login').value.trim();
        const senha = document.getElementById('senha').value.trim();
        
        // Validações
        if (!nivel || !nome || !funcao || !login || !senha) {
            mostrarErro('Por favor, preencha todos os campos obrigatórios (*)');
            return;
        }
        
        if (senha.length !== 4) {
            mostrarErro('A senha deve ter exatamente 4 dígitos');
            return;
        }
        
        if (!/^\d{4}$/.test(senha)) {
            mostrarErro('A senha deve conter apenas números');
            return;
        }

        // Exibir indicador de carregamento
        const btnSalvar = document.querySelector('.edit');
        const originalBtnText = btnSalvar.innerHTML;
        btnSalvar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';
        btnSalvar.disabled = true;
        
        // Obtém o token CSRF
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // Cria o FormData para enviar
        const formData = new FormData();
        formData.append('nivel', nivel);
        formData.append('nome', nome);
        formData.append('sobrenome', sobrenome);
        formData.append('funcao', funcao);
        formData.append('login', login);
        formData.append('senha', senha);
        
        // Envia os dados para o Laravel usando fetch com FormData
        fetch('{{ route("menu.usuarios.store") }}', {
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
                // Limpar formulário após sucesso
                document.getElementById('cadastroForm').reset();
                setTimeout(() => {
                    voltar();
                }, 3000);
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