<?php
require "config.php";

$data = json_decode(file_get_contents("php://input"), true);
$usuario = "usuario_padrao"; // pode ser substituído por $_SESSION futuramente
$texto = $data["texto"] ?? "";

$sql = "SELECT id FROM textos WHERE usuario = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$usuario]);

if ($stmt->rowCount() > 0) {
    $sql = "UPDATE textos SET conteudo = ? WHERE usuario = ?";
} else {
    $sql = "INSERT INTO textos (conteudo, usuario) VALUES (?, ?)";
}

$stmt = $pdo->prepare($sql);
$stmt->execute([$texto, $usuario]);

echo json_encode(["status" => "ok", "mensagem" => "Texto salvo com sucesso!"]);
?>
