<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/conexao.php';

$eleicao_id = $_GET['id'] ?? 0;

// Buscar eleição
$stmt = $pdo->prepare("SELECT * FROM eleicao WHERE id = ?");
$stmt->execute([$eleicao_id]);
$eleicao = $stmt->fetch(PDO::FETCH_OBJ);

if (!$eleicao) {
    header('Location: eleicao.php');
    exit;
}

// Buscar candidatos com breakdown de votos
$stmt = $pdo->prepare("
    SELECT 
        c.id,
        f.nome,
        COUNT(v.id) as total_votos,
        SUM(CASE WHEN v.voto_fisico = 1 THEN 1 ELSE 0 END) as votos_fisicos,
        SUM(CASE WHEN v.voto_fisico = 0 THEN 1 ELSE 0 END) as votos_online
    FROM candidatura c
    LEFT JOIN funcionario f ON c.id_funcionario = f.id
    LEFT JOIN voto v ON c.id = v.id_candidato AND v.id_eleicao = ?
    WHERE c.id_eleicao = ? AND c.status = 'Aprovado'
    GROUP BY c.id
    ORDER BY total_votos DESC
");
$stmt->execute([$eleicao_id, $eleicao_id]);
$candidatos = $stmt->fetchAll(PDO::FETCH_OBJ);

// Total de votos
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM voto WHERE id_eleicao = ?");
$stmt->execute([$eleicao_id]);
$total_votos = $stmt->fetch(PDO::FETCH_OBJ)->count;

// Votos online vs físicos
$stmt = $pdo->prepare("
    SELECT 
        SUM(CASE WHEN voto_fisico = 0 THEN 1 ELSE 0 END) as online,
        SUM(CASE WHEN voto_fisico = 1 THEN 1 ELSE 0 END) as fisicos
    FROM voto WHERE id_eleicao = ?
");
$stmt->execute([$eleicao_id]);
$votos_tipo = $stmt->fetch(PDO::FETCH_OBJ);
$votos_online = $votos_tipo->online ?? 0;
$votos_fisicos = $votos_tipo->fisicos ?? 0;

// Vencedor
$vencedor = $candidatos[0] ?? null;
?>
<?php require_once '../includes/layout-header.php'; ?>

<div style="max-width: 1200px; margin: 30px auto; padding: 20px;">
    <h1 style="color: #009002; margin-bottom: 10px;">🏆 Resultado Final da Eleição</h1>
    <p style="color: #6b7280; margin-bottom: 30px; font-size: 14px;">
        <strong><?php echo htmlspecialchars($eleicao->titulo); ?></strong> | 
        Data: <?php echo date('d/m/Y', strtotime($eleicao->data_eleicao)); ?>
    </p>

    <!-- Estatísticas Principais -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px;">
        <div style="background: white; padding: 20px; border-radius: 10px; border-top: 3px solid #009002; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <p style="color: #6b7280; font-size: 12px; margin-bottom: 5px;">TOTAL DE VOTOS</p>
            <h2 style="color: #009002; font-size: 32px; margin: 0;"><?php echo $total_votos; ?></h2>
        </div>

        <div style="background: white; padding: 20px; border-radius: 10px; border-top: 3px solid #10b981; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <p style="color: #6b7280; font-size: 12px; margin-bottom: 5px;">VOTOS ONLINE</p>
            <h2 style="color: #10b981; font-size: 32px; margin: 0;"><?php echo $votos_online; ?></h2>
        </div>

        <div style="background: white; padding: 20px; border-radius: 10px; border-top: 3px solid #ef4444; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <p style="color: #6b7280; font-size: 12px; margin-bottom: 5px;">VOTOS FÍSICOS (CÉDULA)</p>
            <h2 style="color: #ef4444; font-size: 32px; margin: 0;"><?php echo $votos_fisicos; ?></h2>
        </div>
    </div>

    <!-- Gráfico de Votos Online vs Físicos -->
    <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 40px;">
        <h3 style="color: #009002; margin-bottom: 20px;">📊 Distribuição: Online vs Físico</h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
            <!-- Gráfico em barras -->
            <div>
                <div style="margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                        <span style="font-weight: 600; color: #10b981;">Votos Online</span>
                        <span style="font-weight: 600; color: #10b981;"><?php echo $votos_online; ?></span>
                    </div>
                    <div style="background: #e5e7eb; border-radius: 8px; height: 30px; overflow: hidden;">
                        <div style="background: linear-gradient(to right, #10b981, #059669); height: 100%; width: <?php echo $total_votos > 0 ? ($votos_online / $total_votos) * 100 : 0; ?>%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 12px;">
                            <?php echo $total_votos > 0 ? round(($votos_online / $total_votos) * 100, 1) : 0; ?>%
                        </div>
                    </div>
                </div>

                <div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                        <span style="font-weight: 600; color: #ef4444;">Votos Físicos</span>
                        <span style="font-weight: 600; color: #ef4444;"><?php echo $votos_fisicos; ?></span>
                    </div>
                    <div style="background: #e5e7eb; border-radius: 8px; height: 30px; overflow: hidden;">
                        <div style="background: linear-gradient(to right, #ef4444, #dc2626); height: 100%; width: <?php echo $total_votos > 0 ? ($votos_fisicos / $total_votos) * 100 : 0; ?>%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 12px;">
                            <?php echo $total_votos > 0 ? round(($votos_fisicos / $total_votos) * 100, 1) : 0; ?>%
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resumo -->
            <div style="display: flex; flex-direction: column; justify-content: center;">
                <div style="background: #f3f4f6; padding: 20px; border-radius: 8px; border-left: 4px solid #10b981; margin-bottom: 15px;">
                    <p style="color: #6b7280; font-size: 12px; margin: 0 0 5px 0;">Votos Realizados Online</p>
                    <h3 style="color: #10b981; margin: 0; font-size: 24px;"><?php echo $votos_online; ?> votos</h3>
                </div>
                <div style="background: #f3f4f6; padding: 20px; border-radius: 8px; border-left: 4px solid #ef4444;">
                    <p style="color: #6b7280; font-size: 12px; margin: 0 0 5px 0;">Votos Realizados com Cédula</p>
                    <h3 style="color: #ef4444; margin: 0; font-size: 24px;"><?php echo $votos_fisicos; ?> votos</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Resultado do Vencedor -->
    <?php if ($vencedor): ?>
        <div style="background: linear-gradient(135deg, #fbc02d 0%, #fffb3b 100%); padding: 40px; border-radius: 12px; margin-bottom: 40px; box-shadow: 0 4px 12px rgba(251, 192, 45, 0.3);">
            <h2 style="color: #333; margin-bottom: 20px; text-align: center;">🎉 VENCEDOR(A) DA ELEIÇÃO</h2>
            <h1 style="color: #333; font-size: 36px; margin: 0 0 15px 0; text-align: center;"><?php echo htmlspecialchars($vencedor->nome); ?></h1>
            <p style="color: #333; text-align: center; font-size: 18px; margin: 0;">
                <strong><?php echo $vencedor->total_votos; ?> votos</strong> 
                (<span style="color: #10b981;">Online: <?php echo $vencedor->votos_online; ?></span> + 
                <span style="color: #ef4444;">Físico: <?php echo $vencedor->votos_fisicos; ?></span>)
            </p>
        </div>
    <?php endif; ?>

    <!-- Ranking Completo -->
    <h2 style="color: #009002; margin-bottom: 20px; margin-top: 40px;">🏆 Ranking Completo</h2>
    
    <?php if ($candidatos): ?>
        <div style="display: grid; gap: 15px;">
            <?php $posicao = 1; foreach ($candidatos as $candidato): ?>
                <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); display: grid; grid-template-columns: 70px 1fr auto; gap: 20px; align-items: center;">
                    <div style="text-align: center;">
                        <div style="font-size: 32px; font-weight: bold; background: linear-gradient(to right, #fbc02d, #fffb3b); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                            #<?php echo $posicao; ?>
                        </div>
                    </div>
                    
                    <div>
                        <p style="color: #1f2937; font-weight: 600; margin-bottom: 10px; font-size: 16px;"><?php echo htmlspecialchars($candidato->nome); ?></p>
                        <div style="display: flex; gap: 20px; font-size: 13px;">
                            <span style="background: #d1fae5; color: #059669; padding: 6px 12px; border-radius: 20px;">
                                <strong>Online:</strong> <?php echo $candidato->votos_online ?? 0; ?>
                            </span>
                            <span style="background: #fee2e2; color: #dc2626; padding: 6px 12px; border-radius: 20px;">
                                <strong>Física:</strong> <?php echo $candidato->votos_fisicos ?? 0; ?>
                            </span>
                        </div>
                    </div>
                    
                    <div style="text-align: right;">
                        <div style="font-size: 32px; font-weight: bold; color: #009002; margin-bottom: 5px;">
                            <?php echo $candidato->total_votos ?? 0; ?>
                        </div>
                        <small style="color: #6b7280;">votos</small>
                        <br>
                        <small style="color: #6b7280;">
                            <?php echo $total_votos > 0 ? round(($candidato->total_votos / $total_votos) * 100, 1) : 0; ?>%
                        </small>
                    </div>
                </div>
                <?php $posicao++; endforeach; ?>
        </div>
    <?php else: ?>
        <p style="color: #6b7280; text-align: center; padding: 40px;">Nenhum candidato aprovado nesta eleição.</p>
    <?php endif; ?>

    <!-- Botões de Ação -->
    <div style="text-align: center; margin-top: 40px; display: flex; gap: 10px; justify-content: center;">
        <a href="eleicao.php" style="display: inline-block; padding: 12px 30px; background: #009002; color: white; text-decoration: none; border-radius: 8px; font-weight: 600; transition: background 0.2s;">
            ← Voltar às Eleições
        </a>
        <?php if ($_SESSION['user_tipo'] === 'Administrador'): ?>
            <button onclick="window.print()" style="padding: 12px 30px; background: #fbc02d; color: #333; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: background 0.2s;">
                🖨️ Imprimir Resultado
            </button>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/layout-footer.php'; ?>
