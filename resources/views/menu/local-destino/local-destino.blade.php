@extends('layouts.app')

@section('title', 'Cadastro de Local de Destino')

@section('header-title', 'Cadastro de Local de Destino')

@push('styles')
    @vite(['resources/css/menu/local-destino/local-destino.css'])
@endpush

@section('content')
    <div class="form">
        <div class="buttons" id="default-buttons">
            <button class="new" onclick="novo()">
                <i class="fas fa-plus-circle"></i> Novo
            </button>
            <button class="search" onclick="window.location.href='{{ route('menu.local-destino.local-destino-buscar') }}'">
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
                <select id="tipo" class="input-field" required disabled>
                    <option value="" disabled selected>* Tipo de Local</option>
                    <option value="Descarte">Descarte</option>
                    <option value="Reaproveitamento">Reaproveitamento</option>
                </select>
                <input type="text" id="nome" class="input-field" maxlength="34" placeholder="* Nome do local:" required disabled>
                <input type="text" id="observacao" class="input-field" maxlength="34" placeholder="Observações:" disabled>
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
            document.getElementById('tipo').disabled = false;
            document.getElementById('nome').disabled = false;
            document.getElementById('observacao').disabled = false;
        }

        function desabilitarCampos() {
            document.getElementById('tipo').disabled = true;
            document.getElementById('nome').disabled = true;
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
            const tipo_Local = document.getElementById('tipo').value.trim();
            const nome_Local = document.getElementById('nome').value.trim();
            const observacao = document.getElementById('observacao').value.trim();

            // Verifica se todos os campos obrigatórios estão preenchidos
            if (!tipo_Local || !nome_Local) {
                mostrarErro('Por favor, preencha todos os campos com * antes de adicionar um local de destino.');
                return;
            }
    
            // Exibir indicador de carregamento
            const btnSalvar = document.querySelector('.edit');
            const originalBtnText = btnSalvar.innerHTML;
            btnSalvar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';
            btnSalvar.disabled = true;
            
            // Obtém o token CSRF
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            // Cria um objeto com os dados do local de destino
            const formData = new FormData();
            formData.append('tipo_Local', tipo_Local);
            formData.append('nome_Local', nome_Local);
            formData.append('observacao', observacao);
    
            // Envia os dados para o Laravel
            fetch('{{ route("menu.local-destino.store") }}', {
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
@endpush