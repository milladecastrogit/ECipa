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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = $_POST['titulo'] ?? '';
    $data_posse = $_POST['data_posse'] ?? '';
    $data_inicio_inscricao = $_POST['data_inicio_inscricao'] ?? '';
    $data_fim_inscricao = $_POST['data_fim_inscricao'] ?? '';
    $data_eleicao = $_POST['data_eleicao'] ?? '';
    $status = $_POST['status'] ?? 'Planejamento';

    if (!$titulo || !$data_posse || !$data_eleicao) {
        $mensagem = 'Preencha todos os campos obrigatórios!';
        $tipo_mensagem = 'erro';
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO eleicao (titulo, data_posse, data_inicio_inscricao, data_fim_inscricao, data_eleicao, status)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$titulo, $data_posse, $data_inicio_inscricao, $data_fim_inscricao, $data_eleicao, $status]);
            
            $mensagem = '✅ Eleição criada com sucesso!';
            $tipo_mensagem = 'sucesso';
        } catch (PDOException $e) {
            $mensagem = 'Erro ao criar eleição: ' . $e->getMessage();
            $tipo_mensagem = 'erro';
        }
    }
}

// Buscar eleições existentes
$eleicoes = $pdo->query("SELECT * FROM eleicao ORDER BY data_eleicao DESC LIMIT 10")->fetchAll(PDO::FETCH_OBJ);
?>
<?php require_once '../includes/layout-header.php'; ?>

<div style="max-width: 900px; margin: 30px auto; padding: 20px;">
    <h1 style="color: #009002; margin-bottom: 30px; font-size: 28px;">📋 Criar Nova Eleição</h1>

    <?php if ($mensagem): ?>
        <div style="padding: 15px; border-radius: 8px; margin-bottom: 20px; background: <?php echo $tipo_mensagem === 'sucesso' ? '#d4edda' : '#f8d7da'; ?>; color: <?php echo $tipo_mensagem === 'sucesso' ? '#155724' : '#721c24'; ?>; border: 1px solid <?php echo $tipo_mensagem === 'sucesso' ? '#c3e6cb' : '#f5c6cb'; ?>;">
            <?php echo $mensagem; ?>
        </div>
    <?php endif; ?>

    <!-- Formulário de Criação -->
    <form method="POST" style="background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 40px;">
        
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; color: #009002; font-weight: 600;">Título da Eleição *</label>
            <input type="text" name="titulo" placeholder="Ex: CIPA 2024-2026" required style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 14px;">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <label style="display: block; margin-bottom: 8px; color: #009002; font-weight: 600;">Data de Posse *</label>
                <input type="date" name="data_posse" id="data_posse" required style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 14px;">
                <small style="color: #6b7280; display: block; margin-top: 5px;">Data em que os eleitos começam o mandato</small>
            </div>
            
            <div>
                <label style="display: block; margin-bottom: 8px; color: #009002; font-weight: 600;">Data da Eleição *</label>
                <input type="date" name="data_eleicao" id="data_eleicao" required style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 14px;" onchange="calcularDatasInscricao()">
                <small style="color: #6b7280; display: block; margin-top: 5px;">Data em que acontecerá a votação</small>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <label style="display: block; margin-bottom: 8px; color: #009002; font-weight: 600;">Início de Inscrições</label>
                <input type="date" name="data_inicio_inscricao" id="data_inicio_inscricao" style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 14px;" readonly>
                <small style="color: #6b7280; display: block; margin-top: 5px;">Calculado automaticamente (30 dias antes da eleição)</small>
            </div>
            
            <div>
                <label style="display: block; margin-bottom: 8px; color: #009002; font-weight: 600;">Fim de Inscrições</label>
                <input type="date" name="data_fim_inscricao" id="data_fim_inscricao" style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 14px;" readonly>
                <small style="color: #6b7280; display: block; margin-top: 5px;">Calculado automaticamente (5 dias antes da eleição)</small>
            </div>
        </div>

        <div style="margin-bottom: 25px;">
            <label style="display: block; margin-bottom: 8px; color: #009002; font-weight: 600;">Status da Eleição *</label>
            <select name="status" style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 14px;">
                <option value="Planejamento">Planejamento</option>
                <option value="Inscricoes">Inscrições Abertas</option>
                <option value="Votacao">Votação Aberta</option>
                <option value="Finalizada">Finalizada</option>
            </select>
        </div>

        <button type="submit" style="width: 100%; padding: 14px; background: linear-gradient(to right, #009002, #007001); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 16px; transition: transform 0.2s;">
            ✅ Criar Eleição
        </button>
    </form>

    <!-- Eleições Anteriores -->
    <h2 style="color: #009002; margin-top: 50px; margin-bottom: 20px; font-size: 22px;">📅 Eleições Anteriores</h2>
    
    <?php if ($eleicoes): ?>
        <div style="display: grid; gap: 15px;">
            <?php foreach ($eleicoes as $eleicao): ?>
                <div style="background: white; padding: 20px; border-radius: 10px; border-left: 4px solid #009002; box-shadow: 0 2px 4px rgba(0,0,0,0.1); display: grid; grid-template-columns: 1fr auto; align-items: center; gap: 20px;">
                    <div>
                        <h3 style="color: #009002; margin-bottom: 8px; font-size: 18px;"><?php echo htmlspecialchars($eleicao->titulo); ?></h3>
                        <p style="color: #6b7280; margin-bottom: 5px;"><strong>Eleição:</strong> <?php echo date('d/m/Y', strtotime($eleicao->data_eleicao)); ?></p>
                        <p style="color: #6b7280; margin-bottom: 5px;"><strong>Posse:</strong> <?php echo date('d/m/Y', strtotime($eleicao->data_posse)); ?></p>
                        <p style="color: #6b7280;"><strong>Status:</strong> 
                            <span style="display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; background: 
                                <?php 
                                    if ($eleicao->status === 'Planejamento') echo '#f59e0b; color: #92400e;';
                                    elseif ($eleicao->status === 'Inscricoes') echo '#3b82f6; color: white;';
                                    elseif ($eleicao->status === 'Votacao') echo '#10b981; color: white;';
                                    else echo '#6b7280; color: white;';
                                ?>">
                                <?php echo $eleicao->status; ?>
                            </span>
                        </p>
                    </div>
                    <div style="text-align: right;">
                        <a href="editar-eleicao.php?id=<?php echo $eleicao->id; ?>" style="display: inline-block; padding: 10px 20px; background: #009002; color: white; text-decoration: none; border-radius: 6px; margin-bottom: 10px; transition: background 0.2s;">
                            ✏️ Editar
                        </a>
                        <br>
                        <a href="dashboard-eleicao.php?id=<?php echo $eleicao->id; ?>" style="display: inline-block; padding: 10px 20px; background: #fbc02d; color: #333; text-decoration: none; border-radius: 6px; margin-bottom: 10px; transition: background 0.2s;">
                            📊 Detalhes
                        </a>
                        <br>
                        <?php if ($eleicao->status === 'Planejamento' || $eleicao->status === 'Inscricoes'): ?>
                            <button onclick="deletarEleicao(<?php echo $eleicao->id; ?>, '<?php echo htmlspecialchars($eleicao->titulo); ?>')" style="padding: 10px 20px; background: #ef4444; color: white; border: none; border-radius: 6px; cursor: pointer; transition: background 0.2s;">
                                🗑️ Deletar
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p style="color: #6b7280; text-align: center; padding: 40px;">Nenhuma eleição criada ainda.</p>
    <?php endif; ?>
