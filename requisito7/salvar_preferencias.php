<?php
require "config.php";

$data = json_decode(file_get_contents("php://input"), true);
$usuario = "usuario_padrao"; // pode ser substituído por sessão/login

$sql = "SELECT id FROM preferencias WHERE usuario = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$usuario]);

if ($stmt->rowCount() > 0) {
    $sql = "UPDATE preferencias SET fonte=?, tamanho=?, cor=?, contraste=?, espacamento_letras=?, espacamento_palavras=?, espacamento_linhas=? WHERE usuario=?";
} else {
    $sql = "INSERT INTO preferencias (fonte, tamanho, cor, contraste, espacamento_letras, espacamento_palavras, espacamento_linhas, usuario)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
}

$stmt = $pdo->prepare($sql);
$stmt->execute([
    $data["fonte"],
    $data["tamanho"],
    $data["cor"],
    $data["contraste"],
    $data["espacamentoLetras"],
    $data["espacamentoPalavras"],
    $data["espacamentoLinhas"],
    $usuario
]);

echo json_encode(["status" => "ok"]);
?>
