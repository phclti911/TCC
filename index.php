<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MAAD - Painel Lateral com Comentários</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <!-- MENU LATERAL -->
        <aside class="sidebar">
            <div class="logo">
                <h1>MAAD</h1>
                <p>Painel de Aplicações PHP</p>
            </div>

            <nav class="menu">
                <?php
                $projetos = [
                    "Editor de Texto Simples " => "requisito0",
                    "Personalização Tipográfica" => "requisito1",
                    "Integração Multimodal de Leitura" => "requisito2",
                    "Recurso de Realce Dinâmico" => "requisito3",
                    "Apoio Inteligente à Escrita" => "requisito4",
                    "Simplificação Textual" => "requisito5",
                    "Gamificação e Recompensa" => "requisito6",
                    "Configuração Persistente" => "requisito7"
                ];

                foreach ($projetos as $nome => $pasta) {
                    echo "
                    <button class='menu-btn' onclick=\"abrirProjeto('$pasta','$nome')\">
                        <img src='img/icon.png' alt='Ícone'>
                        <span>$nome</span>
                    </button>
                    ";
                }
                ?>
            </nav>
        </aside>

        <!-- CONTEÚDO PRINCIPAL -->
        <main class="conteudo">
            <iframe id="janela" src="" frameborder="0"></iframe>

            <!-- Campo de comentário -->
            <div class="comentario-box" id="comentarioBox" style="display: none;">
                <h3 id="tituloProjeto">Comentário:</h3>
                <textarea id="comentarioTexto" placeholder="Escreva seu comentário sobre esta aplicação..."></textarea>
                <button onclick="salvarComentario()">Salvar Comentário</button>
                <p id="mensagemSalva" class="mensagem"></p>
            </div>
        </main>
    </div>

    <script>
        function abrirProjeto(pasta, nome) {
            document.getElementById('janela').src = pasta + '/';
            document.getElementById('comentarioBox').style.display = 'block';
            document.getElementById('tituloProjeto').innerText = "Comentário sobre " + nome + ":";
            document.getElementById('comentarioTexto').value = localStorage.getItem("comentario_" + pasta) || "";
            document.getElementById('mensagemSalva').innerText = "";
        }

        function salvarComentario() {
            const iframe = document.getElementById('janela').src;
            if (!iframe) return;

            const pasta = iframe.split('/').slice(-2, -1)[0];
            const comentario = document.getElementById('comentarioTexto').value;
            localStorage.setItem("comentario_" + pasta, comentario);

            const msg = document.getElementById('mensagemSalva');
            msg.innerText = "💾 Comentário salvo!";
            setTimeout(() => msg.innerText = "", 2000);
        }
    </script>
</body>
</html>
