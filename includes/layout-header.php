<?php
/**
 * Layout Principal com Header e Sidebar
 * Incluir no início de cada página após login
 */

if (!isset($_SESSION['user_id'])) {
    header('Location: ../pages/login.php');
    exit;
}

$pageTitle = $pageTitle ?? 'E-CIPA';
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - E-CIPA</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-color: #009002;
            --secondary-color: #007001;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #fbc02d;
            --text-dark: #1f2937;
            --text-light: #6b7280;
            --border-color: #e5e7eb;
            --bg-light: #f9fafb;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: url('../assets/img/background.jpg') center/cover fixed;
            color: var(--text-dark);
            display: flex;
        }

        header {
            background: #fff9c4;
            color: #009002;
            padding: 0 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 0;
            flex: 0;
            max-width: 400px;
        }

        .logo-header {
            display: flex;
            align-items: center;
            gap: 0;
        
            color: white;
            font-weight: bold;
        
        }

        .logo-icon {
            width: auto;
            height: 150px;
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
            height: 150px;
            width: auto;
            max-width: 450px;
            display: block;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-info {
            text-align: right;
            font-size: 14px;
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

        /* SIDEBAR FIXO */
        aside {
            width: 250px;
            background: white;
            border-right: 1px solid var(--border-color);
            position: fixed;
            left: 0;
            top: 70px;
            bottom: 0;
            overflow-y: auto;
            z-index: 999;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.05);
        }

        .sidebar-menu {
            list-style: none;
            padding: 20px 0;
        }

        .sidebar-menu li {
            margin: 0;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: var(--text-dark);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
            font-size: 15px;
        }

        .sidebar-menu a:hover {
            background-color: var(--bg-light);
            border-left-color: var(--primary-color);
            color: var(--primary-color);
        }

        .sidebar-menu a.active {
            background-color: rgba(102, 126, 234, 0.1);
            border-left-color: var(--primary-color);
            color: var(--primary-color);
            font-weight: 600;
        }

        .menu-icon {
            font-size: 18px;
            width: 20px;
            text-align: center;
        }

        /* MAIN CONTENT */
        main {
            margin-left: 250px;
            margin-top: 70px;
            padding: 30px 20px;
            flex: 1;
            min-height: calc(100vh - 70px);
        }

        .page-header {
            margin-top: 200px;
            margin-bottom: 30px;
        }

        .page-title {
            font-size: 28px;
            font-weight: bold;
            color: var(--text-dark);
            margin-bottom: 5px;
        }

        .page-subtitle {
            color: var(--text-light);
            font-size: 14px;
        }

        /* FOOTER */
        footer {
            margin-left: 250px;
            background-color: var(--text-dark);
            color: #9ca3af;
            text-align: center;
            padding: 20px;
            font-size: 13px;
        }

        /* SCROLLBAR */
        aside::-webkit-scrollbar {
            width: 8px;
        }

        aside::-webkit-scrollbar-track {
            background: var(--bg-light);
        }

        aside::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 4px;
        }

        aside::-webkit-scrollbar-thumb:hover {
            background: #a0aec0;
        }

        /* RESPONSIVO */
        @media (max-width: 768px) {
            aside {
                width: 0;
                overflow: hidden;
                transition: width 0.3s ease;
            }

            aside.open {
                width: 250px;
            }

            main {
                margin-left: 0;
            }

            footer {
                margin-left: 0;
            }

            .menu-toggle {
                display: flex;
                align-items: center;
                gap: 10px;
                background: rgba(255, 255, 255, 0.2);
                color: white;
                border: none;
                padding: 8px 12px;
                border-radius: 6px;
                cursor: pointer;
                font-size: 18px;
            }

            .header-right {
                gap: 10px;
            }

            .user-info {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- HEADER FIXO -->
    <header>
        <div class="header-left">
            <a href="../index.html" class="logo-header">
                <div class="logo-icon">
                    <img src="../assets/img/logoe-cipa.png" alt="E-CIPA Logo">
                </div>
            </a>
        </div>
        <div class="header-right">
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
        </div>
    </header>

    <!-- SIDEBAR FIXO -->
    <aside>
        <ul class="sidebar-menu">
            <li>
                <a href="../index.php" class="<?php echo $currentPage === 'index' ? 'active' : ''; ?>">
                    <span class="menu-icon">🏠</span>
                    <span>Home</span>
                </a>
            </li>

            <?php if ($_SESSION['user_tipo'] === 'Administrador'): ?>
                <li>
                    <a href="dashboard-adm.php" class="<?php echo $currentPage === 'dashboard-adm' ? 'active' : ''; ?>">
                        <span class="menu-icon">📊</span>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="criar-eleicao.php" class="<?php echo $currentPage === 'criar-eleicao' ? 'active' : ''; ?>">
                        <span class="menu-icon">➕</span>
                        <span>Criar Eleição</span>
                    </a>
                </li>
                <li>
                    <a href="gerenciar-usuarios.php" class="<?php echo $currentPage === 'gerenciar-usuarios' ? 'active' : ''; ?>">
                        <span class="menu-icon">👤</span>
                        <span>Aprovar Usuários</span>
                    </a>
                </li>
                <li>
                    <a href="gerenciar-funcionarios.php" class="<?php echo $currentPage === 'gerenciar-funcionarios' ? 'active' : ''; ?>">
                        <span class="menu-icon">👥</span>
                        <span>Gerenciar Funcionários</span>
                    </a>
                </li>
                <li>
                    <a href="cadastro-funcionario.php" class="<?php echo $currentPage === 'cadastro-funcionario' ? 'active' : ''; ?>">
                        <span class="menu-icon">➕</span>
                        <span>Cadastro Funcionário</span>
                    </a>
                </li>
                <li>
                    <a href="cadastro-candidato.php" class="<?php echo $currentPage === 'cadastro-candidato' ? 'active' : ''; ?>">
                        <span class="menu-icon">📝</span>
                        <span>Cadastrar Candidato</span>
                    </a>
                </li>
                <li>
                    <a href="gerenciar-candidatos.php" class="<?php echo $currentPage === 'gerenciar-candidatos' ? 'active' : ''; ?>">
                        <span class="menu-icon">👥</span>
                        <span>Gerenciar Candidatos</span>
                    </a>
                </li>
                <li>
                    <a href="auditoria.php" class="<?php echo $currentPage === 'auditoria' ? 'active' : ''; ?>">
                        <span class="menu-icon">🔍</span>
                        <span>Auditoria</span>
                    </a>
                </li>
            <?php elseif ($_SESSION['user_tipo'] === 'Comissao'): ?>
                <li>
                    <a href="acompanhamento-votacao.php" class="<?php echo $currentPage === 'acompanhamento-votacao' ? 'active' : ''; ?>">
                        <span class="menu-icon">📊</span>
                        <span>Acompanhamento</span>
                    </a>
                </li>
                <li>
                    <a href="votos-fisicos-comissao.php" class="<?php echo $currentPage === 'votos-fisicos-comissao' ? 'active' : ''; ?>">
                        <span class="menu-icon">🗳️</span>
                        <span>Votos Físicos</span>
                    </a>
                </li>
                <li>
                    <a href="cadastro-candidato.php" class="<?php echo $currentPage === 'cadastro-candidato' ? 'active' : ''; ?>">
                        <span class="menu-icon">📝</span>
                        <span>Cadastrar Candidato</span>
                    </a>
                </li>
                <li>
                    <a href="gerenciar-candidatos.php" class="<?php echo $currentPage === 'gerenciar-candidatos' ? 'active' : ''; ?>">
                        <span class="menu-icon">👥</span>
                        <span>Gerenciar Candidatos</span>
                    </a>
                </li>
            <?php endif; ?>

            <li>
                <a href="eleicao.php" class="<?php echo $currentPage === 'eleicao' ? 'active' : ''; ?>">
                    <span class="menu-icon">🗳️</span>
                    <span>Eleições</span>
                </a>
            </li>

            <?php if ($_SESSION['user_tipo'] === 'Funcionario'): ?>
                <li>
                    <a href="candidatar-se.php" class="<?php echo $currentPage === 'candidatar-se' ? 'active' : ''; ?>">
                        <span class="menu-icon">📝</span>
                        <span>Candidatar-se</span>
                    </a>
                </li>
            <?php endif; ?>

            <li>
                <a href="votacao.php" class="<?php echo $currentPage === 'votacao' ? 'active' : ''; ?>">
                    <span class="menu-icon">✅</span>
                    <span>Votação</span>
                </a>
            </li>

            <li>
                <a href="resultado-final.php" class="<?php echo $currentPage === 'resultado-final' ? 'active' : ''; ?>">
                    <span class="menu-icon">🏆</span>
                    <span>Resultado Final</span>
                </a>
            </li>

            <li style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
                <a href="contato.php" class="<?php echo $currentPage === 'contato' ? 'active' : ''; ?>">
                    <span class="menu-icon">📞</span>
                    <span>Contatos</span>
                </a>
            </li>
        </ul>
    </aside>

    <!-- CONTEÚDO PRINCIPAL -->
    <main>
