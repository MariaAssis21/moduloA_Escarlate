<?php
// api/login.php
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['erro' => 'Método não permitido'], 405);
}

$email = trim($_POST['email'] ?? '');
$senha = $_POST['senha'] ?? '';
$perfil = $_POST['perfil'] ?? '';

if (empty($email) || empty($senha) || empty($perfil)) {
    jsonResponse(['erro' => 'Preencha todos os campos'], 400);
}

$pdo = getConnection();
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ? AND perfil = ? AND status = 'Ativo'");
$stmt->execute([$email, $perfil]);
$usuario = $stmt->fetch();

if (!$usuario || !password_verify($senha, $usuario['senha'])) {
    jsonResponse(['erro' => 'E-mail, senha ou perfil inválidos'], 401);
}

// Autenticado — grava na sessão
$_SESSION['usuario_id'] = $usuario['id'];
$_SESSION['usuario_nome'] = $usuario['nome'];
$_SESSION['usuario_perfil'] = $usuario['perfil'];

jsonResponse([
    'sucesso' => true,
    'usuario' => [
        'id' => $usuario['id'],
        'nome' => $usuario['nome'],
        'perfil' => $usuario['perfil']
    ]
]);