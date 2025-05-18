@extends('layouts.app')

@section('title', 'Alterar Produto')

@section('header-title', 'Alterar Produto')

@push('styles')
    @vite(['resources/css/menu/produtos/produtos.css'])
@endpush

@section('content')
    <div class="form">
        <div class="Conteudo">
            <div class="buttons" id="new-button">
                <button class="back" onclick="window.location.href='{{ route('menu.produtos.produtos-buscar') }}'">
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
                        <input type="number" id="pcusto" step="0.01" class="input-field" placeholder="Preço de Custo"
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
                <div class="buttons-edit" id="edit-buttons">
                    <button class="edit" type="button" onclick="alterar()" id="change-button">
                        <i class="fas fa-pencil-alt"></i> Alterar
                    </button>
                    <button class="edit" type="submit" id="save-button" style="display: none;">
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
        // Variável para armazenar os dados originais
        let dadosOriginais = {};
        const caminhoBase = "../../../Backend/CadastroProdutos/";

        // Obtem o ID do Produto da URL
        const params = new URLSearchParams(window.location.search);
        const cod_Produto = params.get('cod_Produto');

        // Função para habilitar a edição dos campos
        function alterar() {
            document.getElementById('preview').classList.remove('image-disabled');
            document.getElementById('preview').classList.add('image-enabled');
            document.getElementById('imagem').disabled = false;
            document.getElementById('produto').disabled = false;
            document.getElementById('tipoQuantidade').disabled = false;
            document.getElementById('pcusto').disabled = false;
            document.getElementById('grupo').disabled = false;
            document.getElementById('barras').disabled = false;
            document.getElementById('pvenda').disabled = false;
            document.getElementById('subgrupo').disabled = false;
            document.getElementById('observacao').disabled = false;

            // Alternar botões
            document.getElementById('change-button').style.display = 'none';
            document.getElementById('save-button').style.display = 'inline';
        }

        // Função para carregar os dados do produto
        function carregarProduto() {
            // Mostrar indicador de carregamento
            document.getElementById('produto').placeholder = "Carregando...";

            // Pegar o token CSRF
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            fetch(`{{ route('produto.find') }}?cod_Produto=${cod_Produto}`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Erro na requisição: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.status === 'sucesso') {
                        dadosOriginais = { ...data.produto };

                        document.getElementById('codigo').value = cod_Produto;
                        document.getElementById('produto').value = data.produto.nome_Produto;
                        document.getElementById('tipoQuantidade').value = data.produto.tipo_Produto;
                        document.getElementById('pcusto').value = data.produto.preco_Custo;
                        document.getElementById('grupo').value = data.produto.grupo;
                        document.getElementById('barras').value = data.produto.cod_Barras;
                        document.getElementById('pvenda').value = data.produto.preco_Venda;
                        document.getElementById('subgrupo').value = data.produto.sub_Grupo;
                        document.getElementById('observacao').value = data.produto.observacao;

                        if (data.produto.imagem) {
                            document.getElementById('preview').src = `data:image/jpeg;base64,${data.produto.imagem}`;
                        } else {
                            document.getElementById('preview').src = "{{ asset('images/defaultimg.png') }}";
                        }

                        document.getElementById('produto').placeholder = "* Nome do Produto";
                    } else if (data.status === 'erro') {
                        mostrarErro(data.mensagem || "Erro ao carregar dados do produto");
                    }
                })
                .catch(error => {
                    console.error('Erro ao carregar dados do Produto:', error);
                    mostrarErro("Erro ao carregar dados do produto: " + error.message);
                });
        }

        function exibirImagem(input) {
            const arquivo = input.files[0];
            if (arquivo) {
                const leitor = new FileReader();

                leitor.onload = function (e) {
                    const preview = document.getElementById('preview');
                    preview.src = e.target.result;
                };

                leitor.readAsDataURL(arquivo);
            }
        }

        function mostrarSucesso(mensagem) {
            const mensagemSucesso = document.getElementById('mensagemSucesso');
            mensagemSucesso.innerText = mensagem;
            mensagemSucesso.style.display = 'block';
            mensagemSucesso.style.backgroundColor = 'rgba(40, 167, 69, 0.8)';
            mensagemSucesso.style.color = 'white';

            document.getElementById('mensagemErro').style.display = 'none';

            setTimeout(() => {
                mensagemSucesso.style.display = 'none';
            }, 4000);
        }

        function mostrarErro(mensagem) {
            const mensagemErro = document.getElementById('mensagemErro');
            mensagemErro.innerText = mensagem;
            mensagemErro.style.display = 'block';
            mensagemErro.style.backgroundColor = 'rgba(220, 53, 69, 0.8)';
            mensagemErro.style.color = 'white';

            document.getElementById('mensagemSucesso').style.display = 'none';

            setTimeout(() => {
                mensagemErro.style.display = 'none';
            }, 4000);
        }

        // Evento para enviar o formulário
        document.getElementById('produtoForm').addEventListener('submit', function (event) {
            event.preventDefault();

            // Pega os valores dos campos
            const codigo = document.getElementById('codigo').value.trim();
            const nome = document.getElementById('produto').value.trim();
            const tipo = document.getElementById('tipoQuantidade').value.trim();
            const codigoBarras = document.getElementById('barras').value.trim();
            const precoCusto = document.getElementById('pcusto').value.trim();
            const precoVenda = document.getElementById('pvenda').value.trim();
            const grupo = document.getElementById('grupo').value.trim();
            const subgrupo = document.getElementById('subgrupo').value.trim();
            const observacao = document.getElementById('observacao').value.trim();
            const imagem = document.getElementById('imagem').files[0];

            // Verifica se os campos obrigatórios estão preenchidos
            if (!codigo || !nome || !tipo || !precoCusto || !grupo) {
                mostrarErro('Os campos com * são obrigatórios');
                return;
            }

            // Verifica se algum campo ou a imagem foi alterada
            if (
                nome === dadosOriginais.nome_Produto &&
                tipo === dadosOriginais.tipo_Produto &&
                codigoBarras === dadosOriginais.cod_Barras &&
                precoCusto === dadosOriginais.preco_Custo &&
                precoVenda === dadosOriginais.preco_Venda &&
                grupo === dadosOriginais.grupo &&
                subgrupo === dadosOriginais.sub_Grupo &&
                observacao === dadosOriginais.observacao &&
                !imagem // Verifica se uma nova imagem foi selecionada
            ) {
                mostrarErro("Nenhuma alteração realizada.");
                return;
            }

            // Exibir indicador de carregamento
            const btnSalvar = document.getElementById('save-button');
            const originalBtnText = btnSalvar.innerHTML;
            btnSalvar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';
            btnSalvar.disabled = true;

            // Pegar o token CSRF
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // Criar um FormData para enviar os dados do formulário e a imagem
            const formData = new FormData();
            formData.append('cod_Produto', codigo);
            formData.append('nome_Produto', nome);
            formData.append('tipo_Produto', tipo);
            formData.append('cod_Barras', codigoBarras);
            formData.append('preco_Custo', precoCusto);
            formData.append('preco_Venda', precoVenda);
            formData.append('grupo', grupo);
            formData.append('sub_Grupo', subgrupo);
            formData.append('observacao', observacao);

            if (imagem) {
                formData.append('imagem', imagem);
            }

            // Envia os dados para a rota do Laravel
            fetch('{{ route("produto.update") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    // Não definir 'Content-Type' para que o navegador defina automaticamente com multipart/form-data
                },
                body: formData
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Erro na requisição: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.status === 'sucesso') {
                        mostrarSucesso(data.mensagem);
                        setTimeout(() => {
                            window.location.href = '{{ route("menu.produtos.produtos-buscar") }}';
                        }, 2000); // Delay de 2 segundos
                    } else if (data.status === 'erro') {
                        mostrarErro(data.mensagem);
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    mostrarErro('Ocorreu um erro ao processar a requisição: ' + error.message);
                })
                .finally(() => {
                    // Restaurar o botão
                    btnSalvar.innerHTML = originalBtnText;
                    btnSalvar.disabled = false;
                });
        });

        if (!cod_Produto) {
            mostrarErro('ID do produto não encontrado');
            document.getElementById('produtoForm').style.display = 'none';
        } else {
            carregarProduto();

            const editar = params.get('editar');
            if (editar === 'true') {
                alterar();
            }
        }
    </script>
@endpush
