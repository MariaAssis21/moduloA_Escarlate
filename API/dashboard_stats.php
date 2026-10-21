<?php
// api/dashboard_stats.php
require_once '../config/database.php';
if (!isset($_SESSION['usuario_id'])) jsonResponse(['erro' => 'Não autenticado'], 401);

$pdo = getConnection();

// Contagem de Pedidos
$pedidos_pendentes = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE status = 'Pendente'")->fetchColumn();
$pedidos_entregues = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE status = 'Entregue'")->fetchColumn();

// Contagem de Frota
$frota_disponivel = $pdo->query("SELECT COUNT(*) FROM veiculos WHERE status = 'Disponível'")->fetchColumn();
$frota_rota = $pdo->query("SELECT COUNT(*) FROM veiculos WHERE status = 'Em Rota'")->fetchColumn();
$frota_manutencao = $pdo->query("SELECT COUNT(*) FROM veiculos WHERE status = 'Em Manutenção'")->fetchColumn();

jsonResponse([
    'pedidos_pendentes' => (int)$pedidos_pendentes,
    'pedidos_entregues' => (int)$pedidos_entregues,
    'frota_disponivel' => (int)$frota_disponivel,
    'frota_rota' => (int)$frota_rota,
    'frota_manutencao' => (int)$frota_manutencao
]);