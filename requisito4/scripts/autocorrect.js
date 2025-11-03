const editor = document.getElementById("editor");
const suggestionsBox = document.getElementById("suggestions");

// Dicionário básico de autocorreção
const autocorrectDict = {
    "teh": "the",
    "recieve": "receive",
    "acompanhar": "acompanhar",
    "escreever": "escrever",
    "dislexia": "dislexia",
    "tecnolgia": "tecnologia"
};

// Palavras sugeridas por predição
const wordSuggestions = ["dislexia", "educação", "tecnologia", "inclusão", "leitura", "acessibilidade"];

// Função de autocorreção e feedback
editor.addEventListener("input", () => {
    const text = editor.value;
    const words = text.split(/\s+/);
    const lastWord = words[words.length - 1].toLowerCase();

    // Autocorreção simples
    if (autocorrectDict[lastWord]) {
        words[words.length - 1] = autocorrectDict[lastWord];
        editor.value = words.join(" ");
    }

    // Sugestão de predição
    const predictions = wordSuggestions.filter(w => w.startsWith(lastWord) && lastWord.length > 1);
    if (predictions.length > 0) {
        suggestionsBox.innerHTML = `<strong>Sugestões:</strong> ${predictions.join(", ")}`;
    } else {
        suggestionsBox.innerHTML = "";
    }

    // Feedback visual (verifica erros simples)
    checkSpellingFeedback();
});

// Função simples de feedback visual
function checkSpellingFeedback() {
    let text = editor.value;
    // Simulação: realce de sílabas erradas
    const pattern = /(rr|ss|çç|bb)/gi;
    const hasError = pattern.test(text);

    if (hasError) {
        editor.style.backgroundColor = "#fff3f3";
    } else {
        editor.style.backgroundColor = "#ffffff";
    }
}
