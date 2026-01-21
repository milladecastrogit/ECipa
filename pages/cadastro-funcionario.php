<?php
session_start();
require_once '../config/conexao.php';

if ($_SESSION['user_tipo'] !== 'Administrador') {
    header('Location: eleicao.php');
    exit;
}

$pageTitle = 'Cadastro de Funcionário';
$mensagem = '';

include '../includes/layout-header.php';

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST'):
    $nome = $_POST['nome'] ?? '';
    $cpf = $_POST['cpf'] ?? '';
    $matricula = $_POST['matricula'] ?? '';
    $email = $_POST['email'] ?? '';
    $setor = $_POST['setor'] ?? '';
    $cargo = $_POST['cargo'] ?? '';
    $tipo = $_POST['tipo'] ?? 'Funcionario';
    $senha = password_hash($_POST['senha'] ?? 'senha123', PASSWORD_DEFAULT);
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO funcionario (nome, cpf, matricula, email, setor, cargo, tipo, senha)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([$nome, $cpf, $matricula, $email, $setor, $cargo, $tipo, $senha]);
        $mensagem = '<div style="background: #d1fae5; border: 1px solid #6ee7b7; padding: 15px; border-radius: 8px; color: #065f46; margin-bottom: 20px;">✅ Funcionário cadastrado com sucesso!</div>';
    } catch (Exception $e) {
        $mensagem = '<div style="background: #fee2e2; border: 1px solid #fca5a5; padding: 15px; border-radius: 8px; color: #991b1b; margin-bottom: 20px;">❌ Erro: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
endif;
?>

        <div class="page-header">
            <h1 class="page-title">Cadastro de Funcionário</h1>
            <p class="page-subtitle">Adicione um novo funcionário ao sistema</p>
        </div>

        <?php echo $mensagem; ?>

        <div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); max-width: 800px;">
            <form action="" method="POST">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div>
                        <label style="display: block; color: #374151; font-weight: 600; margin-bottom: 8px; font-size: 14px;">Nome Completo *</label>
                        <input type="text" name="nome" required style="width: 100%; padding: 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px;">
                    </div>
                    <div>
                        <label style="display: block; color: #374151; font-weight: 600; margin-bottom: 8px; font-size: 14px;">CPF *</label>
                        <input type="text" name="cpf" placeholder="000.000.000-00" required style="width: 100%; padding: 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div>
                        <label style="display: block; color: #374151; font-weight: 600; margin-bottom: 8px; font-size: 14px;">Matrícula *</label>
                        <input type="text" name="matricula" required style="width: 100%; padding: 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px;">
                    </div>
                    <div>
                        <label style="display: block; color: #374151; font-weight: 600; margin-bottom: 8px; font-size: 14px;">E-mail *</label>
                        <input type="email" name="email" required style="width: 100%; padding: 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div>
                        <label style="display: block; color: #374151; font-weight: 600; margin-bottom: 8px; font-size: 14px;">Setor</label>
                        <input type="text" name="setor" style="width: 100%; padding: 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px;">
                    </div>
                    <div>
                        <label style="display: block; color: #374151; font-weight: 600; margin-bottom: 8px; font-size: 14px;">Cargo</label>
                        <input type="text" name="cargo" style="width: 100%; padding: 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div>
                        <label style="display: block; color: #374151; font-weight: 600; margin-bottom: 8px; font-size: 14px;">Tipo de Usuário *</label>
                        <select name="tipo" required style="width: 100%; padding: 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px;">
                            <option value="Funcionario">Funcionário</option>
                            <option value="Comissao">Membro da Comissão</option>
                            <option value="Administrador">Administrador</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; color: #374151; font-weight: 600; margin-bottom: 8px; font-size: 14px;">Senha *</label>
                        <input type="password" name="senha" placeholder="Deixe em branco para usar padrão" style="width: 100%; padding: 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px;">
                    </div>
                </div>

                <button type="submit" style="width: 100%; padding: 12px; background: linear-gradient(135deg, #2d5016 0%, #1a3a0a 100%); color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">
                    ➕ Cadastrar Funcionário
                </button>
            </form>
        </div>

<?php include '../includes/layout-footer.php'; ?>
