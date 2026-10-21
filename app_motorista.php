<?php
// app_motorista.php
require_once 'config/database.php';
requireAuth();

// Apenas motoristas acessam
if ($_SESSION['usuario_perfil'] !== 'motorista') {
    header('Location: dashboard.php');
    exit;
}

$pdo = getConnection();
$usuario_id = $_SESSION['usuario_id'];

// Busca o ID do motorista logado
$stmt = $pdo->prepare("SELECT id FROM motoristas WHERE usuario_id = ?");
$stmt->execute([$usuario_id]);
$motorista = $stmt->fetch();

$pedidos = [];
if ($motorista) {
    // ⚠️ MUDANÇA 1: Ordena pela ordem da rota (criada pela IA) e depois por prioridade
    $sql = "SELECT * FROM pedidos WHERE status IN ('Pendente', 'Em Rota') ORDER BY ordem_rota ASC, prioridade = 'Alta' DESC";
    $pedidos = $pdo->query($sql)->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Entregas - SENN AI-X</title>
    <link rel="stylesheet" href="assets/css/motorista.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="motorista-page">
    
    <div class="container-motorista">
        <header class="topbar-motorista">
            <h1>Olá, <?= explode(' ', $_SESSION['usuario_nome'])[0] ?></h1>
            <a href="api/logout.php" class="btn-sair">Sair</a>
        </header>

        <?php if(empty($pedidos)): ?>
            <div style="text-align: center; padding: 40px 20px; color: #6b7280;">
                <i class="fa-solid fa-mug-hot" style="font-size: 3rem; color: #d1d5db; margin-bottom: 15px;"></i>
                <p>Nenhuma entrega pendente no momento. Bom descanso!</p>
            </div>
        <?php endif; ?>

        <?php foreach($pedidos as $p): ?>
            <div class="card-entrega <?= $p['prioridade'] === 'Alta' ? 'prioridade-alta' : '' ?>">
                <div class="card-header">
                    <div>
                        <!-- ⚠️ MUDANÇA 2: Exibe a numeração da rota definida pela IA -->
                        <?php if (!empty($p['ordem_rota'])): ?>
                            <div style="background: #8b5cf6; color: white; display: inline-block; padding: 4px 12px; border-radius: 20px; font-weight: bold; font-size: 0.8rem; margin-bottom: 10px;">
                                <i class="fa-solid fa-route"></i> Parada <?= $p['ordem_rota'] ?>
                            </div>
                            <br>
                        <?php endif; ?>

                        <!-- Badge dinâmica de prioridade -->
                        <?php 
                            $badgeClass = 'badge-normal';
                            if ($p['prioridade'] === 'Alta') $badgeClass = 'badge-alta';
                            if ($p['prioridade'] === 'Baixa') $badgeClass = 'badge-baixa';
                        ?>
                        <span class="badge-prioridade <?= $badgeClass ?>"><?= $p['prioridade'] ?></span>
                        
                        <h3><?= htmlspecialchars($p['cliente']) ?></h3>
                        <p class="endereco"><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($p['endereco']) ?></p>
                    </div>
                    <div class="codigo-pedido">
                        <span>Código</span>
                        <strong><?= $p['codigo'] ?></strong>
                    </div>
                </div>
                
                <button class="btn-confirmar" onclick="abrirModalPOD(<?= $p['id'] ?>, '<?= $p['codigo'] ?>')">
                    <i class="fa-solid fa-check-double"></i> Confirmar Entrega 
                </button>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Modal de Comprovação de Entrega -->
    <div id="modal-pod" class="modal-overlay">
        <div class="modal-content">
            <h2>Comprovar Entrega</h2>
            <p id="pod-codigo"></p>
            
            <form id="form-pod">
                <input type="hidden" name="pedido_id" id="pod-id">
                
                <div class="form-group-modal">
                    <label>Foto do Comprovante</label>
                    <input type="file" name="foto" accept="image/*" capture="environment" required>
                </div>
                
                <div class="form-group-modal">
                    <label>Assinatura do Cliente (Opcional)</label>
                    <input type="text" name="assinatura" placeholder="Nome de quem recebeu">
                </div>

                <div class="modal-botoes">
                    <button type="button" class="btn-cancelar" onclick="fecharModalPOD()">Cancelar</button>
                    <button type="submit" class="btn-enviar">Confirmar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/motorista.js"></script>
</body>
</html>