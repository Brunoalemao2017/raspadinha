<?php
session_start();
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// CONFIGURAÇÃO DE LOGS
define('DEBUG_MODE', true);
define('LOG_FILE', 'logs_velana.txt');

function writeLog($message)
{
    if (DEBUG_MODE) {
        file_put_contents(LOG_FILE, date('d/m/Y H:i:s') . " - " . $message . PHP_EOL, FILE_APPEND);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

writeLog("PAYLOAD VELANA: " . print_r($data, true));

if (!$data || !isset($data['type']) || $data['type'] !== 'transaction') {
    http_response_code(400);
    echo json_encode(['error' => 'Payload inválido ou tipo incorreto']);
    exit;
}

$txnData = $data['data'] ?? [];
$status = $txnData['status'] ?? '';
$transactionId = $txnData['id'] ?? '';

if ($status !== 'paid' || empty($transactionId)) {
    writeLog("PAGAMENTO NÃO CONFIRMADO OU SEM ID. Status: $status, ID: $transactionId");
    echo json_encode(['message' => 'Aguardando pagamento ou dados insuficientes']);
    exit;
}

require_once __DIR__ . '/../conexao.php';

try {
    $pdo->beginTransaction();

    writeLog("INICIANDO PROCESSO PARA TXN VELANA: " . $transactionId);

    $stmt = $pdo->prepare("SELECT id, user_id, valor, status FROM depositos WHERE transactionId = :txid LIMIT 1 FOR UPDATE");
    $stmt->execute([':txid' => $transactionId]);
    $deposito = $stmt->fetch();

    if (!$deposito) {
        $pdo->commit();
        writeLog("ERRO: Depósito não encontrado para TXN VELANA: " . $transactionId);
        http_response_code(404);
        echo json_encode(['error' => 'Depósito não encontrado']);
        exit;
    }

    if ($deposito['status'] === 'PAID') {
        $pdo->commit();
        echo json_encode(['message' => 'Este pagamento já foi aprovado']);
        exit;
    }

    // Atualiza o status do depósito
    $stmt = $pdo->prepare("UPDATE depositos SET status = 'PAID', updated_at = NOW() WHERE id = :id");
    $stmt->execute([':id' => $deposito['id']]);

    // Credita o saldo do usuário
    $stmt = $pdo->prepare("UPDATE usuarios SET saldo = saldo + :valor WHERE id = :uid");
    $stmt->execute([
        ':valor' => $deposito['valor'],
        ':uid' => $deposito['user_id']
    ]);

    writeLog("SALDO CREDITADO: R$ " . $deposito['valor'] . " para usuário " . $deposito['user_id']);

    // VERIFICAÇÃO PARA CPA (Baseado no modelo ondapay)
    $stmt = $pdo->prepare("SELECT indicacao FROM usuarios WHERE id = :uid");
    $stmt->execute([':uid' => $deposito['user_id']]);
    $usuario = $stmt->fetch();

    if ($usuario && !empty($usuario['indicacao'])) {
        $stmt = $pdo->prepare("SELECT id, comissao_cpa, banido FROM usuarios WHERE id = :afiliado_id");
        $stmt->execute([':afiliado_id' => $usuario['indicacao']]);
        $afiliado = $stmt->fetch();

        if ($afiliado && $afiliado['banido'] != 1 && !empty($afiliado['comissao_cpa'])) {
            $comissao = ($deposito['valor'] * $afiliado['comissao_cpa']) / 100;

            $stmt = $pdo->prepare("UPDATE usuarios SET saldo = saldo + :comissao WHERE id = :afiliado_id");
            $stmt->execute([
                ':comissao' => $comissao,
                ':afiliado_id' => $afiliado['id']
            ]);

            try {
                $stmt = $pdo->prepare("INSERT INTO transacoes_afiliados (afiliado_id, usuario_id, deposito_id, valor, created_at) VALUES (:afiliado_id, :usuario_id, :deposito_id, :valor, NOW())");
                $stmt->execute([
                    ':afiliado_id' => $afiliado['id'],
                    ':usuario_id' => $deposito['user_id'],
                    ':deposito_id' => $deposito['id'],
                    ':valor' => $comissao
                ]);
            } catch (Exception $e) {
            }
        }
    }

    $pdo->commit();
    writeLog("TRANSAÇÃO VELANA FINALIZADA COM SUCESSO");
    echo json_encode(['message' => 'OK']);

} catch (Exception $e) {
    if ($pdo->inTransaction())
        $pdo->rollBack();
    writeLog("ERRO GERAL VELANA: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno']);
}
?>