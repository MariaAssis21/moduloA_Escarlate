<?php
// cadastro.php
require_once 'config/database.php';

// Se já estiver logado, vai direto pro dashboard
if (isset($_SESSION['usuario_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SENN AI-X - Criar Conta</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="logo">
            <i class="fa-solid fa-truck-fast"></i>
            <h1>Criar Conta</h1>
            <p>Junte-se à SENN AI-X</p>
        </div>

        <form id="cadastro-form">
            <div class="input-group">
                <i class="fa-solid fa-user"></i>
                <input type="text" name="nome" placeholder="Nome Completo" required>
            </div>
            <div class="input-group">
                <i class="fa-solid fa-envelope"></i>
                <input type="email" name="email" placeholder="E-mail" required>
            </div>
            <div class="input-group">
                <i class="fa-solid fa-lock"></i>
                <input type="password" name="senha" placeholder="Senha (mín. 6 caracteres)" required minlength="6">
            </div>
            <div class="input-group">
                <i class="fa-solid fa-user-tag"></i>
                <select name="perfil" required>
                    <option value="" disabled selected>Selecione seu Perfil</option>
                    <option value="gestor">Gestor de Frota</option>
                    <option value="despachante">Despachante</option>
                    <option value="motorista">Motorista</option>
                </select>
            </div>

            <div style="margin-bottom: 15px; display: flex; align-items: flex-start; gap: 10px;">
                <input type="checkbox" id="lgpd" required style="margin-top: 5px; cursor: pointer;">
                <label for="lgpd" style="font-size: 0.8rem; color: #6b7280; line-height: 1.2; cursor: pointer;">
                    Eu concordo com os <a href="#" onclick="alert('Termos de Uso: Seus dados serão utilizados exclusivamente para fins de logística e segurança da frota, conforme a Lei Geral de Proteção de Dados (LGPD). Você pode solicitar a exclusão a qualquer momento.')" style="color: #2563eb;">Termos de Uso e Política de Privacidade</a>.
                </label>
            </div>
            
            <button type="submit" class="btn-primary">Cadastrar</button>
            <p id="cadastro-msg" class="error-msg"></p>
            
            <div style="text-align: center; margin-top: 15px;">
                <a href="index.php" style="color: var(--primary-color); text-decoration: none; font-size: 0.9rem;">Já tenho uma conta. Fazer login</a>
            </div>
        </form>
    </div>

    <script>
        // Script exclusivo para a página de cadastro
        document.getElementById('cadastro-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = e.target;
            const msgEl = document.getElementById('cadastro-msg');
            const btn = form.querySelector('button');
            
            msgEl.style.color = '#f59e0b'; // Laranja para carregando
            msgEl.textContent = 'Cadastrando...';
            btn.disabled = true;

            try {
                const res = await fetch('api/cadastro.php', { 
                    method: 'POST', 
                    body: new FormData(form) 
                });
                const data = await res.json();

                if (data.sucesso) {
                    msgEl.style.color = '#10b981'; // Verde sucesso
                    msgEl.textContent = 'Conta criada! Redirecionando para o login...';
                    setTimeout(() => window.location.href = 'index.php', 2000);
                } else {
                    msgEl.style.color = '#ef4444'; // Vermelho erro
                    msgEl.textContent = data.erro || 'Erro ao cadastrar';
                    btn.disabled = false;
                }
            } catch (err) {
                msgEl.style.color = '#ef4444';
                msgEl.textContent = 'Erro de conexão com o servidor';
                btn.disabled = false;
            }
        });
    </script>
</body>
</html>