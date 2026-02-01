<?php
// api/spell.php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['ok' => false, 'error' => 'Método não permitido.']);
  exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

$text = isset($data['text']) ? (string)$data['text'] : '';
$text = trim($text);

// Limites simples para evitar abuso
if (mb_strlen($text) > 20000) {
  echo json_encode(['ok' => false, 'error' => 'Texto muito grande (limite 20.000 caracteres).']);
  exit;
}

if ($text === '') {
  echo json_encode(['ok' => true, 'matches' => []]);
  exit;
}

$endpoint = "https://api.languagetool.org/v2/check";

$postFields = http_build_query([
  'text' => $text,
  'language' => 'pt-BR'
]);

$ch = curl_init($endpoint);
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_POST => true,
  CURLOPT_POSTFIELDS => $postFields,
  CURLOPT_TIMEOUT => 12,
  CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded']
]);

$response = curl_exec($ch);
$err = curl_error($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response === false || $code >= 400) {
  echo json_encode(['ok' => false, 'error' => 'Falha ao consultar o serviço de correção.', 'details' => $err]);
  exit;
}

$parsed = json_decode($response, true);
$matches = $parsed['matches'] ?? [];

echo json_encode(['ok' => true, 'matches' => $matches], JSON_UNESCAPED_UNICODE);
