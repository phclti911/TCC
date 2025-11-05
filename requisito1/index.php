<?php
// futuramente salvar o texto em banco de dados
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <title>Editor de Texto Inclusivo</title>

  <!-- Fonte local: REMOVIDO CDN, usando @font-face -->
  <style>
    /* --------- Fontes locais OpenDyslexic --------- */
    @font-face {
      font-family: 'OpenDyslexic';
      src:
        url('fonts/OpenDyslexic-Regular.woff2') format('woff2'),
        url('fonts/OpenDyslexic-Regular.woff') format('woff'),
        url('fonts/OpenDyslexic-Regular.ttf') format('truetype');
      font-weight: 400;
      font-style: normal;
      font-display: swap;
    }
    @font-face {
      font-family: 'OpenDyslexic';
      src:
        url('fonts/OpenDyslexic-Italic.woff2') format('woff2'),
        url('fonts/OpenDyslexic-Italic.woff') format('woff'),
        url('fonts/OpenDyslexic-Italic.ttf') format('truetype');
      font-weight: 400;
      font-style: italic;
      font-display: swap;
    }
    @font-face {
      font-family: 'OpenDyslexic';
      src:
        url('fonts/OpenDyslexic-Bold.woff2') format('woff2'),
        url('fonts/OpenDyslexic-Bold.woff') format('woff'),
        url('fonts/OpenDyslexic-Bold.ttf') format('truetype');
      font-weight: 700;
      font-style: normal;
      font-display: swap;
    }
    @font-face {
      font-family: 'OpenDyslexic';
      src:
        url('fonts/OpenDyslexic-BoldItalic.woff2') format('woff2'),
        url('fonts/OpenDyslexic-BoldItalic.woff') format('woff'),
        url('fonts/OpenDyslexic-BoldItalic.ttf') format('truetype');
      font-weight: 700;
      font-style: italic;
      font-display: swap;
    }

    /* --------- Estilos da página --------- */
    :root {
      --bg: #eef2f7;
      --fg: #222;
      --brand: #003366;
      --panel: #fff;
      --ring: #2a72e5;
    }

    * { box-sizing: border-box; }

    body {
      font-family: 'OpenDyslexic', Arial, sans-serif;
      background: var(--bg);
      color: var(--fg);
      margin: 0;
      padding: 0;
    }

    header {
      background: var(--brand);
      color: #fff;
      text-align: center;
      padding: 15px;
      font-size: 1.4em;
    }

    .container {
      display: flex;
      flex-direction: column;
      align-items: center;
      margin: 30px auto;
      width: 90%;
      max-width: 900px;
    }

    .controls {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 12px;
      background: var(--panel);
      padding: 15px;
      border-radius: 10px;
      box-shadow: 0 0 8px rgba(0,0,0,0.08);
      margin-bottom: 20px;
    }

    label {
      font-weight: bold;
      display: flex;
      align-items: center;
      gap: 6px;
    }

    select, input[type="number"], input[type="color"] {
      padding: 6px 8px;
      border-radius: 8px;
      border: 1px solid #cfd6e0;
      background: #fff;
      font-family: inherit;
      font-size: 14px;
      outline: none;
    }
    select:focus, input:focus {
      border-color: var(--ring);
      box-shadow: 0 0 0 3px rgba(42,114,229,0.20);
    }

    #editor {
      width: 100%;
      min-height: 320px;
      background: var(--panel);
      padding: 16px;
      border-radius: 12px;
      box-shadow: 0 0 5px rgba(0,0,0,0.08);
      outline: none;
      resize: vertical;
      font-family: 'OpenDyslexic', Arial, sans-serif;
      font-size: 18px;
      line-height: 1.5;
      letter-spacing: 1px;
      word-spacing: 2px;
    }

    /* Acessibilidade do foco dentro do editor */
    #editor:focus {
      box-shadow: 0 0 0 3px rgba(42,114,229,0.20);
      border: 1px solid var(--ring);
    }

    /* Melhor leitura: evita hifenização e mantém palavras inteiras */
    #editor {
      hyphens: none;
      overflow-wrap: anywhere;
    }
  </style>
</head>
<body>

<header>📝 Editor de Texto Inclusivo para Disléxicos</header>

<div class="container">
  <div class="controls" role="group" aria-label="Controles de formatação">
    <label>Fonte:
      <select id="fontSelect" aria-label="Selecione a fonte">
        <option value="'OpenDyslexic', Arial, sans-serif">OpenDyslexic</option>
        <option value="Arial, sans-serif">Arial</option>
        <option value="'Times New Roman', serif">Times New Roman</option>
        <option value="Verdana, sans-serif">Verdana</option>
      </select>
    </label>

    <label>Tamanho:
      <input type="number" id="fontSize" value="18" min="12" max="40" aria-label="Tamanho da fonte"> px
    </label>

    <label>Cor do Texto:
      <input type="color" id="fontColor" value="#000000" aria-label="Cor do texto">
    </label>

    <label>Espaçamento de Letras:
      <input type="number" id="letterSpacing" value="1" min="0" max="10" step="0.1" aria-label="Espaçamento de letras"> px
    </label>

    <label>Espaçamento de Palavras:
      <input type="number" id="wordSpacing" value="2" min="0" max="20" step="0.5" aria-label="Espaçamento de palavras"> px
    </label>

    <label>Espaçamento de Linhas:
      <input type="number" id="lineHeight" value="1.5" step="0.1" min="1" max="3" aria-label="Espaçamento de linhas">
    </label>
  </div>

  <div id="editor" contenteditable="true" spellcheck="false" aria-label="Área de edição de texto">
    Digite seu texto aqui...
  </div>
</div>

<script>
  const editor = document.getElementById('editor');
  const fontSelect = document.getElementById('fontSelect');
  const fontSize = document.getElementById('fontSize');
  const fontColor = document.getElementById('fontColor');
  const letterSpacing = document.getElementById('letterSpacing');
  const wordSpacing = document.getElementById('wordSpacing');
  const lineHeight = document.getElementById('lineHeight');

  function applyStyles() {
    editor.style.fontFamily = fontSelect.value;
    editor.style.fontSize = fontSize.value + 'px';
    editor.style.color = fontColor.value;
    editor.style.letterSpacing = letterSpacing.value + 'px';
    editor.style.wordSpacing = wordSpacing.value + 'px';
    editor.style.lineHeight = lineHeight.value;
  }

  fontSelect.addEventListener('change', applyStyles);
  fontSize.addEventListener('input', applyStyles);
  fontColor.addEventListener('input', applyStyles);
  letterSpacing.addEventListener('input', applyStyles);
  wordSpacing.addEventListener('input', applyStyles);
  lineHeight.addEventListener('input', applyStyles);

  // aplica estilos iniciais garantindo a fonte local
  applyStyles();
</script>

</body>
</html>
