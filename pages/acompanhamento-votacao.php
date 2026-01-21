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

// Verificar se pode ver (Admin ou Comissao)
if ($_SESSION['user_tipo'] !== 'Administrador' && $_SESSION['user_tipo'] !== 'Comissao') {
    header('Location: eleicao.php');
    exit;
}

// Total de funcionários
$total_funcionarios = $pdo->query("SELECT COUNT(*) as count FROM funcionario WHERE status = 'Ativo'")->fetch(PDO::FETCH_OBJ)->count;

// Total de candidatos aprovados
$total_candidatos = $pdo->prepare("
    SELECT COUNT(DISTINCT c.id) as count 
    FROM candidatura c 
    WHERE c.id_eleicao = ? AND c.status = 'Aprovado'
")->fetchOne([$eleicao_id])->count ?? 0;

// Total de votos
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM voto WHERE id_eleicao = ?");
$stmt->execute([$eleicao_id]);
$total_votos = $stmt->fetch(PDO::FETCH_OBJ)->count;

// Total de votos físicos
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM voto WHERE id_eleicao = ? AND voto_fisico = 1");
$stmt->execute([$eleicao_id]);
$total_votos_fisicos = $stmt->fetch(PDO::FETCH_OBJ)->count;

// Total de votos online
$total_votos_online = $total_votos - $total_votos_fisicos;

// Candidatos com votos em tempo real
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
?>
<?php require_once '../includes/layout-header.php'; ?>

<div style="max-width: 1200px; margin: 30px auto; padding: 20px;">
    <h1 style="color: #009002; margin-bottom: 10px;">📊 Acompanhamento de Votação</h1>
    <p style="color: #6b7280; margin-bottom: 30px; font-size: 14px;">Eleição: <strong><?php echo htmlspecialchars($eleicao->titulo); ?></strong> | Data: <strong><?php echo date('d/m/Y', strtotime($eleicao->data_eleicao)); ?></strong></p>

    <!-- Estatísticas Principais -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 40px;">
        <div style="background: white; padding: 20px; border-radius: 10px; border-top: 3px solid #009002; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <p style="color: #6b7280; font-size: 12px; margin-bottom: 5px;">FUNCIONÁRIOS CADASTRADOS</p>
            <h2 style="color: #009002; font-size: 32px; margin: 0;"><?php echo $total_funcionarios; ?></h2>
        </div>

        <div style="background: white; padding: 20px; border-radius: 10px; border-top: 3px solid #007001; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <p style="color: #6b7280; font-size: 12px; margin-bottom: 5px;">CANDIDATOS INSCRITOS</p>
            <h2 style="color: #007001; font-size: 32px; margin: 0;"><?php echo $total_candidatos; ?></h2>
        </div>

        <div style="background: white; padding: 20px; border-radius: 10px; border-top: 3px solid #fbc02d; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <p style="color: #6b7280; font-size: 12px; margin-bottom: 5px;">TOTAL DE VOTOS</p>
            <h2 style="background: linear-gradient(to right, #fbc02d, #fffb3b); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; font-size: 32px; margin: 0;"><?php echo $total_votos; ?></h2>
        </div>

        <div style="background: white; padding: 20px; border-radius: 10px; border-top: 3px solid #ef4444; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <p style="color: #6b7280; font-size: 12px; margin-bottom: 5px;">VOTOS FÍSICOS (CÉDULA)</p>
            <h2 style="color: #ef4444; font-size: 32px; margin: 0;"><?php echo $total_votos_fisicos; ?></h2>
        </div>
    </div>

    <!-- Percentual de Participação -->
    <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 40px;">
        <h3 style="color: #009002; margin-bottom: 15px;">📈 Participação na Votação</h3>
        <?php 
        $percentual = $total_funcionarios > 0 ? round(($total_votos / $total_funcionarios) * 100, 1) : 0;
        ?>
        <div style="background: #f3f4f6; border-radius: 8px; overflow: hidden;">
            <div style="background: linear-gradient(to right, #009002, #007001); width: <?php echo $percentual; ?>%; height: 40px; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                <?php echo $percentual; ?>%
            </div>
        </div>
        <p style="color: #6b7280; margin-top: 10px; font-size: 14px;">
            <strong><?php echo $total_votos; ?></strong> votos de <strong><?php echo $total_funcionarios; ?></strong> funcionários cadastrados
        </p>
    </div>

    <!-- Ranking em Tempo Real -->
    <h2 style="color: #009002; margin-bottom: 20px; margin-top: 40px;">🏆 Ranking em Tempo Real</h2>
    
    <?php if ($candidatos): ?>
        <div style="display: grid; gap: 15px;">
            <?php $posicao = 1; foreach ($candidatos as $candidato): ?>
                <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); display: grid; grid-template-columns: 60px 1fr auto; gap: 20px; align-items: center;">
                    <div style="text-align: center;">
                        <div style="font-size: 28px; font-weight: bold; background: linear-gradient(to right, #fbc02d, #fffb3b); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                            #<?php echo $posicao; ?>
                        </div>
                    </div>
                    
                    <div>
                        <p style="color: #1f2937; font-weight: 600; margin-bottom: 8px;"><?php echo htmlspecialchars($candidato->nome); ?></p>
                        <div style="display: flex; gap: 20px; font-size: 12px;">
                            <span style="color: #009002;"><strong>Online:</strong> <?php echo $candidato->votos_online ?? 0; ?></span>
                            <span style="color: #ef4444;"><strong>Física:</strong> <?php echo $candidato->votos_fisicos ?? 0; ?></span>
                        </div>
                    </div>
                    
                    <div style="text-align: right;">
                        <div style="font-size: 28px; font-weight: bold; color: #009002; margin-bottom: 5px;">
                            <?php echo $candidato->total_votos ?? 0; ?>
                        </div>
                        <small style="color: #6b7280;">votos</small>
                    </div>
                </div>
                <?php $posicao++; endforeach; ?>
        </div>
    <?php else: ?>
        <p style="color: #6b7280; text-align: center; padding: 40px;">Nenhum candidato aprovado ainda.</p>
    <?php endif; ?>

    <!-- Botão de Atualização -->
    <div style="text-align: center; margin-top: 30px;">
        <button onclick="location.reload()" style="padding: 12px 30px; background: #009002; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: background 0.2s;">
            🔄 Atualizar em Tempo Real
        </button>
    </div>

    <!-- Script para atualização automática a cada 30 segundos -->
    <script>
        setInterval(function() {
            location.reload();
        }, 30000); // 30 segundos
    </script>
</div>

<?php require_once '../includes/layout-footer.php'; ?>
