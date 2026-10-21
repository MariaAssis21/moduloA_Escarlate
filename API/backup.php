<?php
// api/backup.php
require_once '../config/database.php';

// Apenas admin pode fazer backup
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_perfil'] !== 'admin') {
    jsonResponse(['erro' => 'Acesso negado'], 403);
}

$tabelas = ['usuarios', 'veiculos', 'motoristas', 'pedidos', 'rastreamento', 'logs_notificacoes'];
$pdo = getConnection();
$sqlDump = "-- Backup SENN AI-X\n-- Data: " . date('d/m/Y H:i:s') . "\n\n";

foreach ($tabelas as $tabela) {
    // Pega a estrutura da tabela
    $stmt = $pdo->query("SHOW CREATE TABLE $tabela");
    $row = $stmt->fetch();
    $sqlDump .= $row['Create Table'] . ";\n\n";

    // Pega os dados da tabela
    $stmt = $pdo->query("SELECT * FROM $tabela");
    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($dados) > 0) {
        foreach ($dados as $linha) {
            $valores = array_map(function($v) use ($pdo) {
                return $v === null ? 'NULL' : $pdo->quote($v);
            }, array_values($linha));
            
            $sqlDump .= "INSERT INTO $tabela (" . implode(', ', array_keys($linha)) . ") VALUES (" . implode(', ', $valores) . ");\n";
        }
        $sqlDump .= "\n";
    }
}

// Configura o download do arquivo .sql
header('Content-Type: application/sql');
header('Content-Disposition: attachment; filename="backup_senn_' . date('Y-m-d_H-i') . '.sql"');
header('Content-Length: ' . strlen($sqlDump));
echo $sqlDump;
exit;