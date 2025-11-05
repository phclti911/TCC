<?php
// editor_acessivel.php
// ------------------------------------------------------------
// Editor de texto acessível: simplificação, resumo e fragmentação
// ------------------------------------------------------------
mb_internal_encoding('UTF-8');

function normalizar($texto) {
    // Remove tags, normaliza espaços e trim
    $t = strip_tags($texto);
    $t = preg_replace('/\s+/u', ' ', $t);
    return trim($t);
}

function dividirEmFrases($texto) {
    // Separa por . ! ? mantendo o delimitador
    $partes = preg_split('/(?<=[\.\!\?])\s+/u', $texto, -1, PREG_SPLIT_NO_EMPTY);
    return $partes ?: [$texto];
}

function resumirTexto($texto, $qtdSentencas = 3) {
    $texto = normalizar($texto);
    if ($texto === '') return '';
    $frases = dividirEmFrases($texto);

    // Heurística simples: prioriza frases mais curtas (informativas) primeiro,
    // mantendo a ordem original dentre as Top-N
    $ordenado = $frases;
    usort($ordenado, function($a, $b) {
        return mb_strlen($a) <=> mb_strlen($b);
    });
    $top = array_slice($ordenado, 0, min($qtdSentencas, count($frases)));

    // Restaura a ordem original das selecionadas
    $map = array_flip($top);
    $final = array_values(array_filter($frases, fn($f) => array_key_exists($f, $map)));
    $resumo = implode(' ', array_slice($final, 0, $qtdSentencas));

    // Se havia mais conteúdo, indica com "..."
    $temMais = count($frases) > $qtdSentencas ? ' ...' : '';
    return trim($resumo . $temMais);
}

function fragmentarTexto($texto, $tamanho = 300) {
    $texto = normalizar($texto);
    if ($texto === '') return [];
    $palavras = preg_split('/\s+/u', $texto, -1, PREG_SPLIT_NO_EMPTY);

    $fragmentos = [];
    $bloco = '';

    foreach ($palavras as $p) {
        $adicao = ($bloco === '' ? $p : $bloco . ' ' . $p);
        if (mb_strlen($adicao) <= $tamanho) {
            $bloco = $adicao;
        } else {
            if ($bloco !== '') $fragmentos[] = $bloco;
            // Se palavra isolada maior que $tamanho, quebra por hífen seguro
            if (mb_strlen($p) > $tamanho) {
                $offset = 0;
                while ($offset < mb_strlen($p)) {
                    $fragmentos[] = mb_substr($p, $offset, $tamanho - 1) . ( ($offset + $tamanho - 1) < mb_strlen($p) ? '-' : '' );
                    $offset += $tamanho - 1;
                }
                $bloco = '';
            } else {
                $bloco = $p;
            }
        }
    }
    if ($bloco !== '') $fragmentos[] = $bloco;
    return $fragmentos;
}

function simplificarTexto($texto) {
    $texto = ' ' . normalizar($texto) . ' ';

    // Dicionário: termo complexo => simples
    $subs = [
        "inicialmente" => "no começo",
        "posteriormente" => "depois",
        "subsequente" => "depois",
        "utilizar" => "usar",
        "demonstrar" => "mostrar",
        "compreensão" => "entendimento",
        "realizar" => "fazer",
        "eficaz" => "que funciona",
        "efetivo" => "que funciona",
        "metodologia" => "método",
        "complexo" => "difícil",
        "viabilidade" => "possibilidade",
        "otimizar" => "melhorar",
        "implementar" => "colocar em prática",
        "evidenciar" => "mostrar",
        "subsidiar" => "ajudar",
        "corroborar" => "confirmar",
        "todavia" => "mas",
        "entretanto" => "mas",
        "conquanto" => "embora",
        "haja vista" => "porque",
        "outrossim" => "também",
        "consoante" => "de acordo",
        "pressuposto" => "ideia",
        "disfunção" => "problema",
        "sobretudo" => "principalmente",
        "mediante" => "por meio de",
        "no intuito de" => "para",
        "visando" => "para",
        "acerca de" => "sobre",
        "por conseguinte" => "por isso",
        "diante do exposto" => "assim",
    ];

    foreach ($subs as $dif => $simples) {
        $padrao = '/(\s)' . preg_quote($dif, '/') . '(\s)/iu';
        $texto  = preg_replace($padrao, '$1' . $simples . '$2', $texto);
    }

    // Queda de jargões e números longos -> versões curtas
    // substitui números muito grandes por formato mais simples (ex.: 1000000 -> 1 milhão)
    $texto = preg_replace_callback('/(\d{5,})/u', function($m) {
        $n = (int)$m[1];
        if ($n >= 1000000) {
            $milhoes = round($n / 1000000, 1);
            return $milhoes . ' milhão' . ($milhoes != 1.0 ? 's' : '');
        }
        if ($n >= 1000) {
            $mil = round($n / 1000, 1);
            return $mil . ' mil';
        }
        return $m[1];
    }, $texto);

    // Frases mais curtas: quebra vírgulas repetidas em pontos quando muito extensas
    $texto = preg_replace('/, (que|onde|o qual|a qual)/iu', '. $1', $texto);

    // Remove duplicações de espaço
    $texto = preg_replace('/\s+/u', ' ', $texto);

    return trim($texto);
}

