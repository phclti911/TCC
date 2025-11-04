<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Editor de Texto Acessível</title>
<style>
  /* 🔧 Corrigido: não use Google Fonts para OpenDyslexic. Auto-hospede: */
  @font-face {
    font-family: 'OpenDyslexic';
    src: url('fonts/OpenDyslexic-Regular.woff2') format('woff2'),
         url('fonts/OpenDyslexic-Regular.woff') format('woff'),
         url('fonts/OpenDyslexic-Regular.ttf') format('truetype');
    font-weight: normal;
    font-style: normal;
    font-display: swap;
  }

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
    /* Deixe um default acessível; o usuário pode trocar na UI */
    font-family: 'OpenDyslexic', Arial, sans-serif;
    font-size: 16px;
    line-height: 1.5;
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

<h2>📝 Editor Acessível com TTS e Salvamento de dados</h2>

<div class="toolbar">
  <label>Fonte:
    <select id="fontSelect">
      <option value="Arial, sans-serif">Arial</option>
      <option value="'OpenDyslexic', Arial, sans-serif">OpenDyslexic</option>
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
const fontSelect = document.getElementById("fontSelect");
const fontSize = document.getElementById("fontSize");
const fontColor = document.getElementById("fontColor");
const contrastMode = document.getElementById("contrastMode");
const letterSpacing = document.getElementById("letterSpacing");
const wordSpacing = document.getElementById("wordSpacing");
const lineHeight = document.getElementById("lineHeight");

/* ---------- Carregar preferências ---------- */
async function carregarPreferencias() {
  try {
    const res = await fetch("carregar_preferencias.php");
    const prefs = await res.json();
    if (!prefs) return;

    // 🔧 Corrigido: use um padrão CONSISTENTE com underscore
    editor.style.fontFamily = prefs.fonte || "'OpenDyslexic', Arial, sans-serif";
    editor.style.fontSize = (prefs.tamanho || 16) + "px";
    editor.style.color = prefs.cor || "#000000";
    editor.style.letterSpacing = (prefs.espacamento_letras ?? 0) + "px";
    editor.style.wordSpacing = (prefs.espacamento_palavras ?? 0) + "px";
    editor.style.lineHeight = prefs.espacamento_linhas || 1.5;

    if (prefs.contraste === "dark") body.classList.add("dark"); else body.classList.remove("dark");

    fontSelect.value = prefs.fonte || "'OpenDyslexic', Arial, sans-serif";
    fontSize.value = prefs.tamanho || 16;
    fontColor.value = prefs.cor || "#000000";
    contrastMode.value = prefs.contraste || "normal";
    letterSpacing.value = prefs.espacamento_letras ?? 0;
    wordSpacing.value = prefs.espacamento_palavras ?? 0;
    lineHeight.value = prefs.espacamento_linhas || 1.5;
  } catch(e) {
    console.warn("Preferências não carregadas:", e);
  }
}

/* ---------- Carregar texto salvo ---------- */
async function carregarTexto() {
  try {
    const res = await fetch("carregar_texto.php");
    const dados = await res.json();
    editor.value = (dados && dados.conteudo) ? dados.conteudo : "";
  } catch(e) {
    console.warn("Texto não carregado:", e);
  }
}

/* ---------- Salvar texto ---------- */
async function salvarTexto() {
  const texto = editor.value.trim();
  await fetch("salvar_texto.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ texto })
  });
  alert("Texto salvo com sucesso!");
}

/* ---------- Salvar preferências ---------- */
async function salvarPreferencias() {
  const prefs = {
    fonte: fontSelect.value,
    tamanho: Number(fontSize.value),
    cor: fontColor.value,
    contraste: contrastMode.value,
    // 🔧 Corrigido: padrão com underscore
    espacamento_letras: Number(letterSpacing.value),
    espacamento_palavras: Number(wordSpacing.value),
    espacamento_linhas: Number(lineHeight.value)
  };
  await fetch("salvar_preferencias.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(prefs)
  });
  alert("Preferências salvas!");
  aplicarPreferencias();
}

/* ---------- Aplicar preferências na UI ---------- */
function aplicarPreferencias() {
  editor.style.fontFamily = fontSelect.value;
  editor.style.fontSize = fontSize.value + "px";
  editor.style.color = fontColor.value;
  editor.style.letterSpacing = letterSpacing.value + "px";
  editor.style.wordSpacing = wordSpacing.value + "px";
  editor.style.lineHeight = lineHeight.value;

  if (contrastMode.value === "dark") body.classList.add("dark");
  else body.classList.remove("dark");
}

/* Aplicação imediata ao mudar qualquer controle */
[fontSelect, fontSize, fontColor, contrastMode, letterSpacing, wordSpacing, lineHeight]
  .forEach(el => el.addEventListener('input', aplicarPreferencias));

/* ---------- TTS com separação silábica ---------- */
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
  (function lerPalavra(){
    if (i >= palavras.length) return;
    const silabas = separarSilabas(palavras[i]);
    utterance = new SpeechSynthesisUtterance(silabas);
    utterance.lang = "pt-BR";
    utterance.rate = 1;
    utterance.onend = () => { i++; lerPalavra(); };
    synth.speak(utterance);
  })();
}
function pararLeitura() { if (synth.speaking) synth.cancel(); }

/* ---------- Inicialização ---------- */
window.onload = () => {
  carregarPreferencias();
  carregarTexto();
};
</script>

</body>
</html>
