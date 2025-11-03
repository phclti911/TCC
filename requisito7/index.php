<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Editor de Texto Acessível</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=OpenDyslexic&display=swap');
  body {
    background-color: var(--fundo, #f8f9fa);
    color: var(--texto, #000);
    font-family: Arial, sans-serif;
    margin: 20px;
    transition: 0.3s;
  }
  .toolbar {
    background: #e9ecef;
    padding: 10px;
    border-radius: 10px;
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    align-items: center;
    margin-bottom: 15px;
  }
  textarea {
    width: 100%;
    height: 350px;
    padding: 15px;
    border-radius: 10px;
    border: 1px solid #ccc;
    resize: vertical;
    outline: none;
  }
  button {
    background: #007bff;
    color: white;
    border: none;
    padding: 8px 12px;
    border-radius: 6px;
    cursor: pointer;
  }
  button:hover { background: #0056b3; }
  label { font-size: 0.9em; }
  .dark {
    --fundo: #111;
    --texto: #fff;
  }
</style>
</head>
<body>

<h2>📝 Editor Acessível com TTS e Salvamento de dados </h2>

<div class="toolbar">
  <label>Fonte:
    <select id="fontSelect">
      <option value="Arial, sans-serif">Arial</option>
      <option value="'OpenDyslexic', sans-serif">OpenDyslexic</option>
    </select>
  </label>

  <label>Tamanho:
    <input type="number" id="fontSize" min="12" max="48" value="16"> px
  </label>

  <label>Cor:
    <input type="color" id="fontColor" value="#000000">
  </label>

  <label>Contraste:
    <select id="contrastMode">
      <option value="normal">Normal</option>
      <option value="dark">Alto Contraste</option>
    </select>
  </label>

  <label>Letras:
    <input type="number" id="letterSpacing" min="0" max="10" value="0"> px
  </label>

  <label>Palavras:
    <input type="number" id="wordSpacing" min="0" max="20" value="0"> px
  </label>

  <label>Linhas:
    <input type="number" id="lineHeight" min="1" max="3" step="0.1" value="1.5">
  </label>

  <button onclick="falarTexto()">🔊 Ler</button>
  <button onclick="pararLeitura()">⏹️ Parar</button>
  <button onclick="salvarPreferencias()">💾 Salvar Preferências</button>
  <button onclick="salvarTexto()">🗂️ Salvar Texto</button>
</div>

<textarea id="editor" placeholder="Digite seu texto aqui..."></textarea>

<script>
const editor = document.getElementById("editor");
const body = document.body;

// --- Carregar preferências ---
async function carregarPreferencias() {
  const res = await fetch("carregar_preferencias.php");
  const prefs = await res.json();
  if (prefs) {
    editor.style.fontFamily = prefs.fonte;
    editor.style.fontSize = prefs.tamanho + "px";
    editor.style.color = prefs.cor;
    editor.style.letterSpacing = prefs.espacamento_letras + "px";
    editor.style.wordSpacing = prefs.espacamento_palavras + "px";
    editor.style.lineHeight = prefs.espacamento_linhas;
    if (prefs.contraste === "dark") body.classList.add("dark");
    document.getElementById("fontSelect").value = prefs.fonte;
    document.getElementById("fontSize").value = prefs.tamanho;
    document.getElementById("fontColor").value = prefs.cor;
    document.getElementById("contrastMode").value = prefs.contraste;
    document.getElementById("letterSpacing").value = prefs.espacamento_letras;
    document.getElementById("wordSpacing").value = prefs.espacamento_palavras;
    document.getElementById("lineHeight").value = prefs.espacamento_linhas;
  }
}

// --- Carregar texto salvo ---
async function carregarTexto() {
  const res = await fetch("carregar_texto.php");
  const dados = await res.json();
  editor.value = dados.conteudo || "";
}

// --- Salvar texto ---
async function salvarTexto() {
  const texto = editor.value.trim();
  await fetch("salvar_texto.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ texto })
  });
  alert("Texto salvo com sucesso!");
}

// --- Aplicar e salvar preferências ---
async function salvarPreferencias() {
  const prefs = {
    fonte: document.getElementById("fontSelect").value,
    tamanho: document.getElementById("fontSize").value,
    cor: document.getElementById("fontColor").value,
    contraste: document.getElementById("contrastMode").value,
    espacamentoLetras: document.getElementById("letterSpacing").value,
    espacamentoPalavras: document.getElementById("wordSpacing").value,
    espacamentoLinhas: document.getElementById("lineHeight").value
  };
  await fetch("salvar_preferencias.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(prefs)
  });
  alert("Preferências salvas!");
  aplicarPreferencias();
}

function aplicarPreferencias() {
  editor.style.fontFamily = document.getElementById("fontSelect").value;
  editor.style.fontSize = document.getElementById("fontSize").value + "px";
  editor.style.color = document.getElementById("fontColor").value;
  editor.style.letterSpacing = document.getElementById("letterSpacing").value + "px";
  editor.style.wordSpacing = document.getElementById("wordSpacing").value + "px";
  editor.style.lineHeight = document.getElementById("lineHeight").value;
  if (document.getElementById("contrastMode").value === "dark") body.classList.add("dark");
  else body.classList.remove("dark");
}

// --- TTS com separação silábica ---
let synth = window.speechSynthesis;
let utterance;

function separarSilabas(palavra) {
  return palavra.replace(/([aeiouáéíóúâêôãõ])/gi, "$1-").replace(/-$/,"");
}

function falarTexto() {
  pararLeitura();
  const texto = editor.value.trim();
  if (texto === "") return alert("Digite algo!");
  const palavras = texto.split(/\s+/);
  let i = 0;
  function lerPalavra() {
    if (i >= palavras.length) return;
    const palavra = palavras[i];
    const silabas = separarSilabas(palavra);
    utterance = new SpeechSynthesisUtterance(silabas);
    utterance.lang = "pt-BR";
    utterance.rate = 1;
    utterance.onend = () => { i++; lerPalavra(); };
    synth.speak(utterance);
  }
  lerPalavra();
}
function pararLeitura() { if (synth.speaking) synth.cancel(); }

// Carregar tudo ao iniciar
window.onload = () => {
  carregarPreferencias();
  carregarTexto();
};
</script>

</body>
</html>
