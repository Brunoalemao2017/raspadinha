<?php
// setup_database.php - Script para importar banco de dados
// APAGUE ESTE ARQUIVO APÓS O USO POR SEGURANÇA!

include 'conexao.php';

echo "<h1>Importador de Banco de Dados</h1>";

// Verifica a senha para evitar execução acidental (opcional, mas recomendado)
if (!isset($_GET['run']) || $_GET['run'] != 'sim') {
    die("Para executar, acesse com ?run=sim");
}

try {
    // Lê o arquivo SQL
    $sqlFile = 'database.sql';
    if (!file_exists($sqlFile)) {
        die("Arquivo $sqlFile não encontrado!");
    }

    $sql = file_get_contents($sqlFile);

    // Separa as queries (suporte básico para scripts SQL)
    // Nota: Isso é um parser simples, pode falhar com procedures complexas
    $queries = explode(';', $sql);

    $count = 0;
    foreach ($queries as $query) {
        $query = trim($query);
        if (empty($query))
            continue;

        try {
            $pdo->exec($query);
            $count++;
        } catch (PDOException $e) {
            echo "<p style='color:red'>Erro na query: " . substr($query, 0, 100) . "... <br>Erro: " . $e->getMessage() . "</p>";
        }
    }

    echo "<h2>Sucesso!</h2>";
    echo "<p>$count instruções SQL executadas.</p>";
    echo "<p>Agora você pode acessar o site principal.</p>";
    echo "<p><strong>IMPORTANTE: Delete este arquivo do repositório agora!</strong></p>";

} catch (Exception $e) {
    echo "<h1>Erro Fatal</h1>";
    echo $e->getMessage();
}
