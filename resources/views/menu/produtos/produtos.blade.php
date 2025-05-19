@extends('layouts.app')

@section('title', 'Cadastro de Produtos')

@section('header-title', 'Cadastro de Produtos')

@push('styles')
    @vite(['resources/css/menu/produtos/produtos.css'])
@endpush

@push('scripts')
    @vite(['resources/js/menu/produtos/produtos-ia.js'])
    @vite(['resources/js/menu/produtos/produtos.js'])
@endpush

@section('content')
    <div class="form">
        <div class="buttons" id="default-buttons">
            <button class="new" onclick="novo()">
                <i class="fas fa-plus-circle"></i> Novo
            </button>
            <button class="search" onclick="window.location.href='{{ route('menu.produtos.produtos-buscar') }}'">
                <i class="fas fa-search"></i> Buscar
            </button>
        </div>
        <div class="Conteudo">
            <div class="buttons" id="new-button" style="display: none;">
                <button class="back" onclick="voltar(); recarregarPagina()">
                    <i class="fas fa-arrow-left"></i> Voltar
                </button>
            </div>
            <form id="produtoForm" autocomplete="off" enctype="multipart/form-data">
                <div class="image-placeholder">
                    <input type="file" id="imagem" accept="image/*" style="display: none;" onchange="exibirImagem(this)"
                        disabled>
                    <img id="preview" src="{{ asset('images/defaultimg.png') }}" class="image-disabled">
                    <label for="imagem" class="botao-upload">Selecionar Imagem</label>
                </div>

                <!-- Indicador de carregamento -->
                <div id="loading" style="display: none; margin-top: 1rem;">
                    <span>Processando imagem...</span>
                    <div class="loader"></div>
                </div>

                <!-- Mensagem de resultado -->
                <p id="resultadoFruta" style="margin-top: 1rem; font-weight: bold;"></p>

                <button type="button" id="auto-load" class="botao-upload" onclick="processarImagem()" style="display: none;">Cadastro Automático</button>
                <input type="number" id="codigo" class="input-field" maxlength="5" placeholder="* Código" required disabled>
                <input type="text" id="produto" class="input-field" maxlength="50" placeholder="* Nome do Produto" required
                    disabled>
                <div class="container2">
                    <div class="coluna1">
                        <select id="tipoQuantidade" required disabled>
                            <option value="" disabled selected>Selecione o tipo</option>
                            <option value="Quilo">Quilos(KG)</option>
                            <option value="Caixa">Caixas(CX)</option>
                            <option value="Unidade">Unidades(UN)</option>
                            <option value="Saco">Sacos(SC)</option>
                        </select>
                        <input type="number" id="pcusto" step="0.01" class="input-field" placeholder="* Preço de Custo"
                            required disabled>
                        <input type="text" id="grupo" class="input-field" maxlength="15" placeholder="* Grupo" required
                            disabled>
                    </div>
                    <div class="coluna2">
                        <input type="number" id="barras" class="input-field" maxlength="15" placeholder="Cod. de Barras"
                            disabled>
                        <input type="number" id="pvenda" step="0.01" class="input-field" placeholder="Preço de Venda"
                            disabled>
                        <input type="text" id="subgrupo" class="input-field" maxlength="15" placeholder="Sub. Grupo"
                            disabled>
                    </div>
                </div>
                <textarea id="observacao" maxlength="150" placeholder="Observações" disabled></textarea>
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

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@4.2.0"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow-models/mobilenet"></script>
    <script src="https://cdn.jsdelivr.net/npm/@teachablemachine/image@0.8/dist/teachablemachine-image.min.js"></script>

    <script>
        function novo() {
            document.getElementById('default-buttons').style.display = 'none';
            document.getElementById('auto-load').style.display = 'flex';
            document.getElementById('new-button').style.display = 'flex';
            document.getElementById('edit-buttons').style.display = 'flex';
            habilitarCampos();
            buscarUltimoCod();
        }

        function voltar() {
            document.getElementById('new-button').style.display = 'none';
            document.getElementById('default-buttons').style.display = 'flex';
            document.getElementById('edit-buttons').style.display = 'none';
            desabilitarCampos();

            // Limpar formulário
            document.getElementById('produtoForm').reset();
            document.getElementById('preview').src = "{{ asset('images/defaultimg.png') }}";
            document.getElementById('preview').classList.remove('image-enabled');
            document.getElementById('preview').classList.add('image-disabled');

            // Esconder mensagens
            document.getElementById('mensagemSucesso').style.display = 'none';
            document.getElementById('mensagemErro').style.display = 'none';
        }

        function habilitarCampos() {
            document.getElementById('preview').classList.remove('image-disabled');
            document.getElementById('preview').classList.add('image-enabled');
            document.getElementById("imagem").disabled = false;
            document.getElementById("produto").disabled = false;
            document.getElementById("tipoQuantidade").disabled = false;
            document.getElementById("pcusto").disabled = false;
            document.getElementById("grupo").disabled = false;
            document.getElementById("barras").disabled = false;
            document.getElementById("pvenda").disabled = false;
            document.getElementById("subgrupo").disabled = false;
            document.getElementById("observacao").disabled = false;
        }

        function desabilitarCampos() {
            document.getElementById('preview').classList.remove('image-enabled');
            document.getElementById('preview').classList.add('image-disabled');
            document.getElementById("imagem").disabled = true;
            document.getElementById("codigo").disabled = true;
            document.getElementById("produto").disabled = true;
            document.getElementById("tipoQuantidade").disabled = true;
            document.getElementById("pcusto").disabled = true;
            document.getElementById("grupo").disabled = true;
            document.getElementById("barras").disabled = true;
            document.getElementById("pvenda").disabled = true;
            document.getElementById("subgrupo").disabled = true;
            document.getElementById("observacao").disabled = true;
        }

        function recarregarPagina() {
            location.reload();
        }

        function exibirImagem(input) {
            const arquivo = input.files[0];
            if (arquivo) {
                const leitor = new FileReader();

                // Quando a imagem é carregada, a exibe no elemento <img>
                leitor.onload = function (e) {
                    const preview = document.getElementById('preview');
                    preview.src = e.target.result;
                }

                leitor.readAsDataURL(arquivo); // Lê o arquivo como uma URL de dados
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

        // Script para enviar os dados para o servidor e exibir a resposta
        document.getElementById('produtoForm').addEventListener('submit', function (event) {
            event.preventDefault(); // Impede o envio padrão do formulário

            // Pega os valores dos campos do formulário
            const codigo = document.getElementById('codigo').value.trim();
            const produto = document.getElementById('produto').value.trim();
            const tipoQuantidade = document.getElementById('tipoQuantidade').value.trim();
            const pcusto = document.getElementById('pcusto').value.trim();
            const grupo = document.getElementById('grupo').value.trim();
            const barras = document.getElementById('barras').value.trim();
            const pvenda = document.getElementById('pvenda').value.trim();
            const subgrupo = document.getElementById('subgrupo').value.trim();
            const observacao = document.getElementById('observacao').value.trim();
            const imagem = document.getElementById('imagem').files[0];

            // Verifica se todos os campos obrigatórios estão preenchidos
            if (!codigo || !produto || !tipoQuantidade || !pcusto || !grupo) {
                mostrarErro('Por favor, preencha todos os campos com * antes de adicionar um produto.');
                return;
            }

            // Exibir indicador de carregamento
            const btnSalvar = document.querySelector('.edit');
            const originalBtnText = btnSalvar.innerHTML;
            btnSalvar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';
            btnSalvar.disabled = true;

            // Cria um FormData para enviar os dados do formulário e a imagem
            const formData = new FormData();
            formData.append('cod_Produto', codigo);
            formData.append('nome_Produto', produto);
            formData.append('tipo_Produto', tipoQuantidade);
            formData.append('cod_Barras', barras);
            formData.append('preco_Custo', pcusto);
            formData.append('preco_Venda', pvenda);
            formData.append('grupo', grupo);
            formData.append('sub_Grupo', subgrupo);
            formData.append('observacao', observacao);

            if (imagem) {
                formData.append('imagem', imagem);
            }

            // Pegar o token CSRF
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // Envia os dados para a rota do Laravel
            fetch('{{ route("produto.store") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    // Não definir 'Content-Type' para que o navegador defina automaticamente com multipart/form-data
                },
                body: formData
            })
                .then(response => {
                    if (!response.ok) {
                        if (response.status === 422) {
                            // Erro de validação
                            return response.json().then(data => {
                                throw new Error(Object.values(data.errors).flat().join(' '));
                            });
                        }
                        throw new Error('Erro na requisição: ' + response.status);
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
                    mostrarErro(error.message || "Ocorreu um erro ao processar a solicitação");
                })
                .finally(() => {
                    // Restaurar o botão
                    btnSalvar.innerHTML = originalBtnText;
                    btnSalvar.disabled = false;
                });
        });

        // Substituir verificação de login baseada em PHP
        document.addEventListener("DOMContentLoaded", function () {
            // Verificação de login agora é feita pelo Laravel middleware
        });
    </script>
@endpush
