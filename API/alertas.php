<?php
// api/alertas.php
require_once '../config/database.php';
if (!isset($_SESSION['usuario_id'])) jsonResponse(['erro' => 'Não autenticado'], 401);

$pdo = getConnection();
$alertasGerados = [];

// Função para registrar no log (evita duplicar se o mesmo alerta já estiver "Não Lido")
function registrarLog($pdo, $tipo, $msg) {
    $stmt = $pdo->prepare("SELECT id FROM logs_notificacoes WHERE tipo = ? AND mensagem = ? AND status = 'Não Lido'");
    $stmt->execute([$tipo, $msg]);
    if (!$stmt->fetch()) {
        $pdo->prepare("INSERT INTO logs_notificacoes (tipo, mensagem) VALUES (?, ?)")->execute([$tipo, $msg]);
    }
}

// 1. Manutenção
$stmt = $pdo->query("SELECT placa, proxima_manutencao FROM veiculos WHERE proxima_manutencao <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)");
while ($v = $stmt->fetch()) {
    $dias = (strtotime($v['proxima_manutencao']) - strtotime(date('Y-m-d'))) / 86400;
    $msg = $dias < 0 ? "Veículo {$v['placa']}: Manutenção ATRASADA!" : "Veículo {$v['placa']}: Manutenção em {$dias} dias.";
    registrarLog($pdo, 'manutencao', $msg);
}

// 2. CNH
$stmt = $pdo->query("SELECT nome, validade_cnh FROM motoristas WHERE validade_cnh <= DATE_ADD(CURDATE(), INTERVAL 15 DAY)");
while ($m = $stmt->fetch()) {
    $dias = (strtotime($m['validade_cnh']) - strtotime(date('Y-m-d'))) / 86400;
    $msg = $dias < 0 ? "Motorista {$m['nome']}: CNH VENCIDA!" : "Motorista {$m['nome']}: CNH vence em {$dias} dias.";
    registrarLog($pdo, 'cnh', $msg);
}

// 3. Pedidos Atrasados
$agora = date('Y-m-d H:i:s');
$stmt = $pdo->query("SELECT codigo, cliente FROM pedidos WHERE status = 'Pendente' AND janela_fim < '$agora'");
while ($p = $stmt->fetch()) {
    $msg = "Pedido {$p['codigo']} ({$p['cliente']}) está ATRASADO.";
    registrarLog($pdo, 'pedido', $msg);
}

// 4. IA Riscos (Reaproveitando a lógica)
$agora = date('Y-m-d H:i:s');
$sqlRisco = "SELECT codigo, (CASE WHEN prioridade = 'Alta' THEN 40 ELSE 0 END) + (CASE WHEN janela_fim < '$agora' THEN 60 ELSE 0 END) as score 
             FROM pedidos WHERE status = 'Pendente'";
$stmtRisco = $pdo->query($sqlRisco);
while ($r = $stmtRisco->fetch()) {
    if ($r['score'] >= 80) {
        $msg = "IA: Pedido {$r['codigo']} com alto risco de falha ({$r['score']}%).";
        registrarLog($pdo, 'ia_risco', $msg);
    }
}

// Retorna apenas os alertas NÃO LIDOS para o Sininho
$alertasAtivos = $pdo->query("SELECT * FROM logs_notificacoes WHERE status = 'Não Lido' ORDER BY criado_em DESC")->fetchAll();

jsonResponse($alertasAtivos);