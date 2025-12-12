<?php
include '../includes/session.php';
include '../conexao.php';

// Apenas admin
if (!isset($_SESSION['usuario_id'])) {
    die('Acesso negado');
}
$uid = $_SESSION['usuario_id'];
$stmt = $pdo->prepare("SELECT admin FROM usuarios WHERE id = ?");
$stmt->execute([$uid]);
if ($stmt->fetchColumn() != 1) {
    die('Acesso negado');
}

$logFile = __DIR__ . '/../callback/webhook_log.txt';
$logContent = file_exists($logFile) ? file_get_contents($logFile) : "Nenhum log encontrado ainda.";
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Logs Mercado Pago</title>
    <style>
        body {
            background: #111;
            color: #eee;
            font-family: monospace;
            padding: 20px;
        }

        h1 {
            color: #fed000;
        }

        .log-box {
            background: #222;
            border: 1px solid #444;
            padding: 15px;
            border-radius: 8px;
            white-space: pre-wrap;
            height: 80vh;
            overflow-y: auto;
        }

        .refresh {
            margin-bottom: 20px;
            padding: 10px 20px;
            background: #22c55e;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <h1>Logs do Webhook Mercado Pago</h1>
    <button class="refresh" onclick="location.reload()">Atualizar Logs</button>
    <div class="log-box"><?= htmlspecialchars($logContent) ?></div>

    <script>
        // Auto scroll to bottom
        const box = document.querySelector('.log-box');
        box.scrollTop = box.scrollHeight;
    </script>
</body>

</html>