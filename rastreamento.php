<?php
// rastreamento.php
require_once 'config/database.php';
requireAuth();

$perfil = $_SESSION['usuario_perfil'];
if (!in_array($perfil, ['admin', 'gestor', 'despachante'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Rastreamento em Tempo Real - SENN AI-X</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Biblioteca Leaflet para o Mapa -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        #map { height: 500px; width: 100%; border-radius: 12px; z-index: 1; }
        .painel-rastreamento { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-top: 20px; }
        @media (max-width: 900px) { .painel-rastreamento { grid-template-columns: 1fr; } }
        .lista-rastreamento { max-height: 500px; overflow-y: auto; }
        .item-rastreamento { padding: 15px; border-bottom: 1px solid #e5e7eb; font-size: 0.9rem; }
        .item-rastreamento:hover { background: #f9fafb; }
    </style>
</head>
<body class="app-page">
    <aside class="sidebar">
        <div class="sidebar-header">
            <i class="fa-solid fa-truck-fast"></i>
            <h2>SENN AI-X</h2>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-item"><i class="fa-solid fa-arrow-left"></i> Voltar ao Dashboard</a>
            <a href="#" class="nav-item active"><i class="fa-solid fa-map-location-dot"></i> Rastreamento </a>
        </nav>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <h1>Rastreamento de Entregas em Tempo Real</h1>
            <div class="user-info">
                <span><?= htmlspecialchars($_SESSION['usuario_nome']) ?></span>
                <div class="avatar"><i class="fa-solid fa-user"></i></div>
            </div>
        </header>

        <div class="content-area">
            <div class="card" style="margin-bottom: 20px;">
                <div style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                    <div class="form-group" style="margin: 0; flex: 1; min-width: 200px;">
                        <input type="text" id="filtro-busca" placeholder="Buscar por código, cliente ou motorista..." style="padding: 10px; width: 100%; border: 1px solid #ccc; border-radius: 6px;">
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <select id="filtro-status" style="padding: 10px; border: 1px solid #ccc; border-radius: 6px;">
                            <option value="Todos">Todos os Status</option>
                            <option value="Em Rota">A Caminho (Em Rota)</option>
                            <option value="Entregue">Entregue</option>
                            <option value="Pendente">Pendente / Falha</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="painel-rastreamento">
                <!-- MAPA -->
                <div class="card" style="padding: 0; overflow: hidden;">
                    <div id="map"></div>
                </div>

                <!-- LISTA LATERAL -->
                <div class="card lista-rastreamento">
                    <h3 style="padding: 15px; border-bottom: 1px solid #e5e7eb; margin: 0;">Entregas Monitoradas</h3>
                    <div id="lista-entregas">
                        <!-- Preenchido via JS -->
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="assets/js/rastreamento.js"></script>
</body>
</html>