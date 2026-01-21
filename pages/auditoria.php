<?php
session_start();
require_once '../config/conexao.php';

if ($_SESSION['user_tipo'] !== 'Administrador') {
    header('Location: eleicao.php');
    exit;
}

$pageTitle = 'Auditoria';

include '../includes/layout-header.php';

// Buscar logs
$stmt = $pdo->prepare("
    SELECT * FROM audit_log
    ORDER BY created_at DESC
    LIMIT 100
");
$stmt->execute();
$logs = $stmt->fetchAll();
?>

        <div class="page-header">
            <h1 class="page-title">Auditoria do Sistema</h1>
            <p class="page-subtitle">Histórico de operações e atividades</p>
        </div>

        <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: linear-gradient(135deg, #2d5016 0%, #1a3a0a 100%); color: white;">
                        <th style="padding: 15px; text-align: left; font-weight: 600;">Usuário</th>
                        <th style="padding: 15px; text-align: left; font-weight: 600;">Tipo</th>
                        <th style="padding: 15px; text-align: left; font-weight: 600;">Ação</th>
                        <th style="padding: 15px; text-align: left; font-weight: 600;">Alvo</th>
                        <th style="padding: 15px; text-align: left; font-weight: 600;">Data/Hora</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (empty($logs)):
                    ?>
                        <tr>
                            <td colspan="5" style="padding: 20px; text-align: center; color: #6b7280;">
                                Nenhum registro de auditoria
                            </td>
                        </tr>
                    <?php
                    else:
                        foreach ($logs as $log):
                            $cor_tipo = match($log->user_type) {
                                'Administrador' => '#2d5016',
                                'Funcionario' => '#10b981',
                                'Comissao' => '#f59e0b',
                                default => '#6b7280'
                            };
                    ?>
                        <tr style="border-bottom: 1px solid #e5e7eb;">
                            <td style="padding: 15px;">
                                <div style="font-weight: 600; color: #1f2937;"><?php echo htmlspecialchars($log->user_id); ?></div>
                            </td>
                            <td style="padding: 15px;">
                                <span style="display: inline-block; padding: 4px 12px; background: <?php echo $cor_tipo; ?>; color: white; border-radius: 6px; font-size: 12px; font-weight: 600;">
                                    <?php echo htmlspecialchars($log->user_type); ?>
                                </span>
                            </td>
                            <td style="padding: 15px; color: #1f2937;">
                                <?php echo htmlspecialchars($log->action); ?>
                            </td>
                            <td style="padding: 15px; color: #6b7280; font-size: 13px;">
                                <?php 
                                if ($log->alvo_tipo) {
                                    echo htmlspecialchars($log->alvo_tipo);
                                    if ($log->alvo_id) echo ' #' . $log->alvo_id;
                                }
                                ?>
                            </td>
                            <td style="padding: 15px; color: #6b7280; font-size: 13px;">
                                <?php echo date('d/m/Y H:i:s', strtotime($log->created_at)); ?>
                            </td>
                        </tr>
                    <?php
                        endforeach;
                    endif;
                    ?>
                </tbody>
            </table>
        </div>

<?php include '../includes/layout-footer.php'; ?>
