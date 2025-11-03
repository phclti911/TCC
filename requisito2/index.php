<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Editor de Texto com Leitura em Voz</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background: #eef2f3;
        margin: 0;
        padding: 20px;
    }
    h1 {
        text-align: center;
        color: #333;
    }
    .editor-container {
        max-width: 800px;
        margin: 0 auto;
        background: white;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    textarea {
        width: 100%;
        height: 300px;
        font-size: 16px;
        line-height: 1.5;
        border: 1px solid #ccc;
        border-radius: 8px;
        padding: 10px;
        resize: vertical;
        outline: none;
    }
    .controls {
        margin-top: 15px;
        text-align: center;
    }
    button {
        background-color: #007bff;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        cursor: pointer;
        margin: 5px;
    }
    button:hover {
        background-color: #0056b3;
    }
    select {
        padding: 6px;
        border-radius: 6px;
        margin: 5px;
    }
</style>
</head>
<body>

<h1>🗣️ Editor de Texto com Conversão em Áudio</h1>

<div class="editor-container">
    <textarea id="texto" placeholder="Digite seu texto aqui..."></textarea>

    <div class="controls">
        <label for="voz">Voz:</label>
        <select id="voz"></select>

        <label for="velocidade">Velocidade:</label>
        <input type="range" id="velocidade" min="0.5" max="2" step="0.1" value="1">

        <button id="ouvir">▶ Ouvir</button>
        <button id="parar">⏹ Parar</button>
    </div>
</div>

<script>
// Inicialização das vozes disponíveis
let synth = window.speechSynthesis;
let vozSelect = document.getElementById('voz');
let vozes = [];

function carregarVozes() {
    vozes = synth.getVoices();
    vozSelect.innerHTML = '';
    vozes.forEach((voz, i) => {
        let option = document.createElement('option');
        option.value = i;
        option.textContent = `${voz.name} (${voz.lang})`;
        vozSelect.appendChild(option);
    });
}
carregarVozes();
if (speechSynthesis.onvoiceschanged !== undefined) {
    speechSynthesis.onvoiceschanged = carregarVozes;
}

document.getElementById('ouvir').addEventListener('click', () => {
    let texto = document.getElementById('texto').value.trim();
    if (texto.length === 0) {
        alert('Digite algum texto para ouvir.');
        return;
    }

    let fala = new SpeechSynthesisUtterance(texto);
    let vozSelecionada = vozes[vozSelect.value];
    fala.voice = vozSelecionada;
    fala.rate = document.getElementById('velocidade').value;
    synth.cancel(); // Interrompe qualquer fala anterior
    synth.speak(fala);
});

document.getElementById('parar').addEventListener('click', () => {
    synth.cancel();
});
</script>

</body>
</html>
