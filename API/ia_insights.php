<?php
// api/ia_insights.php
require_once '../config/database.php';
if (!isset($_SESSION['usuario_id'])) jsonResponse(['erro' => 'Não autenticado'], 401);

$pdo = getConnection();
$insights = [];

// 1. IA analisando Custo/Volume de Entregas por Região
$pedidosEnderecos = $pdo->query("SELECT endereco, peso_kg FROM pedidos")->fetchAll();
$regioes = [];

foreach ($pedidosEnderecos as $p) {
    $partes = explode(' ', trim($p['endereco']));
    $ultimaPalavra = end($partes);
    $nomeRegiao = preg_replace('/[^a-zA-ZÀ-ÿ]/', '', $ultimaPalavra);
    
    if (empty($nomeRegiao) && count($partes) > 1) {
        $nomeRegiao = preg_replace('/[^a-zA-ZÀ-ÿ]/', '', $partes[count($partes)-2]);
    }

    if (!empty($nomeRegiao)) {
        if (!isset($regioes[$nomeRegiao])) {
            $regioes[$nomeRegiao] = ['qtd' => 0, 'peso' => 0];
        }
        $regioes[$nomeRegiao]['qtd']++;
        $regioes[$nomeRegiao]['peso'] += $p['peso_kg'];
    }
}

arsort($regioes);
$topRegiaoNome = array_key_first($regioes);

if ($topRegiaoNome) {
    $topRegiaoDados = $regioes[$topRegiaoNome];
    $insights[] = [
        'tipo' => 'regiao',
        'icone' => 'fa-map-location-dot',
        'cor' => '#2563eb',
        'texto' => "A região com maior volume de entregas é a área da <strong>{$topRegiaoNome}</strong> com {$topRegiaoDados['qtd']} pedidos (Total: {$topRegiaoDados['peso']} kg). Recomendação: Alocar um veículo fixo para esta área."
    ];
}

// 2. IA analisando Gargalos de Operação (Entregas atrasadas)
$agora = date('Y-m-d H:i:s');
$sqlAtrasos = "SELECT COUNT(*) FROM pedidos WHERE status = 'Pendente' AND janela_fim < '$agora'";
$atrasos = $pdo->query($sqlAtrasos)->fetchColumn();

if ($atrasos > 0) {
    $insights[] = [
        'tipo' => 'gargalo',
        'icone' => 'fa-triangle-exclamation',
        'cor' => '#ef4444',
        'texto' => "Detectamos <strong>{$atrasos} entregas atrasadas</strong> no sistema. Recomendação: Acionar motoristas em rotas próximas para assumir essas entregas e evitar multas contratuais."
    ];
} else {
    $insights[] = [
        'tipo' => 'positivo',
        'icone' => 'fa-check-circle',
        'cor' => '#10b981',
        'texto' => "Excelente! Não há entregas fora da janela de tempo. A operação está com <strong>100% de pontualidade</strong> no momento."
    ];
}

// 3. IA analisando Prioridades
$sqlPrioridade = "SELECT COUNT(*) FROM pedidos WHERE prioridade = 'Alta' AND status = 'Pendente'";
$altas = $pdo->query($sqlPrioridade)->fetchColumn();

if ($altas > 0) {
    $insights[] = [
        'tipo' => 'prioridade',
        'icone' => 'fa-fire',
        'cor' => '#f59e0b',
        'texto' => "Existem <strong>{$altas} pedidos de alta prioridade</strong> aguardando. Recomendação: Priorizar o despacho destes pedidos antes das 10h para garantir o SLA."
    ];
}

// Preparando dados para o Gráfico de Regiões
$dadosGraficoRegioes = ['labels' => [], 'data' => []];
$top5Regioes = array_slice($regioes, 0, 5, true);
foreach ($top5Regioes as $nome => $dados) {
    $dadosGraficoRegioes['labels'][] = $nome;
    $dadosGraficoRegioes['data'][] = $dados['qtd'];
}

// Retorna APENAS UMA VEZ os insights de texto E os dados do gráfico juntos
jsonResponse([
    'insights' => $insights,
    'grafico_regioes' => $dadosGraficoRegioes
]);