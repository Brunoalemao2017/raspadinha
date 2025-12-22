<?php
require_once 'conexao.php';
try {
    $stmt = $pdo->query("DESCRIBE depositos");
    $structure = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "DEPOSITOS STRUCTURE:\n";
    print_r($structure);

    $stmt = $pdo->query("SELECT * FROM velana LIMIT 1");
    $velana = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "\nVELANA CREDENTIALS (REDACTED):\n";
    if ($velana) {
        echo "API_KEY: " . substr($velana['api_key'], 0, 5) . "...\n";
        echo "SECRET_KEY: " . substr($velana['secret_key'], 0, 5) . "...\n";
    } else {
        echo "No credentials found in 'velana' table.\n";
    }

    $stmt = $pdo->query("SELECT active FROM gateway LIMIT 1");
    echo "\nACTIVE GATEWAY: " . $stmt->fetchColumn() . "\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
?>