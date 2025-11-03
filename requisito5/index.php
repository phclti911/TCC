<?php
// --- Funções principais ---
function resumirTexto($texto, $tamanho = 300) {
    $texto = strip_tags(trim($texto));
    if (strlen($texto) > $tamanho) {
        $texto = substr($texto, 0, $tamanho) . "...";
    }
    return $texto;
}

function fragmentarTexto($texto, $tamanho = 300) {
    return str_split($texto, $tamanho);
}

function simplificarTexto($texto) {
    // Dicionário de simplificação (palavra complexa => termo simples)
    $substituicoes = [
        "inicialmente" => "no começo",
        "posteriormente" => "depois",
        "utilizar" => "usar",
        "demonstrar" => "mostrar",
        "compreensão" => "entendimento",
        "realizar" => "fazer",
        "dificuldade" => "problema",
        "facilidade" => "facilitação",
        "eficaz" => "efetivo",
        "metodologia" => "método",
        "complexo" => "difícil",
        "implementação" => "uso",
        "tecnologia" => "ferramenta",
        "aprendizagem" => "aprendizado",
        "usuário" => "pessoa",
        "plataforma" => "site",
        "acessibilidade" => "facilidade de acesso",
        "desenvolvimento" => "criação",
        "necessidade" => "precisão",
        "cognitivo" => "mental",
        "recurso" => "ferramenta",
        "aprofundar" => "entender melhor"
    ];

    foreach ($substituicoes as $dif => $simples) {
        $texto = preg_replace("/\b$dif\b/ui", $simples, $texto);
    }
    return $texto;
}

// --- Lógica principal ---
$resumo = "";
$fragmentos = [];
$simplificado = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $texto = $_POST["texto"] ?? "";

    if (isset($_POST["resumir"])) {
        $resumo = resumirTexto($texto);
    } elseif (isset($_POST["fragmentar"])) {
        $fragmentos = fragmentarTexto($texto);
    } elseif (isset($_POST["simplificar"])) {
        $simplificado = simplificarTexto($texto);
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Editor de Texto Acessível</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background: #f5f7fa;
        margin: 0; padding: 20px;
    }
    h1 { color: #004080; text-align: center; }
    form {
        background: white;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    textarea {
        width: 100%;
        height: 220px;
        font-size: 16px;
        padding: 10px;
        margin-top: 10px;
        border-radius: 8px;
        border: 1px solid #ccc;
    }
    button {
        margin: 8px;
        padding: 10px 20px;
        background: #007BFF;
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
    }
    button:hover { background: #0056b3; }
    .resultado {
        background: white;
        padding: 15px;
        margin-top: 20px;
        border-radius: 10px;
        box-shadow: 0 0 5px rgba(0,0,0,0.1);
    }
    .bloco {
        background: #eef;
        padding: 10px;
        margin: 10px 0;
        border-radius: 6px;
    }
</style>
</head>
<body>

<h1>🧩 Editor de Texto Acessível</h1>

<form method="post">
    <label for="texto"><strong>Digite ou cole seu texto:</strong></label><br>
    <textarea id="texto" name="texto" placeholder="Escreva seu texto aqui..."><?= htmlspecialchars($_POST['texto'] ?? '') ?></textarea><br>
    
    <button type="submit" name="fragmentar">Fragmentar Texto</button>
    <button type="submit" name="resumir">Gerar Resumo</button>
    <button type="submit" name="simplificar">Simplificar Texto</button>
</form>

<?php if ($resumo): ?>
<div class="resultado">
    <h2>🔹 Versão Resumida:</h2>
    <p><?= nl2br(htmlspecialchars($resumo)) ?></p>
</div>
<?php endif; ?>

<?php if ($simplificado): ?>
<div class="resultado">
    <h2>🔹 Versão Simplificada:</h2>
    <p><?= nl2br(htmlspecialchars($simplificado)) ?></p>
</div>
<?php endif; ?>

<?php if (!empty($fragmentos)): ?>
<div class="resultado">
    <h2>🔹 Texto Fragmentado:</h2>
    <?php foreach ($fragmentos as $i => $parte): ?>
        <div class="bloco">
            <strong>Bloco <?= $i + 1 ?>:</strong><br>
            <?= nl2br(htmlspecialchars($parte)) ?>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

</body>
</html>
