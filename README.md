⚙️ Como Executar o Projeto:

Siga os passos abaixo para rodar o projeto localmente:

1️⃣ Instalar o XAMPP

Baixe em: https://www.apachefriends.org

Inicie Apache e MySQL

2️⃣ Clonar o repositório
git clone https://github.com/phclti911/TCC
cd TCC

3️⃣ Mover para o diretório do XAMPP

Coloque a pasta do projeto dentro de:

C:\xampp\htdocs\

4️⃣ Criar o banco de dados

Acesse:

http://localhost/phpmyadmin


Crie um banco com o nome:

editor_acessivel


Importe o arquivo bd.sql (se existir), normalmente encontrado em /requisito7/.

5️⃣ Ajustar configurações de conexão

Exemplo:

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "editor_acessivel";

6️⃣ Executar

Acesse no navegador:

http://localhost/
