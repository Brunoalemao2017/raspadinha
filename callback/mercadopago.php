<?php
// Habilitar display de erros para o log (se possível)
ini_set('log_errors', 1);
ini_set('error_log', 'mp_error_log.txt');

// Definir arquivo de log customizado
$logFile = __DIR__ . '/webhook_log.txt';

function mpLog($msg, $file)
{
    $date = date('d/m/Y H:i:s');
    file_put_contents($file, "[$date] $msg" . PHP_EOL, FILE_APPEND);
}

// Iniciar log
mpLog("--- Início da execução do Webhook ---", $logFile);

// Capturar o corpo da requisição
$json = file_get_contents('php://input');
mpLog("Payload recebido: " . $json, $logFile);

$data = json_decode($json, true);

if (!$data) {
    mpLog("ERRO: JSON inválido ou vazio.", $logFile);
    http_response_code(400);
    exit;
}

require_once '../conexao.php';

// Verificar ID do pagamento
$paymentId = null;
if (isset($data['action']) && strpos($data['action'], 'payment.') !== false) {
    $paymentId = $data['data']['id'];
} elseif (isset($data['type']) && $data['type'] === 'payment') {
    $paymentId = $data['data']['id'];
} elseif (isset($data['id'])) {
    // As vezes o MP manda o ID direto na raiz em testes
    $paymentId = $data['id'];
}

if (!$paymentId) {
    mpLog("AVISO: ID de pagamento não encontrado no payload. Ignorando.", $logFile);
    http_response_code(200);
    exit;
}

mpLog("Payment ID identificado: $paymentId", $logFile);

try {
    // 1. Buscar token
    $stmt = $pdo->query("SELECT access_token FROM mercadopago LIMIT 1");
    $mpConfig = $stmt->fetch();

    if (!$mpConfig) {
        throw new Exception("Configuração MP (access_token) não encontrada no banco.");
    }

    $accessToken = $mpConfig['access_token'];

    // 2. Consultar status na API do MP
    mpLog("Consultando API do Mercado Pago...", $logFile);

    $ch = curl_init("https://api.mercadopago.com/v1/payments/$paymentId");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $accessToken"
    ]);
    // Desabilitar verificação SSL temporariamente para evitar erro de certificado no servidor
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($httpCode != 200) {
        throw new Exception("Erro na API MP. HTTP: $httpCode. Erro CURL: $curlError. Resp: $response");
    }

    $paymentData = json_decode($response, true);
    $status = $paymentData['status'];
    $transactionAmount = $paymentData['transaction_amount'] ?? 0;

    mpLog("Dados da API: Status=$status | Valor=$transactionAmount", $logFile);

    // 3. Buscar depósito no banco
    $stmt = $pdo->prepare("SELECT id, status, user_id, valor, nome FROM depositos WHERE transactionId = ? LIMIT 1");
    $stmt->execute([$paymentId]);
    $deposito = $stmt->fetch();

    if ($deposito) {
        mpLog("Depósito encontrado no banco. ID: {$deposito['id']}, Status Atual: {$deposito['status']}", $logFile);

        if ($status === 'approved' && $deposito['status'] === 'PENDING') {

            $pdo->beginTransaction();

            // Atualiza depósito
            $stmtUpdate = $pdo->prepare("UPDATE depositos SET status = 'PAID', updated_at = NOW() WHERE id = ?");
            $stmtUpdate->execute([$deposito['id']]);

            // Credita saldo
            $stmtSaldo = $pdo->prepare("UPDATE usuarios SET saldo = saldo + ? WHERE id = ?");
            if ($stmtSaldo->execute([$deposito['valor'], $deposito['user_id']])) {
                mpLog("Saldo creditado com sucesso para User ID: {$deposito['user_id']}", $logFile);
            } else {
                mpLog("ERRO ao creditar saldo!", $logFile);
            }

            $pdo->commit();
            mpLog("Transação finalizada com SUCESSO.", $logFile);

        } elseif ($status === 'approved' && $deposito['status'] === 'PAID') {
            mpLog("Pagamento já estava aprovado. Nenhuma ação tomada.", $logFile);
        } else {
            mpLog("Status não requer aprovação (MP: $status, DB: {$deposito['status']}).", $logFile);
        }

    } else {
        mpLog("ERRO CRÍTICO: Depósito com TransactionID $paymentId não encontrado no banco de dados local!", $logFile);
        // Tentar buscar por External Reference se TransactionID falhar? (Implementação futura se necessario)
    }

} catch (Exception $e) {
    mpLog("EXCEÇÃO: " . $e->getMessage(), $logFile);
    http_response_code(500);
    exit;
}

http_response_code(200);
?>