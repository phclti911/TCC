<?php
// editor_tts_acessivel.php
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Editor Acessível com TTS e Realce de Sílabas</title>
<style>
  @import url('https://fonts.cdnfonts.com/css/opendyslexic');

  body {
    font-family: 'OpenDyslexic', Arial, sans-serif;
    background: #f3f6fb;
    padding: 20px;
  }

  h2 {
    text-align: center;
    color: #004aad;
  }

  #editor {
    width: 100%;
    height: 200px;
    font-size: 18px;
    line-height: 1.6;
    letter-spacing: 1px;
    word-spacing: 4px;
    border: 2px solid #ccc;
    border-radius: 10px;
    padding: 10px;
    resize: none;
  }

  #ajustes {
    margin-top: 20px;
    text-align: center;
    background: #e6ecf5;
    padding: 15px;
    border-radius: 10px;
  }

  label {
    display: inline-block;
    margin: 5px 10px;
  }

  input[type="range"] {
    width: 150px;
  }

  #controls {
    margin-top: 15px;
    text-align: center;
  }

  button {
    background: #0078d7;
    border: none;
    color: white;
    padding: 10px 20px;
    margin: 5px;
    border-radius: 6px;
    font-size: 16px;
    cursor: pointer;
  }

  button:hover {
    background: #005fa3;
  }

  #output {
    margin-top: 25px;
    font-size: 20px;
    padding: 10px;
    background: white;
    border-radius: 10px;
    line-height: 1.8;
  }

  .highlight {
    background: #a3c4ff;
    border-radius: 4px;
  }

  .silaba {
    padding: 2px;
    margin: 1px;
  }
</style>
</head>
<body>

<h2>🧩 Editor de Texto Acessível com Leitura Sincronizada</h2>

<textarea id="editor" placeholder="Digite seu texto aqui..."></textarea>

<div id="ajustes">
  <label>Tamanho da fonte:
    <input type="range" id="fontSize" min="14" max="32" value="18">
    <span id="fontSizeValue">18px</span>
  </label>
  <label>Espaçamento entre letras:
    <input type="range" id="letterSpace" min="0" max="10" value="1">
    <span id="letterSpaceValue">1px</span>
  </label>
  <label>Espaçamento entre palavras:
    <input type="range" id="wordSpace" min="0" max="20" value="4">
    <span id="wordSpaceValue">4px</span>
  </label>
  <label>Espaçamento entre linhas:
    <input type="range" id="lineSpace" min="1" max="3" step="0.1" value="1.6">
    <span id="lineSpaceValue">1.6</span>
  </label>
</div>

<div id="controls">
  <button id="lerBtn">🔊 Ler texto</button>
  <button id="pararBtn">⏹️ Parar</button>
</div>

<div id="output"></div>

<script>
const editor = document.getElementById("editor");
const output = document.getElementById("output");
let utterance;
let isSpeaking = false;

// ======= Função simples de separação silábica =======
function separarSilabas(palavra) {
  return palavra
    .replace(/([aeiouáéíóúâêôãõ])/gi, '$1-')
    .replace(/-([^aeiouáéíóúâêôãõ\s])/gi, '$1')
    .split('-')
    .filter(s => s.trim() !== '');
}

// ======= Controles de acessibilidade =======
const fontRange = document.getElementById("fontSize");
const letterSpace = document.getElementById("letterSpace");
const wordSpace = document.getElementById("wordSpace");
const lineSpace = document.getElementById("lineSpace");

function atualizarEstilos() {
  const font = fontRange.value + "px";
  const letter = letterSpace.value + "px";
  const word = wordSpace.value + "px";
  const line = lineSpace.value;

  editor.style.fontSize = font;
  editor.style.letterSpacing = letter;
  editor.style.wordSpacing = word;
  editor.style.lineHeight = line;

  output.style.fontSize = font;
  output.style.letterSpacing = letter;
  output.style.wordSpacing = word;
  output.style.lineHeight = line;

  document.getElementById("fontSizeValue").innerText = font;
  document.getElementById("letterSpaceValue").innerText = letter;
  document.getElementById("wordSpaceValue").innerText = word;
  document.getElementById("lineSpaceValue").innerText = line;
}

[fontRange, letterSpace, wordSpace, lineSpace].forEach(ctrl => {
  ctrl.addEventListener("input", atualizarEstilos);
});

// ======= Leitura em voz alta e sincronização =======
document.getElementById("lerBtn").addEventListener("click", () => {
  if (isSpeaking) return;
  const texto = editor.value.trim();
  if (!texto) return alert("Digite algo para ler!");

  const palavras = texto.split(/\s+/);
  output.innerHTML = "";

  palavras.forEach(p => {
    const silabas = separarSilabas(p);
    const spanPalavra = document.createElement("span");
    silabas.forEach(s => {
      const spanSilaba = document.createElement("span");
      spanSilaba.textContent = s;
      spanSilaba.classList.add("silaba");
      spanPalavra.appendChild(spanSilaba);
    });
    output.appendChild(spanPalavra);
    output.appendChild(document.createTextNode(" "));
  });

  const silabasSpans = output.querySelectorAll(".silaba");
  utterance = new SpeechSynthesisUtterance(texto);
  utterance.lang = "pt-BR";
  utterance.rate = 1;
  utterance.pitch = 1;

  let silabaAtual = 0;
  isSpeaking = true;

  utterance.onboundary = (event) => {
    if (event.name === "word" || event.charIndex !== undefined) {
      silabasSpans.forEach(s => s.classList.remove("highlight"));
      if (silabaAtual < silabasSpans.length) {
        silabasSpans[silabaAtual].classList.add("highlight");
        silabaAtual++;
      }
    }
  };

  utterance.onend = () => {
    isSpeaking = false;
    silabasSpans.forEach(s => s.classList.remove("highlight"));
  };

  speechSynthesis.cancel();
  speechSynthesis.speak(utterance);
});

// ======= Parar leitura =======
document.getElementById("pararBtn").addEventListener("click", () => {
  speechSynthesis.cancel();
  isSpeaking = false;
  const spans = output.querySelectorAll(".silaba");
  spans.forEach(s => s.classList.remove("highlight"));
});
</script>

</body>
</html>