</div>

<?php require_once '../includes/layout-footer.php'; ?>

<script>
/**
 * Calcula as datas de inscrição conforme legislação de CIPA
 * Lei 5.616/70 - Regulamenta as CIPA
 * 
 * Cronograma padrão:
 * - 30 dias antes: Início de inscrições
 * - 5 dias antes: Fim de inscrições
 * - Data definida: Data da eleição
 */
function calcularDatasInscricao() {
    const dataEleicao = document.getElementById('data_eleicao').value;
    
    if (!dataEleicao) {
        document.getElementById('data_inicio_inscricao').value = '';
        document.getElementById('data_fim_inscricao').value = '';
        return;
    }
    
    // Converter string de data para objeto Date
    const eleicao = new Date(dataEleicao + 'T00:00:00');
    
    // Calcular data de início (30 dias antes)
    const dataInicio = new Date(eleicao);
    dataInicio.setDate(dataInicio.getDate() - 30);
    
    // Calcular data de fim (5 dias antes)
    const dataFim = new Date(eleicao);
    dataFim.setDate(dataFim.getDate() - 5);
    
    // Formatar para YYYY-MM-DD
    function formatarData(data) {
        const ano = data.getFullYear();
        const mes = String(data.getMonth() + 1).padStart(2, '0');
        const dia = String(data.getDate()).padStart(2, '0');
        return `${ano}-${mes}-${dia}`;
    }
    
    // Preencher campos automaticamente
    document.getElementById('data_inicio_inscricao').value = formatarData(dataInicio);
    document.getElementById('data_fim_inscricao').value = formatarData(dataFim);
}

// Calcular também quando mudar a data de posse
if (document.getElementById('data_posse')) {
    document.getElementById('data_posse').addEventListener('change', function() {
        const posse = new Date(this.value + 'T00:00:00');
        const eleicaoSugerida = new Date(posse);
        eleicaoSugerida.setDate(eleicaoSugerida.getDate() - 5);
        console.log('💡 Data de posse: ' + this.value + 
            ' → Sugestão de eleição: ' + 
            eleicaoSugerida.toISOString().split('T')[0]);
    });
}

// Função para deletar eleição
function deletarEleicao(eleicaoId, titulo) {
    if (!confirm('⚠️ Tem certeza que deseja deletar a eleição "' + titulo + '"?\n\nEsta ação é irreversível!')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('eleicao_id', eleicaoId);
    
    fetch('../api/deletar-eleicao.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.sucesso) {
            alert('✅ ' + data.mensagem);
            location.reload();
        } else {
            alert('❌ Erro: ' + data.erro);
        }
    })
    .catch(error => {
        alert('❌ Erro ao processar: ' + error);
    });
}
</script>
