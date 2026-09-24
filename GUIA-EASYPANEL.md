# Instalar o Saber Dizer no EasyPanel

Este guia publica o repositório `yeshmedia/saberdizer` como um serviço **App** e guarda os contatos em um serviço **MariaDB** no mesmo projeto. O domínio principal será `institutosaberdizer.com.br`.

## 1. Crie o banco

1. No EasyPanel, crie um projeto, por exemplo `saberdizer`, ou abra um projeto existente.
2. Clique em **New Service → MariaDB**. Nome sugerido: `saberdizer-db`.
3. Informe o nome do banco `saberdizer`; deixe o EasyPanel gerar uma senha forte para o usuário ou defina uma senha forte no painel.
4. Crie o serviço e espere ele iniciar. Em **Credentials**, anote o **host interno**, a porta, o nome do banco, o usuário e a senha. Use os valores reais mostrados pelo painel; o nome do serviço não é necessariamente o host de conexão.
5. Mantenha o banco na rede privada. Não use **Expose** para publicar a porta 3306 na internet.

O EasyPanel oferece um serviço MariaDB persistente, com endereço interno e credenciais na aba **Credentials**. Serviços do mesmo projeto usam a rede privada entre si. [Documentação do MariaDB](https://easypanel.io/docs/services/mariadb).

## 2. Crie o App a partir do GitHub

1. No **mesmo projeto** do banco, clique em **New Service → App**. Nome sugerido: `saberdizer-web`.
2. Em **Source**, escolha **GitHub** e informe `yeshmedia/saberdizer`.
3. Escolha a branch `main` e **Build Path** `/` (raiz do repositório).
4. Em **Build**, escolha **Dockerfile** e informe o caminho `Dockerfile`. Não selecione o tipo de fonte **Dockerfile inline**, pois ele não inclui os arquivos do GitHub.

O repositório está público e, por isso, o EasyPanel pode lê-lo sem token do GitHub. O Dockerfile deste projeto instala PHP com MariaDB e serve a página na porta interna **80**. [Documentação do App](https://easypanel.io/docs/services/app) e [do builder](https://easypanel.io/docs/builders).

## 3. Configure as variáveis do App

Em **Environment**, cole as linhas abaixo, substituindo os valores entre `<...>` pelas credenciais exibidas em **MariaDB → Credentials**:

```dotenv
DB_HOST=<host-interno-do-mariadb>
DB_PORT=3306
DB_NAME=saberdizer
DB_USER=<usuario-do-mariadb>
DB_PASSWORD=<senha-do-usuario-do-mariadb>
SITE_ORIGIN=https://institutosaberdizer.com.br
```

Se criou o banco com nome diferente, use esse nome em `DB_NAME`. Não copie `HTTP_PORT` nem `MARIADB_ROOT_PASSWORD` do `.env.example`: esses valores são para o modo Docker Compose, não para o serviço **App** do EasyPanel. Não envie a senha pelo chat nem a grave no GitHub.

Salve as variáveis e clique em **Deploy** no App. Salvar a configuração, sozinho, não atualiza o contêiner em execução. Confira o resultado em **Deployments** e, caso a compilação conclua mas o serviço não inicie, consulte **Logs**.

## 4. Crie a tabela dos contatos

Depois que o App iniciar, abra **App → Shell** e execute:

```bash
php /var/www/bin/migrate.php
```

O resultado esperado é `Migration complete.`. O script cria a tabela `course_leads` somente se ela ainda não existir. Se aparecer **access denied**, confira usuário/senha e as permissões do usuário no banco. Se aparecer erro de conexão, confirme o host interno e que os serviços estão no mesmo projeto. Caso o usuário não tenha permissão para criar tabelas, execute o conteúdo de `database/001_leads.sql` no cliente MariaDB do serviço com uma conta administradora.

## 5. Configure o domínio

1. No provedor que administra o DNS do domínio, crie ou ajuste o registro **A** da raiz `institutosaberdizer.com.br` para o IPv4 público da VPS. Se desejar usar `www`, crie um **CNAME** de `www` para `institutosaberdizer.com.br` (ou um A para o mesmo IP).
2. Em **App → Domains**, adicione `institutosaberdizer.com.br`, caminho `/`, protocolo interno **HTTP** e porta de destino **80**. Marque como domínio principal e habilite **HTTPS** e o resolvedor de certificado do painel.
3. Se configurou `www`, adicione esse nome ao painel e redirecione-o para `https://institutosaberdizer.com.br/`.
4. Confirme que a VPS recebe tráfego externo nas portas **80 e 443**. Espere o DNS apontar para a VPS e o certificado ser emitido antes de testar HTTPS.

O App recebe HTTP internamente na porta 80; o EasyPanel termina HTTPS e encaminha o tráfego ao serviço. Não é necessário publicar outra porta na aba **Advanced → Ports**. [Documentação de domínios do App](https://easypanel.io/docs/services/app#domains).

## 6. Teste e mantenha

- Abra `https://institutosaberdizer.com.br/` no computador e no celular e confira imagens, botões e WhatsApp.
- Envie um contato de teste com consentimento pelo formulário. No cliente MariaDB do EasyPanel, confira `SELECT id, full_name, whatsapp, created_at FROM course_leads ORDER BY id DESC LIMIT 5;`.
- Configure backup do MariaDB em **Backups** e teste uma cópia manual.
- Para publicar futuras alterações, faça **Deploy** no App. Se configurar **Auto Deploy** para a fonte GitHub, as alterações na branch `main` podem iniciar uma nova implantação automaticamente.

O arquivo `compose.yaml` é uma alternativa para rodar o projeto diretamente com Docker Compose; ao seguir este guia com serviços **App + MariaDB** no EasyPanel, não crie um serviço Compose adicional.
