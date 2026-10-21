<?php
// dashboard.php
require_once 'config/database.php';
requireAuth();

$pdo = getConnection();
$perfil = $_SESSION['usuario_perfil'];
$nome = $_SESSION['usuario_nome'];

// Estatísticas
$totalVeiculos = $pdo->query("SELECT COUNT(*) FROM veiculos")->fetchColumn();
$totalMotoristas = $pdo->query("SELECT COUNT(*) FROM motoristas")->fetchColumn();
$totalPedidos = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE status = 'Pendente'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - SENN AI-X</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="app-page">
    <aside class="sidebar">
        <div class="sidebar-header">
            <i class="fa-solid fa-truck-fast"></i>
            <h2>SENN AI-X</h2>
        </div>
        <nav class="sidebar-nav">
            <a href="#" class="nav-item active" data-target="dashboard-content">
                <i class="fa-solid fa-chart-line"></i> Visão Geral
            </a>
            <a href="manual.php" class="nav-item">
                <i class="fa-solid fa-book"></i> Manual do Usuário
            </a>
            <a href="rastreamento.php" class="nav-item">
                <i class="fa-solid fa-map-location-dot"></i> Rastreamento
            </a>
            <a href="roteirizacao.php" class="nav-item">
                <i class="fa-solid fa-route"></i> Otimização de Rotas
            </a>
            <?php if ($perfil === 'admin'): ?>
                <a href="gestao_usuarios.php" class="nav-item">
                    <i class="fa-solid fa-users-gear"></i> Gestão de Usuários
                </a>
            <?php endif; ?>
            <?php if (in_array($perfil, ['admin', 'gestor'])): ?>
                <a href="#" class="nav-item" data-target="frota-content">
                    <i class="fa-solid fa-truck"></i> Frota 
                </a>
                <a href="#" class="nav-item" data-target="motoristas-content">
                    <i class="fa-solid fa-id-card"></i> Motoristas 
                </a>
            <?php endif; ?>
            <?php if (in_array($perfil, ['admin', 'despachante'])): ?>
                <a href="#" class="nav-item" data-target="pedidos-content">
                    <i class="fa-solid fa-box"></i> Pedidos 
                </a>
            <?php endif; ?>
        </nav>
        <div class="sidebar-footer">
            <a href="api/logout.php" class="btn-logout">
                <i class="fa-solid fa-sign-out-alt"></i> Sair
            </a>
        </div>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <h1 id="page-title">Visão Geral</h1>
            
            <div style="display:flex; align-items:center; gap:25px;">
                <div style="position: relative; cursor: pointer;" onclick="toggleAlertas()">
                    <i class="fa-solid fa-bell" style="font-size: 1.3rem; color: #6b7280;"></i>
                    <span id="badge-alertas" style="display:none; position:absolute; top:-5px; right:-5px; background:#ef4444; color:white; font-size:0.65rem; padding:2px 5px; border-radius:50%; font-weight:bold;">0</span>
                </div>
                
                <div id="painel-alertas" style="display:none; position:absolute; top:70px; right:30px; width:340px; background:white; border-radius:12px; box-shadow:0 10px 25px rgba(0,0,0,0.1); z-index:100; border:1px solid #e5e7eb;">
                    <div style="padding:15px; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center;">
                        <strong style="color:#1f2937;">Notificações</strong>
                        <a href="historico_alertas.php" style="font-size:0.8rem; color:#2563eb; text-decoration:none;">Ver Histórico</a>
                    </div>
                    <div id="lista-alertas" style="max-height:300px; overflow-y:auto;"></div>
                </div>

                <div class="user-info">
                    <span><?= htmlspecialchars($nome) ?> (<?= htmlspecialchars($perfil) ?>)</span>
                    <div class="avatar"><i class="fa-solid fa-user"></i></div>
                </div>
            </div>
        </header>

        <div class="content-area">
            <!-- VISÃO GERAL -->
            <div id="dashboard-content" class="tab-content active">
                <div class="cards-grid">
                    <div class="card stat-card">
                        <i class="fa-solid fa-truck"></i>
                        <div><h3>Veículos</h3><p><?= $totalVeiculos ?></p></div>
                    </div>
                    <div class="card stat-card">
                        <i class="fa-solid fa-users"></i>
                        <div><h3>Motoristas</h3><p><?= $totalMotoristas ?></p></div>
                    </div>
                    <div class="card stat-card">
                        <i class="fa-solid fa-box-open"></i>
                        <div><h3>Pedidos Pendentes</h3><p><?= $totalPedidos ?></p></div>
                    </div>
                </div>

                <!-- PAINEL DE RISCOS -->
                <div id="painel-ia-risco" class="card" style="margin-top: 24px; display: none;">
                    <h3 style="margin-bottom: 15px; font-size: 1rem; color: #ef4444;">
                        <i class="fa-solid fa-triangle-exclamation"></i> IA: Entregas Críticas (Risco de Atraso)
                    </h3>
                    <div id="lista-riscos"></div>
                </div>

                <!-- GRÁFICOS GERENCIAIS (RF10) -->
                <div class="cards-grid" style="margin-top: 24px;">
                    <div class="card">
                        <h3 style="margin-bottom: 15px; font-size: 1rem; color: #6b7280;">Entregas por Status</h3>
                        <canvas id="chart-status" style="max-height: 250px;"></canvas>
                    </div>
                    <div class="card">
                        <h3 style="margin-bottom: 15px; font-size: 1rem; color: #6b7280;">Ocupação da Frota</h3>
                        <canvas id="chart-frota" style="max-height: 250px;"></canvas>
                    </div>
                </div>

                <!-- PREVISÃO DE DEMANDA -->
                <div class="card" style="margin-top: 24px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <h3 style="font-size: 1rem; color: #6b7280; margin: 0;">
                            <i class="fa-solid fa-robot"></i> Previsão de Demanda (IA)
                        </h3>
                        <div style="display: flex; background: #f3f4f6; border-radius: 8px; padding: 4px;">
                            <button id="btn-grafico-7dias" onclick="mudarVisaoPrevisao('7dias')" style="background: white; border: none; padding: 6px 12px; border-radius: 6px; font-size: 0.8rem; font-weight: bold; color: #8b5cf6; cursor: pointer; box-shadow: 0 1px 3px rgba(0,0,0,0.1); transition: all 0.2s;">
                                Próximos 7 Dias
                            </button>
                            <button id="btn-grafico-mes" onclick="mudarVisaoPrevisao('mes')" style="background: transparent; border: none; padding: 6px 12px; border-radius: 6px; font-size: 0.8rem; font-weight: bold; color: #6b7280; cursor: pointer; transition: all 0.2s;">
                                Visão do Mês
                            </button>
                        </div>
                    </div>
                    <div style="position: relative; height: 250px; width: 100%;">
                        <canvas id="chart-previsao"></canvas>
                    </div>
                    <p id="projecao-30" style="text-align: right; font-size: 0.85rem; color: #6b7280; margin-top: 10px;"></p>
                </div>

                <!-- ANÁLISE INTELIGENTE (RF12) -->
                <div class="card" style="margin-top: 24px; border-left: 4px solid #8b5cf6;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <h3 style="font-size: 1.1rem; color: #4c1d95; margin: 0;">
                            <i class="fa-solid fa-brain"></i> Análise Inteligente de Desempenho
                        </h3>
                        <button onclick="solicitarRelatorioIA()" id="btn-relatorio-ia" class="btn-export" style="background: #8b5cf6; padding: 8px 16px; font-size: 0.85rem; box-shadow: none;">
                            <i class="fa-solid fa-wand-magic-sparkles"></i> Gerar Relatório
                        </button>
                    </div>

                    <div id="ia-placeholder" style="text-align: center; padding: 30px; color: #6b7280;">
                        <i class="fa-solid fa-microchip" style="font-size: 3rem; color: #d1d5db; margin-bottom: 15px;"></i>
                        <p>Clique em "Gerar Relatório" para que a Inteligência Artificial analise os dados históricos.</p>
                    </div>

                    <div id="ia-resultados" style="display: none;">
                        <div id="lista-insights" style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 20px;"></div>
                        <div style="border-top: 1px solid #e5e7eb; padding-top: 20px;">
                            <h4 style="font-size: 0.9rem; color: #6b7280; margin-bottom: 15px;">Top 5 Regiões com Mais Entregas</h4>
                            <canvas id="chart-regioes" style="max-height: 200px;"></canvas>
                        </div>
                    </div>
                </div>

                <!-- BOTÃO EXPORTAR -->
                <div class="action-bar" style="margin-top: 20px; text-align: right;">
                    <button onclick="exportarExcel()" class="btn-export">
                        <i class="fa-solid fa-file-excel"></i> Exportar Relatório
                    </button>
                </div>
            </div> <!-- FIM DASHBOARD-CONTENT -->

            <!-- RF02: FROTA -->
            <?php if (in_array($perfil, ['admin', 'gestor'])): ?>
            <div id="frota-content" class="tab-content">
                <div class="card form-card">
                    <h2>Cadastrar Novo Veículo</h2>
                    <form id="form-veiculo">
                        <div class="form-row">
                            <div class="form-group"><label>Placa</label><input name="placa" required></div>
                            <div class="form-group"><label>Tipo</label><input name="tipo" required></div>
                        </div>
                        <div class="form-row">
                            <div class="form-group"><label>Capacidade (kg)</label><input type="number" name="capacidade" step="0.01" required></div>
                            <div class="form-group"><label>Volume (m³)</label><input type="number" name="volume" step="0.01" required></div>
                        </div>
                        <div class="form-row">
                            <div class="form-group"><label>Autonomia (km)</label><input type="number" name="autonomia" required></div>
                            <div class="form-group"><label>Status</label>
                                <select name="status" required>
                                    <option>Disponível</option><option>Em Rota</option><option>Em Manutenção</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group"><label>Próxima Manutenção</label><input type="date" name="manutencao" required></div>
                        <button type="submit" class="btn-primary"><i class="fa-solid fa-plus"></i> Cadastrar Veículo</button>
                    </form>
                </div>
                <div class="card list-card">
                    <h2>Frota Cadastrada</h2>
                    <div class="table-responsive">
                        <table id="table-veiculos">
                            <thead><tr><th>Placa</th><th>Tipo</th><th>Capacidade</th><th>Status</th><th>Manutenção</th></tr></thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- RF03: MOTORISTAS -->
            <?php if (in_array($perfil, ['admin', 'gestor'])): ?>
            <div id="motoristas-content" class="tab-content">
                <div class="card form-card">
                    <h2>Cadastrar Motorista</h2>
                    <form id="form-motorista">
                        <div class="form-row">
                            <div class="form-group"><label>Nome Completo</label><input name="nome" required></div>
                            <div class="form-group"><label>Contato (Telefone)</label><input type="tel" name="contato" placeholder="(11) 99999-9999" required></div>
                        </div>
                        <div class="form-row">
                            <div class="form-group"><label>E-mail de Acesso (Login)</label><input type="email" name="email" required></div>
                            <div class="form-group"><label>Senha de Acesso</label><input type="password" name="senha" value="123456" required></div>
                        </div>
                        <div class="form-row">
                            <div class="form-group"><label>Categoria CNH</label>
                                <select name="categoria_cnh" required>
                                    <option>B</option><option>C</option><option>D</option><option>E</option>
                                </select>
                            </div>
                            <div class="form-group"><label>Validade CNH</label><input type="date" name="validade_cnh" required></div>
                        </div>
                        <div class="form-row">
                            <div class="form-group"><label>Disponibilidade</label>
                                <select name="disponibilidade" required>
                                    <option>Disponível</option><option>Em Rota</option><option>Folga</option>
                                </select>
                            </div>
                            <div class="form-group"><label>Veículo Vinculado</label>
                                <select name="veiculo_id" id="select-veiculos"><option value="">Nenhum</option></select>
                            </div>
                        </div>
                        <button type="submit" class="btn-primary"><i class="fa-solid fa-plus"></i> Cadastrar Motorista</button>
                    </form>
                </div>
                <div class="card list-card">
                    <h2>Motoristas Cadastrados</h2>
                    <div class="table-responsive">
                        <table id="table-motoristas">
                            <thead><tr><th>Nome</th><th>CNH</th><th>Contato</th><th>Status</th><th>Veículo</th></tr></thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- RF04: PEDIDOS -->
            <?php if (in_array($perfil, ['admin', 'despachante'])): ?>
            <div id="pedidos-content" class="tab-content">
                <div class="card form-card">
                    <div class="tabs">
                        <button class="tab-btn active" onclick="mudarAbaPedido('manual')">Cadastro Manual</button>
                        <button class="tab-btn" onclick="mudarAbaPedido('csv')">Importar CSV</button>
                    </div>
                    
                    <form id="form-pedido" style="display: block;">
                        <div class="form-row">
                            <div class="form-group"><label>Cliente</label><input name="cliente" required></div>
                            <div class="form-group"><label>Endereço de Entrega</label><input name="endereco" required></div>
                        </div>
                        <div class="form-row">
                            <div class="form-group"><label>Peso (kg)</label><input type="number" step="0.01" name="peso_kg" required></div>
                            <div class="form-group"><label>Volume (m³)</label><input type="number" step="0.01" name="volume_m3" required></div>
                        </div>
                        <div class="form-row">
                            <div class="form-group"><label>Prioridade</label>
                                <select name="prioridade" required>
                                    <option value="Baixa">Baixa</option>
                                    <option value="Normal" selected>Normal</option>
                                    <option value="Alta">Alta</option>
                                </select>
                            </div>
                            <div class="form-group"><label>Janela Início</label><input type="datetime-local" name="janela_inicio" required></div>
                        </div>
                        <div class="form-row">
                            <div class="form-group"><label>Janela Fim</label><input type="datetime-local" name="janela_fim" required></div>
                        </div>
                        <button type="submit" class="btn-primary"><i class="fa-solid fa-plus"></i> Criar Pedido</button>
                    </form>

                    <form id="form-pedido-csv" style="display: none;">
                        <div class="upload-area">
                            <i class="fa-solid fa-file-csv"></i>
                            <p>Selecione o arquivo CSV</p>
                            <input type="file" name="arquivo_csv" accept=".csv" required>
                            <small>Formato: Cliente;Endereco;Peso;Volume;Prioridade;JanelaInicio;JanelaFim</small>
                        </div>
                        <button type="submit" class="btn-primary" style="margin-top: 20px;"><i class="fa-solid fa-upload"></i> Importar Pedidos</button>
                    </form>
                </div>
                
                <div class="card list-card">
                    <h2>Pedidos Cadastrados</h2>
                    <div class="table-responsive">
                        <table id="table-pedidos">
                            <thead><tr><th>Código</th><th>Cliente</th><th>Carga</th><th>Prioridade</th><th>Janela de Entrega</th><th>Status</th></tr></thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- CHATBOT -->
    <div id="chat-widget" style="position: fixed; bottom: 20px; right: 20px; z-index: 1000;">
        <button id="chat-toggle" style="width: 60px; height: 60px; border-radius: 50%; background: #8b5cf6; color: white; border: none; font-size: 1.5rem; cursor: pointer; box-shadow: 0 4px 12px rgba(139, 92, 246, 0.4); transition: transform 0.2s;">
            <i class="fa-solid fa-robot"></i>
        </button>

        <div id="chat-window" style="display: none; position: absolute; bottom: 75px; right: 0; width: 320px; height: 400px; background: white; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); flex-direction: column; overflow: hidden; border: 1px solid #e5e7eb;">
            <div style="background: #8b5cf6; color: white; padding: 15px; display: flex; justify-content: space-between; align-items: center;">
                <strong><i class="fa-solid fa-robot"></i> SENN AI-X Helper</strong>
                <button id="chat-close" style="background: none; border: none; color: white; cursor: pointer; font-size: 1.2rem;"><i class="fa-solid fa-xmark"></i></button>
            </div>
            
            <div id="chat-messages" style="flex: 1; padding: 15px; overflow-y: auto; background: #f9fafb; display: flex; flex-direction: column; gap: 10px;">
                <div style="background: #e5e7eb; padding: 10px; border-radius: 8px; font-size: 0.85rem; align-self: flex-start; max-width: 80%;">
                    Olá! Sou o assistente virtual. Como posso ajudar com sua logística hoje?
                </div>
            </div>

            <div id="chat-sugestoes" style="padding: 10px; display: flex; flex-wrap: wrap; gap: 8px; background: #fff; border-top: 1px solid #e5e7eb; max-height: 100px; overflow-y: auto;">
                <button class="chat-chip" onclick="enviarPerguntaRapida('Qual o total de pedidos?')">📦 Pedidos</button>
                <button class="chat-chip" onclick="enviarPerguntaRapida('Como está a frota de veículos?')">🚚 Frota</button>
                <button class="chat-chip" onclick="enviarPerguntaRapida('Quantos motoristas disponíveis?')">👤 Motoristas</button>
                <button class="chat-chip" onclick="enviarPerguntaRapida('Tem entregas atrasadas?')">⚠️ Atrasos</button>
            </div>

            <form id="chat-form" style="display: flex; border-top: 1px solid #e5e7eb; padding: 10px;">
                <input type="text" id="chat-input" placeholder="Digite sua dúvida..." autocomplete="off" style="flex: 1; padding: 10px; border: 1px solid #ccc; border-radius: 20px; outline: none;">
                <button type="submit" style="background: #8b5cf6; color: white; border: none; width: 40px; height: 40px; border-radius: 50%; margin-left: 10px; cursor: pointer;"><i class="fa-solid fa-paper-plane"></i></button>
            </form>
        </div>
    </div>

    <script src="assets/js/app.js"></script>
</body>
</html>