<?php
// api/suggest.php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['ok' => false, 'error' => 'Método não permitido.']);
  exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

$text  = isset($data['text']) ? (string)$data['text'] : '';
$caret = isset($data['caret']) ? (int)$data['caret'] : 0;

if ($caret < 0) $caret = 0;
if ($caret > strlen($text)) $caret = strlen($text);

// pega a palavra atual (considera letras e alguns apóstrofos/hífens)
$left  = substr($text, 0, $caret);
$right = substr($text, $caret);

preg_match("/[\\p{L}'’-]+$/u", $left, $m1);
preg_match("/^[\\p{L}'’-]+/u", $right, $m2);

$wordLeft  = $m1[0] ?? '';
$wordRight = $m2[0] ?? '';
$word = $wordLeft . $wordRight;

$start = $caret - mb_strlen($wordLeft);
$len   = mb_strlen($word);

$wordTrim = trim($word);
if ($wordTrim === '' || mb_strlen($wordTrim) < 2) {
  echo json_encode(['ok' => true, 'suggestions' => []], JSON_UNESCAPED_UNICODE);
  exit;
}

// consulta LanguageTool e tenta achar o match exatamente nessa palavra
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
  CURLOPT_TIMEOUT => 10,
  CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded']
]);

$response = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$suggestions = [];

if ($response !== false && $code < 400) {
  $parsed = json_decode($response, true);
  $matches = $parsed['matches'] ?? [];

  // tenta achar match cuja faixa overlap com a palavra atual
  foreach ($matches as $match) {
    $off = $match['offset'] ?? -1;
    $l   = $match['length'] ?? 0;

    // overlap simples
    $overlap = !($off + $l <= $start || $start + $len <= $off);
    if ($overlap) {
      foreach (($match['replacements'] ?? []) as $r) {
        if (!empty($r['value'])) $suggestions[] = $r['value'];
      }
      break;
    }
  }
}

// fallback: sugestões por distância (bem simples) se não veio nada
if (!$suggestions) {
  $dict = [
    "pessoa","pessoas","texto","editor","correção","correcao","sugestão","sugestao","palavra","palavras",
    "sistema","informação","informacao","acessibilidade","dislexia","plataforma","projeto","conteúdo","conteudo",
    "português","portugues","verificação","verificacao","digitar","substituir"
  ];

  $w = mb_strtolower($wordTrim);
  $cands = [];
  foreach ($dict as $d) {
    $dist = levenshtein($w, mb_strtolower($d));
    if ($dist <= 3) $cands[$d] = $dist;
  }
  asort($cands);
  $suggestions = array_slice(array_keys($cands), 0, 8);
}

// remove duplicados
$suggestions = array_values(array_unique($suggestions));

echo json_encode(['ok' => true, 'suggestions' => $suggestions], JSON_UNESCAPED_UNICODE);
