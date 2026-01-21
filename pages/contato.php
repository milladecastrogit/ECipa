<?php
session_start();
require_once '../config/conexao.php';

$pageTitle = 'Contatos';

include '../includes/layout-header.php';
?>

        <div class="page-header">
            <h1 class="page-title">Entre em Contato</h1>
            <p class="page-subtitle">Dúvidas? Estamos aqui para ajudar!</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 30px; margin-bottom: 40px;">
            <div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-left: 4px solid #009002; text-align: center;">
                <div style="font-size: 40px; margin-bottom: 15px;">📧</div>
                <h3 style="color: #1f2937; margin-bottom: 10px; font-size: 18px;">E-mail</h3>
                <p style="color: #6b7280; margin-bottom: 15px;">Envie-nos um e-mail para dúvidas e sugestões</p>
                <a href="mailto:contato@ecipa.com.br" style="color: #009002; font-weight: 600; text-decoration: none;">contato@ecipa.com.br</a>
            </div>

            <div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-left: 4px solid #009002; text-align: center;">
                <div style="font-size: 40px; margin-bottom: 15px;">📱</div>
                <h3 style="color: #1f2937; margin-bottom: 10px; font-size: 18px;">WhatsApp</h3>
                <p style="color: #6b7280; margin-bottom: 15px;">Envie uma mensagem através do WhatsApp</p>
                <a href="https://wa.me/5511987654321" target="_blank" style="color: #009002; font-weight: 600; text-decoration: none;">(11) 98765-4321</a>
            </div>

            <div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-left: 4px solid #009002; text-align: center;">
                <div style="font-size: 40px; margin-bottom: 15px;">📞</div>
                <h3 style="color: #1f2937; margin-bottom: 10px; font-size: 18px;">Telefone</h3>
                <p style="color: #6b7280; margin-bottom: 15px;">Ligue para nossa central de atendimento</p>
                <a href="tel:1133334444" style="color: #009002; font-weight: 600; text-decoration: none;">(11) 3333-4444</a>
            </div>
        </div>

        <div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); max-width: 600px; margin: 0 auto;">
            <h2 style="color: #1f2937; margin-bottom: 20px;">Formulário de Contato</h2>
            <form action="" method="POST">
                <div style="margin-bottom: 20px;">
                    <label style="display: block; color: #333; font-weight: 600; margin-bottom: 8px;">Nome *</label>
                    <input type="text" name="nome" required style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px;">
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; color: #333; font-weight: 600; margin-bottom: 8px;">E-mail *</label>
                    <input type="email" name="email" required style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px;">
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; color: #333; font-weight: 600; margin-bottom: 8px;">Assunto *</label>
                    <input type="text" name="assunto" required style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px;">
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; color: #333; font-weight: 600; margin-bottom: 8px;">Mensagem *</label>
                    <textarea name="mensagem" rows="6" required style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px; resize: vertical; font-family: inherit;"></textarea>
                </div>

                <button type="submit" style="width: 100%; padding: 12px; background: linear-gradient(135deg, #009002 0%, #007001 100%); color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer;">
                    Enviar Mensagem
                </button>
            </form>
        </div>

<?php include '../includes/layout-footer.php'; ?>
