<?php
// Configuração do banco de dados usando variáveis de ambiente
// No Railway, você definirá essas variáveis no painel

// Verifica se estamos em produção (Railway) ou desenvolvimento local
$isProduction = isset($_ENV['RAILWAY_ENVIRONMENT']) || isset($_SERVER['RAILWAY_ENVIRONMENT']);

if ($isProduction) {
    // Configuração para Railway (produção)
    $host = getenv('DB_HOST') ?: $_ENV['DB_HOST'] ?? '127.0.0.1';
    $db   = getenv('DB_NAME') ?: $_ENV['DB_NAME'] ?? 'railway';
    $user = getenv('DB_USER') ?: $_ENV['DB_USER'] ?? 'root';
    $pass = getenv('DB_PASS') ?: $_ENV['DB_PASS'] ?? '';
    $port = getenv('DB_PORT') ?: $_ENV['DB_PORT'] ?? '3306';
} else {
    // Configuração local (desenvolvimento)
    $host = '127.0.0.1';
    $db   = 'seu_banco'; // Substitua pelo nome do seu banco de dados local
    $user = 'seu_usuario'; // Substitua pelo seu usuário do banco de dados local
    $pass = 'sua_senha'; // Substitua pela sua senha do banco de dados local
    $port = '3306';
}

$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     // Em produção, não mostre detalhes do erro
     if ($isProduction) {
         error_log("Erro de conexão com banco de dados: " . $e->getMessage());
         die("Erro ao conectar com o banco de dados. Por favor, tente novamente mais tarde.");
     } else {
         throw new \PDOException($e->getMessage(), (int)$e->getCode());
     }
}

// Busca configurações do site
try {
    $site = $pdo->query("SELECT nome_site, logo, deposito_min, saque_min, cpa_padrao, revshare_padrao FROM config LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    $nomeSite = $site['nome_site'] ?? 'RaspaGreen'; 
    $logoSite = $site['logo'] ?? '';
    $depositoMin = $site['deposito_min'] ?? 10;
    $saqueMin = $site['saque_min'] ?? 50;
    $cpaPadrao = $site['cpa_padrao'] ?? 10;
    $revshare_padrao = $site['revshare_padrao'] ?? 10;
    
    // URL do site
    if ($isProduction) {
        $urlSite = getenv('SITE_URL') ?: $_ENV['SITE_URL'] ?? 'https://seu-app.up.railway.app';
    } else {
        $urlSite = 'http://localhost:8080';
    }
} catch (\PDOException $e) {
    // Valores padrão se a tabela config não existir ainda
    $nomeSite = 'RaspaGreen';
    $logoSite = '';
    $depositoMin = 10;
    $saqueMin = 50;
    $cpaPadrao = 10;
    $revshare_padrao = 10;
    $urlSite = $isProduction ? 'https://seu-app.up.railway.app' : 'http://localhost:8080';
}