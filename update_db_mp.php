<?php
include 'conexao.php';

try {
    // 1. Criar tabela mercadopago
    $sql = "CREATE TABLE IF NOT EXISTS `mercadopago` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `access_token` varchar(255) NOT NULL,
      `public_key` varchar(255) NOT NULL,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

    $pdo->exec($sql);
    echo "Tabela 'mercadopago' criada ou já existente.<br>";

    // 2. Inserir dados padrão se vazio
    $stmt = $pdo->query("SELECT COUNT(*) FROM mercadopago");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO `mercadopago` (`access_token`, `public_key`) VALUES ('APP_USR-xxxx', 'TEST-xxxx')");
        echo "Dados padrão inseridos em 'mercadopago'.<br>";
    }

    // 3. Alterar coluna 'active' da tabela 'gateway' para aceitar 'mercadopago'
    // Verifica se a tabela gateway existe e altera o ENUM
    try {
        $pdo->exec("ALTER TABLE `gateway` MODIFY COLUMN `active` ENUM('ondapay', 'mercadopago') DEFAULT 'ondapay'");
        echo "Coluna 'active' da tabela 'gateway' atualizada para suportar 'mercadopago'.<br>";
    } catch (PDOException $e) {
        // Se der erro, pode ser que não seja enum ou outro problema, mas tentamos
        echo "Aviso ao alterar tabela gateway (talvez já suporte ou não seja ENUM): " . $e->getMessage() . "<br>";
    }

    echo "<h1>Sucesso! Banco de dados atualizado para Mercado Pago.</h1>";
    echo "<p>Agora delete este arquivo e vá para o painel admin configurar.</p>";

} catch (PDOException $e) {
    echo "<h1>Erro:</h1>" . $e->getMessage();
}
?>