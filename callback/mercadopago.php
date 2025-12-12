<?php
require_once '../conexao.php';

// Pegar POST body
$json = file_get_contents('php://input');
$data = json_decode($json, true);
$logFile = 'mp_logs.txt';

// Log opcional
// file_put_contents($logFile, date('Y-m-d H:i:s') . " - Recebido: " . $json . "\n", FILE_APPEND);

// Verificar formato do Webhook (V1)
// Mercado Pago geralmente manda action="payment.created" ou "payment.updated" e data.id
if (isset($data['action']) && strpos($data['action'], 'payment.') !== false) {
    $paymentId = $data['data']['id'];
} elseif (isset($data['type']) && $data['type'] === 'payment') {
    $paymentId = $data['data']['id'];
} else {
    // Pode ser notificação de teste ou tópico irrelevante
    http_response_code(200);
    exit;
}

try {
    // 1. Buscar Access Token configurado
    $stmt = $pdo->query("SELECT access_token FROM mercadopago LIMIT 1");
    $mpConfig = $stmt->fetch();

    if (!$mpConfig) {
        throw new Exception("Configuração MP não encontrada.");
    }

    $accessToken = $mpConfig['access_token'];

    // 2. Consultar status real na API do Mercado Pago
    $ch = curl_init("https://api.mercadopago.com/v1/payments/$paymentId");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $accessToken"
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode != 200) {
        throw new Exception("Erro API MP ($httpCode): $response");
    }

    $paymentData = json_decode($response, true);
    $status = $paymentData['status']; // 'approved', 'pending', etc.
    // $paidAmount = $paymentData['transaction_amount'];

    // 3. Buscar o depósito no nosso banco pelo ID da transação
    $stmt = $pdo->prepare("SELECT id, status, user_id, valor, nome FROM depositos WHERE transactionId = ? LIMIT 1");
    $stmt->execute([$paymentId]);
    $deposito = $stmt->fetch();

    if ($deposito) {
        // Se status no MP for approved E no nosso banco estiver pendente -> Aprovar
        if ($status === 'approved' && $deposito['status'] === 'PENDING') {

            $pdo->beginTransaction();

            // Atualiza status do depósito
            $stmtUpdate = $pdo->prepare("UPDATE depositos SET status = 'PAID', updated_at = NOW() WHERE id = ?");
            $stmtUpdate->execute([$deposito['id']]);

            // Adiciona saldo ao usuário
            $stmtSaldo = $pdo->prepare("UPDATE usuarios SET saldo = saldo + ? WHERE id = ?");
            $stmtSaldo->execute([$deposito['valor'], $deposito['user_id']]);

            // Opcional: Registrar movimentação financeira ou bônus CPA/RevShare aqui se o sistema tiver

            $pdo->commit();

            // file_put_contents($logFile, "SUCESSO: Deposito #{$deposito['id']} (Usuario {$deposito['user_id']}) aprovado!\n", FILE_APPEND);
        }

        // Se foi rejeitado ou cancelado
        elseif (($status === 'cancelled' || $status === 'rejected') && $deposito['status'] !== 'PAID') {
            // Poderia marcar como falha, mas geralmente mantém pendente ou deleta
            // file_put_contents($logFile, "CANCELADO: Deposito #{$deposito['id']} status MP: $status\n", FILE_APPEND);
        }

    } else {
        // Depósito não encontrado (talvez criado agora e o webhook chegou antes do insert? raro)
        // file_put_contents($logFile, "ERRO: Deposito $paymentId não encontrado no banco.\n", FILE_APPEND);
    }

} catch (Exception $e) {
    // file_put_contents($logFile, "EXCECAO: " . $e->getMessage() . "\n", FILE_APPEND);
    http_response_code(500); // Indicar erro pro MP tentar de novo (opcional, cuidado com loops)
    exit;
}

http_response_code(200);
?>