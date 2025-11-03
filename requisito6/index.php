<?php
session_start();

// Dicionário básico de palavras corretas
$palavrasCorretas = ["casa", "carro", "feliz", "escola", "amizade", "programar", "editor", "texto", "dislexia", "aprendizagem"];

if (!isset($_SESSION['pontos'])) $_SESSION['pontos'] = 0;
if (!isset($_SESSION['nivel'])) $_SESSION['nivel'] = 1;

$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $texto = strtolower(trim($_POST['texto']));
    $palavras = preg_split('/\s+/', $texto);

    $acertos = 0;
    foreach ($palavras as $p) {
        if (in_array($p, $palavrasCorretas)) {
            $acertos++;
        }
    }

    $_SESSION['pontos'] += $acertos * 10; // 10 pontos por palavra correta
    $pontos = $_SESSION['pontos'];

    // Atualiza o nível com base nos pontos
    if ($pontos >= 100) $_SESSION['nivel'] = 3;
    elseif ($pontos >= 50) $_SESSION['nivel'] = 2;

    $mensagem = "Você acertou $acertos palavras e ganhou " . ($acertos * 10) . " pontos!";
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Editor de Textos Gamificado</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>📝 Editor de Textos Gamificado</h1>

    <form method="post">
        <textarea name="texto" placeholder="Digite seu texto aqui..."></textarea>
        <button type="submit">Verificar</button>
    </form>

    <div class="feedback">
        <p><?= $mensagem ?></p>
        <p><strong>Pontuação:</strong> <?= $_SESSION['pontos'] ?></p>
        <p><strong>Nível:</strong> <?= $_SESSION['nivel'] ?></p>
        <div class="emblema">
            <?php if ($_SESSION['nivel'] == 1): ?>
                <img src="https://img.icons8.com/color/96/novice.png" alt="Iniciante"><p>Iniciante</p>
            <?php elseif ($_SESSION['nivel'] == 2): ?>
                <img src="https://img.icons8.com/color/96/intermediate.png" alt="Intermediário"><p>Intermediário</p>
            <?php else: ?>
                <img src="https://img.icons8.com/color/96/champion.png" alt="Mestre"><p>Mestre da Escrita!</p>
            <?php endif; ?>
        </div>
    </div>

    <button onclick="resetar()">🔄 Reiniciar Jogo</button>
</div>

<script src="script.js"></script>
</body>
</html>
