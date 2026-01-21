<?php
session_start();
require_once '../config/conexao.php';

$pageTitle = 'Resultados';

include '../includes/layout-header.php';

$eleicao_id = $_GET['id'] ?? null;

if ($eleicao_id):
    $stmt = $pdo->prepare("SELECT * FROM eleicao WHERE id = ?");
    $stmt->execute([$eleicao_id]);
    $eleicao = $stmt->fetch();
    
    if (!$eleicao):
        echo '<p>Eleição não encontrada</p>';
        exit;
    endif;
    
    // Buscar candidatos com contagem de votos
    $stmt = $pdo->prepare("
        SELECT c.*, f.nome, COUNT(v.id) as total_votos 
        FROM candidatura c
        LEFT JOIN funcionario f ON c.id_funcionario = f.id
        LEFT JOIN voto v ON v.id_candidato = c.id AND v.id_eleicao = ?
        WHERE c.id_eleicao = ?
        GROUP BY c.id
        ORDER BY total_votos DESC
    ");
    $stmt->execute([$eleicao_id, $eleicao_id]);
    $resultados = $stmt->fetchAll();
    
    // Total de votos
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM voto WHERE id_eleicao = ?");
    $stmt->execute([$eleicao_id]);
    $total_votos = $stmt->fetch()->total;
?>

        <div class="page-header">
            <h1 class="page-title">Resultados - <?php echo htmlspecialchars($eleicao->titulo); ?></h1>
            <p class="page-subtitle">Status: <strong><?php echo $eleicao->status; ?></strong> | Total de votos: <strong><?php echo $total_votos; ?></strong></p>
        </div>

        <div style="background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 20px;">
            <h2 style="margin-bottom: 20px; color: #1f2937;">Ranking de Candidatos</h2>
            
            <?php
            // Calcular máximo de votos com segurança
            $votos_array = array_map(function($r) { return $r->total_votos; }, $resultados);
            $max_votos = !empty($votos_array) ? max($votos_array) : 0;
            $max_votos = $max_votos ?: 1;
            
            if (empty($resultados)):
            ?>
                <p style="text-align: center; color: #6b7280; padding: 20px;">Nenhum resultado disponível</p>
            <?php
            else:
                foreach ($resultados as $index => $resultado):
                    $percentual = $total_votos > 0 ? ($resultado->total_votos / $total_votos) * 100 : 0;
                    $largura_barra = $max_votos > 0 ? ($resultado->total_votos / $max_votos) * 100 : 0;
            ?>
                <div style="margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span style="font-weight: bold; color: #2d5016; font-size: 18px;"><?php echo $index + 1; ?>º</span>
                            <span style="color: #1f2937; font-weight: 600;"><?php echo htmlspecialchars($resultado->nome); ?></span>
                        </div>
                        <div style="text-align: right;">
                            <span style="font-weight: bold; color: #1f2937; font-size: 18px;"><?php echo $resultado->total_votos; ?></span>
                            <span style="color: #6b7280; margin-left: 10px;"><?php echo round($percentual, 1); ?>%</span>
                        </div>
                    </div>
                    <div style="width: 100%; height: 30px; background: #e5e7eb; border-radius: 6px; overflow: hidden;">
                        <div style="width: <?php echo $largura_barra; ?>%; height: 100%; background: linear-gradient(90deg, #2d5016 0%, #1a3a0a 100%); transition: width 0.3s ease;"></div>
                    </div>
                </div>
            <?php
                endforeach;
            endif;
            ?>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
            <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-top: 4px solid #2d5016;">
                <div style="font-size: 32px; margin-bottom: 10px;">✅</div>
                <div style="color: #6b7280; font-size: 14px; margin-bottom: 5px;">Total de Votos</div>
                <div style="font-size: 28px; font-weight: bold; color: #1f2937;"><?php echo $total_votos; ?></div>
            </div>

            <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-top: 4px solid #10b981;">
                <div style="font-size: 32px; margin-bottom: 10px;">🏆</div>
                <div style="color: #6b7280; font-size: 14px; margin-bottom: 5px;">Vencedor(a)</div>
                <div style="font-size: 20px; font-weight: bold; color: #1f2937;">
                    <?php 
                    if (!empty($resultados)) {
                        echo htmlspecialchars($resultados[0]->nome);
                    } else {
                        echo '-';
                    }
                    ?>
                </div>
            </div>

            <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-top: 4px solid #f59e0b;">
                <div style="font-size: 32px; margin-bottom: 10px;">📅</div>
                <div style="color: #6b7280; font-size: 14px; margin-bottom: 5px;">Data da Eleição</div>
                <div style="font-size: 16px; font-weight: bold; color: #1f2937;">
                    <?php echo $eleicao->data_eleicao ? date('d/m/Y', strtotime($eleicao->data_eleicao)) : '-'; ?>
                </div>
            </div>
        </div>

<?php
else:
?>
        <p>Eleição não selecionada</p>
<?php
endif;

include '../includes/layout-footer.php';
?>
