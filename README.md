# Saber Dizer — instalação em servidor próprio

Landing page para `https://institutosaberdizer.com.br/`, com formulário de interesse armazenado em MariaDB local. A página e o botão de WhatsApp continuam disponíveis mesmo se o banco estiver temporariamente indisponível.

**Instalação no EasyPanel:** siga o [guia passo a passo](GUIA-EASYPANEL.md) para criar o MariaDB e o App a partir do GitHub.

## Estrutura

- `public/`: página, estilos, imagens e endpoint do formulário.
- `database/001_leads.sql`: tabela `course_leads`.
- `app/database.php`: conexão PDO com MariaDB por variáveis de ambiente.
- `Dockerfile`: aplicação Apache/PHP, pronta para EasyPanel.
- `compose.yaml`: aplicação e MariaDB isolados para uso com Docker Compose.

O formulário registra nome, WhatsApp, e-mail opcional e a confirmação de contato. O número de WhatsApp no botão atual é `5575991610500`; para alterá-lo, edite as duas URLs `wa.me` em `public/index.html` e publique uma nova versão no GitHub.

## Rodar com Docker Compose

1. Copie `.env.example` para `.env` e substitua **as duas senhas** por valores fortes. Para servir no domínio, ajuste `SITE_ORIGIN=https://institutosaberdizer.com.br`.
2. Rode `docker compose up -d --build`.
3. Localmente, abra `http://localhost:8080`. A porta HTTP fica acessível somente em `127.0.0.1`; publique-a por um proxy HTTPS. O MariaDB **não expõe a porta 3306**.

Na primeira inicialização de um volume vazio, a imagem MariaDB cria banco/usuário e executa `database/001_leads.sql`. Em volumes existentes, o script de inicialização não é repetido. Nesse caso, aplique a migração manualmente:

```bash
docker compose exec web php /var/www/bin/migrate.php
```

Um backup do volume `db_data` deve ser feito antes de atualizações do banco.

## Publicar pelo EasyPanel na VPS

1. Conecte o repositório `yeshmedia/saberdizer` ao EasyPanel como serviço **App**, selecionando o `Dockerfile` da raiz e a porta interna **80**.
2. Use um serviço MariaDB local da VPS/EasyPanel na mesma rede privada do App. Pode ser uma instância separada para este site; não é necessário alterar o banco de outros projetos.
3. Crie o banco `saberdizer` com `utf8mb4`, aplique `database/001_leads.sql` como administrador do MariaDB e crie um usuário de aplicação com `SELECT` e `INSERT` na tabela. O usuário que executa `bin/migrate.php` precisa temporariamente da permissão `CREATE` se a tabela ainda não existir.
4. Configure no App as variáveis abaixo. **Não grave senhas no GitHub.**

| Variável | Valor esperado |
| --- | --- |
| `DB_HOST` | Hostname privado do MariaDB acessível ao contêiner |
| `DB_PORT` | `3306` |
| `DB_NAME` | `saberdizer` |
| `DB_USER` | Usuário restrito do site |
| `DB_PASSWORD` | Senha desse usuário |
| `SITE_ORIGIN` | `https://institutosaberdizer.com.br` |

5. Aponte o registro **A** de `institutosaberdizer.com.br` para o IPv4 da VPS. Configure `www` como alias e redirecione-o para o domínio sem `www` no proxy/EasyPanel. Ative o certificado TLS no EasyPanel. Se usar Cloudflare, configure o SSL de origem apropriadamente e mantenha o acesso ao MariaDB restrito à rede local.
6. Ative a atualização automática por push na branch principal no EasyPanel, se essa opção estiver disponível. Assim, mudanças publicadas no GitHub entram em uma nova implantação. Confirme a primeira publicação antes de depender da atualização automática.

Se o MariaDB já está instalado diretamente no host, confira o endereço que o contêiner App consegue alcançar; `localhost` dentro do App aponta para o próprio contêiner, não para o host. Mantenha a conexão protegida na rede privada da VPS.

## Verificação após publicar

- Abra `https://institutosaberdizer.com.br/` no desktop e no celular. Confirme logo, retrato, capa do vídeo, navegação e botões.
- Envie o formulário com um contato autorizado e confirme o retorno de sucesso.
- Consulte `SELECT id, full_name, whatsapp, email, created_at FROM course_leads ORDER BY id DESC LIMIT 20;` no MariaDB.
- Confira se `https://www.institutosaberdizer.com.br/` redireciona para o endereço principal e se o certificado HTTPS está válido.
- O vídeo institucional permanece com capa e aviso até ser fornecido o arquivo ou URL do vídeo.

O endpoint do formulário valida os dados, usa consulta preparada, campo oculto contra submissões automatizadas simples e não expõe erros internos ao visitante. Para uma campanha de alto volume, configure proteção adicional contra spam no proxy/Cloudflare.
