<?php
// roteirizacao.php
require_once 'config/database.php';
requireAuth();
$pdo = getConnection();
$veiculos = $pdo->query("SELECT id, placa, tipo FROM veiculos WHERE status = 'Disponível'")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Otimização de Rotas (IA) - SENN AI-X</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        #map { height: 500px; width: 100%; border-radius: 12px; z-index: 1; }
        .painel-rotas { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px; }
        @media (max-width: 900px) { .painel-rotas { grid-template-columns: 1fr; } }
        .step-list { list-style: none; padding: 0; margin: 0; }
        .step-list li { padding: 15px; border-bottom: 1px solid #e5e7eb; display: flex; gap: 15px; align-items: center; }
        .step-list li:last-child { border-bottom: none; }
        .step-icon { width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 0.8rem; }
    </style>
</head>
<body class="app-page">
    <aside class="sidebar">
        <div class="sidebar-header"><i class="fa-solid fa-truck-fast"></i><h2>SENN AI-X</h2></div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-item"><i class="fa-solid fa-arrow-left"></i> Voltar ao Dashboard</a>
            <a href="#" class="nav-item active"><i class="fa-solid fa-route"></i> Otimizador de Rotas</a>
        </nav>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <h1>Roteirização e Otimização (IA)</h1>
            <div class="user-info"><span><?= htmlspecialchars($_SESSION['usuario_nome']) ?></span></div>
        </header>

        <div class="content-area">
            <div class="card" style="margin-bottom: 20px;">
                <form id="form-rota" style="display: flex; gap: 15px; align-items: flex-end;">
                    <div class="form-group" style="flex: 1; margin-bottom: 0;">
                        <label>Selecione o Veículo para a Rota:</label>
                        <select name="veiculo_id" required style="padding: 12px; border-radius: 8px; border: 1px solid #ccc; width: 100%;">
                            <option value="">Escolha um veículo...</option>
                            <?php foreach($veiculos as $v): ?>
                                <option value="<?= $v['id'] ?>"><?= $v['placa'] ?> (<?= $v['tipo'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn-export" style="height: 45px;">
                        <i class="fa-solid fa-wand-magic-sparkles"></i> Gerar Rota Otimizada
                    </button>
                </form>
            </div>

            <div id="resultado-rota" style="display: none;">
                <div class="cards-grid" style="margin-bottom: 20px;">
                    <div class="card stat-card">
                        <i class="fa-solid fa-road"></i>
                        <div><h3>Distância Total</h3><p id="info-distancia">0 km</p></div>
                    </div>
                    <div class="card stat-card">
                        <i class="fa-solid fa-dollar-sign"></i>
                        <div><h3>Custo Estimado</h3><p id="info-custo">R$ 0,00</p></div>
                    </div>
                    <div class="card stat-card">
                        <i class="fa-solid fa-box"></i>
                        <div><h3>Entregas Planejadas</h3><p id="info-entregas">0</p></div>
                    </div>
                </div>

                <div class="painel-rotas">
                    <div class="card" style="padding: 0; overflow: hidden;">
                        <div id="map"></div>
                    </div>
                    <div class="card">
                        <h3 style="margin-bottom: 15px; border-bottom: 1px solid #e5e7eb; padding-bottom: 10px;">Sequência da Rota</h3>
                        <ul class="step-list" id="lista-passos"></ul>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="assets/js/roteirizacao.js"></script>
</body>
</html>