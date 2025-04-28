<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MorpheusERP - Alterar Local de Destino</title>
    <link rel="shortcut icon" href="{{ asset('images/logo.png') }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @include('layouts.nav_bottom')
    @include('layouts.background')
    
    @vite(['resources/css/menu/local-destino/local-destino.css'])

</head>
<body>
    <div class="header">
        <h1>Alterar Local de Destino</h1>
    </div>
    
    <div class="container">
        <div class="form">
            <div class="Conteudo">
                <div class="buttons" id="new-button">
                    <button class="back" onclick="window.location.href='{{ route('menu.local-destino.local-destino-buscar') }}'">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </button>
                </div>
                
                <form id="cadastroForm" autocomplete="off">
                    <select id="tipo" class="input-field" required>
                        <option value="" disabled selected>* Tipo de Local</option>
                        <option value="Descarte">Descarte</option>
                        <option value="Reaproveitamento">Reaproveitamento</option>
                    </select>
                    <input type="text" id="nome" class="input-field" maxlength="34" placeholder="* Nome do local:" required>
                    <input type="text" id="observacao" class="input-field" maxlength="34" placeholder="Observações:">

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
        // Obtem o ID do local de destino da URL
        const params = new URLSearchParams(window.location.search);
        const id_Local = params.get('id_Local');

        // Verifica se o ID foi fornecido
        if (!id_Local) {
            mostrarErro('ID do local de destino não encontrado');
            document.getElementById('cadastroForm').style.display = 'none';
            document.getElementById('edit-buttons').style.display = 'none';
        } else {
            // Preenche o formulário com os dados do local de destino
            carregarLocal(id_Local);
        }

        // Função para carregar os dados do local de destino
        function carregarLocal(id) {
            // Mostrar indicador de carregamento
            document.getElementById('nome').disabled = true;
            document.getElementById('nome').placeholder = "Carregando...";
            document.getElementById('edit-buttons').style.display = 'none';

            // Obtém o token CSRF
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // Cria o FormData
            const formData = new FormData();
            formData.append('id_Local', id);

            // Envia a requisição para buscar os dados do local de destino
            fetch('{{ route("menu.local-destino.find") }}', {
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
                if (data.status === 'erro' || !data.resultados || data.resultados.length === 0) {
                    mostrarErro(data.mensagem || 'Local de destino não encontrado');
                    return;
                }

                const local = data.resultados[0]; // Primeiro resultado
                
                document.getElementById('tipo').value = local.tipo_Local || '';
                document.getElementById('nome').value = local.nome_Local || '';
                document.getElementById('observacao').value = local.observacao || '';

                // Habilitar campos e botão após carregamento
                document.getElementById('nome').disabled = false;
                document.getElementById('nome').placeholder = "* Nome do local:";
                document.getElementById('edit-buttons').style.display = 'flex';
            })
            .catch(error => {
                console.error('Erro ao carregar dados do Local de Destino:', error);
                mostrarErro("Erro ao carregar dados do local de destino");
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
            const tipo_Local = document.getElementById('tipo').value.trim();
            const nome_Local = document.getElementById('nome').value.trim();
            const observacao = document.getElementById('observacao').value.trim();

            // Verifica se os campos obrigatórios estão preenchidos
            if (!tipo_Local || !nome_Local) {
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
            formData.append('id_Local', id_Local);
            formData.append('tipo_Local', tipo_Local);
            formData.append('nome_Local', nome_Local);
            formData.append('observacao', observacao);

            // Envia os dados para o Laravel
            fetch('{{ route("menu.local-destino.local-destino-update") }}', {
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
                        window.location.href = '{{ route('menu.local-destino.local-destino-buscar') }}';
                    }, 2000); // Delay de 2 segundos
                } else {
                    mostrarErro(data.mensagem || 'Erro ao atualizar o local de destino');
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
</body>
</html>