<?php
// api/ia_previsao.php
require_once '../config/database.php';
if (!isset($_SESSION['usuario_id'])) jsonResponse(['erro' => 'Não autenticado'], 401);

$pdo = getConnection();

// 1. Pega a média de pedidos por dia da semana
$sql = "SELECT WEEKDAY(criado_em) as dia_semana, COUNT(*) as total 
        FROM pedidos 
        GROUP BY WEEKDAY(criado_em)";
$stmt = $pdo->query($sql);
$historico = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$diasNomes = [0 => 'Dom', 1 => 'Seg', 2 => 'Ter', 3 => 'Qua', 4 => 'Qui', 5 => 'Sex', 6 => 'Sáb'];

$previsao = [];
$totalPedidos = array_sum($historico);
$mediaGeral = $totalPedidos > 0 ? round($totalPedidos / 7) : 5;

// 2. Gera a previsão para os próximos 7 dias
for ($i = 0; $i < 7; $i++) {
    $timestamp = strtotime("+$i days");
    $data = date('Y-m-d', $timestamp);
    $diaSemanaPHP = (int) date('w', $timestamp);
    
    $qtdHistorico = $historico[$diaSemanaPHP] ?? $mediaGeral;
    $variacao = rand(-10, 10) / 100;
    $previsaoQtd = max(1, round($qtdHistorico * (1 + $variacao)));

    $previsao[] = [
        'data' => date('d/m', $timestamp),
        'dia_semana' => $diasNomes[$diaSemanaPHP],
        'quantidade' => $previsaoQtd
    ];
}

// 3. Gera a previsão para o mês inteiro
$previsao_mes = [];
$dias_no_mes = (int)date('t');

for ($dia = 1; $dia <= $dias_no_mes; $dia++) {
    $data_dia = date('Y-m-') . str_pad($dia, 2, '0', STR_PAD_LEFT);
    $dia_semana = (int) date('w', strtotime($data_dia));
    
    $qtdBase = $mediaGeral;
    if ($dia_semana == 0 || $dia_semana == 6) {
        $qtdBase = round($mediaGeral * 0.5); 
    }
    
    $variacao = rand(-5, 5) / 100;
    $qtdPrevista = max(0, round($qtdBase * (1 + $variacao)));

    $previsao_mes[] = [
        'dia' => $dia,
        'quantidade' => $qtdPrevista
    ];
}

// Retorna tudo junto
jsonResponse([
    'previsao_7_dias' => $previsao,
    'projecao_30_dias' => array_sum(array_column($previsao, 'quantidade')) * 4,
    'previsao_mes' => $previsao_mes
]);