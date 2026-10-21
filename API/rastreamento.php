<?php
// api/rastreamento.php
require_once '../config/database.php';
if (!isset($_SESSION['usuario_id'])) jsonResponse(['erro' => 'Não autenticado'], 401);

$pdo = getConnection();

$sql = "SELECT p.id, p.codigo, p.cliente, p.endereco, p.status,
               m.nome AS motorista_nome, v.placa AS veiculo_placa,
               (SELECT latitude FROM rastreamento WHERE pedido_id = p.id ORDER BY id DESC LIMIT 1) AS lat,
               (SELECT longitude FROM rastreamento WHERE pedido_id = p.id ORDER BY id DESC LIMIT 1) AS lng
        FROM pedidos p
        LEFT JOIN motoristas m ON m.veiculo_id = (SELECT veiculo_id FROM motoristas LIMIT 1) -- Simulação de vínculo
        LEFT JOIN veiculos v ON m.veiculo_id = v.id
        WHERE p.status IN ('Em Rota', 'Entregue', 'Pendente')";

$pedidos = $pdo->query($sql)->fetchAll();

// Simulador de GPS para testes (já que o da empresa é bloqueado)
// Coordenadas aleatórias em volta de São Paulo
foreach ($pedidos as &$p) {
    if (empty($p['lat']) || empty($p['lng'])) {
        $p['lat'] = -23.550520 + (rand(-500, 500) / 10000);
        $p['lng'] = -46.633308 + (rand(-500, 500) / 10000);
        $p['simulado'] = true;
    } else {
        $p['simulado'] = false;
    }
    $p['motorista_nome'] = $p['motorista_nome'] ?? 'João da Silva (Simulado)';
}

jsonResponse($pedidos);