<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Editor de Texto Completo</title>

<style>
    body {
        font-family: Arial, Helvetica, sans-serif;
        background-color: #f2f4f7;
        margin: 0;
        padding: 0;
    }

    h1 {
        background-color: #1f2937;
        color: #ffffff;
        padding: 15px;
        margin: 0;
        text-align: center;
        font-size: 20px;
    }

    .toolbar {
        background-color: #ffffff;
        padding: 10px;
        border-bottom: 1px solid #ccc;
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        justify-content: center;
        align-items: center;
    }

    .toolbar select,
    .toolbar input[type="color"],
    .toolbar input[type="text"],
    .toolbar button {
        padding: 6px 8px;
        font-size: 14px;
        border: 1px solid #bbb;
        border-radius: 4px;
        cursor: pointer;
        background-color: #f9fafb;
    }

    .toolbar button:hover {
        background-color: #e5e7eb;
    }

    .editor-container {
        display: flex;
        justify-content: center;
        margin: 20px 0;
        padding: 0 10px;
    }

    .editor {
        width: 80%;
        max-width: 1000px;
        min-height: 350px;
        background-color: #ffffff;
        border: 1px solid #ccc;
        padding: 15px;
        font-size: 16px;
        outline: none;
        overflow-y: auto;
        border-radius: 8px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.06);
    }

    .hint {
        text-align: center;
        color: #6b7280;
        font-size: 13px;
        margin-top: -10px;
        margin-bottom: 14px;
    }
</style>
</head>

<body>

<h1>Editor de Texto Completo</h1>

<div class="toolbar">
    <!-- Fonte -->
    <select id="fontSelect" onchange="execCmd('fontName', this.value)">
        <option value="Arial" selected>Arial</option>
        <option value="Times New Roman">Times New Roman</option>
        <option value="Verdana">Verdana</option>
        <option value="Georgia">Georgia</option>
        <option value="Courier New">Courier New</option>
    </select>

    <!-- Tamanho (execCommand usa 1..7) -->
    <select id="sizeSelect" onchange="execCmd('fontSize', this.value)">
        <option value="2">Pequeno</option>
        <option value="3" selected>Médio</option>
        <option value="4">Grande</option>
        <option value="5">Muito Grande</option>
        <option value="6">Gigante</option>
    </select>

    <!-- Cor -->
    <input type="color" id="colorPicker" value="#111827" onchange="execCmd('foreColor', this.value)">

    <!-- Estilos -->
    <button type="button" onclick="execCmd('bold')"><b>B</b></button>
    <button type="button" onclick="execCmd('italic')"><i>I</i></button>
    <button type="button" onclick="execCmd('underline')"><u>U</u></button>

    <!-- Alinhamento -->
    <button type="button" onclick="execCmd('justifyLeft')" title="Alinhar à esquerda">⬅</button>
    <button type="button" onclick="execCmd('justifyCenter')" title="Centralizar">⬍</button>
    <button type="button" onclick="execCmd('justifyRight')" title="Alinhar à direita">➡</button>
    <button type="button" onclick="execCmd('justifyFull')" title="Justificar">☰</button>

    <!-- Linha -->
    <button type="button" onclick="execCmd('insertHorizontalRule')" title="Linha horizontal">—</button>

    <!-- Limpar formatação -->
    <button type="button" onclick="execCmd('removeFormat')" title="Remover formatação">Tx</button>

    <!-- Nome do arquivo -->
    <input type="text" id="fileName" value="meu-texto" style="width: 140px;" title="Nome do arquivo (sem extensão)">

    <!-- Salvar -->
    <button type="button" onclick="saveAsTXT()" title="Salvar como TXT">Salvar TXT</button>
    <button type="button" onclick="saveAsPDF()" title="Salvar como PDF">Salvar PDF</button>
</div>

<p class="hint">Dica: selecione um trecho do texto e aplique formatação pela barra acima.</p>

<div class="editor-container">
    <div class="editor" id="editor" contenteditable="true">Digite seu texto aqui...</div>
</div>

<!-- jsPDF (para salvar em PDF) -->
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>

<script>
    function execCmd(command, value = null) {
        document.execCommand(command, false, value);
        document.getElementById('editor').focus();
    }

    function getSafeFileName() {
        const raw = (document.getElementById('fileName').value || 'meu-texto').trim();
        // remove caracteres inválidos em nomes de arquivo
        return raw.replace(/[\\\/:*?"<>|]+/g, '').replace(/\s+/g, '-').toLowerCase() || 'meu-texto';
    }

    function getPlainTextFromEditor() {
        // innerText preserva quebras de linha visíveis
        const el = document.getElementById('editor');
        return (el.innerText || '').replace(/\u00A0/g, ' ').trim();
    }

    function downloadBlob(blob, filename) {
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        a.remove();
        URL.revokeObjectURL(url);
    }

    // ✅ Salvar em TXT
    function saveAsTXT() {
        const text = getPlainTextFromEditor();
        const name = getSafeFileName();
        const blob = new Blob([text], { type: 'text/plain;charset=utf-8' });
        downloadBlob(blob, `${name}.txt`);
    }

    // ✅ Salvar em PDF (texto puro)
    function saveAsPDF() {
        const text = getPlainTextFromEditor();
        const name = getSafeFileName();

        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({
            orientation: 'p',
            unit: 'mm',
            format: 'a4'
        });

        // Configurações de layout
        const marginLeft = 15;
        const marginTop = 20;
        const maxWidth = 180; // largura útil em mm (A4 ~ 210mm, menos margens)
        const lineHeight = 6;

        doc.setFont('helvetica', 'normal');
        doc.setFontSize(12);

        // Quebra o texto em linhas que cabem na página
        const lines = doc.splitTextToSize(text || ' ', maxWidth);

        let y = marginTop;
        const pageHeight = doc.internal.pageSize.getHeight();

        lines.forEach(line => {
            if (y + lineHeight > pageHeight - 15) {
                doc.addPage();
                y = marginTop;
            }
            doc.text(line, marginLeft, y);
            y += lineHeight;
        });

        doc.save(`${name}.pdf`);
    }
</script>

</body>
</html>
