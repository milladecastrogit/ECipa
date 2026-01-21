<?php
/**
 * E-CIPA - Conexão com o Banco de Dados
 */

$host = 'localhost';
$dbname = 'ecipa';
$username = 'root';
$password = ''; // Padrão XAMPP

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // Configurar para lançar exceções em caso de erro
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Configurar fetch mode padrão para objetos
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
    
} catch (PDOException $e) {
    // Em produção, não exibir detalhes do erro
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}

/**
 * Função auxiliar para logs de auditoria
 */
function registrarLog($pdo, $user_type, $user_id, $action, $alvo_tipo = null, $alvo_id = null, $detalhes = null) {
    $sql = "INSERT INTO audit_log (user_type, user_id, action, alvo_tipo, alvo_id, detalhes) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_type, $user_id, $action, $alvo_tipo, $alvo_id, $detalhes]);
}
?>
