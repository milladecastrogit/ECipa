<?php
session_start();
require_once '../config/conexao.php';

if ($_SESSION['user_tipo'] !== 'Administrador') {
    header('Location: eleicao.php');
    exit;
}

$pageTitle = 'Dashboard';

include '../includes/layout-header.php';
?>

        <div class="page-header">
            <h1 class="page-title">Dashboard Administrativo</h1>
            <p class="page-subtitle">Visão geral do sistema</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 40px;">
            <?php
            // Estatísticas
            $stats = [];
            
            // Total de funcionários
            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM funcionario");
            $stmt->execute();
            $stats['funcionarios'] = $stmt->fetch()->total;
            
            // Total de eleições
            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM eleicao");
            $stmt->execute();
            $stats['eleicoes'] = $stmt->fetch()->total;
            
            // Total de candidaturas
            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM candidatura");
            $stmt->execute();
            $stats['candidatos'] = $stmt->fetch()->total;
            
            // Total de votos
            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM voto");
            $stmt->execute();
            $stats['votos'] = $stmt->fetch()->total;
            ?>

            <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-top: 4px solid #2d5016;">
                <div style="font-size: 32px; margin-bottom: 10px;">👥</div>
                <div style="color: #6b7280; font-size: 14px; margin-bottom: 5px;">Funcionários</div>
                <div style="font-size: 28px; font-weight: bold; color: #1f2937;"><?php echo $stats['funcionarios']; ?></div>
            </div>

            <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-top: 4px solid #10b981;">
                <div style="font-size: 32px; margin-bottom: 10px;">🗳️</div>
                <div style="color: #6b7280; font-size: 14px; margin-bottom: 5px;">Eleições</div>
                <div style="font-size: 28px; font-weight: bold; color: #1f2937;"><?php echo $stats['eleicoes']; ?></div>
            </div>

            <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-top: 4px solid #f59e0b;">
                <div style="font-size: 32px; margin-bottom: 10px;">📝</div>
                <div style="color: #6b7280; font-size: 14px; margin-bottom: 5px;">Candidatos</div>
                <div style="font-size: 28px; font-weight: bold; color: #1f2937;"><?php echo $stats['candidatos']; ?></div>
            </div>

            <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-top: 4px solid #3b82f6;">
                <div style="font-size: 32px; margin-bottom: 10px;">✅</div>
                <div style="color: #6b7280; font-size: 14px; margin-bottom: 5px;">Votos Registrados</div>
                <div style="font-size: 28px; font-weight: bold; color: #1f2937;"><?php echo $stats['votos']; ?></div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
            <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <h2 style="margin-bottom: 20px; color: #1f2937;">Eleições Recentes</h2>
                <?php
                $stmt = $pdo->prepare("SELECT * FROM eleicao ORDER BY created_at DESC LIMIT 5");
                $stmt->execute();
                $eleicoes = $stmt->fetchAll();

                if ($eleicoes):
                    foreach ($eleicoes as $eleicao):
                ?>
                    <div style="padding: 15px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div style="font-weight: 600; color: #1f2937;"><?php echo htmlspecialchars($eleicao->titulo); ?></div>
                            <div style="font-size: 12px; color: #6b7280;"><?php echo $eleicao->status; ?></div>
                        </div>
                        <a href="resultado.php?id=<?php echo $eleicao->id; ?>" style="color: #2d5016; text-decoration: none; font-weight: 600; font-size: 14px;">Ver →</a>
                    </div>
                <?php
                    endforeach;
                else:
                    echo '<p style="color: #6b7280; text-align: center; padding: 20px;">Nenhuma eleição criada</p>';
                endif;
                ?>
            </div>

            <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <h2 style="margin-bottom: 20px; color: #1f2937;">Ações Rápidas</h2>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <a href="cadastro-funcionario.php" style="padding: 12px; background: #2d5016; color: white; text-align: center; border-radius: 8px; text-decoration: none; font-weight: 600; transition: background 0.3s;">
                        ➕ Novo Funcionário
                    </a>
                    <a href="eleicao.php" style="padding: 12px; background: #10b981; color: white; text-align: center; border-radius: 8px; text-decoration: none; font-weight: 600; transition: background 0.3s;">
                        ➕ Nova Eleição
                    </a>
                    <a href="auditoria.php" style="padding: 12px; background: #f59e0b; color: white; text-align: center; border-radius: 8px; text-decoration: none; font-weight: 600; transition: background 0.3s;">
                        🔍 Ver Auditoria
                    </a>
                </div>
            </div>
        </div>

<?php include '../includes/layout-footer.php'; ?>
