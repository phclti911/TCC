<?php
declare(strict_types=1);
session_start();

/**
 * Editor de Texto Gamificado (1 arquivo)
 * - Pontuação por palavra acertada (palavra-alvo)
 * - Combo
 * - Níveis (fácil/médio/difícil)
 * - Tempo por rodada
 * - Armazena recorde em $_SESSION
 */

$levels = [
  "facil" => [
    "label" => "Fácil",
    "time" => 120,
    "basePoints" => 10,
    "wrongPenalty" => 2,
    "hint" => true,
    "words" => [
      "casa","bola","gato","livro","sol","chuva","amigo","festa","praia","carro",
      "verde","azul","amor","tempo","paz","café","pão","rua","flor","doce"
    ],
  ],
  "medio" => [
    "label" => "Médio",
    "time" => 90,
    "basePoints" => 20,
    "wrongPenalty" => 5,
    "hint" => false,
    "words" => [
      "computador","programacao","teclado","janela","internet","biblioteca","sistema","seguranca","acessibilidade","documentacao",
      "estrutura","variavel","funcoes","objetivo","interface","requisito","otimizacao","desempenho","prototipo","validacao"
    ],
  ],
  "dificil" => [
    "label" => "Difícil",
    "time" => 60,
    "basePoints" => 35,
    "wrongPenalty" => 10,
    "hint" => false,
    "words" => [
      "heterogeneidade","interoperabilidade","confiabilidade","criptografia","idempotencia","assintotico","hiperparametro","metodologia","heuristica","orquestracao",
      "escalabilidade","telemetria","observabilidade","vulnerabilidade","normalizacao","sincronizacao","serializacao","polimorfismo","concorrencia","encapsulamento"
    ],
  ],
];

// Recorde por nível
if (!isset($_SESSION["highscores"])) {
  $_SESSION["highscores"] = ["facil"=>0, "medio"=>0, "dificil"=>0];
}

$selectedLevel = $_GET["nivel"] ?? "facil";
if (!isset($levels[$selectedLevel])) $selectedLevel = "facil";

