<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Editor Padrão - Inacessível</title>
<style>
    body {
        font-family: "Times New Roman", serif;
        font-size: 12px;
        color: #555;
        background-color: #f8f8f8;
        text-align: center;
    }
    h1 {
        background-color: #e0e0e0;
        color: #222;
        padding: 8px;
        font-size: 16px;
    }
    textarea {
        width: 60%;
        height: 200px;
        margin-top: 10px;
        background-color: #fff;
        color: #555;
        line-height: 1;
        letter-spacing: -0.5px;
        word-spacing: -2px;
        border: 1px solid #aaa;
        font-family: "Times New Roman", serif;
        font-size: 12px;
        resize: none;
    }
    button {
        margin-top: 10px;
        background-color: #ccc;
        border: 1px solid #999;
        color: #333;
        padding: 4px 10px;
        cursor: pointer;
        font-size: 12px;
    }
    button:hover {
        background-color: #bbb;
    }
</style>
</head>
<body>
    <h1>Editor de Texto Padrão</h1>
    <form method="post">
        <textarea name="texto"><?php
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                echo htmlspecialchars($_POST["texto"]);
            }
        ?></textarea><br>
        <button type="submit">Salvar</button>
    </form>
</body>
</html>
