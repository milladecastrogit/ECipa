<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die(json_encode(['error' => 'Não autorizado']));
}

// Verifica se é Admin ou Comissão
if (!in_array($_SESSION['user_tipo'], ['Administrador', 'Comissao'])) {
    http_response_code(403);
    die(json_encode(['error' => 'Permissão negada']));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['error' => 'Método não permitido']));
}

require_once '../config/conexao.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['candidato_id'])) {
    http_response_code(400);
    die(json_encode(['error' => 'ID do candidato não fornecido']));
}

$candidato_id = intval($data['candidato_id']);

try {
    // Buscar informações do candidato para auditoria
    $stmt = $pdo->prepare("
        SELECT c.*, f.nome FROM candidatura c
        JOIN funcionario f ON c.id_funcionario = f.id
        WHERE c.id = :id
    ");
    $stmt->execute([':id' => $candidato_id]);
    $candidato = $stmt->fetch(PDO::FETCH_OBJ);

    if (!$candidato) {
        http_response_code(404);
        die(json_encode(['error' => 'Candidato não encontrado']));
    }

    // Deletar candidato
    $stmt = $pdo->prepare("DELETE FROM candidatura WHERE id = :id");
    $stmt->execute([':id' => $candidato_id]);

    // Registro de auditoria
    $stmt = $pdo->prepare("
        INSERT INTO audit_log (usuario_id, acao, descricao, created_at) 
        VALUES (:usuario_id, :acao, :descricao, NOW())
    ");
    $stmt->execute([
        ':usuario_id' => $_SESSION['user_id'],
        ':acao' => 'CANDIDATO_DELETADO',
        ':descricao' => "Candidato {$candidato->nome} deletado",
    ]);

    echo json_encode(['success' => true, 'message' => 'Candidato deletado com sucesso']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao deletar candidato']);
}
?>
