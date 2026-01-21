<?php
session_start();
require_once '../config/conexao.php';

if ($_SESSION['user_tipo'] !== 'Administrador') {
    header('Location: eleicao.php');
    exit;
}

$pageTitle = 'Gerenciar Usuários';

include '../includes/layout-header.php';

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST'):
    $acao = $_POST['acao'] ?? '';
    $user_id = $_POST['user_id'] ?? '';
    
    if ($acao === 'aprovar'):
        $stmt = $pdo->prepare("UPDATE funcionario SET status = 'Ativo' WHERE id = ?");
        $stmt->execute([$user_id]);
        registrarLog($pdo, $_SESSION['user_tipo'], $_SESSION['user_id'], 'Aprovação de usuário', 'Funcionario', $user_id);
    elseif ($acao === 'rejeitar'):
        $stmt = $pdo->prepare("UPDATE funcionario SET status = 'Inativo' WHERE id = ?");
        $stmt->execute([$user_id]);
        registrarLog($pdo, $_SESSION['user_tipo'], $_SESSION['user_id'], 'Rejeição de usuário', 'Funcionario', $user_id);
    endif;
endif;

// Buscar usuários pendentes
$stmt = $pdo->prepare("SELECT * FROM funcionario WHERE status = 'Pendente' ORDER BY created_at ASC");
$stmt->execute();
$pendentes = $stmt->fetchAll();

// Buscar usuários ativos
$stmt = $pdo->prepare("SELECT * FROM funcionario WHERE status = 'Ativo' ORDER BY nome ASC");
$stmt->execute();
$ativos = $stmt->fetchAll();
?>

        <div class="page-header">
            <h1 class="page-title">Gerenciar Usuários</h1>
            <p class="page-subtitle">Aprovar ou rejeitar registros de novos usuários</p>
        </div>

        <!-- USUÁRIOS PENDENTES -->
        <div style="margin-bottom: 40px;">
            <h2 style="color: #1f2937; margin-bottom: 20px; font-size: 20px;">
                ⏳ Pendentes de Aprovação (<?php echo count($pendentes); ?>)
            </h2>

            <?php if (empty($pendentes)): ?>
                <div style="background: white; border-radius: 12px; padding: 30px; text-align: center; color: #6b7280; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    Nenhum usuário pendente de aprovação
                </div>
            <?php else: ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
                    <?php foreach ($pendentes as $user): ?>
                        <div style="background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-left: 4px solid #f59e0b;">
                            <h3 style="color: #1f2937; margin-bottom: 10px; font-size: 16px;"><?php echo htmlspecialchars($user->nome); ?></h3>
                            <p style="color: #6b7280; font-size: 13px; margin-bottom: 8px;">📧 <?php echo htmlspecialchars($user->email); ?></p>
                            <p style="color: #6b7280; font-size: 13px; margin-bottom: 8px;">🆔 CPF: <?php echo htmlspecialchars($user->cpf); ?></p>
                            <p style="color: #6b7280; font-size: 13px; margin-bottom: 15px;">📅 Cadastro: <?php echo date('d/m/Y H:i', strtotime($user->created_at)); ?></p>

                            <div style="display: flex; gap: 10px;">
                                <form action="" method="POST" style="flex: 1;">
                                    <input type="hidden" name="acao" value="aprovar">
                                    <input type="hidden" name="user_id" value="<?php echo $user->id; ?>">
                                    <button type="submit" style="width: 100%; padding: 10px; background: #10b981; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 13px;">✅ Aprovar</button>
                                </form>
                                <form action="" method="POST" style="flex: 1;">
                                    <input type="hidden" name="acao" value="rejeitar">
                                    <input type="hidden" name="user_id" value="<?php echo $user->id; ?>">
                                    <button type="submit" style="width: 100%; padding: 10px; background: #ef4444; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 13px;">❌ Rejeitar</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- USUÁRIOS ATIVOS -->
        <div>
            <h2 style="color: #1f2937; margin-bottom: 20px; font-size: 20px;">
                ✅ Usuários Ativos (<?php echo count($ativos); ?>)
            </h2>

            <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: linear-gradient(135deg, #009002 0%, #007001 100%); color: white;">
                            <th style="padding: 15px; text-align: left; font-weight: 600;">Nome</th>
                            <th style="padding: 15px; text-align: left; font-weight: 600;">E-mail</th>
                            <th style="padding: 15px; text-align: left; font-weight: 600;">CPF</th>
                            <th style="padding: 15px; text-align: left; font-weight: 600;">Tipo</th>
                            <th style="padding: 15px; text-align: left; font-weight: 600;">Data Cadastro</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ativos as $user): ?>
                            <tr style="border-bottom: 1px solid #e5e7eb;">
                                <td style="padding: 15px; color: #1f2937;"><?php echo htmlspecialchars($user->nome); ?></td>
                                <td style="padding: 15px; color: #6b7280;"><?php echo htmlspecialchars($user->email); ?></td>
                                <td style="padding: 15px; color: #6b7280;"><?php echo htmlspecialchars($user->cpf); ?></td>
                                <td style="padding: 15px;">
                                    <span style="display: inline-block; padding: 4px 12px; background: 
                                        <?php echo match($user->tipo) {
                                            'Administrador' => '#009002',
                                            'Comissao' => '#f59e0b',
                                            default => '#3b82f6'
                                        }; ?>; color: white; border-radius: 6px; font-size: 12px; font-weight: 600;">
                                        <?php echo match($user->tipo) {
                                            'Administrador' => 'Administrador',
                                            'Comissao' => 'Comissão',
                                            default => 'Funcionário'
                                        }; ?>
                                    </span>
                                </td>
                                <td style="padding: 15px; color: #6b7280; font-size: 13px;"><?php echo date('d/m/Y', strtotime($user->created_at)); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

<?php include '../includes/layout-footer.php'; ?>
