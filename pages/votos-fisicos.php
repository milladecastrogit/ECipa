<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_tipo'] !== 'Comissao') {
    header('Location: login.php');
    exit;
}

require_once '../config/conexao.php';

$eleicao_id = $_GET['id'] ?? 0;
$mensagem = '';
$tipo_mensagem = '';

// Buscar eleição
$stmt = $pdo->prepare("SELECT * FROM eleicao WHERE id = ? AND status = 'Votacao'");
$stmt->execute([$eleicao_id]);
$eleicao = $stmt->fetch(PDO::FETCH_OBJ);

if (!$eleicao) {
    header('Location: eleicao.php');
    exit;
}

// Processar voto físico
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cpf_funcionario'], $_POST['id_candidato'])) {
    $cpf = str_replace(['.', '-'], '', $_POST['cpf_funcionario']);
    $id_candidato = $_POST['id_candidato'];
    
    // Buscar funcionário
    $stmt = $pdo->prepare("SELECT id FROM funcionario WHERE REPLACE(REPLACE(cpf, '.', ''), '-', '') = ? AND status = 'Ativo'");
    $stmt->execute([$cpf]);
    $funcionario = $stmt->fetch(PDO::FETCH_OBJ);
    
    if (!$funcionario) {
        $mensagem = '❌ CPF não encontrado ou funcionário inativo!';
        $tipo_mensagem = 'erro';
    } else {
        // Verificar se já votou
        $stmt = $pdo->prepare("SELECT id FROM voto WHERE id_eleicao = ? AND id_funcionario = ?");
        $stmt->execute([$eleicao_id, $funcionario->id]);
        
        if ($stmt->fetch()) {
            $mensagem = '⚠️ Este funcionário já votou nesta eleição!';
            $tipo_mensagem = 'erro';
        } else {
            // Registrar voto físico
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO voto (id_eleicao, id_funcionario, id_candidato, tipo_voto, voto_fisico, data_voto)
                    VALUES (?, ?, ?, 'Fisico', 1, NOW())
                ");
                $stmt->execute([$eleicao_id, $funcionario->id, $id_candidato ?: null]);
                
                // Atualizar contador de votos do candidato
                $pdo->prepare("
                    UPDATE candidatura 
                    SET votos_count = (SELECT COUNT(*) FROM voto WHERE id_candidato = id AND id_eleicao = ?)
                    WHERE id = ? AND id_eleicao = ?
                ")->execute([$eleicao_id, $id_candidato, $eleicao_id]);
                
                $mensagem = '✅ Voto físico registrado com sucesso!';
                $tipo_mensagem = 'sucesso';
            } catch (PDOException $e) {
                $mensagem = '❌ Erro ao registrar voto: ' . $e->getMessage();
                $tipo_mensagem = 'erro';
            }
        }
    }
}

