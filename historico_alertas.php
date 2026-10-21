<?php
// historico_alertas.php
require_once 'config/database.php';
requireAuth();

$pdo = getConnection();
$logs = $pdo->query("SELECT * FROM logs_notificacoes ORDER BY criado_em DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Histórico de Alertas - SENN AI-X</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="app-page">
    <aside class="sidebar">
        <div class="sidebar-header">
            <i class="fa-solid fa-truck-fast"></i><h2>SENN AI-X</h2>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-item"><i class="fa-solid fa-arrow-left"></i> Voltar ao Dashboard</a>
            <a href="#" class="nav-item active"><i class="fa-solid fa-list"></i> Logs de Alertas</a>
        </nav>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <h1>Histórico e Logs de Notificações</h1>
        </header>
        <div class="content-area">
            <div class="card">
                <table style="width:100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background:#f9fafb; text-align:left; font-size:0.85rem; color:#6b7280;">
                            <th style="padding:12px;">Data</th>
                            <th style="padding:12px;">Tipo</th>
                            <th style="padding:12px;">Mensagem</th>
                            <th style="padding:12px;">Status</th>
                            <th style="padding:12px;">Resolvido em</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($logs as $l): ?>
                        <tr style="border-bottom:1px solid #e5e7eb;">
                            <td style="padding:12px; font-size:0.85rem;"><?= date('d/m/Y H:i', strtotime($l['criado_em'])) ?></td>
                            <td style="padding:12px;"><span class="badge <?= $l['tipo'] === 'pedido' ? 'manutencao' : 'rota' ?>"><?= ucfirst($l['tipo']) ?></span></td>
                            <td style="padding:12px; font-size:0.9rem;"><?= htmlspecialchars($l['mensagem']) ?></td>
                            <td style="padding:12px;">
                                <?php if($l['status'] === 'Resolvido'): ?>
                                    <span style="color:#10b981; font-weight:bold; font-size:0.85rem;"><i class="fa-solid fa-check"></i> Resolvido</span>
                                <?php else: ?>
                                    <span style="color:#ef4444; font-weight:bold; font-size:0.85rem;"><i class="fa-solid fa-clock"></i> Pendente</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding:12px; font-size:0.85rem; color:#6b7280;">
                                <?= $l['resolvido_em'] ? date('d/m/Y H:i', strtotime($l['resolvido_em'])) : '-' ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($logs)): ?>
                            <tr><td colspan="5" style="padding:20px; text-align:center; color:#6b7280;">Nenhum log registrado ainda.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>