// ---------- Controller ----------
$input = $_POST['texto'] ?? '';
$sentencas = max(1, (int)($_POST['sentencas'] ?? 3));
$tamanho   = max(50, (int)($_POST['tamanho'] ?? 300));
$result = [
    'simplificado' => '',
    'resumo' => '',
    'fragmentos' => []
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $textoOk = normalizar($input);
    $result['simplificado'] = simplificarTexto($textoOk);
    $result['resumo']       = resumirTexto($textoOk, $sentencas);
    $result['fragmentos']   = fragmentarTexto($textoOk, $tamanho);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Editor Acessível (Dislexia) — Simplificar | Resumir | Fragmentar</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=OpenDyslexic&display=swap" rel="stylesheet">
<style>
:root{
  --bg:#f7f9fc; --fg:#0f172a; --card:#ffffff; --primary:#2563eb; --muted:#64748b; --border:#e2e8f0;
}
*{box-sizing:border-box}
body{
  margin:0; padding:24px; background:var(--bg); color:var(--fg); font:16px/1.7 system-ui, -apple-system, Segoe UI, Roboto, "OpenDyslexic", Arial, sans-serif;
}
.container{max-width:1100px; margin:0 auto;}
h1{margin:0 0 8px; font-size:22px}
.desc{color:var(--muted); margin-bottom:16px}
.toolbar, .card{
  background:var(--card); border:1px solid var(--border); border-radius:16px; padding:16px; box-shadow:0 4px 20px rgba(2,6,23,.04);
}
.toolbar{display:flex; gap:12px; flex-wrap:wrap; align-items:center; margin-bottom:12px}
label{font-size:14px; color:var(--muted)}
input[type=number]{width:92px; padding:8px; border:1px solid var(--border); border-radius:10px}
button{
  background:var(--primary); color:#fff; border:none; border-radius:12px; padding:10px 14px; cursor:pointer; font-weight:600;
}
button.secondary{background:#0ea5e9}
button.ghost{background:transparent; color:var(--primary); border:1px solid var(--primary)}
.controls{display:flex; gap:10px; flex-wrap:wrap}
textarea{
  width:100%; min-height:220px; resize:vertical; padding:14px; border-radius:16px; border:1px solid var(--border); outline:none;
  font-size:18px; line-height:1.8; letter-spacing:.2px; background:#fff;
}
.grid{display:grid; gap:16px; grid-template-columns:1fr; margin-top:16px}
@media (min-width:900px){ .grid{grid-template-columns:1fr 1fr} }
.card h2{margin:0 0 8px; font-size:18px}
.badge{display:inline-block; padding:4px 8px; background:#eef2ff; color:#3730a3; border-radius:999px; font-size:12px; margin-left:6px}
.block{
  background:#ffffff; border:1px dashed var(--border); border-radius:12px; padding:10px; margin-bottom:10px
}
/* Modo Dislexia */
.dyslexic body, .use-od{ font-family:"OpenDyslexic", system-ui, Arial, sans-serif; }
.use-od{ font-size:20px; letter-spacing:.35px; word-spacing:.4px; line-height:2 }
.high-contrast{ --bg:#0b1220; --fg:#f8fafc; --card:#0f172a; --muted:#cbd5e1; --border:#1f2a44 }
.line-focus span{transition:background .2s ease}
.line-focus span.focused{ background: #fff7cc; }
/* largura confortável */
.reading-width{ max-width: 70ch; }
/* Botão TTS */
.tts{display:flex; gap:8px; align-items:center}
.small{font-size:12px; color:var(--muted)}
</style>
</head>
<body>
<div class="container">
  <h1 id="titulo">Editor Acessível para Dislexia <span class="badge" aria-label="funções: simplificar, resumir e fragmentar">Simplificar • Resumir • Fragmentar</span></h1>
  <p class="desc">Cole o texto, ajuste as preferências e clique em <strong>Processar</strong>. Use os botões de acessibilidade para fonte, contraste e leitura em voz alta.</p>

  <form method="post" aria-label="Formulário do editor">
    <div class="toolbar" role="group" aria-label="Controles do processador">
      <div>
        <label for="sentencas">Frases no resumo</label><br>
        <input type="number" id="sentencas" name="sentencas" min="1" max="10" value="<?=htmlspecialchars($sentencas)?>">
      </div>
      <div>
        <label for="tamanho">Tamanho do bloco (caracteres)</label><br>
        <input type="number" id="tamanho" name="tamanho" min="50" max="2000" value="<?=htmlspecialchars($tamanho)?>">
      </div>
      <div class="controls">
        <button type="submit">Processar</button>
        <button type="button" class="secondary" id="btnLimpar">Limpar</button>
        <button type="button" class="ghost" id="btnCopiarTudo">Copiar Tudo</button>
      </div>
    </div>

    <textarea id="texto" name="texto" aria-label="Área de texto principal" class="reading-width use-od" placeholder="Cole ou digite seu texto aqui..."><?=
      htmlspecialchars($input ?: "A metodologia proposta visa otimizar processos; todavia, sua implementação demanda compreensão adequada. Inicialmente, demonstramos a viabilidade do modelo por meio de experimentos.")
    ?></textarea>

    <div class="toolbar" role="group" aria-label="Acessibilidade">
      <button type="button" id="toggleFont">Fonte OpenDyslexic</button>
      <button type="button" id="toggleContrast">Contraste alto</button>
      <button type="button" id="toggleWidth">Largura confortável</button>
      <div class="tts">
        <button type="button" id="ttsPlay">▶ Ler</button>
        <button type="button" id="ttsStop">■ Parar</button>
        <span class="small" id="statusTTS" aria-live="polite"></span>
      </div>
    </div>
  </form>

  <div class="grid" aria-live="polite">
    <section class="card" aria-labelledby="h2simp">
      <h2 id="h2simp">Texto Simplificado</h2>
      <div id="outSimplificado" class="reading-width"><?= nl2br(htmlspecialchars($result['simplificado'])) ?: '<em class="small">— sem saída ainda —</em>' ?></div>
    </section>

    <section class="card" aria-labelledby="h2res">
      <h2 id="h2res">Resumo</h2>
      <div id="outResumo" class="reading-width"><?= nl2br(htmlspecialchars($result['resumo'])) ?: '<em class="small">— sem saída ainda —</em>' ?></div>
    </section>

    <section class="card" aria-labelledby="h2frag" style="grid-column: 1 / -1;">
      <h2 id="h2frag">Blocos (fragmentação)</h2>
      <?php if (!empty($result['fragmentos'])): ?>
        <?php foreach ($result['fragmentos'] as $i => $bloco): ?>
          <div class="block reading-width"><strong>Bloco <?= $i+1 ?>:</strong> <?= nl2br(htmlspecialchars($bloco)) ?></div>
        <?php endforeach; ?>
      <?php else: ?>
        <em class="small">— sem saída ainda —</em>
      <?php endif; ?>
    </section>
  </div>
</div>

<script>
// ===== Acessibilidade visual =====
const html = document.documentElement;
const textarea = document.getElementById('texto');
document.getElementById('toggleFont').addEventListener('click', ()=>{
  textarea.classList.toggle('use-od');
});
document.getElementById('toggleContrast').addEventListener('click', ()=>{
  document.body.classList.toggle('high-contrast');
});
document.getElementById('toggleWidth').addEventListener('click', ()=>{
  document.querySelectorAll('.reading-width').forEach(el => el.classList.toggle('reading-width'));
});

// ===== TTS (Leitura em voz alta) =====
const synth = window.speechSynthesis;
const btnPlay = document.getElementById('ttsPlay');
const btnStop = document.getElementById('ttsStop');
const statusTTS = document.getElementById('statusTTS');

function speak(text){
  if (!text) return;
  if (synth.speaking) synth.cancel();
  const utter = new SpeechSynthesisUtterance(text);
  utter.lang = 'pt-BR';
  utter.rate = 0.95; // levemente mais lento
  utter.onstart = ()=> statusTTS.textContent = 'Lendo...';
  utter.onend = ()=> statusTTS.textContent = 'Pronto';
  synth.speak(utter);
}

btnPlay.addEventListener('click', ()=>{
  // Prioriza ler o Simplificado, senão lê o texto original
  const out = document.getElementById('outSimplificado').innerText.trim();
  const src = out && out !== '— sem saída ainda —' ? out : textarea.value;
  speak(src);
});
btnStop.addEventListener('click', ()=>{ synth.cancel(); statusTTS.textContent = 'Parado'; });

// ===== Utilidades =====
document.getElementById('btnLimpar').addEventListener('click', ()=>{
  textarea.value = '';
});
document.getElementById('btnCopiarTudo').addEventListener('click', async ()=>{
  const simp = document.getElementById('outSimplificado').innerText;
  const resumo = document.getElementById('outResumo').innerText;
  const blocos = Array.from(document.querySelectorAll('.block')).map(b => b.innerText).join('\n\n');
  const tudo = `# Simplificado\n${simp}\n\n# Resumo\n${resumo}\n\n# Blocos\n${blocos}`;
  try{
    await navigator.clipboard.writeText(tudo);
    alert('Copiado para a área de transferência.');
  }catch(e){ alert('Não foi possível copiar automaticamente.'); }
});
</script>
</body>
</html>
