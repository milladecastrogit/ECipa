<?php
session_start();
require_once '../config/conexao.php';

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST'):
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $cpf = $_POST['cpf'] ?? '';
    $telefone = $_POST['telefone'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';
    
    // Validações
    if (empty($nome) || empty($email) || empty($cpf) || empty($telefone) || empty($senha)) {
        $erro = 'Todos os campos são obrigatórios.';
    } elseif ($senha !== $confirmar_senha) {
        $erro = 'As senhas não conferem.';
    } elseif (strlen($senha) < 6) {
        $erro = 'A senha deve ter pelo menos 6 caracteres.';
    } else {
        // Verificar se email já existe
        $stmt = $pdo->prepare("SELECT id FROM funcionario WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $erro = 'Este e-mail já está cadastrado.';
        } else {
            try {
                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("
                    INSERT INTO funcionario (nome, email, cpf, telefone, senha, tipo, status)
                    VALUES (?, ?, ?, ?, ?, 'Funcionario', 'Pendente')
                ");
                
                $stmt->execute([$nome, $email, $cpf, $telefone, $senha_hash]);
                $sucesso = 'Cadastro realizado com sucesso! Aguarde a aprovação do administrador.';
            } catch (Exception $e) {
                $erro = 'Erro ao cadastrar: ' . $e->getMessage();
            }
        }
    }
endif;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar - E-CIPA</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #f1efe7 0%, #f9f8f4 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .register-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 45px rgba(0, 0, 0, 0.1);
            padding: 50px 40px;
            width: 100%;
            max-width: 500px;
            text-align: center;
        }

        .logo-register {
            width: 80px;
            height: 80px;
            margin: 0 auto 30px;
            background: linear-gradient(135deg, #009002 0%, #007001 100%);
            border-radius: 15px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 40px;
            box-shadow: 0 8px 20px rgba(0, 144, 2, 0.3);
        }

        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 26px;
        }

        .subtitle {
            color: #666;
            font-size: 14px;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 18px;
            text-align: left;
        }

        .form-group label {
            display: block;
            color: #333;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 13px;
        }

        .form-group input {
            width: 100%;
            padding: 11px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #009002;
            box-shadow: 0 0 0 3px rgba(0, 144, 2, 0.1);
            background: #f9fdf8;
        }

        .error-message {
            background: #ffebee;
            border: 1px solid #ef5350;
            color: #c62828;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13px;
        }

        .success-message {
            background: #e8f5e9;
            border: 1px solid #66bb6a;
            color: #2e7d32;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13px;
        }

        .btn-register {
            width: 100%;
            padding: 11px;
            background: linear-gradient(135deg, #009002 0%, #007001 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 144, 2, 0.3);
        }

        .links {
            margin-top: 20px;
            text-align: center;
            font-size: 13px;
            color: #666;
        }

        .links a {
            color: #009002;
            text-decoration: none;
            font-weight: 600;
        }

        .links a:hover {
            text-decoration: underline;
        }

        @media (max-width: 600px) {
            .register-container {
                padding: 30px 20px;
            }

            h1 {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="logo-register">🗳️</div>
        
        <h1>E-CIPA</h1>
        <p class="subtitle">Criar sua conta</p>
        
        <?php if ($erro): ?>
            <div class="error-message">❌ <?php echo htmlspecialchars($erro); ?></div>
        <?php endif; ?>

        <?php if ($sucesso): ?>
            <div class="success-message">✅ <?php echo htmlspecialchars($sucesso); ?></div>
            <p style="color: #666; font-size: 13px; margin-top: 15px;">
                <a href="login.php" style="color: #009002; text-decoration: none; font-weight: 600;">Ir para login →</a>
            </p>
        <?php else: ?>
            <form action="" method="POST">
                <div class="form-group">
                    <label for="nome">Nome Completo *</label>
                    <input type="text" id="nome" name="nome" placeholder="Seu nome completo" required>
                </div>
                
                <div class="form-group">
                    <label for="email">E-mail *</label>
                    <input type="email" id="email" name="email" placeholder="seu@email.com" required>
                </div>
                
                <div class="form-group">
                    <label for="cpf">CPF *</label>
                    <input type="text" id="cpf" name="cpf" placeholder="000.000.000-00" required>
                </div>
                
                <div class="form-group">
                    <label for="telefone">Telefone Celular *</label>
                    <input type="tel" id="telefone" name="telefone" placeholder="(11) 9XXXX-XXXX" required>
                </div>
                
                <div class="form-group">
                    <label for="senha">Senha *</label>
                    <input type="password" id="senha" name="senha" placeholder="Mínimo 6 caracteres" required>
                </div>
                
                <div class="form-group">
                    <label for="confirmar_senha">Confirmar Senha *</label>
                    <input type="password" id="confirmar_senha" name="confirmar_senha" placeholder="Confirme sua senha" required>
                </div>
                
                <button type="submit" class="btn-register">Criar Conta</button>
            </form>

            <div class="links">
                Já tem conta? <a href="login.php">Faça login aqui</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
