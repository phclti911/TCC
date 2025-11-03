<?php
// futuramente salvar o texto em banco de dados
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editor de Texto Inclusivo</title>
    <link href="https://fonts.googleapis.com/css2?family=Open+Dyslexic&family=Arial&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #eef2f7;
            color: #222;
            margin: 0;
            padding: 0;
        }

        header {
            background: #003366;
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
            gap: 10px;
            background: #fff;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 0 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
        }

        select, input {
            padding: 5px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        #editor {
            width: 100%;
            min-height: 300px;
            background: #fff;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
            outline: none;
            resize: vertical;
        }
    </style>
</head>
<body>

<header>📝 Editor de Texto Inclusivo para Disléxicos</header>

<div class="container">
    <div class="controls">
        <label>Fonte:
            <select id="fontSelect">
                <option value="'Open Dyslexic', Arial, sans-serif">OpenDyslexic</option>
                <option value="Arial, sans-serif">Arial</option>
                <option value="'Times New Roman', serif">Times New Roman</option>
                <option value="Verdana, sans-serif">Verdana</option>
            </select>
        </label>

        <label>Tamanho:
            <input type="number" id="fontSize" value="18" min="12" max="40"> px
        </label>

        <label>Cor do Texto:
            <input type="color" id="fontColor" value="#000000">
        </label>

        <label>Espaçamento de Letras:
            <input type="number" id="letterSpacing" value="1" min="0" max="10"> px
        </label>

        <label>Espaçamento de Palavras:
            <input type="number" id="wordSpacing" value="2" min="0" max="20"> px
        </label>

        <label>Espaçamento de Linhas:
            <input type="number" id="lineHeight" value="1.5" step="0.1" min="1" max="3">
        </label>
    </div>

    <div id="editor" contenteditable="true" spellcheck="false">
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

    applyStyles(); // aplica estilos iniciais
</script>

</body>
</html>
