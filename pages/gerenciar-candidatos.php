<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Verifica se é Admin ou Comissão
if (!in_array($_SESSION['user_tipo'], ['Administrador', 'Comissao'])) {
    header("Location: ../index.php");
    exit;
}

require_once '../config/conexao.php';

// Define a página atual para o menu
$currentPage = 'gerenciar-candidatos';

// Ação de aprovar/rejeitar candidato
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
    $acao = $_POST['acao'];
    $candidato_id = intval($_POST['candidato_id']);
    
    if ($acao === 'aprovar' || $acao === 'rejeitar') {
        $novo_status = ($acao === 'aprovar') ? 'Aprovado' : 'Rejeitado';
        
        // Atualiza status da candidatura
        $stmt = $pdo->prepare("UPDATE candidatura SET status = :status WHERE id = :id");
        $stmt->execute([
            ':status' => $novo_status,
            ':id' => $candidato_id
        ]);
        
        // Registro de auditoria
        $stmt = $pdo->prepare("
            INSERT INTO audit_log (usuario_id, acao, descricao, created_at) 
            VALUES (:usuario_id, :acao, :descricao, NOW())
        ");
        $stmt->execute([
            ':usuario_id' => $_SESSION['user_id'],
            ':acao' => $acao === 'aprovar' ? 'CANDIDATO_APROVADO' : 'CANDIDATO_REJEITADO',
            ':descricao' => "Candidato ID {$candidato_id} foi " . ($acao === 'aprovar' ? 'aprovado' : 'rejeitado'),
        ]);
        
        header("Location: gerenciar-candidatos.php");
        exit;
    }
}

