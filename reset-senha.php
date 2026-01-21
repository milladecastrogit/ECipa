<?php
require_once 'config/conexao.php';

// Gerar hash correto
$nova_senha = password_hash("password", PASSWORD_DEFAULT);

// Atualizar Comissão
$stmt = $pdo->prepare("UPDATE funcionario SET senha = ? WHERE email = 'comissao@ecipa.com.br'");
$stmt->execute([$nova_senha]);

echo "✅ Senha atualizada para Comissão!<br>";
echo "Nova hash: " . $nova_senha . "<br>";

// Atualizar Admin também
$stmt = $pdo->prepare("UPDATE funcionario SET senha = ? WHERE email = 'admin@ecipa.com.br'");
$stmt->execute([$nova_senha]);

echo "✅ Senha atualizada para Admin!<br>";

// Verificar
$stmt = $pdo->prepare("SELECT email, tipo, status FROM funcionario WHERE email IN ('admin@ecipa.com.br', 'comissao@ecipa.com.br')");
$stmt->execute();
$usuarios = $stmt->fetchAll();

echo "<h3>Usuários atualizados:</h3>";
foreach ($usuarios as $user) {
    echo "📧 " . $user->email . " | 👤 " . $user->tipo . " | ✅ " . $user->status . "<br>";
}

echo "<br><strong>Credenciais:</strong><br>";
echo "Email: admin@ecipa.com.br ou comissao@ecipa.com.br<br>";
echo "Senha: password<br>";
?>
