<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['user_tipo'] !== 'Administrador' && $_SESSION['user_tipo'] !== 'Comissao')) {
    http_response_code(403);
    echo json_encode(['erro' => 'Acesso negado']);
    exit;
}

require_once '../config/conexao.php';

$eleicao_id = $_POST['eleicao_id'] ?? null;

if (!$eleicao_id) {
    http_response_code(400);
    echo json_encode(['erro' => 'ID da eleição não especificado']);
    exit;
}

try {
    // Buscar eleição
    $stmt = $pdo->prepare("SELECT * FROM eleicao WHERE id = ?");
    $stmt->execute([$eleicao_id]);
    $eleicao = $stmt->fetch(PDO::FETCH_OBJ);
    
    if (!$eleicao) {
        throw new Exception('Eleição não encontrada');
    }
    
    // Verificar se pode deletar (só se estiver em planejamento ou inscrições)
    if ($eleicao->status !== 'Planejamento' && $eleicao->status !== 'Inscricoes') {
        throw new Exception('Só é possível deletar eleições em status Planejamento ou Inscrições');
    }
    
    // Deletar eleição (as candidaturas e votos serão deletados em cascata)
    $stmt = $pdo->prepare("DELETE FROM eleicao WHERE id = ?");
    $stmt->execute([$eleicao_id]);
    
    // Registrar auditoria
    $pdo->prepare("
        INSERT INTO audit_log (user_type, user_id, action, alvo_tipo, alvo_id, detalhes)
        VALUES (?, ?, 'Eleição deletada', 'Eleicao', ?, ?)
    ")->execute([
        $_SESSION['user_tipo'],
        $_SESSION['user_id'],
        $eleicao_id,
        json_encode(['titulo' => $eleicao->titulo])
    ]);
    
    echo json_encode(['sucesso' => true, 'mensagem' => 'Eleição deletada com sucesso!']);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['erro' => $e->getMessage()]);
}
?>
