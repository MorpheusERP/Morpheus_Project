const cod = document.getElementById('codigo');

let url =  '/produtos/ultimo-codigo';
const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

export function buscarUltimoCod() {
   // Envia a requisição para a rota do Laravel
    fetch(url, {
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
        if (data.status === "sucesso") {
            cod.value = Number(data.cod_Produto) + 1;
        } else if (data.status === 'erro'){
            mostrarErro(data.mensagem || 'Nenhum resultado encontrado');
        }
    })
    .catch(error => {
        console.error("Erro:", error);
        mostrarErro("Ocorreu um erro ao processar a solicitação: " + error.message);
    });
}

function mostrarErro(mensagem) {
    const mensagemErro = document.getElementById('mensagemErro');
    mensagemErro.innerText = mensagem;
    mensagemErro.style.display = 'block';
    mensagemErro.style.backgroundColor = 'rgba(220, 53, 69, 0.8)';
    mensagemErro.style.color = 'white';
    
    // Esconder após alguns segundos
    setTimeout(() => {
        mensagemErro.style.opacity = '0.7';
    }, 3000);
    
    setTimeout(() => {
        mensagemErro.style.display = 'none';
        mensagemErro.style.opacity = '1';
    }, 4000);
}
        
window.buscarUltimoCod = buscarUltimoCod;