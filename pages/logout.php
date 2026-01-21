<?php
session_start();
require_once '../config/conexao.php';

if (isset($_SESSION['user_id'])) {
    registrarLog($pdo, $_SESSION['user_tipo'], $_SESSION['user_id'], 'Logout efetuado');
}

session_destroy();
header('Location: login.php');
exit;
?>
