<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

// sleep(2); // Removido para evitar timeout e erros de buffer

$amount = isset($_POST['amount']) ? floatval(str_replace(',', '.', $_POST['amount'])) : 0;
$cpf = isset($_POST['cpf']) ? preg_replace('/\D/', '', $_POST['cpf']) : '';

if ($amount <= 0 || strlen($cpf) !== 11) {
    http_response_code(400);
    echo json_encode(['error' => 'Dados inválidos']);
    exit;
}

require_once __DIR__ . '/../conexao.php';

try {
    // Verificar gateway ativo
    $stmt = $pdo->query("SELECT active FROM gateway LIMIT 1");
    $activeGateway = $stmt->fetchColumn();

    if (!in_array($activeGateway, ['ondapay', 'mercadopago', 'velana'])) {
        throw new Exception('Gateway não configurado ou não suportado.');
    }

    // Verificar autenticação do usuário
    if (!isset($_SESSION['usuario_id'])) {
        throw new Exception('Usuário não autenticado.');
    }

    $usuario_id = $_SESSION['usuario_id'];

    // Buscar dados do usuário
    $stmt = $pdo->prepare("SELECT nome, email FROM usuarios WHERE id = :id LIMIT 1");
    $stmt->bindParam(':id', $usuario_id, PDO::PARAM_INT);
    $stmt->execute();
    $usuario = $stmt->fetch();

    if (!$usuario) {
        throw new Exception('Usuário não encontrado.');
    }

    // Configurar URLs base
    // Configurar URLs base (Forçar HTTPS no Railway/Produção)
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https://" : "http://";
    if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
        $protocol = "https://";
    }
    // Se estiver no Railway (.app), força HTTPS por segurança
    if (strpos($_SERVER['HTTP_HOST'], 'railway.app') !== false) {
        $protocol = "https://";
    }

    $host = $_SERVER['HTTP_HOST'];
    $base = $protocol . $host;

    $external_id = uniqid();
    $idempotencyKey = uniqid() . '-' . time();

    if ($activeGateway === 'ondapay') {
        // ===== PROCESSAR COM OndaPay =====
        $stmt = $pdo->query("SELECT url, client_id, client_secret FROM ondapay LIMIT 1");
        $ondapay = $stmt->fetch();

        if (!$ondapay) {
            throw new Exception('Credenciais OndaPay não encontradas.');
        }

        $url = rtrim($ondapay['url'], '/');
        $ci = $ondapay['client_id'];
        $cs = $ondapay['client_secret'];

        $ch = curl_init("$url/api/v1/login");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                "client_id: $ci",
                "client_secret: $cs",
            ]
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        $authData = json_decode($response, true);
        if (!isset($authData['token'])) {
            throw new Exception('Falha ao obter access_token da OndaPay.');
        }

        $accessToken = $authData['token'];
        $postbackUrl = $base . '/callback/ondapay.php';

        $payload = [
            'amount' => (float) $amount,
            'external_id' => $external_id,
            'webhook' => $postbackUrl,
            'description' => 'Pagamento Raspadinha',
            'payer' => [
                'name' => $usuario['nome'],
                'document' => $cpf,
                'email' => $usuario['email']
            ],
            'dueDate' => date('Y-m-d H:i:s', strtotime('+1 day'))
        ];

        $ch = curl_init("$url/api/v1/deposit/pix");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer $accessToken",
                "Content-Type: application/json"
            ]
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        $pixData = json_decode($response, true);

        if (!isset($pixData['id_transaction'], $pixData['qrcode'])) {
            // Include api error message if available
            $apiError = isset($pixData['message']) ? $pixData['message'] : json_encode($pixData);
            throw new Exception('Falha ao gerar QR Code. OndaPay disse: ' . $apiError);
        }

        // Salvar no banco
        $stmt = $pdo->prepare("
            INSERT INTO depositos (transactionId, user_id, nome, cpf, valor, status, qrcode, gateway, idempotency_key)
            VALUES (:transactionId, :user_id, :nome, :cpf, :valor, 'PENDING', :qrcode, 'ondapay', :idempotency_key)
        ");

        $stmt->execute([
            ':transactionId' => $pixData['id_transaction'],
            ':user_id' => $usuario_id,
            ':nome' => $usuario['nome'],
            ':cpf' => $cpf,
            ':valor' => $amount,
            ':qrcode' => $pixData['qrcode'],
            ':idempotency_key' => $external_id
        ]);

        $_SESSION['transactionId'] = $pixData['id_transaction'];

        echo json_encode([
            'qrcode' => $pixData['qrcode'],
            'gateway' => 'ondapay'
        ]);

    } elseif ($activeGateway === 'mercadopago') {
        // ===== PROCESSAR COM Mercado Pago =====
        $stmt = $pdo->query("SELECT access_token FROM mercadopago LIMIT 1");
        $mpConfig = $stmt->fetch();

        if (!$mpConfig) {
            throw new Exception('Credenciais Mercado Pago não encontradas.');
        }

        $postbackUrl = $base . '/callback/mercadopago.php';

        $payload = [
            "transaction_amount" => (float) $amount,
            "description" => "Depósito " . $usuario['nome'],
            "payment_method_id" => "pix",
            "payer" => [
                "email" => $usuario['email'],
                "first_name" => explode(' ', $usuario['nome'])[0],
                "identification" => [
                    "type" => "CPF",
                    "number" => $cpf
                ]
            ],
            "notification_url" => $postbackUrl
        ];

        $ch = curl_init("https://api.mercadopago.com/v1/payments");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer " . $mpConfig['access_token'],
                "Content-Type: application/json",
                "X-Idempotency-Key: " . $idempotencyKey
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $mpData = json_decode($response, true);

        if ($httpCode !== 201 || !isset($mpData['id'], $mpData['point_of_interaction']['transaction_data']['qr_code'])) {
            $msg = $mpData['message'] ?? 'Erro ao criar PIX no Mercado Pago.';
            throw new Exception($msg);
        }

        $pixCopiaCola = $mpData['point_of_interaction']['transaction_data']['qr_code'];
        $transactionId = $mpData['id'];

        // Salvar no banco
        $stmt = $pdo->prepare("
            INSERT INTO depositos (transactionId, user_id, nome, cpf, valor, status, qrcode, gateway, idempotency_key)
            VALUES (:transactionId, :user_id, :nome, :cpf, :valor, 'PENDING', :qrcode, 'mercadopago', :idempotency_key)
        ");

        $stmt->execute([
            ':transactionId' => $transactionId,
            ':user_id' => $usuario_id,
            ':nome' => $usuario['nome'],
            ':cpf' => $cpf,
            ':valor' => $amount,
            ':qrcode' => $pixCopiaCola,
            ':idempotency_key' => $idempotencyKey
        ]);

        $_SESSION['transactionId'] = $transactionId;

        echo json_encode([
            'qrcode' => $pixCopiaCola,
            'gateway' => 'mercadopago'
        ]);

    } elseif ($activeGateway === 'velana') {
        // ===== PROCESSAR COM Velana =====
        $stmt = $pdo->query("SELECT api_key, secret_key FROM velana LIMIT 1");
        $velana = $stmt->fetch();

        if (!$velana) {
            throw new Exception('Credenciais Velana não encontradas.');
        }

        $postbackUrl = $base . '/callback/velana.php';

        $payload = [
            'amount' => (int) ($amount * 100), // Em centavos
            'paymentMethod' => 'pix',
            'postbackUrl' => $postbackUrl,
            'customer' => [
                'name' => $usuario['nome'],
                'email' => $usuario['email'],
                'document' => $cpf,
                'phone' => '11999999999' // Campo obrigatório pela Velana, usando fixo ou do banco se existir
            ]
        ];

        // A autenticação da Velana usa a SECRET_KEY com ":x" no final antes do base64
        $auth = base64_encode($velana['secret_key'] . ':x');

        $ch = curl_init("https://api.velana.com.br/v1/transactions");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                "Authorization: Basic $auth",
                "Content-Type: application/json"
            ]
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $pixData = json_decode($response, true);

        if ($httpCode !== 200 || !isset($pixData['id']) || !isset($pixData['pix']['qrcode'])) {
            $apiError = isset($pixData['message']) ? $pixData['message'] : 'Erro Velana: ' . $response;
            throw new Exception('Falha ao gerar PIX Velana. ' . $apiError);
        }

        $transactionId = $pixData['id'];
        $qrcode = $pixData['pix']['qrcode'];

        // Salvar no banco
        $stmt = $pdo->prepare("
            INSERT INTO depositos (transactionId, user_id, nome, cpf, valor, status, qrcode, gateway, idempotency_key)
            VALUES (:transactionId, :user_id, :nome, :cpf, :valor, 'PENDING', :qrcode, 'velana', :idempotency_key)
        ");

        $stmt->execute([
            ':transactionId' => $transactionId,
            ':user_id' => $usuario_id,
            ':nome' => $usuario['nome'],
            ':cpf' => $cpf,
            ':valor' => $amount,
            ':qrcode' => $qrcode,
            ':idempotency_key' => $external_id
        ]);

        $_SESSION['transactionId'] = $transactionId;

        echo json_encode([
            'qrcode' => $qrcode,
            'gateway' => 'velana'
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}
?>