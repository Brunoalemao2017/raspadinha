<?php
include 'conexao.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $novaSenha = $_POST['senha'];

    if (empty($email) || empty($novaSenha)) {
        $message = '<div style="color: red; padding: 10px; background: #fee2e2; border-radius: 5px; margin-bottom: 20px;">Por favor, preencha todos os campos.</div>';
    } else {
        // Verifica se usuário existe
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            // Gera hash seguro
            $hash = password_hash($novaSenha, PASSWORD_DEFAULT);

            // Atualiza senha e garante que é admin
            $stmtUpdate = $pdo->prepare("UPDATE usuarios SET senha = ?, admin = 1 WHERE email = ?");
            if ($stmtUpdate->execute([$hash, $email])) {
                $message = '<div style="color: green; padding: 10px; background: #dcfce7; border-radius: 5px; margin-bottom: 20px;">✅ Senha atualizada com sucesso! Agora você pode fazer login.</div>';
            } else {
                $message = '<div style="color: red; padding: 10px; background: #fee2e2; border-radius: 5px; margin-bottom: 20px;">Erro ao atualizar senha.</div>';
            }
        } else {
            $message = '<div style="color: red; padding: 10px; background: #fee2e2; border-radius: 5px; margin-bottom: 20px;">Usuário não encontrado com este e-mail.</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Mudar Senha Admin</title>
    <style>
        body {
            background: #111;
            color: #eee;
            font-family: sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .box {
            background: #222;
            padding: 2rem;
            border-radius: 10px;
            border: 1px solid #333;
            width: 100%;
            max-width: 400px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: #ccc;
        }

        input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            background: #333;
            border: 1px solid #444;
            color: white;
            border-radius: 5px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 10px;
            background: #fed000;
            border: none;
            font-weight: bold;
            cursor: pointer;
            border-radius: 5px;
            color: #000;
        }

        button:hover {
            background: #e5bd00;
        }
    </style>
</head>

<body>
    <div class="box">
        <h2 style="text-align: center; color: #fed000; margin-top: 0;">Alterar Senha Admin</h2>
        <?= $message ?>
        <form method="POST">
            <label>E-mail do Administrador</label>
            <input type="email" name="email" required placeholder="admin@email.com">

            <label>Nova Senha</label>
            <input type="password" name="senha" required placeholder="******">

            <button type="submit">Atualizar Senha</button>
        </form>
    </div>
</body>

</html>