// Buscar todas as candidaturas com informações do candidato e eleição
$stmt = $pdo->prepare("
    SELECT 
        c.id,
        c.id_funcionario,
        c.id_eleicao,
        c.foto_path,
        c.proposta,
        c.status,
        c.created_at,
        f.nome,
        f.cpf,
        f.matricula,
        f.telefone,
        e.titulo,
        e.data_eleicao
    FROM candidatura c
    JOIN funcionario f ON c.id_funcionario = f.id
    JOIN eleicao e ON c.id_eleicao = e.id
    ORDER BY e.data_eleicao DESC, c.created_at DESC
");
$stmt->execute();
$candidatos = $stmt->fetchAll(PDO::FETCH_OBJ);

// Agrupar por eleição
$candidatos_por_eleicao = [];
foreach ($candidatos as $candidato) {
    $eleicao_id = $candidato->id_eleicao;
    if (!isset($candidatos_por_eleicao[$eleicao_id])) {
        $candidatos_por_eleicao[$eleicao_id] = [
            'titulo' => $candidato->titulo,
            'data' => $candidato->data_eleicao,
            'candidatos' => []
        ];
    }
    $candidatos_por_eleicao[$eleicao_id]['candidatos'][] = $candidato;
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Candidatos - E-CIPA</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f1efe7;
            color: #333;
        }

        .page-title {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #009002;
        }

        .page-title h1 {
            color: #009002;
            font-size: 28px;
            margin-bottom: 5px;
        }

        .page-title p {
            color: #666;
            font-size: 14px;
        }

        .eleicao-section {
            background: white;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .eleicao-header {
            background: linear-gradient(135deg, #fbc02d 0%, #f9a50c 100%);
            padding: 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            color: #333;
        }

        .eleicao-header h2 {
            font-size: 20px;
            margin-bottom: 5px;
        }

        .eleicao-header p {
            font-size: 13px;
            opacity: 0.9;
        }

        .candidatos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .candidato-card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .candidato-card:hover {
            box-shadow: 0 4px 12px rgba(0, 144, 2, 0.15);
            transform: translateY(-2px);
        }

        .candidato-photo {
            width: 100%;
            height: 200px;
            background: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            color: #999;
            font-size: 12px;
        }

        .candidato-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .candidato-info {
            padding: 15px;
        }

        .candidato-nome {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 8px;
            color: #009002;
        }

        .candidato-details {
            font-size: 12px;
            color: #666;
            margin-bottom: 10px;
            line-height: 1.6;
        }

        .candidato-details strong {
            color: #333;
        }

        .candidato-proposta {
            font-size: 12px;
            color: #555;
            background: #f9f9f9;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 10px;
            max-height: 80px;
            overflow-y: auto;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            margin-bottom: 12px;
            width: 100%;
            text-align: center;
        }

        .status-pendente {
            background-color: #fef3c7;
            color: #b45309;
        }

        .status-aprovado {
            background-color: #d1fae5;
            color: #065f46;
        }

        .status-rejeitado {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .candidato-actions {
            display: flex;
            gap: 8px;
        }

        .btn-action {
            flex: 1;
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            color: white;
            text-align: center;
        }

        .btn-aprovar {
            background-color: #009002;
        }

        .btn-aprovar:hover {
            background-color: #007001;
        }

        .btn-rejeitar {
            background-color: #dc2626;
        }

        .btn-rejeitar:hover {
            background-color: #b91c1c;
        }

        .btn-action:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #009002;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <?php include '../includes/layout-header.php'; ?>

    <main>
        <div class="page-title">
            <h1>Gerenciar Candidatos</h1>
            <p>Aprove ou rejeite candidaturas para as eleições</p>
        </div>

        <?php if (!empty($candidatos)): ?>
            <?php
            // Calcular estatísticas
            $total = count($candidatos);
            $pendentes = count(array_filter($candidatos, fn($c) => $c->status === 'Pendente'));
            $aprovados = count(array_filter($candidatos, fn($c) => $c->status === 'Aprovado'));
            $rejeitados = count(array_filter($candidatos, fn($c) => $c->status === 'Rejeitado'));
            ?>

            <div class="stats">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total; ?></div>
                    <div class="stat-label">Total de Candidatos</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" style="color: #f59e0b;"><?php echo $pendentes; ?></div>
                    <div class="stat-label">Pendentes</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" style="color: #009002;"><?php echo $aprovados; ?></div>
                    <div class="stat-label">Aprovados</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" style="color: #dc2626;"><?php echo $rejeitados; ?></div>
                    <div class="stat-label">Rejeitados</div>
                </div>
            </div>

            <?php foreach ($candidatos_por_eleicao as $eleicao_id => $eleicao_data): ?>
                <div class="eleicao-section">
                    <div class="eleicao-header">
                        <h2><?php echo htmlspecialchars($eleicao_data['titulo']); ?></h2>
                        <p>📅 <?php echo date('d/m/Y', strtotime($eleicao_data['data'])); ?></p>
                    </div>

                    <div class="candidatos-grid">
                        <?php foreach ($eleicao_data['candidatos'] as $candidato): ?>
                            <div class="candidato-card">
                                <div class="candidato-photo">
                                    <?php if (!empty($candidato->foto_path) && file_exists('../' . $candidato->foto_path)): ?>
                                        <img src="../<?php echo htmlspecialchars($candidato->foto_path); ?>" alt="<?php echo htmlspecialchars($candidato->nome); ?>">
                                    <?php else: ?>
                                        <span>Sem foto</span>
                                    <?php endif; ?>
                                </div>
                                <div class="candidato-info">
                                    <div class="candidato-nome"><?php echo htmlspecialchars($candidato->nome); ?></div>
                                    
                                    <div class="candidato-details">
                                        <strong>CPF:</strong> <?php echo htmlspecialchars($candidato->cpf); ?><br>
                                        <strong>Matrícula:</strong> <?php echo htmlspecialchars($candidato->matricula); ?><br>
                                        <strong>Telefone:</strong> <?php echo htmlspecialchars($candidato->telefone); ?>
                                    </div>

                                    <div class="candidato-proposta">
                                        <strong>Perfil:</strong><br>
                                        <?php echo nl2br(htmlspecialchars(substr($candidato->proposta, 0, 150))); ?>
                                        <?php if (strlen($candidato->proposta) > 150): ?>
                                            ...
                                        <?php endif; ?>
                                    </div>

                                    <div class="status-badge status-<?php echo strtolower($candidato->status); ?>">
                                        <?php echo htmlspecialchars($candidato->status); ?>
                                    </div>

                                    <?php if ($candidato->status === 'Pendente'): ?>
                                        <div class="candidato-actions">
                                            <form method="POST" style="flex: 1;">
                                                <input type="hidden" name="candidato_id" value="<?php echo $candidato->id; ?>">
                                                <input type="hidden" name="acao" value="aprovar">
                                                <button type="submit" class="btn-action btn-aprovar" onclick="return confirm('Deseja aprovar este candidato?')">
                                                    ✓ Aprovar
                                                </button>
                                            </form>
                                            <form method="POST" style="flex: 1;">
                                                <input type="hidden" name="candidato_id" value="<?php echo $candidato->id; ?>">
                                                <input type="hidden" name="acao" value="rejeitar">
                                                <button type="submit" class="btn-action btn-rejeitar" onclick="return confirm('Deseja rejeitar este candidato?')">
                                                    ✕ Rejeitar
                                                </button>
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        <div style="padding: 8px; background: #f3f4f6; border-radius: 4px; text-align: center; color: #666; font-size: 12px;">
                                            Status definido em: <?php echo date('d/m/Y H:i', strtotime($candidato->created_at)); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="eleicao-section">
                <div class="empty-state">
                    <div class="empty-state-icon">📭</div>
                    <h3>Nenhuma candidatura registrada</h3>
                    <p>Nenhum funcionário se candidatou ainda.</p>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <footer style="margin-left: 250px; padding: 20px; text-align: center; color: #999; font-size: 12px; margin-top: 40px;">
        <p>&copy; 2024 E-CIPA - Sistema de Votação Digital. Todos os direitos reservados.</p>
    </footer>
</body>
</html>
