<?php
// index.php
require_once 'config/database.php';

// Se já logado, redireciona
if (isset($_SESSION['usuario_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>SENN AI-X - Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="logo">
            <i class="fa-solid fa-truck-fast"></i>
            <h1>SENN AI-X</h1>
            <p>Gestão Logística Inteligente</p>
        </div>

        <form id="login-form">
            <div class="input-group">
                <i class="fa-solid fa-envelope"></i>
                <input type="email" name="email" placeholder="E-mail" required>
            </div>
            <div class="input-group">
                <i class="fa-solid fa-lock"></i>
                <input type="password" name="senha" placeholder="Senha" required>
            </div>
            <div class="input-group">
                <i class="fa-solid fa-user-tag"></i>
                <select name="perfil" required>
                    <option value="" disabled selected>Selecione seu Perfil</option>
                    <option value="admin">Administrador</option>
                    <option value="gestor">Gestor de Frota</option>
                    <option value="despachante">Despachante</option>
                    <option value="motorista">Motorista</option>
                </select>
            </div>
            <button type="submit" class="btn-primary">Entrar no Sistema</button>
            <p id="login-error" class="error-msg"></p>
            <div style="text-align: center; margin-top: 15px;">
    <a href="cadastro.php" style="color: var(--primary-color); text-decoration: none; font-size: 0.9rem;">Não tem conta? Cadastre-se</a>
</div>
        </form>
    </div>
    <script src="assets/js/app.js"></script>
</body>
</html>