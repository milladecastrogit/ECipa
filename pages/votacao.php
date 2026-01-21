<?php
session_start();
require_once '../config/conexao.php';

$pageTitle = 'Votação';

include '../includes/layout-header.php';

$eleicao_id = $_GET['id'] ?? null;

// Buscar eleição
if ($eleicao_id):
    $stmt = $pdo->prepare("SELECT * FROM eleicao WHERE id = ?");
    $stmt->execute([$eleicao_id]);
    $eleicao = $stmt->fetch();
    
    if (!$eleicao):
        echo '<p>Eleição não encontrada</p>';
        exit;
    endif;
    
    // Buscar candidatos aprovados
    $stmt = $pdo->prepare("
        SELECT c.*, f.nome FROM candidatura c
        JOIN funcionario f ON c.id_funcionario = f.id
        WHERE c.id_eleicao = ? AND c.status = 'Aprovado'
        ORDER BY f.nome
    ");
    $stmt->execute([$eleicao_id]);
    $candidatos = $stmt->fetchAll();
?>

        <div class="page-header">
            <h1 class="page-title">Votação - <?php echo htmlspecialchars($eleicao->titulo); ?></h1>
            <p class="page-subtitle">Selecione seu candidato</p>
        </div>

        <?php if ($eleicao->status !== 'Votacao'): ?>
            <div style="background: #fef3c7; border: 1px solid #fbbf24; padding: 15px; border-radius: 8px; margin-bottom: 20px; color: #92400e;">
                <strong>⚠️ Atenção:</strong> A votação não está aberta no momento. Status atual: <strong><?php echo $eleicao->status; ?></strong>
            </div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
            <?php
            if (empty($candidatos)):
            ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; color: #6b7280;">
                    <p style="font-size: 18px;">Nenhum candidato disponível nesta eleição</p>
                </div>
            <?php
            else:
                foreach ($candidatos as $candidato):
            ?>
                <div style="background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <div style="font-size: 60px; text-align: center; margin-bottom: 15px;">👤</div>
                    <h3 style="text-align: center; margin-bottom: 10px; color: #1f2937;"><?php echo htmlspecialchars($candidato->nome); ?></h3>
                    
                    <?php if ($candidato->proposta): ?>
                        <p style="color: #6b7280; font-size: 14px; margin-bottom: 15px;">
                            <?php echo htmlspecialchars(substr($candidato->proposta, 0, 100)); ?>...
                        </p>
                    <?php endif; ?>

                    <div style="text-align: center; margin-bottom: 15px;">
                        <span style="display: inline-block; padding: 4px 12px; background: #2d5016; color: white; border-radius: 6px; font-size: 12px; font-weight: 600;">
                            📊 <?php echo $candidato->votos_count; ?> votos
                        </span>
                    </div>

                    <?php if ($eleicao->status === 'Votacao'): ?>
                        <button onclick="votar(<?php echo $candidato->id; ?>, <?php echo $eleicao_id; ?>)" style="width: 100%; padding: 10px; background: #2d5016; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
                            ✅ Votar
                        </button>
                    <?php else: ?>
                        <button disabled style="width: 100%; padding: 10px; background: #d1d5db; color: #6b7280; border: none; border-radius: 8px; cursor: not-allowed; font-weight: 600;">
                            Votação fechada
                        </button>
                    <?php endif; ?>
                </div>
            <?php
                endforeach;
            endif;
            ?>
        </div>

<?php
else:
?>
        <p>Eleição não selecionada</p>
<?php
endif;

include '../includes/layout-footer.php';
?>

<script>
function votar(candidato_id, eleicao_id) {
    if (confirm('Tem certeza que deseja votar neste candidato?')) {
        fetch('../api/votacao.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                candidato_id: candidato_id,
                eleicao_id: eleicao_id
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.sucesso) {
                alert('Voto registrado com sucesso!');
                location.reload();
            } else {
                alert('Erro: ' + data.mensagem);
            }
        })
        .catch(error => {
            alert('Erro ao registrar voto: ' + error);
        });
    }
}
</script>
