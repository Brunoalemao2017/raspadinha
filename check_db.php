<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Diagnóstico de Conexão</h1>";

echo "<h2>Variáveis de Ambiente Detectadas:</h2>";
echo "DB_HOST: " . (getenv('DB_HOST') ? 'OK (' . getenv('DB_HOST') . ')' : '<b style="color:red">FALTANDO</b>') . "<br>";
echo "DB_NAME: " . (getenv('DB_NAME') ? 'OK (' . getenv('DB_NAME') . ')' : '<b style="color:red">FALTANDO</b>') . "<br>";
echo "DB_USER: " . (getenv('DB_USER') ? 'OK (' . getenv('DB_USER') . ')' : '<b style="color:red">FALTANDO</b>') . "<br>";
echo "DB_PORT: " . (getenv('DB_PORT') ? 'OK (' . getenv('DB_PORT') . ')' : '<b style="color:red">FALTANDO</b>') . "<br>";
echo "DB_PASS: " . (getenv('DB_PASS') ? 'OK (******)' : '<b style="color:red">FALTANDO</b>') . "<br>";

echo "<h2>Tentando Conexão...</h2>";

$host = getenv('DB_HOST');
$db = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');
$port = getenv('DB_PORT') ?: 3306;
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "<h3 style='color:green'>SUCESSO! Conexão realizada.</h3>";

    // Teste tabela
    try {
        $stmt = $pdo->query("SELECT count(*) FROM config");
        echo "Tabela 'config' encontrada.";
    } catch (Exception $e) {
        echo "Conectou, mas a tabela 'config' não existe. Você precisa rodar o importador.";
    }

} catch (\PDOException $e) {
    echo "<h3 style='color:red'>FALHA NA CONEXÃO:</h3>";
    echo "<strong>Erro:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>Código:</strong> " . $e->getCode();
}
