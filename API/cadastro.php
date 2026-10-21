<?php
// api/cadastro.php
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['erro' => 'Método não permitido'], 405);
}

$nome = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? '');
$senha = $_POST['senha'] ?? '';
$perfil = $_POST['perfil'] ?? '';

// Validações básicas
if (empty($nome) || empty($email) || empty($senha) || empty($perfil)) {
    jsonResponse(['erro' => 'Todos os campos são obrigatórios'], 400);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(['erro' => 'E-mail inválido'], 400);
}

if (strlen($senha) < 6) {
    jsonResponse(['erro' => 'A senha deve ter no mínimo 6 caracteres'], 400);
}

$perfisPermitidos = ['admin', 'gestor', 'despachante', 'motorista'];
if (!in_array($perfil, $perfisPermitidos)) {
    jsonResponse(['erro' => 'Perfil inválido'], 400);
}

$pdo = getConnection();

// Verifica se e-mail já existe
$stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    jsonResponse(['erro' => 'Este e-mail já está cadastrado'], 409);
}

// Cria o hash seguro da senha
$senhaHash = password_hash($senha, PASSWORD_DEFAULT);

try {
    $sql = "INSERT INTO usuarios (nome, email, senha, perfil) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nome, $email, $senhaHash, $perfil]);

    jsonResponse(['sucesso' => true, 'mensagem' => 'Usuário cadastrado com sucesso!']);
} catch (PDOException $e) {
    jsonResponse(['erro' => 'Erro ao cadastrar usuário'], 500);
}