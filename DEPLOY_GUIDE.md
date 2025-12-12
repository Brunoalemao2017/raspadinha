# 🚀 GUIA RÁPIDO - Deploy no Railway

## ✅ Preparação Concluída!

Seu projeto já está pronto para o deploy! Os seguintes arquivos foram criados:

- ✅ `railway.json` - Configuração do Railway
- ✅ `nixpacks.toml` - Configuração do PHP 8.2
- ✅ `.gitignore` - Arquivos a ignorar
- ✅ `conexao.php` - Atualizado para usar variáveis de ambiente
- ✅ Git inicializado e commit feito

---

## 📝 PRÓXIMOS PASSOS

### 1️⃣ Criar Repositório no GitHub

1. Acesse: https://github.com/new
2. Nome do repositório: `raspadinha-online` (ou outro nome)
3. **IMPORTANTE:** Deixe como **Private** (privado)
4. **NÃO** marque "Initialize with README"
5. Clique em "Create repository"

### 2️⃣ Conectar seu Código ao GitHub

Copie e cole estes comandos no terminal (substitua SEU_USUARIO pelo seu usuário do GitHub):

```bash
git branch -M main
git remote add origin https://github.com/SEU_USUARIO/raspadinha-online.git
git push -u origin main
```

**Exemplo:**
Se seu usuário do GitHub é "joaosilva", use:
```bash
git remote add origin https://github.com/joaosilva/raspadinha-online.git
```

### 3️⃣ Criar Conta no Railway

1. Acesse: https://railway.app
2. Clique em "Login"
3. Escolha "Login with GitHub"
4. Autorize o Railway a acessar seus repositórios

### 4️⃣ Criar Novo Projeto no Railway

1. No Railway, clique em "New Project"
2. Selecione "Deploy from GitHub repo"
3. Escolha o repositório `raspadinha-online`
4. Aguarde o Railway detectar automaticamente que é PHP

### 5️⃣ Adicionar Banco de Dados MySQL

1. No seu projeto Railway, clique em "+ New"
2. Selecione "Database"
3. Escolha "Add MySQL"
4. Aguarde a criação (leva ~1 minuto)

### 6️⃣ Configurar Variáveis de Ambiente

1. Clique no serviço do seu app PHP (não no MySQL)
2. Vá na aba "Variables"
3. Clique em "+ New Variable"
4. Adicione as seguintes variáveis:

**IMPORTANTE:** Copie os valores do MySQL que o Railway criou!

```
DB_HOST = mysql.railway.internal
DB_NAME = railway
DB_USER = root
DB_PASS = [copie a senha do MySQL Railway]
DB_PORT = 3306
```

**Como encontrar a senha do MySQL:**
- Clique no serviço MySQL
- Vá em "Variables"
- Copie o valor de `MYSQL_ROOT_PASSWORD`

### 7️⃣ Importar Banco de Dados

Você precisa importar a estrutura do seu banco. Duas opções:

**Opção A - Usando Railway CLI (Recomendado):**
```bash
# Instalar Railway CLI
npm i -g @railway/cli

# Fazer login
railway login

# Conectar ao projeto
railway link

# Importar banco (substitua pelo seu arquivo .sql)
railway run mysql -h mysql.railway.internal -u root -p railway < seu_banco.sql
```

**Opção B - Usando MySQL Workbench ou DBeaver:**
1. No Railway, clique no MySQL
2. Vá em "Connect"
3. Copie as credenciais
4. Use um cliente MySQL para conectar
5. Importe seu arquivo .sql

### 8️⃣ Gerar Domínio Público

1. Clique no serviço do seu app PHP
2. Vá em "Settings"
3. Role até "Networking"
4. Clique em "Generate Domain"
5. Aguarde alguns segundos
6. Seu site estará disponível em: `https://seu-app.up.railway.app`

---

## 🎉 PRONTO!

Seu site estará no ar! Acesse a URL gerada pelo Railway.

---

## 🔧 Comandos Git Úteis

```bash
# Ver status dos arquivos
git status

# Adicionar novos arquivos
git add .

# Fazer commit
git commit -m "Descrição das mudanças"

# Enviar para GitHub
git push

# Ver histórico
git log --oneline
```

---

## ❓ Problemas Comuns

### Erro de conexão com banco
- Verifique se as variáveis de ambiente estão corretas
- Confirme que o MySQL está rodando no Railway
- Use `mysql.railway.internal` como host

### Site não carrega
- Verifique os logs no Railway (aba "Deployments")
- Confirme que o domínio foi gerado
- Aguarde alguns minutos após o deploy

### Erro 500
- Verifique se o banco foi importado
- Confira os logs de erro no Railway
- Certifique-se que a tabela `config` existe

---

## 📞 Precisa de Ajuda?

Se tiver dúvidas, me avise! Estou aqui para ajudar! 🚀

---

**Desenvolvido com ❤️**
