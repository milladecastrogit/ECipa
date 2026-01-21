<?php
session_start();

// Verificar se é Administrador
if (!isset($_SESSION['user_id']) || $_SESSION['user_tipo'] !== 'Administrador') {
    header('Location: login.php');
    exit;
}

require_once '../config/conexao.php';

$mensagem = '';
$tipo_mensagem = '';

// Processar deleção de funcionário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'deletar') {
    $funcionario_id = $_POST['funcionario_id'] ?? null;
    
    try {
        // Verificar se existe
        $stmt = $pdo->prepare("SELECT nome FROM funcionario WHERE id = ?");
        $stmt->execute([$funcionario_id]);
        $func = $stmt->fetch();
        
        if (!$func) {
            throw new Exception('Funcionário não encontrado');
        }
        
        // Deletar
        $stmt = $pdo->prepare("DELETE FROM funcionario WHERE id = ?");
        $stmt->execute([$funcionario_id]);
        
        // Registrar auditoria
        $pdo->prepare("
            INSERT INTO audit_log (user_type, user_id, action, alvo_tipo, alvo_id, detalhes)
            VALUES (?, ?, 'Funcionário deletado', 'Funcionario', ?, ?)
        ")->execute([
            $_SESSION['user_tipo'],
            $_SESSION['user_id'],
            $funcionario_id,
            json_encode(['nome' => $func->nome])
        ]);
        
        $mensagem = '✅ Funcionário deletado com sucesso!';
        $tipo_mensagem = 'sucesso';
    } catch (Exception $e) {
        $mensagem = '❌ Erro: ' . $e->getMessage();
        $tipo_mensagem = 'erro';
    }
}

// Buscar funcionários
$stmt = $pdo->prepare("SELECT * FROM funcionario ORDER BY nome ASC");
$stmt->execute();
$funcionarios = $stmt->fetchAll(PDO::FETCH_OBJ);
?>
<?php require_once '../includes/layout-header.php'; ?>

<div style="max-width: 1200px; margin: 30px auto; padding: 20px;">
    <h1 style="color: #009002; margin-bottom: 10px; font-size: 28px;">👥 Gerenciar Funcionários</h1>
    <p style="color: #6b7280; margin-bottom: 30px;">Visualizar, editar e deletar funcionários cadastrados</p>

    <?php if ($mensagem): ?>
        <div style="padding: 15px; border-radius: 8px; margin-bottom: 20px; background: <?php echo $tipo_mensagem === 'sucesso' ? '#d4edda' : '#f8d7da'; ?>; color: <?php echo $tipo_mensagem === 'sucesso' ? '#155724' : '#721c24'; ?>; border: 1px solid <?php echo $tipo_mensagem === 'sucesso' ? '#c3e6cb' : '#f5c6cb'; ?>;">
            <?php echo $mensagem; ?>
        </div>
    <?php endif; ?>

    <div style="background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: linear-gradient(to right, #009002, #007001); color: white;">
                    <th style="padding: 15px; text-align: left; font-weight: 600;">Nome</th>
                    <th style="padding: 15px; text-align: left; font-weight: 600;">Email</th>
                    <th style="padding: 15px; text-align: left; font-weight: 600;">CPF</th>
                    <th style="padding: 15px; text-align: left; font-weight: 600;">Tipo</th>
                    <th style="padding: 15px; text-align: left; font-weight: 600;">Status</th>
                    <th style="padding: 15px; text-align: center; font-weight: 600;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($funcionarios as $func): ?>
                    <tr style="border-bottom: 1px solid #e5e7eb;">
                        <td style="padding: 15px; color: #1f2937;"><?php echo htmlspecialchars($func->nome); ?></td>
                        <td style="padding: 15px; color: #6b7280;"><?php echo htmlspecialchars($func->email); ?></td>
                        <td style="padding: 15px; color: #6b7280;"><?php echo htmlspecialchars($func->cpf); ?></td>
                        <td style="padding: 15px;">
                            <span style="display: inline-block; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; background: 
                                <?php 
                                    if ($func->tipo === 'Administrador') echo '#3b82f6; color: white;';
                                    elseif ($func->tipo === 'Comissao') echo '#f59e0b; color: #92400e;';
                                    else echo '#10b981; color: white;';
                                ?>">
                                <?php echo $func->tipo; ?>
                            </span>
                        </td>
                        <td style="padding: 15px;">
                            <span style="display: inline-block; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; background: 
                                <?php 
                                    if ($func->status === 'Ativo') echo '#10b981; color: white;';
                                    elseif ($func->status === 'Pendente') echo '#f59e0b; color: #92400e;';
                                    else echo '#ef4444; color: white;';
                                ?>">
                                <?php echo $func->status; ?>
                            </span>
                        </td>
                        <td style="padding: 15px; text-align: center;">
                            <?php if ($func->id !== $_SESSION['user_id']): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="acao" value="deletar">
                                    <input type="hidden" name="funcionario_id" value="<?php echo $func->id; ?>">
                                    <button type="submit" onclick="return confirm('⚠️ Deletar ' + '<?php echo htmlspecialchars($func->nome); ?>' + '?')" style="padding: 6px 12px; background: #ef4444; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 600;">
                                        🗑️ Deletar
                                    </button>
                                </form>
                            <?php else: ?>
                                <span style="color: #6b7280; font-size: 12px;">Seu usuário</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <p style="color: #6b7280; margin-top: 30px; text-align: center; font-size: 14px;">
        Total de funcionários: <strong><?php echo count($funcionarios); ?></strong>
    </p>
</div>

<?php require_once '../includes/layout-footer.php'; ?>
