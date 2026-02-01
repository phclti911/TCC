<?php
// index.php
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Editor com IA (correção + sugestões)</title>
  <style>
    body{font-family:system-ui,Arial; background:#0b1220; color:#e7eefc; margin:0}
    .wrap{max-width:1000px; margin:24px auto; padding:0 16px}
    .card{background:#121b2f; border:1px solid #223055; border-radius:14px; padding:16px; box-shadow:0 10px 30px rgba(0,0,0,.25)}
    h1{font-size:20px; margin:0 0 8px}
    .muted{color:#9fb0d6; font-size:13px}
    textarea{
      width:100%; min-height:260px; resize:vertical;
      background:#0e162a; color:#e7eefc;
      border:1px solid #2a3a66; border-radius:12px;
      padding:12px; font-size:16px; line-height:1.5;
      outline:none;
    }
    .row{display:flex; gap:12px; flex-wrap:wrap; margin-top:12px}
    .panel{flex:1; min-width:280px; background:#0e162a; border:1px solid #2a3a66; border-radius:12px; padding:12px}
    .panel h2{font-size:14px; margin:0 0 8px; color:#cfe0ff}
    .btn{cursor:pointer; border:1px solid #35509a; background:#16306a; color:#e7eefc; padding:8px 10px; border-radius:10px; font-size:13px}
    .btn:disabled{opacity:.6; cursor:not-allowed}
    .pill{display:inline-flex; align-items:center; gap:8px; padding:6px 10px; border-radius:999px; border:1px solid #2a3a66; background:#0b1326; margin:6px 6px 0 0}
    .sugg{cursor:pointer; border:1px solid #2a3a66; background:#0b1326; color:#e7eefc; padding:6px 10px; border-radius:999px; font-size:13px; margin:6px 6px 0 0}
    .sugg:hover{border-color:#4b6cff}
    .err{color:#ffb3b3}
    .ok{color:#b5ffcc}
    .small{font-size:12px; color:#9fb0d6}
    code{background:#0b1326; padding:2px 6px; border-radius:8px; border:1px solid #223055}
  </style>
</head>
<body>
  <div class="wrap">
    <div class="card">
      <h1>Editor com IA: correção ortográfica + auto-sugestões</h1>
      <div class="muted">
        Dica: digite normalmente. As sugestões aparecem para a <b>palavra atual</b>.
        Use <code>Corrigir tudo</code> para listar erros no texto inteiro.
      </div>

      <div style="margin-top:12px">
        <textarea id="txt" placeholder="Digite aqui..."></textarea>
      </div>

      <div class="row">
        <div class="panel">
          <h2>Auto-sugestões (palavra atual)</h2>
          <div class="small">Clique em uma sugestão para substituir a palavra onde está o cursor.</div>
          <div id="suggestions"></div>
        </div>

        <div class="panel">
          <h2>Correção do texto</h2>
          <div style="display:flex; gap:8px; flex-wrap:wrap; align-items:center">
            <button class="btn" id="btnCheck">Corrigir tudo (listar erros)</button>
            <button class="btn" id="btnApply" disabled>Aplicar 1ª sugestão em todos</button>
            <span id="status" class="small"></span>
          </div>
          <div id="errors" style="margin-top:10px"></div>
        </div>
      </div>
    </div>
  </div>

<script>
  const txt = document.getElementById('txt');
  const suggestionsBox = document.getElementById('suggestions');
  const errorsBox = document.getElementById('errors');
  const statusEl = document.getElementById('status');
  const btnCheck = document.getElementById('btnCheck');
  const btnApply = document.getElementById('btnApply');

  let lastMatches = [];
  let debounceTimer = null;

  function setStatus(msg, kind="") {
    statusEl.className = "small " + (kind === "ok" ? "ok" : kind === "err" ? "err" : "");
    statusEl.textContent = msg;
  }

  function getCursorWord(text, caretPos) {
    // pega a palavra "onde o cursor está"
    const left = text.slice(0, caretPos);
    const right = text.slice(caretPos);

    const leftMatch = left.match(/[\p{L}'’-]+$/u);
    const rightMatch = right.match(/^[\p{L}'’-]+/u);

    const wordLeft = leftMatch ? leftMatch[0] : "";
    const wordRight = rightMatch ? rightMatch[0] : "";
    const word = wordLeft + wordRight;

    const start = caretPos - wordLeft.length;
    const end = caretPos + wordRight.length;

    return { word, start, end };
  }

  function replaceRange(original, start, end, replacement) {
    return original.slice(0, start) + replacement + original.slice(end);
  }

  function renderSuggestions(suggs, currentWordInfo) {
    suggestionsBox.innerHTML = "";
    if (!currentWordInfo.word || currentWordInfo.word.length < 2) {
      suggestionsBox.innerHTML = `<span class="small">Digite uma palavra (mín. 2 letras) para ver sugestões.</span>`;
      return;
    }

    if (!suggs || !suggs.length) {
      suggestionsBox.innerHTML = `<span class="small">Sem sugestões no momento.</span>`;
      return;
    }

    suggs.slice(0, 10).forEach(s => {
      const btn = document.createElement('button');
      btn.className = "sugg";
      btn.type = "button";
      btn.textContent = s;
      btn.onclick = () => {
        const caret = txt.selectionStart;
        const info = getCursorWord(txt.value, caret);
        if (!info.word) return;

        txt.value = replaceRange(txt.value, info.start, info.end, s);
        // reposiciona cursor
        const newPos = info.start + s.length;
        txt.focus();
        txt.setSelectionRange(newPos, newPos);

        // atualiza sugestões após substituir
        triggerSuggest();
      };
      suggestionsBox.appendChild(btn);
    });
  }

  function renderErrors(matches, text) {
    errorsBox.innerHTML = "";
    lastMatches = matches || [];

    if (!lastMatches.length) {
      errorsBox.innerHTML = `<div class="pill"><span class="ok">✓</span> Nenhum erro encontrado.</div>`;
      btnApply.disabled = true;
      return;
    }

    btnApply.disabled = false;

    lastMatches.slice(0, 20).forEach((m, idx) => {
      const wrong = text.substr(m.offset, m.length);
      const reps = (m.replacements || []).map(r => r.value).slice(0, 6);

      const div = document.createElement('div');
      div.style.marginTop = "10px";
      div.innerHTML = `
        <div class="pill">
          <span class="err">✗</span>
          <b>${wrong.replaceAll("<","&lt;")}</b>
          <span class="small">(${m.rule?.issueType || "erro"})</span>
        </div>
        <div class="small" style="margin-top:6px">${(m.message || "").replaceAll("<","&lt;")}</div>
        <div style="margin-top:6px"></div>
      `;

      const row = div.querySelector('div[style*="margin-top:6px"]');
      reps.forEach(r => {
        const b = document.createElement('button');
        b.className = "sugg";
        b.type = "button";
        b.textContent = r;
        b.onclick = () => {
          txt.value = replaceRange(txt.value, m.offset, m.offset + m.length, r);
          setStatus(`Substituído "${wrong}" por "${r}".`, "ok");
        };
        row.appendChild(b);
      });

      errorsBox.appendChild(div);
    });

    if (lastMatches.length > 20) {
      const more = document.createElement('div');
      more.className = "small";
      more.style.marginTop = "10px";
      more.textContent = `Mostrando 20 de ${lastMatches.length} ocorrências.`;
      errorsBox.appendChild(more);
    }
  }

  async function triggerSuggest() {
    const caret = txt.selectionStart;
    const info = getCursorWord(txt.value, caret);

    if (!info.word || info.word.length < 2) {
      renderSuggestions([], info);
      return;
    }

    try {
      const res = await fetch("api/suggest.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          text: txt.value,
          caret: caret
        })
      });
      const data = await res.json();
      renderSuggestions(data.suggestions || [], info);
    } catch (e) {
      suggestionsBox.innerHTML = `<span class="small err">Falha ao buscar sugestões.</span>`;
    }
  }

  async function checkAll() {
    setStatus("Analisando texto...", "");
    try {
      const res = await fetch("api/spell.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ text: txt.value })
      });
      const data = await res.json();

      if (!data.ok) {
        setStatus(data.error || "Erro ao verificar.", "err");
        return;
      }

      setStatus(`Pronto. Encontrados ${data.matches.length} possíveis erros.`, data.matches.length ? "err" : "ok");
      renderErrors(data.matches, txt.value);
    } catch (e) {
      setStatus("Falha de conexão com a API.", "err");
    }
  }

  function applyFirstSuggestionEverywhere() {
    if (!lastMatches.length) return;

    // Aplica a 1ª sugestão de cada match (de trás pra frente pra não quebrar offsets)
    const matches = [...lastMatches].sort((a,b) => b.offset - a.offset);
    let text = txt.value;

    let changed = 0;
    for (const m of matches) {
      const reps = (m.replacements || []);
      if (!reps.length) continue;

      const replacement = reps[0].value;
      text = replaceRange(text, m.offset, m.offset + m.length, replacement);
      changed++;
    }

    txt.value = text;
    setStatus(`Aplicadas ${changed} substituições com a 1ª sugestão.`, "ok");
  }

  // Debounce para sugestões enquanto digita
  txt.addEventListener('input', () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(triggerSuggest, 250);
  });

  txt.addEventListener('click', () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(triggerSuggest, 150);
  });
  txt.addEventListener('keyup', () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(triggerSuggest, 150);
  });

  btnCheck.addEventListener('click', checkAll);
  btnApply.addEventListener('click', applyFirstSuggestionEverywhere);

  // inicia
  renderSuggestions([], {word:""});
  setStatus("Pronto.", "ok");
</script>
</body>
</html>
