<?php
/**
 * Header com Logo - Incluir em todas as páginas
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'E-CIPA'; ?> - Sistema de Eleição Digital</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        :root {
            --primary-color: #009002;
            --secondary-color: #007001;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #fbc02d;
            --text-dark: #1f2937;
            --text-light: #6b7280;
            --border-color: #e5e7eb;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: url('../assets/img/background.jpg') center/cover fixed;
            color: var(--text-dark);
        }

        header {
            background: #fff9c4;
            color: #009002;
            padding: 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-container {
            max-width: 400px;
            margin: 0 auto;
            padding: 0 2px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-header {
            display: flex;
            align-items: center;
            gap: 0;
            font-size: 24px;
            font-weight: bold;
            text-decoration: none;
            color: #009002;
        }

        .logo-icon {
            width: auto;
            height: 180px;
            background: none;
            border-radius: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 0;
            padding: 0;
            margin: 0;
        }

        .logo-icon img {
            height: 180px;
            width: auto;
            max-width: 500px;
            display: block;
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-info {
            font-size: 14px;
            text-align: right;
            color: #009002;
        }

        .user-name {
            font-weight: 600;
            color: #009002;
        }

        .user-role {
            font-size: 12px;
            opacity: 0.9;
            color: #009002;
        }

        .btn-logout {
            background: rgba(0, 144, 2, 0.2);
            color: #009002;
            border: 1px solid #009002;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 14px;
        }

        .btn-logout:hover {
            background: rgba(0, 144, 2, 0.3);
        }

        main {
            max-width: 1200px;
            margin: 200px auto 30px auto;
            padding: 0 20px;
            min-height: calc(100vh - 200px);
        }

        .page-title {
            font-size: 28px;
            margin-bottom: 10px;
            color: var(--text-dark);
        }

        .page-subtitle {
            color: var(--text-light);
            margin-bottom: 30px;
        }

        footer {
            background-color: #1f2937;
            color: #9ca3af;
            text-align: center;
            padding: 20px;
            margin-top: 40px;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
                gap: 15px;
            }

            .nav-right {
                flex-direction: column;
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <a href="../index.html" class="logo-header">
                <div class="logo-icon">
                    <img src="../assets/img/logoe-cipa.png" alt="E-CIPA Logo">
                </div>
            </a>
            <div class="nav-right">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($_SESSION['user_nome']); ?></div>
                        <div class="user-role">
                            <?php 
                            $roles = [
                                'Administrador' => 'Administrador',
                                'Funcionario' => 'Funcionário',
                                'Comissao' => 'Comissão'
                            ];
                            echo $roles[$_SESSION['user_tipo']] ?? $_SESSION['user_tipo'];
                            ?>
                        </div>
                    </div>
                    <a href="logout.php" class="btn-logout">Sair</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main>
