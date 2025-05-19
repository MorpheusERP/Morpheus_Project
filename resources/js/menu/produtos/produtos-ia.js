const modeloLocal = "./ModelosIA/Frutas/model.json";
const metadados = "./ModelosIA/Frutas/metadata.json";
let model, maxPredictions;

export async function processarImagem() {
        const preview = document.getElementById('preview');
        const resultadoFruta = document.getElementById('resultadoFruta');
        const loading = document.getElementById('loading');

        // Verifica se há imagem
        if (!preview.src || preview.style.display === 'none') {
            resultadoFruta.innerText = "Nenhuma imagem selecionada.";
            resultadoFruta.style.color = "red";
            return;
        }

        // Mostrar o carregando
        resultadoFruta.innerText = "";
        loading.style.display = 'block';

        try {
            if (!model) {
                model = await tmImage.load(modeloLocal, metadados);
                maxPredictions = model.getTotalClasses();
            }

            const prediction = await model.predict(preview);
            prediction.sort((a, b) => b.probability - a.probability);
            const fruta = prediction[0].className;
            const confianca = prediction[0].probability;

            // Esconde o carregamento
            loading.style.display = 'none';

            if (confianca >= 0.75) { // Define um limite de confiança
                preencherCamposComBaseNaFruta(fruta);
                resultadoFruta.innerText = "Fruta reconhecida!";
                resultadoFruta.style.color = "green";
            } else {
                resultadoFruta.innerText = "Fruta não reconhecida!";
                resultadoFruta.style.color = "red";
            }

        } catch (error) {
            loading.style.display = 'none';
            resultadoFruta.innerText = "Erro no processamento da imagem.";
            resultadoFruta.style.color = "red";
            console.error(error);
        }
    }

