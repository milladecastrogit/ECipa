<?php
session_start();
require_once '../config/conexao.php';

$pageTitle = 'Eleições';

include '../includes/layout-header.php';
?>

        <div class="page-header">
            <h1 class="page-title">Eleições</h1>
            <p class="page-subtitle">Acompanhe as eleições disponíveis da CIPA</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
            <?php
            $stmt = $pdo->prepare("SELECT * FROM eleicao ORDER BY data_eleicao DESC");
            $stmt->execute();
            $eleicoes = $stmt->fetchAll();

            if (empty($eleicoes)):
            ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; color: #6b7280;">
                    <p style="font-size: 18px;">Nenhuma eleição disponível no momento</p>
                </div>
            <?php
            else:
                foreach ($eleicoes as $eleicao):
                    $statusClass = match($eleicao->status) {
                        'Planejamento' => '#f59e0b',
                        'Inscricoes' => '#3b82f6',
                        'Votacao' => '#10b981',
                        'Finalizada' => '#6b7280',
                        default => '#2d5016'
                    };
                    
                    $statusText = match($eleicao->status) {
                        'Planejamento' => 'Em Planejamento',
                        'Inscricoes' => 'Inscrições Abertas',
                        'Votacao' => 'Votação em Andamento',
                        'Finalizada' => 'Finalizada',
                        default => $eleicao->status
                    };
            ?>
                <div style="background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-left: 4px solid <?php echo $statusClass; ?>;">
                    <h3 style="margin-bottom: 10px; color: #1f2937;"><?php echo htmlspecialchars($eleicao->titulo); ?></h3>
                    
                    <div style="margin-bottom: 15px; font-size: 13px; color: #6b7280;">
                        <p><strong>Posse:</strong> <?php echo date('d/m/Y', strtotime($eleicao->data_posse)); ?></p>
                        <?php if ($eleicao->data_eleicao): ?>
                            <p><strong>Eleição:</strong> <?php echo date('d/m/Y', strtotime($eleicao->data_eleicao)); ?></p>
                        <?php endif; ?>
                    </div>

                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px;">
                        <span style="display: inline-block; padding: 4px 12px; background: <?php echo $statusClass; ?>; color: white; border-radius: 6px; font-size: 12px; font-weight: 600;">
                            <?php echo $statusText; ?>
                        </span>
                    </div>

                    <div style="display: flex; gap: 10px;">
                        <a href="votacao.php?id=<?php echo $eleicao->id; ?>" style="flex: 1; padding: 10px; background: #2d5016; color: white; text-align: center; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 600; transition: background 0.3s;">
                            Votar
                        </a>
                        <a href="resultado.php?id=<?php echo $eleicao->id; ?>" style="flex: 1; padding: 10px; background: #e5e7eb; color: #1f2937; text-align: center; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 600; transition: background 0.3s;">
                            Resultados
                        </a>
                        <?php if (($_SESSION['user_tipo'] === 'Administrador' || $_SESSION['user_tipo'] === 'Comissao') && ($eleicao->status === 'Planejamento' || $eleicao->status === 'Inscricoes')): ?>
                            <button onclick="deletarEleicao(<?php echo $eleicao->id; ?>, '<?php echo htmlspecialchars($eleicao->titulo); ?>')" style="flex: 0.5; padding: 10px; background: #ef4444; color: white; border: none; border-radius: 6px; font-size: 14px; font-weight: 600; cursor: pointer; transition: background 0.3s;">
                                🗑️
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php
                endforeach;
            endif;
            ?>
        </div>

<?php include '../includes/layout-footer.php'; ?>

<script>
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
