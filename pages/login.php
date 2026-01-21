<?php
session_start();
require_once '../config/conexao.php';

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM funcionario WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($senha, $user->senha)) {
        // Verificar se o usuário foi aprovado
        if ($user->status === 'Pendente') {
            $erro = 'Sua conta está aguardando aprovação do administrador.';
        } elseif ($user->status === 'Inativo') {
            $erro = 'Sua conta foi desativada. Entre em contato com o administrador.';
        } else {
            $_SESSION['user_id'] = $user->id;
            $_SESSION['user_nome'] = $user->nome;
            $_SESSION['user_tipo'] = $user->tipo;
            $_SESSION['user_email'] = $user->email;

            registrarLog($pdo, $user->tipo, $user->id, 'Login efetuado');

            if ($user->tipo === 'Administrador') {
                header('Location: dashboard-adm.php');
            } else {
                header('Location: eleicao.php');
            }
            exit;
        }
    } else {
        $erro = 'E-mail ou senha inválidos.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - E-CIPA</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            background: url('../assets/img/background.jpg') center/cover fixed;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .login-container {
            background: rgba(255, 249, 196, 0.95);
            border-radius: 15px;
            box-shadow: 0 15px 45px rgba(0, 0, 0, 0.2);
            padding: 40px 30px;
            width: 100%;
            max-width: 420px;
            text-align: center;
        }

        .logo-login {
            width: 220px;
            height: 220px;
            margin: 0 auto 30px;
            background: transparent;
            border-radius: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 40px;
            box-shadow: none;
        }

        .logo-login img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        h1 {
            color: #009002;
            margin-bottom: 30px;
            font-size: 26px;
            display: none;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            display: block;
            color: #009002;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #009002;
            box-shadow: 0 0 0 3px rgba(0, 144, 2, 0.1);
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #fbc02d 0%, #ffeb3b 100%);
            color: #333;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(251, 192, 45, 0.3);
        }

        .error-message {
            background: #ffebee;
            border: 1px solid #ef5350;
            color: #c62828;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .footer-links {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
            gap: 10px;
            font-size: 13px;
        }

        .footer-links a {
            color: #009002;
            text-decoration: none;
            font-weight: 600;
        }

        .footer-links a:hover {
            text-decoration: underline;
        }

        @media (max-width: 600px) {
            .login-container {
                padding: 40px 20px;
            }

            h1 {
                font-size: 24px;
            }

            .logo-login {
                width: 100px;
                height: 100px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo-login">
            <img src="../assets/img/logoe-cipa.png" alt="E-CIPA Logo">
        </div>
        
        <h1>E-CIPA</h1>
        
        <?php if ($erro): ?>
            <div class="error-message">❌ <?php echo htmlspecialchars($erro); ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" placeholder="seu@email.com" required>
            </div>
            <div class="form-group">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" placeholder="Sua senha" required>
            </div>
            <button type="submit" class="btn-login">Entrar</button>
        </form>
        
        <div class="footer-links">
            <a href="registro.php">Criar conta</a>
            <a href="../index.php">Home</a>
        </div>
    </div>
</body>
</html>
