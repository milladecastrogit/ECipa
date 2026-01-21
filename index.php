<?php
session_start();

// Se o usuário já está logado, redireciona para o dashboard
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_tipo'] === 'Administrador') {
        header('Location: pages/dashboard-adm.php');
    } else {
        header('Location: pages/eleicao.php');
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>E-CIPA - Eleição Digital para CIPA</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: url('assets/img/background.jpg') center/cover fixed, #f5f5f5;
            min-height: 100vh;
            color: #1f2937;
            background-attachment: fixed;
        }

        header {
            background: #fff9c4;
            padding: 5px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-header {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: #009002;
            font-weight: bold;
            font-size: 20px;
        }

        .logo-icon {
            width: 300px;
            height: 100px;
            display: flex;
            justify-content: center;
            align-items:center;
            padding: 0;
            margin: 0;
            flex-shrink: 0;
            background: transparent;
            border-radius: 0;
        }

        .logo-icon img {
            height: 100%;
            width: 100%;
            object-fit: contain;
            max-width: 300px;
            display: block;
        }

        nav a {
            margin-left: 30px;
            text-decoration: none;
            color: #009002;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        nav a:hover {
            color: #007001;
        }

        .hero {
            max-width: 1100px;
            margin: 60px auto 0;
            padding: 0 2px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
            min-height: calc(100vh - 100px);
        }

        .hero-content h1 {
            font-size: 48px;
            color: #009002;
            margin-bottom: 20px;
            line-height: 1.2;
            text-shadow: none;
        }

        .hero-content p {
            font-size: 18px;
            color: #009002;
            margin-bottom: 30px;
            line-height: 1.6;
            text-shadow: none;
        }

        .hero-buttons {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 14px 35px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .btn-primary {
            background: linear-gradient(135deg, #fbc02d 0%, #ffeb3b 100%);
            color: #333;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(251, 192, 45, 0.3);
        }

        .btn-secondary {
            background: transparent;
            color: #009002;
            border: 2px solid #009002;
        }

        .btn-secondary:hover {
            background: rgba(0, 144, 2, 0.1);
            transform: translateY(-3px);
        }

        .hero-illustration {
            text-align: center;
        }

        .illustration-box {
            background: #fff9c4;
            border-radius: 15px;
            padding: 10px;
            font-size: 120px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            display: flex;
            justify-content: center;
            align-items: center;
            height: auto;
            width: 300px;
        }

        .illustration-box img {
            height: 300px;
            width: auto;
            object-fit: contain;
            max-height: 1000px;
        }

        .features-grid {
            max-width: 1200px;
            margin: 100px auto;
            padding: 0 2px;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 30px;
            justify-items: center;
        }

        .feature-card {
            background: #fff9c4;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.12);
            text-align: center;
            transition: all 0.3s ease;
            border: none;
            width: 100%;
            max-width: 250px;
        }

        .feature-card:nth-child(n+5) {
            grid-column: auto;
        }

        @supports (display: grid) {
            .feature-card:nth-child(5) {
                grid-column: 2 / span 1;
            }
            .feature-card:nth-child(6) {
                grid-column: 3 / span 1;
            }
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.18);
        }

        .feature-icon {
            font-size: 48px;
            margin-bottom: 15px;
            height: 60px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .feature-icon img {
            height: 100%;
            width: auto;
            object-fit: contain;
        }

        .feature-card h3 {
            color: #009002;
            margin-bottom: 10px;
            font-size: 20px;
        }

        .feature-card p {
            color: #009002;
            line-height: 1.6;
            font-size: 14px;
        }

        footer {
            background-color: #00902a;
            color: #ffffff
            text-align: center;
            padding: 30px;
            margin-top: 80px;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .hero {
                grid-template-columns: 1fr;
                margin-top: 40px;
            }

            .header-content {
                flex-direction: column;
                gap: 15px;
            }

            .hero-content h1 {
                font-size: 32px;
            }

            .hero-content p {
                font-size: 16px;
            }

            nav {
                display: none;
            }

            .hero-buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                text-align: center;
            }

            .illustration-box {
                padding: 40px 20px;
                font-size: 80px;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <a href="/" class="logo-header">
                <div class="logo-icon">
                    <img src="assets/img/logoe-cipa.png" alt="E-CIPA Logo">
                </div>
            </a>
            <nav>
                <a href="#features">Recursos</a>
                <a href="#about">Sobre</a>
                <a href="#contatos">Contatos</a>
                <a href="pages/login.php">Login</a>
            </nav>
        </div>
    </header>

    <div class="hero">
        <div class="hero-content">
            <h1>Eleição Digital Segura e Transparente</h1>
            <p>Sistema completo para gerenciar eleições da Comissão Interna de Prevenção de Acidentes (CIPA) com segurança, transparência e facilidade de uso.</p>
            <div class="hero-buttons">
                <a href="pages/login.php" class="btn btn-primary">Começar Agora</a>
                <a href="#features" class="btn btn-secondary">Saiba Mais</a>
            </div>
        </div>
        <div class="hero-illustration">
            <div class="illustration-box">
                <img src="assets/img/logoe-cipa.png" alt="E-CIPA Logo">
            </div>
        </div>
    </div>

    <div id="features" class="features-grid">
        <div class="feature-card">
            <div class="feature-icon"><img src="assets/icons/shield-check.png" alt="Segurança"></div>
            <h3>Seguro e Confiável</h3>
            <p>Autenticação segura e criptografia de dados para proteger a privacidade dos votos.</p>
        </div>

        <div class="feature-card">
            <div class="feature-icon"><img src="assets/icons/chart-histogram.png" alt="Eficiência"></div>
            <h3>Rápido e Eficiente</h3>
            <p>Processo de votação otimizado para resultar em horas, não em dias.</p>
        </div>

        <div class="feature-card">
            <div class="feature-icon"><img src="assets/icons/chart-histogram.png" alt="Relatórios"></div>
            <h3>Relatórios em Tempo Real</h3>
            <p>Acompanhe os resultados das eleições em tempo real com dashboards intuitivos.</p>
        </div>

        <div class="feature-card">
            <div class="feature-icon"><img src="assets/icons/users-alt.png" alt="Acessibilidade"></div>
            <h3>Acessível para Todos</h3>
            <p>Interface amigável que funciona em qualquer dispositivo - computador, tablet ou celular.</p>
        </div>

        <div class="feature-card">
            <div class="feature-icon"><img src="assets/icons/check.png" alt="Compatibilidade"></div>
            <h3>Compatível com a Lei</h3>
            <p>Totalmente em conformidade com legislação de eleições da CIPA.</p>
        </div>

        <div class="feature-card">
            <div class="feature-icon"><img src="assets/icons/home.png" alt="Facilidade"></div>
            <h3>Fácil de Usar</h3>
            <p>Design intuitivo que não requer treinamento técnico.</p>
        </div>
    </div>

    <footer id="contatos">
        <h3>Contatos</h3>
        <p>📧 Email: procipaonline@gmail.com</p>
        <p>📱 Telefone: (71) 99131-1250</p>
        <p>📍 Endereço: Camaçarí, BA</p>
        <hr style="border: none; border-top: 1px solid rgba(255,255,255,0.2); margin: 20px 0;">
        <p>&copy; 2026 E-CIPA - Sistema de Eleição Digital para CIPA. Todos os direitos reservados.</p>
    </footer>

    <script>
        // Limpar cache ao carregar
        if ('caches' in window) {
            caches.keys().then(names => names.forEach(name => caches.delete(name)));
        }
    </script>
</body>
</html>
