<?php
// gestao_usuarios.php
require_once 'config/database.php';
requireAuth();

// Bloqueia quem não é Admin
if ($_SESSION['usuario_perfil'] !== 'admin') {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Gestão de Usuários - SENN AI-X</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .tabela-usuarios th, .tabela-usuarios td { padding: 12px; border-bottom: 1px solid #e5e7eb; }
        .status-ativo { color: #10b981; font-weight: bold; }
        .status-inativo { color: #ef4444; font-weight: bold; }
        .acoes button { background: none; border: none; cursor: pointer; font-size: 1.1rem; margin-right: 8px; transition: transform 0.2s; }
        .acoes button:hover { transform: scale(1.2); }
    </style>
</head>
<body class="app-page">
    <aside class="sidebar">
        <div class="sidebar-header"><i class="fa-solid fa-truck-fast"></i><h2>SENN AI-X</h2></div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-item"><i class="fa-solid fa-arrow-left"></i> Voltar ao Dashboard</a>
            <a href="#" class="nav-item active"><i class="fa-solid fa-users-gear"></i> Gestão de Usuários</a>
        </nav>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <h1>Gestão de Usuários e Permissões</h1>
            <div class="user-info"><span><?= htmlspecialchars($_SESSION['usuario_nome']) ?> (Admin)</span></div>
            <a href="api/backup.php" class="btn-export" style="background: #111827; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; padding: 10px 16px; color: white; border-radius: 8px; font-weight: bold;">
                <i class="fa-solid fa-download"></i> Fazer Backup do Banco
            </a>
        </header>

        <div class="content-area">
            <div class="card" style="margin-bottom: 20px; display: flex; gap: 20px;">
                <div class="stat-card" style="flex:1;">
                    <i class="fa-solid fa-users" style="font-size: 2rem; color: #2563eb;"></i>
                    <div><h3>Total de Contas</h3><p id="total-usuarios">0</p></div>
                </div>
                <div class="stat-card" style="flex:1;">
                    <i class="fa-solid fa-user-check" style="font-size: 2rem; color: #10b981;"></i>
                    <div><h3>Contas Ativas</h3><p id="total-ativos">0</p></div>
                </div>
            </div>

            <div class="card">
                <table class="tabela-usuarios" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="text-align: left; background: #f9fafb; color: #6b7280; font-size: 0.85rem;">
                            <th>Nome / E-mail</th>
                            <th>Perfil de Acesso</th>
                            <th>Status</th>
                            <th>Data de Cadastro</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-usuarios">
                        <!-- Preenchido via JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script src="assets/js/gestao_usuarios.js"></script>
</body>
</html>