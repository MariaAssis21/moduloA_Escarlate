<?php
// api/ia_risco.php
require_once '../config/database.php';
if (!isset($_SESSION['usuario_id'])) jsonResponse(['erro' => 'Não autenticado'], 401);

$pdo = getConnection();
$pedidos = $pdo->query("SELECT * FROM pedidos WHERE status IN ('Pendente', 'Em Rota')")->fetchAll();

$riscos = [];

foreach ($pedidos as $p) {
    $scoreRisco = 0;
    $motivos = [];

    if ($p['prioridade'] === 'Alta') {
        $scoreRisco += 30;
        $motivos[] = "Prioridade Alta";
    }

    $inicio = strtotime($p['janela_inicio']);
    $fim = strtotime($p['janela_fim']);
    $duracaoHoras = ($fim - $inicio) / 3600;
    if ($duracaoHoras <= 2) {
        $scoreRisco += 25;
        $motivos[] = "Janela curta (" . round($duracaoHoras) . "h)";
    }

    if ($p['peso_kg'] > 100) {
        $scoreRisco += 20;
        $motivos[] = "Carga pesada (" . $p['peso_kg'] . "kg)";
    }

    $agora = time();
    $horasRestantes = ($fim - $agora) / 3600;
    if ($p['status'] === 'Pendente' && $horasRestantes < 3 && $horasRestantes > 0) {
        $scoreRisco += 40;
        $motivos[] = "Prazo crítico (" . round($horasRestantes) . "h restantes)";
    } elseif ($horasRestantes <= 0 && $p['status'] === 'Pendente') {
        $scoreRisco += 100;
        $motivos[] = "PRAZO EXPIRADO";
    }

    $scoreFinal = min(99, $scoreRisco);

    // Se o risco for maior que 50%, adicionamos à lista de exibição
    if ($scoreFinal > 50) {
        $riscos[] = [
            'id' => $p['id'],
            'codigo' => $p['codigo'],
            'cliente' => $p['cliente'],
            'risco' => $scoreFinal,
            'motivos' => $motivos,
            'acao_sugerida' => $scoreFinal > 80 ? "Priorizar entrega imediatamente ou contatar cliente" : "Alocar veículo mais próximo"
        ];
    }

    // NOVO: Se o risco for CRÍTICO (> 80%), envia para o Sininho de Alertas
    if ($scoreFinal > 80) {
        $msgAlerta = "IA: Pedido {$p['codigo']} com {$scoreFinal}% de risco de falha. Ação: Replanejar rota.";
        // Verifica se esse alerta já não está no painel
        $stmtCheck = $pdo->prepare("SELECT id FROM logs_notificacoes WHERE mensagem = ? AND status = 'Não Lido'");
        $stmtCheck->execute([$msgAlerta]);
        if (!$stmtCheck->fetch()) {
            $pdo->prepare("INSERT INTO logs_notificacoes (tipo, mensagem) VALUES ('ia_risco', ?)")->execute([$msgAlerta]);
        }
    }
}

usort($riscos, function($a, $b) { return $b['risco'] <=> $a['risco']; });
jsonResponse($riscos);