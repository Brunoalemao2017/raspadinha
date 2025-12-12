<?php
include 'conexao.php';

try {
    // Transforma a coluna gateway em VARCHAR(50) para aceitar qualquer nome (mercadopago, ondapay, etc)
    // Isso resolve o problema de "Data truncated" e evita problemas futuros com novos gateways
    $sql = "ALTER TABLE `depositos` MODIFY COLUMN `gateway` VARCHAR(50) NOT NULL DEFAULT 'ondapay'";

    $pdo->exec($sql);

    echo "<div style='font-family: sans-serif; padding: 20px; background: #dcfce7; color: #166534; border-radius: 8px;'>";
    echo "<h1>✅ Sucesso!</h1>";
    echo "<p>A tabela de depósitos foi corrigida e agora aceita o Mercado Pago.</p>";
    echo "<p>Pode voltar e tentar fazer o PIX novamente.</p>";
    echo "</div>";

} catch (PDOException $e) {
    echo "<div style='font-family: sans-serif; padding: 20px; background: #fee2e2; color: #991b1b; border-radius: 8px;'>";
    echo "<h1>❌ Erro</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}
?>