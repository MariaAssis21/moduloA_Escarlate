<?php
// api/resolver_alerta.php
require_once '../config/database.php';
if (!isset($_SESSION['usuario_id'])) jsonResponse(['erro' => 'Não autenticado'], 401);

$id = (int) ($_POST['id'] ?? 0);
if ($id <= 0) jsonResponse(['erro' => 'ID inválido'], 400);

$pdo = getConnection();
$pdo->prepare("UPDATE logs_notificacoes SET status = 'Resolvido', resolvido_em = NOW() WHERE id = ?")->execute([$id]);

jsonResponse(['sucesso' => true]);