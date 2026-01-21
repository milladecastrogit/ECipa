<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/conexao.php';

$mensagem = '';
$tipo_mensagem = '';
$foto_preview = '';

// Processar candidatura
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eleicao_id = $_POST['eleicao_id'] ?? null;
    $resumo = $_POST['resumo'] ?? '';
    
    if (!$eleicao_id) {
        $mensagem = '❌ Selecione uma eleição!';
        $tipo_mensagem = 'erro';
    } elseif (strlen($resumo) > 300) {
        $mensagem = '❌ Resumo não pode ter mais de 300 caracteres!';
        $tipo_mensagem = 'erro';
    } else {
        try {
            // Processar upload de foto
            $foto_path = null;
            if (isset($_FILES['foto']) && $_FILES['foto']['size'] > 0) {
                $arquivo = $_FILES['foto'];
                
                // Validar tipo
                $tipos_permitidos = ['image/jpeg', 'image/png', 'image/gif'];
                if (!in_array($arquivo['type'], $tipos_permitidos)) {
                    throw new Exception('Tipo de arquivo não permitido. Use: JPG, PNG ou GIF');
                }
                
                // Validar tamanho (máx 5MB)
                if ($arquivo['size'] > 5 * 1024 * 1024) {
                    throw new Exception('Arquivo muito grande. Máximo 5MB');
                }
                
                // Salvar arquivo
                $dir = '../assets/img/candidatos/';
                if (!is_dir($dir)) {
                    mkdir($dir, 0777, true);
                }
                
                $nome_arquivo = 'candidato_' . $_SESSION['user_id'] . '_' . time() . '.' . pathinfo($arquivo['name'], PATHINFO_EXTENSION);
                $caminho_completo = $dir . $nome_arquivo;
                
                if (move_uploaded_file($arquivo['tmp_name'], $caminho_completo)) {
                    $foto_path = 'assets/img/candidatos/' . $nome_arquivo;
                } else {
                    throw new Exception('Erro ao fazer upload da foto');
                }
            }
            
            // Verificar se já é candidato nesta eleição
            $stmt = $pdo->prepare("SELECT id FROM candidatura WHERE id_eleicao = ? AND id_funcionario = ?");
            $stmt->execute([$eleicao_id, $_SESSION['user_id']]);
            
            if ($stmt->fetch()) {
                throw new Exception('Você já é candidato nesta eleição!');
            }
            
            // Inserir candidatura
            $stmt = $pdo->prepare("
                INSERT INTO candidatura (id_eleicao, id_funcionario, foto_path, proposta, status)
                VALUES (?, ?, ?, ?, 'Pendente')
            ");
            $stmt->execute([$eleicao_id, $_SESSION['user_id'], $foto_path, $resumo]);
            
            $candidatura_id = $pdo->lastInsertId();
            
            // Registrar auditoria
            $pdo->prepare("
                INSERT INTO audit_log (user_type, user_id, action, alvo_tipo, alvo_id)
                VALUES (?, ?, 'Candidatura registrada', 'Candidatura', ?)
            ")->execute([$_SESSION['user_tipo'], $_SESSION['user_id'], $candidatura_id]);
            
            $mensagem = '✅ Candidatura registrada com sucesso! Aguarde aprovação do administrador.';
            $tipo_mensagem = 'sucesso';
            
        } catch (Exception $e) {
            $mensagem = '❌ Erro: ' . $e->getMessage();
            $tipo_mensagem = 'erro';
        }
    }
}

// Buscar eleições em período de inscrição
$stmt = $pdo->prepare("
    SELECT * FROM eleicao 
    WHERE status = 'Inscricoes' OR status = 'Votacao'
    ORDER BY data_eleicao DESC
");
$stmt->execute();
$eleicoes = $stmt->fetchAll(PDO::FETCH_OBJ);

// Buscar minhas candidaturas
$stmt = $pdo->prepare("
    SELECT 
        c.id, c.created_at, c.status, c.foto_path,
        e.titulo as eleicao_titulo
    FROM candidatura c
    LEFT JOIN eleicao e ON c.id_eleicao = e.id
    WHERE c.id_funcionario = ?
    ORDER BY c.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$minhas_candidaturas = $stmt->fetchAll(PDO::FETCH_OBJ);
?>
<?php require_once '../includes/layout-header.php'; ?>

<div style="max-width: 900px; margin: 30px auto; padding: 20px;">
    <h1 style="color: #009002; margin-bottom: 30px; font-size: 28px;">📝 Candidatar-se</h1>

    <?php if ($mensagem): ?>
        <div style="padding: 15px; border-radius: 8px; margin-bottom: 20px; background: <?php echo $tipo_mensagem === 'sucesso' ? '#d4edda' : '#f8d7da'; ?>; color: <?php echo $tipo_mensagem === 'sucesso' ? '#155724' : '#721c24'; ?>; border: 1px solid <?php echo $tipo_mensagem === 'sucesso' ? '#c3e6cb' : '#f5c6cb'; ?>;">
            <?php echo $mensagem; ?>
        </div>
    <?php endif; ?>

    <!-- Formulário de Candidatura -->
    <form method="POST" enctype="multipart/form-data" style="background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 40px;">
        
        <h2 style="color: #009002; margin-bottom: 20px; font-size: 20px;">Nova Candidatura</h2>

        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; color: #009002; font-weight: 600;">Eleição *</label>
            <select name="eleicao_id" required style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 14px;">
                <option value="">-- Selecione uma eleição --</option>
                <?php foreach ($eleicoes as $eleicao): ?>
                    <option value="<?php echo $eleicao->id; ?>">
                        <?php echo htmlspecialchars($eleicao->titulo); ?> 
                        (<?php echo date('d/m/Y', strtotime($eleicao->data_eleicao)); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (empty($eleicoes)): ?>
                <small style="color: #ef4444;">Nenhuma eleição disponível no momento</small>
            <?php endif; ?>
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; color: #009002; font-weight: 600;">Foto do Perfil</label>
            <input type="file" name="foto" accept="image/*" style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 14px;">
            <small style="color: #6b7280; display: block; margin-top: 5px;">JPG, PNG ou GIF | Máximo 5MB</small>
            
            <!-- Preview da foto -->
            <div id="foto-preview" style="margin-top: 15px; text-align: center;">
                <div style="width: 150px; height: 150px; border: 2px dashed #e5e7eb; border-radius: 10px; margin: 0 auto; display: flex; align-items: center; justify-content: center; background: #f9fafb;">
                    <span id="preview-text" style="color: #6b7280; text-align: center;">
                        📷 Preview da foto<br><small>Selecione uma imagem</small>
                    </span>
                    <img id="preview-img" src="" style="display: none; width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">
                </div>
            </div>
        </div>

        <div style="margin-bottom: 25px;">
            <label style="display: block; margin-bottom: 8px; color: #009002; font-weight: 600;">Resumo do Perfil (máx 300 caracteres)</label>
            <textarea name="resumo" placeholder="Descreva brevemente sua experiência e motivação para candidatar-se..." maxlength="300" style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 14px; font-family: Arial; resize: vertical; min-height: 120px;" onkeyup="atualizarContador()"></textarea>
            <small style="color: #6b7280; display: block; margin-top: 5px;">
                <span id="contador">0</span>/300 caracteres
            </small>
        </div>

        <button type="submit" style="width: 100%; padding: 14px; background: linear-gradient(to right, #009002, #007001); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 16px; transition: transform 0.2s;">
            ✅ Candidatar-se
        </button>
    </form>

    <!-- Minhas Candidaturas -->
    <h2 style="color: #009002; margin-bottom: 20px; font-size: 20px;">Minhas Candidaturas</h2>
    
    <?php if (empty($minhas_candidaturas)): ?>
        <p style="color: #6b7280; text-align: center; padding: 40px; background: white; border-radius: 10px;">
            Você ainda não tem candidaturas registradas.
        </p>
    <?php else: ?>
        <div style="display: grid; gap: 15px;">
            <?php foreach ($minhas_candidaturas as $cand): ?>
                <div style="background: white; padding: 20px; border-radius: 10px; border-left: 4px solid <?php echo $cand->status === 'Aprovado' ? '#10b981' : ($cand->status === 'Rejeitado' ? '#ef4444' : '#f59e0b'); ?>; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <div style="display: grid; grid-template-columns: 120px 1fr auto; gap: 20px; align-items: flex-start;">
                        <!-- Foto -->
                        <div>
                            <?php if ($cand->foto_path): ?>
                                <img src="../<?php echo htmlspecialchars($cand->foto_path); ?>" style="width: 120px; height: 120px; border-radius: 8px; object-fit: cover; border: 2px solid #e5e7eb;">
                            <?php else: ?>
                                <div style="width: 120px; height: 120px; border-radius: 8px; background: #f3f4f6; display: flex; align-items: center; justify-content: center; color: #6b7280;">
                                    Sem foto
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Info -->
                        <div>
                            <h3 style="color: #009002; margin-bottom: 5px;"><?php echo htmlspecialchars($cand->eleicao_titulo); ?></h3>
                            <p style="color: #6b7280; font-size: 13px; margin-bottom: 10px;">
                                Candidatura em: <?php echo date('d/m/Y H:i', strtotime($cand->created_at)); ?>
                            </p>
                            <span style="display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; background: 
                                <?php 
                                    if ($cand->status === 'Aprovado') echo '#d1fae5; color: #059669;';
                                    elseif ($cand->status === 'Rejeitado') echo '#fee2e2; color: #dc2626;';
                                    else echo '#fef3c7; color: #92400e;';
                                ?>">
                                <?php echo $cand->status; ?>
                            </span>
                        </div>

                        <!-- Botões -->
                        <div>
                            <a href="gerar-pdf.php?tipo=candidatura&id=<?php echo $cand->id; ?>" style="display: inline-block; padding: 8px 16px; background: #fbc02d; color: #333; text-decoration: none; border-radius: 6px; font-size: 12px; font-weight: 600;">
                                📄 PDF
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/layout-footer.php'; ?>

<script>
function atualizarContador() {
    const textarea = document.querySelector('[name="resumo"]');
    const contador = document.getElementById('contador');
    contador.textContent = textarea.value.length;
}

// Preview de foto
document.querySelector('[name="foto"]').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            const previewImg = document.getElementById('preview-img');
            const previewText = document.getElementById('preview-text');
            previewImg.src = event.target.result;
            previewImg.style.display = 'block';
            previewText.style.display = 'none';
        };
        reader.readAsDataURL(file);
    }
});
</script>
