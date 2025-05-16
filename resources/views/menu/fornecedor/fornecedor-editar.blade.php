@extends('layouts.app')

@section('title', 'Alterar Fornecedor')

@section('header-title', 'Alterar Fornecedor')

@push('styles')
    @vite(['resources/css/menu/fornecedor/fornecedor.css'])
@endpush

@section('content')
    <div class="form">
        <div class="Conteudo">
            <div class="buttons" id="new-button">
                <button class="back" onclick="window.location.href='{{ route('menu.fornecedor.fornecedor-buscar') }}'">
                    <i class="fas fa-arrow-left"></i> Voltar
                </button>
            </div>
            <form id="cadastroForm" autocomplete="off">
                <input type="text" id="razao" class="input-field" maxlength="150" placeholder="* Razão Social:" required>
                <input type="text" id="fantasia" class="input-field" maxlength="50" placeholder="Nome Fantasia">
                <input type="text" id="apelido" class="input-field" maxlength="50" placeholder="Apelido">
                <input type="text" id="grupo" class="input-field" maxlength="50" placeholder="* Grupo" required>
                <input type="text" id="subgrupo" class="input-field" maxlength="50" placeholder="Sub.Grupo">
                <input type="text" id="observacao" class="input-field" maxlength="150" placeholder="Observações">
                <div class="buttons-edit" id="edit-buttons">
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
    // Obtem o ID do fornecedor da URL
    const params = new URLSearchParams(window.location.search);
    const id_Fornecedor = params.get('id_Fornecedor');

    // Verifica se o ID foi fornecido
    if (!id_Fornecedor) {
        mostrarErro('ID do fornecedor não encontrado');
        document.getElementById('cadastroForm').style.display = 'none';
        document.getElementById('edit-buttons').style.display = 'none';
    } else {
        // Preenche o formulário com os dados do fornecedor
        carregarFornecedor(id_Fornecedor);
    }

    // Função para carregar os dados do fornecedor
    function carregarFornecedor(id) {
        // Mostrar indicador de carregamento
        document.getElementById('razao').disabled = true;
        document.getElementById('razao').placeholder = "Carregando...";
        document.getElementById('edit-buttons').style.display = 'none';

        // Obtém o token CSRF
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Cria o FormData
        const formData = new FormData();
        formData.append('id_Fornecedor', id);

        // Envia a requisição para buscar os dados do fornecedor pelo ID
        fetch('{{ route("menu.fornecedor.find") }}', {
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
        .then(fornecedor => {
            if (fornecedor.status === 'erro' || !fornecedor.id_Fornecedor) {
                mostrarErro(fornecedor.mensagem || 'Fornecedor não encontrado');
                return;
            }

            document.getElementById('razao').value = fornecedor.razao_Social || '';
            document.getElementById('fantasia').value = fornecedor.nome_Fantasia || '';
            document.getElementById('apelido').value = fornecedor.apelido || '';
            document.getElementById('grupo').value = fornecedor.grupo || '';
            document.getElementById('subgrupo').value = fornecedor.sub_Grupo || '';
            document.getElementById('observacao').value = fornecedor.observacao || '';

            // Habilitar campos e botão após carregamento
            document.getElementById('razao').disabled = false;
            document.getElementById('razao').placeholder = "* Razão Social:";
            document.getElementById('edit-buttons').style.display = 'flex';
        })
        .catch(error => {
            console.error('Erro ao carregar dados do Fornecedor:', error);
            mostrarErro("Erro ao carregar dados do fornecedor");
        });
    }

    // Função para mostrar mensagem de sucesso
    function mostrarSucesso(mensagem) {
        const mensagemSucesso = document.getElementById('mensagemSucesso');
        mensagemSucesso.innerText = mensagem;
        mensagemSucesso.style.display = 'block';
        mensagemSucesso.style.backgroundColor = 'rgba(40, 167, 69, 0.8)';
        mensagemSucesso.style.color = 'white';
        
        // Esconder mensagem de erro se estiver visível
        document.getElementById('mensagemErro').style.display = 'none';
        
        // Esconder após alguns segundos
        setTimeout(() => {
            mensagemSucesso.style.display = 'none';
        }, 4000);
    }
    
    // Função para mostrar mensagem de erro
    function mostrarErro(mensagem) {
        const mensagemErro = document.getElementById('mensagemErro');
        mensagemErro.innerText = mensagem;
        mensagemErro.style.display = 'block';
        mensagemErro.style.backgroundColor = 'rgba(220, 53, 69, 0.8)';
        mensagemErro.style.color = 'white';
        
        // Esconder mensagem de sucesso se estiver visível
        document.getElementById('mensagemSucesso').style.display = 'none';
        
        // Esconder após alguns segundos
        setTimeout(() => {
            mensagemErro.style.display = 'none';
        }, 4000);
    }

    // Evento para enviar as alterações
    document.getElementById('cadastroForm').addEventListener('submit', function(event) {
        event.preventDefault(); // Impede o envio padrão do formulário

        // Pega os valores dos campos
        const razao_Social = document.getElementById('razao').value.trim();
        const nome_Fantasia = document.getElementById('fantasia').value.trim();
        const apelido = document.getElementById('apelido').value.trim();
        const grupo = document.getElementById('grupo').value.trim();
        const sub_Grupo = document.getElementById('subgrupo').value.trim();
        const observacao = document.getElementById('observacao').value.trim();

        // Verifica se os campos obrigatórios estão preenchidos
        if (!razao_Social || !grupo) {
            mostrarErro('Os campos com * são obrigatórios');
            return;
        }

        // Exibir indicador de carregamento
        const btnSalvar = document.querySelector('.edit');
        const originalBtnText = btnSalvar.innerHTML;
        btnSalvar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';
        btnSalvar.disabled = true;

        // Obtém o token CSRF
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Cria o FormData
        const formData = new FormData();
        formData.append('id_Fornecedor', id_Fornecedor);
        formData.append('razao_Social', razao_Social);
        formData.append('nome_Fantasia', nome_Fantasia);
        formData.append('apelido', apelido);
        formData.append('grupo', grupo);
        formData.append('sub_Grupo', sub_Grupo);
        formData.append('observacao', observacao);

        // Envia os dados para o Laravel
        fetch('{{ route("menu.fornecedor.fornecedor-update") }}', {
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
                    window.location.href = '{{ route("menu.fornecedor.fornecedor-buscar") }}';
                }, 2000); // Delay de 2 segundos
            } else {
                mostrarErro(data.mensagem || 'Erro ao atualizar o fornecedor');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            mostrarErro('Ocorreu um erro ao processar a requisição');
        })
        .finally(() => {
            // Restaurar o botão
            btnSalvar.innerHTML = originalBtnText;
            btnSalvar.disabled = false;
        });
    });
</script>
@endpush