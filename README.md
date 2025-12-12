# 🎰 Raspadinha - Sistema de Raspadinhas Online

Sistema completo de raspadinhas online com painel administrativo.

## 🚀 Deploy no Railway

### Pré-requisitos
- Conta no [Railway.app](https://railway.app)
- Conta no GitHub (para hospedar o código)

### Passo a Passo

#### 1. Criar Repositório no GitHub
1. Acesse [GitHub](https://github.com) e faça login
2. Clique em "New Repository"
3. Dê um nome (ex: `raspadinha-online`)
4. Deixe como **Private** (recomendado)
5. Clique em "Create Repository"

#### 2. Enviar Código para o GitHub
Execute os comandos abaixo na pasta do projeto:

```bash
git init
git add .
git commit -m "Initial commit"
git branch -M main
git remote add origin https://github.com/SEU_USUARIO/raspadinha-online.git
git push -u origin main
```

#### 3. Configurar Railway

1. Acesse [Railway.app](https://railway.app)
2. Faça login com GitHub
3. Clique em "New Project"
4. Selecione "Deploy from GitHub repo"
5. Escolha o repositório `raspadinha-online`

#### 4. Adicionar Banco de Dados MySQL

1. No projeto Railway, clique em "+ New"
2. Selecione "Database" → "Add MySQL"
3. Aguarde a criação do banco

#### 5. Configurar Variáveis de Ambiente

No Railway, vá em seu serviço PHP e adicione as variáveis:

**Clique em "Variables" e adicione:**

```
DB_HOST=mysql.railway.internal (ou o host fornecido pelo Railway)
DB_NAME=railway
DB_USER=root
DB_PASS=<senha gerada pelo Railway>
DB_PORT=3306
SITE_URL=https://seu-app.up.railway.app
```

**Dica:** O Railway gera automaticamente as credenciais do MySQL. Copie-as da aba "Variables" do serviço MySQL.

#### 6. Importar Banco de Dados

1. Conecte-se ao MySQL do Railway usando um cliente (MySQL Workbench, DBeaver, etc.)
2. Importe seu arquivo `.sql` com a estrutura do banco
3. Ou use o Railway CLI:

```bash
railway login
railway link
railway run mysql -u root -p < seu_banco.sql
```

#### 7. Deploy Automático

O Railway fará o deploy automaticamente! Aguarde alguns minutos.

#### 8. Acessar o Site

Após o deploy, clique em "Settings" → "Generate Domain" para obter sua URL pública.

## 🔧 Desenvolvimento Local

```bash
# Instalar dependências
composer install

# Iniciar servidor local
php -S localhost:8080

# Acessar
http://localhost:8080
```

## 📝 Configuração Local

Edite o arquivo `conexao.php` e configure suas credenciais locais:

```php
$host = '127.0.0.1';
$db   = 'seu_banco';
$user = 'seu_usuario';
$pass = 'sua_senha';
```

## 🛠️ Tecnologias

- PHP 8.2+
- MySQL
- JavaScript/HTML/CSS
- Composer

## 📦 Estrutura do Projeto

```
.
├── admin/          # Painel administrativo
├── api/            # Endpoints da API
├── assets/         # CSS, JS, imagens
├── classes/        # Classes PHP
├── components/     # Componentes reutilizáveis
├── vendor/         # Dependências do Composer
├── index.php       # Página principal
└── conexao.php     # Configuração do banco
```

## 🔒 Segurança

- Nunca commite senhas ou credenciais no Git
- Use variáveis de ambiente em produção
- Mantenha o repositório privado
- Atualize regularmente as dependências

## 📞 Suporte

Desenvolvido por Daanrox
WhatsApp: +55 31 99281-2273

---

**Boa sorte com seu projeto! 🚀**
