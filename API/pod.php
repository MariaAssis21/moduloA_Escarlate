<?php
// api/pod.php
require_once '../config/database.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_perfil'] !== 'motorista') {
    jsonResponse(['erro' => 'Acesso negado'], 403);
}

$pedido_id = (int) ($_POST['pedido_id'] ?? 0);
$assinatura = trim($_POST['assinatura'] ?? '');
$lat = (float) ($_POST['latitude'] ?? 0);
$lng = (float) ($_POST['longitude'] ?? 0);

if ($pedido_id <= 0) {
    jsonResponse(['erro' => 'Pedido inválido'], 400);
}

// Processa a imagem (converte para Base64 para salvar no BD de forma simples)
$fotoBase64 = null;
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $imageData = file_get_contents($_FILES['foto']['tmp_name']);
    $fotoBase64 = 'data:image/jpeg;base64,' . base64_encode($imageData);
}

$pdo = getConnection();
$sql = "UPDATE pedidos SET status = 'Entregue', entregue_em = NOW(), comprovante_foto = ?, comprovante_assinatura = ?, latitude = ?, longitude = ? WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$fotoBase64, $assinatura, $lat, $lng, $pedido_id]);

jsonResponse(['sucesso' => true, 'mensagem' => 'Entrega confirmada com sucesso!']);