$cfg = $levels[$selectedLevel];
$high = (int)($_SESSION["highscores"][$selectedLevel] ?? 0);
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Editor Gamificado (PHP)</title>
  <style>
    :root { --bg:#0b1220; --card:#101a33; --ink:#eaf0ff; --muted:#aab6df; --accent:#6aa6ff; --good:#3ddc97; --bad:#ff6b6b; }
    * { box-sizing: border-box; }
    body { margin:0; font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial; background: linear-gradient(180deg, #0b1220, #070b14); color: var(--ink); }
    .wrap { max-width: 1100px; margin: 0 auto; padding: 20px; }
    .top { display:flex; gap:12px; flex-wrap: wrap; align-items: center; justify-content: space-between; }
    .card { background: rgba(16,26,51,0.85); border:1px solid rgba(106,166,255,.18); border-radius: 14px; padding: 14px 16px; box-shadow: 0 10px 30px rgba(0,0,0,.25); }
    .grid { display:grid; grid-template-columns: 1.2fr .8fr; gap: 14px; margin-top: 14px; }
    @media (max-width: 900px){ .grid { grid-template-columns: 1fr; } }
    h1 { font-size: 18px; margin: 0; }
    .muted { color: var(--muted); }
    .pill { display:inline-flex; align-items:center; gap:8px; padding: 8px 10px; border-radius: 999px; background: rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.07); }
    .stat { display:flex; gap:10px; flex-wrap: wrap; }
    .stat b { font-size: 18px; }
    .btn { cursor:pointer; border: 0; padding: 10px 12px; border-radius: 10px; background: var(--accent); color: #061028; font-weight: 700; }
    .btn.secondary { background: rgba(255,255,255,.08); color: var(--ink); border:1px solid rgba(255,255,255,.12); }
    .btn:disabled { opacity: .55; cursor:not-allowed; }
    select { padding: 10px 12px; border-radius: 10px; background: rgba(255,255,255,.08); color: var(--ink); border:1px solid rgba(255,255,255,.12); }
    .target { font-size: 26px; letter-spacing: .5px; margin: 6px 0 2px; }
    .hint { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace; padding: 8px 10px; border-radius: 10px; background: rgba(255,255,255,.06); border:1px dashed rgba(255,255,255,.18); }
    textarea { width:100%; min-height: 360px; resize: vertical; padding: 14px; border-radius: 14px; border:1px solid rgba(255,255,255,.12); background: rgba(255,255,255,.04); color: var(--ink); font-size: 16px; line-height: 1.5; outline: none; }
    .log { max-height: 250px; overflow:auto; padding: 10px; border-radius: 14px; border:1px solid rgba(255,255,255,.12); background: rgba(255,255,255,.03); }
    .log div { padding: 6px 8px; border-radius: 10px; margin-bottom: 8px; background: rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.06); }
    .good { border-color: rgba(61,220,151,.35)!important; }
    .bad { border-color: rgba(255,107,107,.35)!important; }
    .kbd { font-family: ui-monospace; font-size: 12px; padding: 2px 6px; border-radius: 8px; background: rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.12); }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="top">
      <div class="card" style="flex:1">
        <h1>Editor de Texto Gamificado (PHP)</h1>
        <div class="muted">Digite no editor e ganhe pontos quando acertar a palavra-alvo. Finalize a palavra com <span class="kbd">espaço</span>, <span class="kbd">enter</span> ou pontuação.</div>
      </div>

      <div class="card">
        <form method="get" style="display:flex; gap:10px; align-items:center; margin:0;">
          <label class="muted" for="nivel">Nível</label>
          <select id="nivel" name="nivel" onchange="this.form.submit()">
            <?php foreach ($levels as $key => $lv): ?>
              <option value="<?= htmlspecialchars($key) ?>" <?= $key === $selectedLevel ? "selected" : "" ?>>
                <?= htmlspecialchars($lv["label"]) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <noscript><button class="btn" type="submit">OK</button></noscript>
        </form>
      </div>
    </div>

    <div class="grid">
      <div class="card">
        <div class="stat">
          <span class="pill">⏱️ Tempo: <b id="timeLeft"><?= (int)$cfg["time"] ?></b>s</span>
          <span class="pill">⭐ Pontos: <b id="score">0</b></span>
          <span class="pill">🔥 Combo: <b id="combo">0</b>x</span>
          <span class="pill">🏆 Recorde: <b id="high"><?= (int)$high ?></b></span>
        </div>

        <hr style="border:0; border-top:1px solid rgba(255,255,255,.10); margin: 14px 0;">

        <div class="muted">Palavra-alvo</div>
        <div class="target" id="targetWord">—</div>

        <?php if ($cfg["hint"]): ?>
          <div class="hint" id="hintBox">Dica: a palavra-alvo deve aparecer exatamente no seu texto.</div>
        <?php else: ?>
          <div class="muted" style="margin-top:6px;">Sem dicas neste nível 🙂</div>
        <?php endif; ?>

        <div style="display:flex; gap:10px; margin-top: 14px; flex-wrap: wrap;">
          <button class="btn" id="btnStart">Iniciar</button>
          <button class="btn secondary" id="btnPause" disabled>Pausar</button>
          <button class="btn secondary" id="btnReset">Reiniciar</button>
          <button class="btn secondary" id="btnSave" disabled>Salvar pontuação</button>
        </div>

        <div class="muted" style="margin-top: 10px;">
          Regras (<?= htmlspecialchars($cfg["label"]) ?>):
          +<?= (int)$cfg["basePoints"] ?> por acerto, penalidade -<?= (int)$cfg["wrongPenalty"] ?> por erro. Combo aumenta pontos.
        </div>
      </div>

      <div class="card">
        <div class="muted" style="margin-bottom:8px;">Registro</div>
        <div class="log" id="log"></div>
      </div>
    </div>

    <div class="card" style="margin-top: 14px;">
      <div class="muted" style="margin-bottom:8px;">Editor</div>
      <textarea id="editor" placeholder="Escreva aqui... (o jogo conta acertos quando você finaliza uma palavra)"></textarea>
    </div>
  </div>

  <script>
    // Config do PHP -> JS
    const CFG = {
      levelKey: <?= json_encode($selectedLevel) ?>,
      levelLabel: <?= json_encode($cfg["label"]) ?>,
      time: <?= (int)$cfg["time"] ?>,
      basePoints: <?= (int)$cfg["basePoints"] ?>,
      wrongPenalty: <?= (int)$cfg["wrongPenalty"] ?>,
      words: <?= json_encode(array_values($cfg["words"]), JSON_UNESCAPED_UNICODE) ?>,
    };

    const el = {
      timeLeft: document.getElementById('timeLeft'),
      score: document.getElementById('score'),
      combo: document.getElementById('combo'),
      high: document.getElementById('high'),
      targetWord: document.getElementById('targetWord'),
      editor: document.getElementById('editor'),
      log: document.getElementById('log'),
      btnStart: document.getElementById('btnStart'),
      btnPause: document.getElementById('btnPause'),
      btnReset: document.getElementById('btnReset'),
      btnSave: document.getElementById('btnSave'),
    };

    let running = false;
    let paused = false;
    let timeLeft = CFG.time;
    let timerId = null;

    let score = 0;
    let combo = 0;
    let target = "";
    let lastCheckedIndex = 0; // índice no texto até onde já processamos palavras

    function pickTarget() {
      const i = Math.floor(Math.random() * CFG.words.length);
      target = CFG.words[i];
      el.targetWord.textContent = target;
    }

    function normalizeWord(w) {
      // remove pontuações nas extremidades e normaliza para comparação
      return (w || "")
        .trim()
        .toLowerCase()
        .replace(/^[^\p{L}\p{N}]+|[^\p{L}\p{N}]+$/gu, "");
    }

    function addLog(ok, msg) {
      const div = document.createElement('div');
      div.className = ok ? 'good' : 'bad';
      div.textContent = msg;
      el.log.prepend(div);
    }

    function updateHUD() {
      el.timeLeft.textContent = String(timeLeft);
      el.score.textContent = String(score);
      el.combo.textContent = String(combo);
    }

    function calcPoints() {
      // combo dá bônus progressivo
      // ex: combo 0 -> 1.0x; combo 3 -> 1.3x; combo 10 -> 2.0x (cap)
      const mult = Math.min(2.0, 1.0 + combo * 0.1);
      return Math.round(CFG.basePoints * mult);
    }

    function processNewWords() {
      // Processa apenas o que foi digitado desde lastCheckedIndex
      const text = el.editor.value;
      const slice = text.slice(lastCheckedIndex);

      // Se não houve finalização de palavra ainda (sem espaço/enter/pontuação), não conta
      if (!/[ \n\t.,;:!?)]/.test(slice)) return;

      // Vamos pegar todas as palavras "fechadas" nesse pedaço
      // e deixar a última incompleta para próxima rodada (se existir)
      const parts = slice.split(/(\s+|[.,;:!?()]+)/);
      let consumed = 0;

      // Reconstrução: a cada token, se ele contém letra/número, é palavra
      for (let i = 0; i < parts.length; i++) {
        const token = parts[i];
        consumed += token.length;

        // Palavra potencial (contém letra ou número)
        if (/[\p{L}\p{N}]/u.test(token)) {
          const w = normalizeWord(token);
          const t = normalizeWord(target);

          if (w.length === 0) continue;

          if (w === t) {
            combo++;
            const pts = calcPoints();
            score += pts;
            addLog(true, `✅ Acertou "${target}" (+${pts}) | combo ${combo}x`);
            pickTarget();
          } else {
            combo = 0;
            score = Math.max(0, score - CFG.wrongPenalty);
            addLog(false, `❌ "${w}" ≠ "${target}" (-${CFG.wrongPenalty}) | combo reset`);
          }

          updateHUD();
        }

        // Se o token atual NÃO finaliza palavra e é o final do slice, paramos (evita contar palavra incompleta)
        // (mas como split inclui separadores, o final costuma ser "" ou uma palavra incompleta)
      }

      // Atualiza lastCheckedIndex até o último separador encontrado (ou tudo)
      // Para não perder a palavra incompleta no final:
      // - se o slice termina com separador, pode consumir tudo
      // - se termina com letra/número, recua até antes do último "token palavra" incompleto
      const endsWithSeparator = /[ \n\t.,;:!?)]$/.test(slice);
      if (endsWithSeparator) {
        lastCheckedIndex = text.length;
      } else {
        // recua: encontra o final do último separador
        const m = slice.match(/^(.*[ \n\t.,;:!?()]+)[^ \n\t.,;:!?()]*$/s);
        if (m && m[1] != null) {
          lastCheckedIndex += m[1].length;
        }
      }
    }

    function tick() {
      if (!running || paused) return;
      timeLeft--;
      updateHUD();
      if (timeLeft <= 0) endGame();
    }

    function startGame() {
      if (running) return;
      running = true;
      paused = false;
      timeLeft = CFG.time;
      score = 0;
      combo = 0;
      lastCheckedIndex = 0;
      el.editor.value = "";
      el.log.innerHTML = "";
      pickTarget();
      updateHUD();

      el.btnStart.disabled = true;
      el.btnPause.disabled = false;
      el.btnSave.disabled = true;

      timerId = setInterval(tick, 1000);
      el.editor.focus();
      addLog(true, `🚀 Jogo iniciado (${CFG.levelLabel})`);
    }

    function pauseGame() {
      if (!running) return;
      paused = !paused;
      el.btnPause.textContent = paused ? "Continuar" : "Pausar";
      addLog(true, paused ? "⏸️ Pausado" : "▶️ Continuando");
      if (!paused) el.editor.focus();
    }

    function resetGame() {
      if (timerId) clearInterval(timerId);
      running = false;
      paused = false;
      timeLeft = CFG.time;
      score = 0;
      combo = 0;
      lastCheckedIndex = 0;
      target = "";
      el.targetWord.textContent = "—";
      el.editor.value = "";
      el.log.innerHTML = "";
      updateHUD();

      el.btnStart.disabled = false;
      el.btnPause.disabled = true;
      el.btnPause.textContent = "Pausar";
      el.btnSave.disabled = true;
    }

    async function endGame() {
      if (timerId) clearInterval(timerId);
      running = false;
      paused = false;
      el.btnStart.disabled = false;
      el.btnPause.disabled = true;
      el.btnPause.textContent = "Pausar";
      el.btnSave.disabled = false;
      addLog(true, `🏁 Fim de jogo! Pontos: ${score}`);

      // Atualiza recorde no servidor (session) automaticamente
      try {
        const resp = await fetch(window.location.pathname + "?nivel=" + encodeURIComponent(CFG.levelKey), {
          method: "POST",
          headers: {"Content-Type":"application/x-www-form-urlencoded"},
          body: "action=save&score=" + encodeURIComponent(score)
        });
        const data = await resp.json();
        if (data && data.ok) {
          el.high.textContent = String(data.highscore);
          addLog(true, data.isNew ? "🏆 Novo recorde!" : "✅ Pontuação salva.");
        }
      } catch (e) {
        addLog(false, "⚠️ Não consegui salvar a pontuação (erro de rede/servidor).");
      }
    }

    // Eventos
    el.btnStart.addEventListener('click', (e) => { e.preventDefault(); startGame(); });
    el.btnPause.addEventListener('click', (e) => { e.preventDefault(); pauseGame(); });
    el.btnReset.addEventListener('click', (e) => { e.preventDefault(); resetGame(); });
    el.btnSave.addEventListener('click', (e) => { e.preventDefault(); endGame(); });

    el.editor.addEventListener('input', () => {
      if (!running || paused) return;
      processNewWords();
    });

    // Impede contar palavras enquanto pausa
    el.editor.addEventListener('keydown', (e) => {
      if (e.key === "Escape" && running) {
        pauseGame();
      }
    });

    // Estado inicial HUD
    updateHUD();
  </script>
</body>
</html>
<?php
// API simples para salvar score (session)
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  header("Content-Type: application/json; charset=utf-8");

  $action = $_POST["action"] ?? "";
  if ($action !== "save") {
    echo json_encode(["ok"=>false, "error"=>"invalid_action"]);
    exit;
  }

  $score = (int)($_POST["score"] ?? 0);
  $lvl = $selectedLevel;

  $current = (int)($_SESSION["highscores"][$lvl] ?? 0);
  $isNew = false;
  if ($score > $current) {
    $_SESSION["highscores"][$lvl] = $score;
    $current = $score;
    $isNew = true;
  }

  echo json_encode(["ok"=>true, "highscore"=>$current, "isNew"=>$isNew], JSON_UNESCAPED_UNICODE);
  exit;
}
?>
