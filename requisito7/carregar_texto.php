<?php
require "config.php";

$usuario = "usuario_padrao";
$sql = "SELECT conteudo FROM textos WHERE usuario = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$usuario]);

if ($stmt->rowCount() > 0) {
    echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
} else {
    echo json_encode(["conteudo" => ""]);
}
?>
