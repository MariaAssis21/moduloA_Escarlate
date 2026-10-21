<?php
require_once 'config/database.php';
requireAuth();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Manual do Usuário - SENN AI-X</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="app-page">
    <aside class="sidebar">
        <div class="sidebar-header"><i class="fa-solid fa-truck-fast"></i><h2>SENN AI-X</h2></div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-item"><i class="fa-solid fa-arrow-left"></i> Voltar ao Dashboard</a>
            <a href="#" class="nav-item active"><i class="fa-solid fa-book"></i> Manual do Usuário</a>
        </nav>
    </aside>
    <main class="main-content">
        <header class="topbar"><h1>Manual do Usuário e Suporte</h1></header>
        <div class="content-area">
            <div class="card" style="line-height: 1.6; color: #374151;">
                <h2>Bem-vindo ao SENN AI-X</h2>
                <p>Este sistema foi projetado para otimizar a logística de última milha usando Inteligência Artificial local.</p>
                
                <h3 style="margin-top: 20px; color: #1f2937;"><i class="fa-solid fa-users"></i> Para Despachantes e Gestores</h3>
                <ul style="padding-left: 20px; margin-top: 10px;">
                    <li><strong>Pedidos:</strong> Cadastre entregas manualmente ou importe um arquivo CSV.</li>
                    <li><strong>Otimização de Rotas:</strong> Selecione um veículo na aba "Otimização de Rotas" e deixe a IA calcular a melhor sequência de entregas.</li>
                    <li><strong>IA e Análise:</strong> Acompanhe os riscos de atraso e as previsões de demanda no Dashboard.</li>
                </ul>

                <h3 style="margin-top: 20px; color: #1f2937;"><i class="fa-solid fa-truck"></i> Para Motoristas</h3>
                <ul style="padding-left: 20px; margin-top: 10px;">
                    <li>Ao fazer login, o motorista verá sua lista de entregas na ordem otimizada pela IA.</li>
                    <li>Após entregar, clique em "Confirmar Entrega" para capturar a foto, GPS e assinatura.</li>
                </ul>

                <h3 style="margin-top: 20px; color: #1f2937;"><i class="fa-solid fa-headset"></i> Suporte Técnico</h3>
                <p>Em caso de dúvidas ou problemas, entre em contato pelo e-mail: <strong>suporte@senn.com.br</strong>.</p>
            </div>
        </div>
    </main>
</body>
</html>