function preencherCamposComBaseNaFruta(fruta) {
  const info = {
    Abiu: { nome: "Abiu", grupo: "Frutas", tipo: "Quilo" },
    Açaí: { nome: "Açaí", grupo: "Frutas", tipo: "Quilo" },
    Acerola: { nome: "Acerola", grupo: "Frutas", tipo: "Quilo" },
    Amora: { nome: "Amora", grupo: "Frutas", tipo: "Quilo" },
    Maçã: { nome: "Maçã", grupo: "Frutas", tipo: "Quilo" },
    Araçá: { nome: "Araçá", grupo: "Frutas", tipo: "Quilo" },
    Araticum: { nome: "Araticum", grupo: "Frutas", tipo: "Quilo" },
    Abacate: { nome: "Abacate", grupo: "Frutas", tipo: "Quilo" },
    Banana: { nome: "Banana", grupo: "Frutas", tipo: "Quilo" },
    Pimentão: { nome: "Pimentão", grupo: "Frutas", tipo: "Quilo" },
    Biribiri: { nome: "Limão Biribiri", grupo: "Frutas", tipo: "Quilo" },
    Sapote: { nome: "Sapote Preto", grupo: "Frutas", tipo: "Quilo" },
    Melão: { nome: "Melão", grupo: "Frutas", tipo: "Quilo" },
    Carambola: { nome: "Carambola", grupo: "Frutas", tipo: "Quilo" },
    Cassis: { nome: "Cassis", grupo: "Frutas", tipo: "Quilo" },
    Castanha_do_Para:{ nome: "Castanha do Pará", grupo: "Frutas", tipo: "Quilo" },
    Cacau: { nome: "Cacau", grupo: "Frutas", tipo: "Quilo" },
    Côco: { nome: "Coco", grupo: "Frutas", tipo: "Quilo" },
    Café: { nome: "Café", grupo: "Frutas", tipo: "Quilo" },
    Cranberry: { nome: "Cranberry", grupo: "Frutas", tipo: "Quilo" },
    Cupuaçu: { nome: "Cupuaçu", grupo: "Frutas", tipo: "Quilo" },
    Damasco: { nome: "Damasco", grupo: "Frutas", tipo: "Quilo" },
    Pitaya: { nome: "Pitaya", grupo: "Frutas", tipo: "Quilo" },
    Beringela: { nome: "Beringela", grupo: "Frutas", tipo: "Quilo" },
    Genipapo: { nome: "Genipapo", grupo: "Frutas", tipo: "Quilo" },
    Goji: { nome: "Goji", grupo: "Frutas", tipo: "Quilo" },
    Uva: { nome: "Uva", grupo: "Frutas", tipo: "Quilo" },
    Toranja: { nome: "Toranja", grupo: "Frutas", tipo: "Quilo" },
    Guaraná: { nome: "Guaraná", grupo: "Frutas", tipo: "Quilo" },
    Goiaba: { nome: "Goiaba", grupo: "Frutas", tipo: "Quilo" },
    Jaboticaba: { nome: "Jaboticaba", grupo: "Frutas", tipo: "Quilo" },
    Jaca: { nome: "Jaca", grupo: "Frutas", tipo: "Quilo" },
    Jambú: { nome: "Jambú", grupo: "Frutas", tipo: "Quilo" },
    Jatobá: { nome: "Jatobá", grupo: "Frutas", tipo: "Quilo" },
    Kiwi: { nome: "Kiwi", grupo: "Frutas", tipo: "Quilo" },
    Limão: { nome: "Limão", grupo: "Frutas", tipo: "Quilo" },
    Lima: { nome: "Lima", grupo: "Frutas", tipo: "Quilo" },
    Macadâmia: { nome: "Macadâmia", grupo: "Frutas", tipo: "Quilo" },
    Tangerina: { nome: "Tangerina", grupo: "Frutas", tipo: "Quilo" },
    Manga: { nome: "Manga", grupo: "Frutas", tipo: "Quilo" },
    Melão: { nome: "Melão de São Caetano", grupo: "Frutas", tipo: "Quilo" },
    Mirtilo: { nome: "Mirtilo", grupo: "Frutas", tipo: "Quilo" },
    Nectarina: { nome: "Nectarina", grupo: "Frutas", tipo: "Quilo" },
    Noz_moscada: { nome: "Noz-moscada", grupo: "Frutas", tipo: "Quilo" },
    Azeitona: { nome: "Azeitona", grupo: "Frutas", tipo: "Quilo" },
    Laranja: { nome: "Laranja", grupo: "Frutas", tipo: "Quilo" },
    Mamão: { nome: "Mamão", grupo: "Frutas", tipo: "Quilo" },
    Passiflora: { nome: "Passiflora", grupo: "Frutas", tipo: "Quilo" },
    Maracujá: { nome: "Maracujá", grupo: "Frutas", tipo: "Quilo" },
    Amendoim: { nome: "Amendoim", grupo: "Frutas", tipo: "Quilo" },
    Pequi: { nome: "Pequi", grupo: "Frutas", tipo: "Quilo" },
    Abacaxi: { nome: "Abacaxi", grupo: "Frutas", tipo: "Quilo" },
    Pitomba: { nome: "Pitomba", grupo: "Frutas", tipo: "Quilo" },
    Romã: { nome: "Romã", grupo: "Frutas", tipo: "Quilo" },
    Pomelo: { nome: "Pomelo", grupo: "Frutas", tipo: "Quilo" },
    Porongo: { nome: "Porongo", grupo: "Frutas", tipo: "Quilo" },
    Abóbora: { nome: "Abóbora", grupo: "Frutas", tipo: "Quilo" },
    Pupunha: { nome: "Pupunha", grupo: "Frutas", tipo: "Quilo" },
    Rambutan: { nome: "Rambutan", grupo: "Frutas", tipo: "Quilo" },
    Framboesa: { nome: "Framboesa", grupo: "Frutas", tipo: "Quilo" },
    Morango: { nome: "Morango", grupo: "Frutas", tipo: "Quilo" },
    Baunilha: { nome: "Baunilha", grupo: "Frutas", tipo: "Quilo" },
    Melancia: { nome: "Melancia", grupo: "Frutas", tipo: "Quilo" },
    Abobrinha: { nome: "Abobrinha", grupo: "Frutas", tipo: "Quilo" },
};

    const dados = info[fruta];

    if(dados) {
        document.getElementById('produto').value = dados.nome;
        document.getElementById('grupo').value = dados.grupo;
        document.getElementById('tipoQuantidade').value = dados.tipo;
    }
}

window.processarImagem = processarImagem;