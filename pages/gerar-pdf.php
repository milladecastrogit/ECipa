<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/conexao.php';

// Verificar se é uma solicitação para gerar PDF
if ($_GET['tipo'] === 'candidatura' && isset($_GET['id'])) {
    $candidatura_id = $_GET['id'];
    
    // Buscar dados da candidatura
    $stmt = $pdo->prepare("
        SELECT 
            c.id, c.created_at,
            f.nome, f.cpf, f.email, f.telefone,
            e.titulo as eleicao_titulo, e.data_eleicao
        FROM candidatura c
        LEFT JOIN funcionario f ON c.id_funcionario = f.id
        LEFT JOIN eleicao e ON c.id_eleicao = e.id
        WHERE c.id = ?
    ");
    $stmt->execute([$candidatura_id]);
    $candidatura = $stmt->fetch(PDO::FETCH_OBJ);
    
    if (!$candidatura || ($candidatura->id_funcionario !== $_SESSION['user_id'] && $_SESSION['user_tipo'] !== 'Administrador')) {
        header('HTTP/1.0 404 Not Found');
        exit;
    }
    
    // HTML do PDF
    $html = "
    <!DOCTYPE html>
    <html lang='pt-BR'>
    <head>
        <meta charset='UTF-8'>
        <title>Registro de Candidatura - E-CIPA</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 40px; color: #333; }
            .header { text-align: center; margin-bottom: 40px; border-bottom: 3px solid #009002; padding-bottom: 20px; }
            .header h1 { color: #009002; margin: 0; }
            .info-section { margin: 30px 0; }
            .info-section h2 { color: #009002; font-size: 16px; border-bottom: 2px solid #e5e7eb; padding-bottom: 10px; margin-bottom: 15px; }
            .info-row { display: flex; margin-bottom: 10px; }
            .info-label { width: 150px; font-weight: bold; color: #666; }
            .info-value { flex: 1; }
            .footer { margin-top: 40px; text-align: center; color: #999; font-size: 12px; }
            .codigo { background: #f9fafb; padding: 15px; border-radius: 8px; text-align: center; font-family: monospace; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class='header'>
            <h1>🗳️ E-CIPA</h1>
            <p>REGISTRO DE CANDIDATURA</p>
        </div>
        
        <div class='info-section'>
            <h2>DADOS DO ELEITOR</h2>
            <div class='info-row'>
                <div class='info-label'>Nome:</div>
                <div class='info-value'>" . htmlspecialchars($candidatura->nome) . "</div>
            </div>
            <div class='info-row'>
                <div class='info-label'>CPF:</div>
                <div class='info-value'>" . htmlspecialchars($candidatura->cpf) . "</div>
            </div>
            <div class='info-row'>
                <div class='info-label'>Email:</div>
                <div class='info-value'>" . htmlspecialchars($candidatura->email) . "</div>
            </div>
            <div class='info-row'>
                <div class='info-label'>Telefone:</div>
                <div class='info-value'>" . htmlspecialchars($candidatura->telefone ?? 'N/A') . "</div>
            </div>
        </div>
        
        <div class='info-section'>
            <h2>DADOS DA CANDIDATURA</h2>
            <div class='info-row'>
                <div class='info-label'>Eleição:</div>
                <div class='info-value'>" . htmlspecialchars($candidatura->eleicao_titulo) . "</div>
            </div>
            <div class='info-row'>
                <div class='info-label'>Data da Eleição:</div>
                <div class='info-value'>" . date('d/m/Y', strtotime($candidatura->data_eleicao)) . "</div>
            </div>
            <div class='info-row'>
                <div class='info-label'>Data de Candidatura:</div>
                <div class='info-value'>" . date('d/m/Y H:i', strtotime($candidatura->created_at)) . "</div>
            </div>
        </div>
        
        <div class='info-section'>
            <h2>COMPROVANTE</h2>
            <div class='codigo'>
                Nº Candidatura: " . str_pad($candidatura->id, 8, '0', STR_PAD_LEFT) . "<br>
                Código: " . substr(hash('sha256', $candidatura->id . $candidatura->created_at), 0, 16) . "
            </div>
        </div>
        
        <div class='footer'>
            <p>Este é um comprovante de candidatura oficial do sistema E-CIPA.</p>
            <p>Gerado em: " . date('d/m/Y H:i:s') . "</p>
        </div>
    </body>
    </html>
    ";
    
    // Retornar como PDF (simulado como HTML para impressão)
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: attachment; filename="candidatura_' . $candidatura->id . '.html"');
    echo $html;
    exit;
}

// Tipo de voto
elseif ($_GET['tipo'] === 'voto' && isset($_GET['id'])) {
    $voto_id = $_GET['id'];
    
    // Buscar dados do voto
    $stmt = $pdo->prepare("
        SELECT 
            v.id, v.data_voto, v.cod_verificacao, v.tipo_voto,
            f.nome, f.cpf, f.email,
            c.nome as candidato_nome,
            e.titulo as eleicao_titulo
        FROM voto v
        LEFT JOIN funcionario f ON v.id_funcionario = f.id
        LEFT JOIN candidatura cand ON v.id_candidato = cand.id
        LEFT JOIN funcionario c ON cand.id_funcionario = c.id
        LEFT JOIN eleicao e ON v.id_eleicao = e.id
        WHERE v.id = ? AND v.id_funcionario = ?
    ");
    $stmt->execute([$voto_id, $_SESSION['user_id']]);
    $voto = $stmt->fetch(PDO::FETCH_OBJ);
    
    if (!$voto) {
        header('HTTP/1.0 404 Not Found');
        exit;
    }
    
    // HTML do PDF
    $html = "
    <!DOCTYPE html>
    <html lang='pt-BR'>
    <head>
        <meta charset='UTF-8'>
        <title>Recibo de Voto - E-CIPA</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 40px; color: #333; }
            .header { text-align: center; margin-bottom: 40px; border: 3px dashed #009002; padding: 20px; }
            .header h1 { color: #009002; margin: 0; }
            .info-section { margin: 30px 0; }
            .info-section h2 { color: #009002; font-size: 16px; border-bottom: 2px solid #e5e7eb; padding-bottom: 10px; margin-bottom: 15px; }
            .info-row { display: flex; margin-bottom: 10px; }
            .info-label { width: 150px; font-weight: bold; color: #666; }
            .info-value { flex: 1; }
            .footer { margin-top: 40px; text-align: center; color: #999; font-size: 12px; }
            .codigo { background: #f9fafb; padding: 15px; border-radius: 8px; text-align: center; font-family: monospace; margin: 20px 0; word-break: break-all; }
            .aviso { background: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 8px; margin: 20px 0; color: #856404; }
        </style>
    </head>
    <body>
        <div class='header'>
            <h1>🗳️ E-CIPA</h1>
            <p>RECIBO DE VOTO</p>
            <p style='color: #ef4444; font-weight: bold;'>GUARDE ESTE DOCUMENTO</p>
        </div>
        
        <div class='aviso'>
            <p><strong>⚠️ IMPORTANTE:</strong> Este recibo é seu comprovante de voto. Guarde-o com segurança.</p>
        </div>
        
        <div class='info-section'>
            <h2>DADOS DO ELEITOR</h2>
            <div class='info-row'>
                <div class='info-label'>Nome:</div>
                <div class='info-value'>" . htmlspecialchars($voto->nome) . "</div>
            </div>
            <div class='info-row'>
                <div class='info-label'>CPF:</div>
                <div class='info-value'>" . htmlspecialchars($voto->cpf) . "</div>
            </div>
        </div>
        
        <div class='info-section'>
            <h2>DADOS DO VOTO</h2>
            <div class='info-row'>
                <div class='info-label'>Eleição:</div>
                <div class='info-value'>" . htmlspecialchars($voto->eleicao_titulo) . "</div>
            </div>
            <div class='info-row'>
                <div class='info-label'>Tipo de Voto:</div>
                <div class='info-value'>" . htmlspecialchars($voto->tipo_voto) . "</div>
            </div>
            <div class='info-row'>
                <div class='info-label'>Voto em:</div>
                <div class='info-value'>" . htmlspecialchars($voto->candidato_nome ?? 'Branco') . "</div>
            </div>
            <div class='info-row'>
                <div class='info-label'>Data/Hora:</div>
                <div class='info-value'>" . date('d/m/Y H:i:s', strtotime($voto->data_voto)) . "</div>
            </div>
        </div>
        
        <div class='info-section'>
            <h2>CÓDIGO DE VERIFICAÇÃO (CRIPTOGRAFADO)</h2>
            <div class='codigo'>" . htmlspecialchars(substr($voto->cod_verificacao, 0, 16)) . "...</div>
        </div>
        
        <div class='footer'>
            <p>Este é um recibo oficial de voto do sistema E-CIPA.</p>
            <p>Gerado em: " . date('d/m/Y H:i:s') . "</p>
            <p>ID do Voto: " . str_pad($voto->id, 8, '0', STR_PAD_LEFT) . "</p>
        </div>
    </body>
    </html>
    ";
    
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: attachment; filename="recibo_voto_' . $voto->id . '.html"');
    echo $html;
    exit;
}

// Se chegou aqui, erro
header('HTTP/1.0 400 Bad Request');
?>