// Buscar candidatos aprovados
$stmt = $pdo->prepare("
    SELECT 
        c.id,
        f.nome,
        COUNT(v.id) as total_votos
    FROM candidatura c
    LEFT JOIN funcionario f ON c.id_funcionario = f.id
    LEFT JOIN voto v ON c.id = v.id_candidato AND v.id_eleicao = ?
    WHERE c.id_eleicao = ? AND c.status = 'Aprovado'
    GROUP BY c.id
    ORDER BY total_votos DESC
");
$stmt->execute([$eleicao_id, $eleicao_id]);
$candidatos = $stmt->fetchAll(PDO::FETCH_OBJ);

// Votos registrados hoje
$stmt = $pdo->prepare("
    SELECT COUNT(*) as count 
    FROM voto 
    WHERE id_eleicao = ? AND voto_fisico = 1 AND DATE(data_voto) = CURDATE()
");
$stmt->execute([$eleicao_id]);
$votos_hoje = $stmt->fetch(PDO::FETCH_OBJ)->count;
?>
<?php require_once '../includes/layout-header.php'; ?>

<div style="max-width: 1000px; margin: 30px auto; padding: 20px;">
    <h1 style="color: #009002; margin-bottom: 10px;">🗳️ Registrar Votos Físicos (Cédula)</h1>
    <p style="color: #6b7280; margin-bottom: 30px;">Eleição: <strong><?php echo htmlspecialchars($eleicao->titulo); ?></strong></p>

    <?php if ($mensagem): ?>
        <div style="padding: 15px; border-radius: 8px; margin-bottom: 20px; background: <?php echo $tipo_mensagem === 'sucesso' ? '#d4edda' : '#f8d7da'; ?>; color: <?php echo $tipo_mensagem === 'sucesso' ? '#155724' : '#721c24'; ?>; border: 1px solid <?php echo $tipo_mensagem === 'sucesso' ? '#c3e6cb' : '#f5c6cb'; ?>;">
            <?php echo $mensagem; ?>
        </div>
    <?php endif; ?>

    <!-- Contador de Votos Hoje -->
    <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 30px; border-top: 3px solid #ef4444;">
        <p style="color: #6b7280; margin: 0; font-size: 12px;">VOTOS FÍSICOS REGISTRADOS HOJE</p>
        <h2 style="color: #ef4444; font-size: 36px; margin: 10px 0 0 0;"><?php echo $votos_hoje; ?></h2>
    </div>

    <!-- Formulário de Registro -->
    <form method="POST" style="background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 40px;">
        
        <h3 style="color: #009002; margin-bottom: 20px; font-size: 18px;">👤 Dados do Eleitor</h3>
        
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; color: #009002; font-weight: 600;">CPF do Funcionário *</label>
            <input type="text" name="cpf_funcionario" placeholder="000.000.000-00" required 
                   pattern="\d{3}\.\d{3}\.\d{3}-\d{2}"
                   style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 14px;">
            <small style="color: #6b7280; display: block; margin-top: 5px;">Digite o CPF do funcionário que está votando</small>
        </div>

        <h3 style="color: #009002; margin-bottom: 20px; margin-top: 30px; font-size: 18px;">🗳️ Selecione o Voto</h3>
        
        <div style="margin-bottom: 25px;">
            <label style="display: block; margin-bottom: 12px; color: #009002; font-weight: 600;">Candidato *</label>
            <div style="display: grid; gap: 10px; max-height: 300px; overflow-y: auto;">
                <label style="padding: 15px; border: 2px solid #e5e7eb; border-radius: 8px; cursor: pointer; transition: all 0.2s; display: flex; align-items: center;">
                    <input type="radio" name="id_candidato" value="" style="margin-right: 10px;">
                    <span style="font-weight: 500;">⚪ Branco</span>
                </label>
                
                <?php foreach ($candidatos as $candidato): ?>
                    <label style="padding: 15px; border: 2px solid #e5e7eb; border-radius: 8px; cursor: pointer; transition: all 0.2s; display: flex; align-items: center;">
                        <input type="radio" name="id_candidato" value="<?php echo $candidato->id; ?>" style="margin-right: 10px;">
                        <div>
                            <span style="font-weight: 600; color: #1f2937;"><?php echo htmlspecialchars($candidato->nome); ?></span>
                            <br>
                            <small style="color: #6b7280;">Votos: <strong><?php echo $candidato->total_votos; ?></strong></small>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <button type="submit" style="width: 100%; padding: 14px; background: linear-gradient(to right, #009002, #007001); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 16px; transition: transform 0.2s;">
            ✅ Confirmar Voto Físico
        </button>
    </form>

    <!-- Ranking Parcial -->
    <h2 style="color: #009002; margin-bottom: 20px;">🏆 Ranking Atual (Votos Físicos)</h2>
    
    <?php if ($candidatos): ?>
        <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid #e5e7eb;">
                        <th style="text-align: left; padding: 12px; color: #009002; font-weight: 600;">Candidato</th>
                        <th style="text-align: center; padding: 12px; color: #009002; font-weight: 600;">Votos Físicos</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($candidatos as $candidato): ?>
                        <tr style="border-bottom: 1px solid #f3f4f6;">
                            <td style="padding: 12px; color: #1f2937;"><?php echo htmlspecialchars($candidato->nome); ?></td>
                            <td style="text-align: center; padding: 12px; font-weight: 600; color: #009002;"><?php echo $candidato->total_votos; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<script>
// Formatar CPF automaticamente
document.querySelector('[name="cpf_funcionario"]').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 0) {
        value = value.substring(0, 11);
        value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
    }
    e.target.value = value;
});
</script>

<?php require_once '../includes/layout-footer.php'; ?>
