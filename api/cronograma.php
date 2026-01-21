<?php
header('Content-Type: application/json');
require_once '../config/conexao.php';

/**
 * Lógica de Cálculo de Prazos E-CIPA (Baseado na NR-5)
 * Referência: Data da Posse
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data_posse = $_POST['data_posse'] ?? null;

    if (!$data_posse) {
        echo json_encode(['error' => 'Data da posse não informada']);
        exit;
    }

    $posse = new DateTime($data_posse);

    // Cálculos baseados na data da posse
    $cronograma = [
        'edital_convocacao' => (clone $posse)->modify('-60 days')->format('Y-m-d'),
        'comissao_eleitoral' => (clone $posse)->modify('-55 days')->format('Y-m-d'),
        'envio_sindicato' => (clone $posse)->modify('-55 days')->format('Y-m-d'),
        'inicio_inscricoes' => (clone $posse)->modify('-45 days')->format('Y-m-d'),
        'fim_inscricoes' => (clone $posse)->modify('-30 days')->format('Y-m-d'),
        'eleicao' => (clone $posse)->modify('-15 days')->format('Y-m-d'),
        'resultado' => (clone $posse)->modify('-14 days')->format('Y-m-d'),
        'curso_cipeiros' => (clone $posse)->modify('-5 days')->format('Y-m-d'),
        'posse' => $posse->format('Y-m-d')
    ];

    echo json_encode($cronograma);
} else {
    echo json_encode(['error' => 'Método não permitido']);
}
?>
