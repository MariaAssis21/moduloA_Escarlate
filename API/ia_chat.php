<?php
// api/ia_chat.php
require_once '../config/database.php';
if (!isset($_SESSION['usuario_id'])) jsonResponse(['erro' => 'Não autenticado'], 401);

$pdo = getConnection();
$mensagem = mb_strtolower(trim($_POST['mensagem'] ?? ''), 'UTF-8');

// Resposta padrão caso não entenda (Corporativa)
$resposta = "Desculpe, para obter essa informação, por favor, entre em contato com o nosso suporte técnico ou utilize os botões de atalho abaixo. Como posso ajudar com a logística hoje?";

// 1. Intenção: Total de Pedidos
if (preg_match('/(total|quantos|todos|qtd).*(pedido|entrega)/i', $mensagem) || preg_match('/(pedido|entrega).*(total|quantos|qtd)/i', $mensagem)) {
    $total = $pdo->query("SELECT COUNT(*) FROM pedidos")->fetchColumn();
    $resposta = "Atualmente temos um total de <strong>{$total} pedidos</strong> cadastrados no sistema.";
} 
// 2. Intenção: Pedidos Pendentes
elseif (preg_match('/(pendente|aguardando|espera|para sair)/i', $mensagem)) {
    $total = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE status = 'Pendente'")->fetchColumn();
    $resposta = "Existem <strong>{$total} pedidos pendentes</strong> aguardando despacho.";
}
// 3. Intenção: Pedidos Atrasados
elseif (preg_match('/(atrasad|falha|erro|fora do prazo)/i', $mensagem)) {
    $agora = date('Y-m-d H:i:s');
    $total = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE status = 'Pendente' AND janela_fim < '$agora'")->fetchColumn();
    if ($total > 0) {
        $resposta = "Atenção: Encontrei <strong>{$total} pedidos atrasados</strong>. Recomendo verificar o painel de rastreamento.";
    } else {
        $resposta = "Boa notícia! Nenhum pedido está atrasado neste momento.";
    }
}
// 4. Intenção: Veículos (Frota)
elseif (preg_match('/(veiculo|carro|frota|caminhao|van)/i', $mensagem)) {
    $total = $pdo->query("SELECT COUNT(*) FROM veiculos")->fetchColumn();
    $disponiveis = $pdo->query("SELECT COUNT(*) FROM veiculos WHERE status = 'Disponível'")->fetchColumn();
    $manutencao = $pdo->query("SELECT COUNT(*) FROM veiculos WHERE status = 'Em Manutenção'")->fetchColumn();
    
    $resposta = "Nossa frota possui <strong>{$total} veículos</strong>.<br>
                 🟢 Disponíveis: <strong>{$disponiveis}</strong><br>
                 🔴 Em manutenção: <strong>{$manutencao}</strong>";
}
// 5. Intenção: Motoristas
elseif (preg_match('/(motorista|condutor|equipe|cnh)/i', $mensagem)) {
    $total = $pdo->query("SELECT COUNT(*) FROM motoristas")->fetchColumn();
    $disponiveis = $pdo->query("SELECT COUNT(*) FROM motoristas WHERE disponibilidade = 'Disponível'")->fetchColumn();
    
    // Verifica se tem alguma CNH vencida
    $vencidas = $pdo->query("SELECT COUNT(*) FROM motoristas WHERE validade_cnh < CURDATE()")->fetchColumn();
    $alertaCnh = $vencidas > 0 ? "<br>⚠️ <strong>Atenção:</strong> {$vencidas} motorista(s) com CNH vencida!" : "";

    $resposta = "Temos <strong>{$total} motoristas</strong> cadastrados.<br>
                 🟢 Disponíveis agora: <strong>{$disponiveis}</strong>{$alertaCnh}";
}
// 6. Intenção: Prioridades
elseif (preg_match('/(prioridade|urgente|alta prioridade)/i', $mensagem)) {
    $total = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE prioridade = 'Alta' AND status = 'Pendente'")->fetchColumn();
    $resposta = "Existem <strong>{$total} pedidos de alta prioridade</strong> aguardando entrega. Sugiro priorizar esses despachos.";
}
// 7. Intenção: Status de um pedido específico
elseif (preg_match('/ped-[a-z0-9]+/i', $mensagem, $matches)) {
    $codigo = strtoupper($matches[0]);
    $stmt = $pdo->prepare("SELECT status, cliente FROM pedidos WHERE codigo = ?");
    $stmt->execute([$codigo]);
    $pedido = $stmt->fetch();
    
    if ($pedido) {
        $resposta = "O pedido <strong>{$codigo}</strong> do cliente {$pedido['cliente']} está com o status: <strong>{$pedido['status']}</strong>.";
    } else {
        $resposta = "Não encontrei nenhum pedido com o código <strong>{$codigo}</strong> no sistema.";
    }
}
// 8. Intenção: Ajuda (O que você faz?)
elseif (preg_match('/(ajuda|socorro|help|o que voce faz|funcoes)/i', $mensagem)) {
    $resposta = "Sou o assistente de operações da SENN AI-X. Posso consultar em tempo real:<br>
                 📦 Total de Pedidos<br>
                 🚚 Status da Frota<br>
                 👤 Motoristas Disponíveis<br>
                 ⚠️ Atrasos e Prioridades<br>
                 Digite ou clique em uma das opções abaixo!";
}
// 9. Intenção: Saudações
elseif (preg_match('/(oi|ola|bom dia|boa tarde|boa noite|e ai)/i', $mensagem)) {
    $resposta = "Olá! Sou o assistente virtual da SENN AI-X. Em que posso ajudar com a sua operação logística hoje?";
}

jsonResponse(['resposta' => $resposta]);