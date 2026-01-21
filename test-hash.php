<?php
// Gerar hash correto para a senha "password"
$hash = password_hash("password", PASSWORD_DEFAULT);
echo "Hash gerado: " . $hash;
echo "\n";

// Testar se o hash funciona
if (password_verify("password", $hash)) {
    echo "✅ Hash válido - senha 'password' funciona";
} else {
    echo "❌ Hash inválido";
}
?>
