<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['erro' => 'Não autenticado']);
    exit;
}

require_once '../config/conexao.php';

$eleicao_id = $_POST['eleicao_id'] ?? null;
$candidato_id = $_POST['candidato_id'] ?? null;
$tipo_voto = $_POST['tipo_voto'] ?? 'Nominal';

if (!$eleicao_id) {
    http_response_code(400);
    echo json_encode(['erro' => 'Eleição não especificada']);
    exit;
}

try {
    // Verificar se eleição existe e está em votação
    $stmt = $pdo->prepare("SELECT * FROM eleicao WHERE id = ? AND status = 'Votacao'");
    $stmt->execute([$eleicao_id]);
    $eleicao = $stmt->fetch(PDO::FETCH_OBJ);
    
    if (!$eleicao) {
        throw new Exception('Eleição não encontrada ou não está em votação');
    }

    // Verificar se já votou
    $stmt = $pdo->prepare("SELECT id FROM voto WHERE id_eleicao = ? AND id_funcionario = ?");
    $stmt->execute([$eleicao_id, $_SESSION['user_id']]);
    
    if ($stmt->fetch()) {
        throw new Exception('Você já votou nesta eleição!');
    }

    // Gerar código de verificação criptografado
    $cod_verificacao = hash('sha256', $_SESSION['user_id'] . $eleicao_id . time() . random_bytes(16));

    // Registrar voto
    $stmt = $pdo->prepare("
        INSERT INTO voto (id_eleicao, id_funcionario, id_candidato, tipo_voto, cod_verificacao, data_voto)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$eleicao_id, $_SESSION['user_id'], $candidato_id ?: null, $tipo_voto, $cod_verificacao]);

    $voto_id = $pdo->lastInsertId();

    // Atualizar contador do candidato
    if ($candidato_id) {
        $pdo->prepare("
            UPDATE candidatura 
            SET votos_count = (SELECT COUNT(*) FROM voto WHERE id_candidato = id AND id_eleicao = ?)
            WHERE id = ? AND id_eleicao = ?
        ")->execute([$eleicao_id, $candidato_id, $eleicao_id]);
    }

    // Registrar auditoria
    $pdo->prepare("
        INSERT INTO audit_log (user_type, user_id, action, alvo_tipo, alvo_id, detalhes)
        VALUES (?, ?, 'Voto registrado', 'Voto', ?, ?)
    ")->execute([
        $_SESSION['user_tipo'],
        $_SESSION['user_id'],
        $voto_id,
        json_encode([
            'eleicao_id' => $eleicao_id,
            'tipo_voto' => $tipo_voto,
            'candidato_id' => $candidato_id
        ])
    ]);

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Voto registrado com sucesso!',
        'cod_verificacao' => substr($cod_verificacao, 0, 16) . '...',
        'voto_id' => $voto_id
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['erro' => $e->getMessage()]);
}
?>
