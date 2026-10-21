<?php
// api/ia_rotas.php
require_once '../config/database.php';
if (!isset($_SESSION['usuario_id'])) jsonResponse(['erro' => 'Não autenticado'], 401);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(['erro' => 'Método inválido'], 405);

$veiculo_id = (int) ($_POST['veiculo_id'] ?? 0);
if ($veiculo_id <= 0) jsonResponse(['erro' => 'Selecione um veículo'], 400);

$pdo = getConnection();

// 1. Pega o veículo (Ponto de partida)
$stmt = $pdo->prepare("SELECT placa, latitude, longitude FROM veiculos WHERE id = ?");
$stmt->execute([$veiculo_id]);
$veiculo = $stmt->fetch();

if (!$veiculo) jsonResponse(['erro' => 'Veículo não encontrado'], 404);

$rota = [];
$rota[] = [
    'tipo' => 'origem',
    'nome' => 'Base (' . $veiculo['placa'] . ')',
    'lat' => (float)$veiculo['latitude'],
    'lng' => (float)$veiculo['longitude']
];

// 2. Pega todos os pedidos pendentes (Destinos)
$pedidos = $pdo->query("SELECT id, codigo, cliente, latitude, longitude, peso_kg FROM pedidos WHERE status = 'Pendente'")->fetchAll();

$qtdPedidos = count($pedidos);
$distanciaTotal = 0;
$latAtual = (float)$veiculo['latitude'];
$lngAtual = (float)$veiculo['longitude'];

// Função matemática para calcular distância entre duas coordenadas (Fórmula de Haversine)
function calculaDistancia($lat1, $lon1, $lat2, $lon2) {
    $theta = $lon1 - $lon2;
    $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
    $dist = acos($dist);
    $dist = rad2deg($dist);
    $miles = $dist * 60 * 1.1515;
    return ($miles * 1.609344); // Retorna em KM
}

// 3. ALGORITMO DE OTIMIZAÇÃO (Vizinho Mais Próximo)
$pedidosRestantes = $pedidos;

while (count($pedidosRestantes) > 0) {
    $menorDistancia = PHP_FLOAT_MAX;
    $indiceMaisProximo = -1;

    foreach ($pedidosRestantes as $index => $p) {
        $dist = calculaDistancia($latAtual, $lngAtual, (float)$p['latitude'], (float)$p['longitude']);
        
        if ($dist < $menorDistancia) {
            $menorDistancia = $dist;
            $indiceMaisProximo = $index;
        }
    }

    // Adiciona o pedido mais próximo à rota
    $proximoPedido = $pedidosRestantes[$indiceMaisProximo];
    
    $rota[] = [
        'tipo' => 'destino',
        'id' => $proximoPedido['id'],
        'nome' => $proximoPedido['codigo'] . ' - ' . $proximoPedido['cliente'],
        'lat' => (float)$proximoPedido['latitude'],
        'lng' => (float)$proximoPedido['longitude'],
        'distancia_anterior' => round($menorDistancia, 2)
    ];

    $distanciaTotal += $menorDistancia;
    
    // Atualiza a localização atual para o pedido que acabou de ser visitado
    $latAtual = (float)$proximoPedido['latitude'];
    $lngAtual = (float)$proximoPedido['longitude'];

    // Remove o pedido da lista para não ser visitado de novo
    unset($pedidosRestantes[$indiceMaisProximo]);
    $pedidosRestantes = array_values($pedidosRestantes);
}

// ==========================================
// NOVO: SALVAR A ORDEM DA ROTA NO BANCO
// ==========================================
$pdo->beginTransaction();
try {
    // Limpa ordens anteriores de rotas não iniciadas para não misturar
    $pdo->query("UPDATE pedidos SET ordem_rota = NULL WHERE status = 'Pendente'");
    
    $ordem = 1;
    foreach ($rota as $ponto) {
        if ($ponto['tipo'] === 'destino') {
            $stmt = $pdo->prepare("UPDATE pedidos SET ordem_rota = ? WHERE id = ?");
            $stmt->execute([$ordem, $ponto['id']]);
            $ordem++;
        }
    }
    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    jsonResponse(['erro' => 'Erro ao salvar a rota no banco de dados'], 500);
}

// Simulação de custo (Ex: R$ 2,50 por KM rodado + R$ 5 por entrega)
$custoEstimado = ($distanciaTotal * 2.50) + ($qtdPedidos * 5);

jsonResponse([
    'sucesso' => true,
    'veiculo' => $veiculo['placa'],
    'rota' => $rota,
    'distancia_total_km' => round($distanciaTotal, 2),
    'custo_estimado' => number_format($custoEstimado, 2, ',', '.'),
    'total_entregas' => $qtdPedidos
]);