<?php
// api/veiculos.php
require_once '../config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    jsonResponse(['erro' => 'Não autenticado'], 401);
}

$pdo = getConnection();
$metodo = $_SERVER['REQUEST_METHOD'];

switch ($metodo) {
    case 'GET':
        $stmt = $pdo->query("SELECT * FROM veiculos ORDER BY criado_em DESC");
        jsonResponse($stmt->fetchAll());
        break;

    case 'POST':
        // Apenas admin/gestor podem cadastrar
        if (!in_array($_SESSION['usuario_perfil'], ['admin', 'gestor'])) {
            jsonResponse(['erro' => 'Sem permissão'], 403);
        }

        $dados = [
            'placa' => strtoupper(trim($_POST['placa'] ?? '')),
            'tipo' => trim($_POST['tipo'] ?? ''),
            'capacidade_kg' => (float) ($_POST['capacidade'] ?? 0),
            'volume_m3' => (float) ($_POST['volume'] ?? 0),
            'autonomia_km' => (int) ($_POST['autonomia'] ?? 0),
            'status' => $_POST['status'] ?? 'Disponível',
            'proxima_manutencao' => $_POST['manutencao'] ?? ''
        ];

        if (empty($dados['placa']) || empty($dados['tipo']) || empty($dados['proxima_manutencao'])) {
            jsonResponse(['erro' => 'Campos obrigatórios faltando'], 400);
        }

        // Verifica duplicidade
        $check = $pdo->prepare("SELECT id FROM veiculos WHERE placa = ?");
        $check->execute([$dados['placa']]);
        if ($check->fetch()) {
            jsonResponse(['erro' => 'Placa já cadastrada'], 409);
        }

        $sql = "INSERT INTO veiculos (placa, tipo, capacidade_kg, volume_m3, autonomia_km, status, proxima_manutencao)
                VALUES (:placa, :tipo, :capacidade_kg, :volume_m3, :autonomia_km, :status, :proxima_manutencao)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($dados);

        jsonResponse(['sucesso' => true, 'id' => $pdo->lastInsertId()], 201);
        break;

    case 'DELETE':
        if ($_SESSION['usuario_perfil'] !== 'admin') {
            jsonResponse(['erro' => 'Sem permissão'], 403);
        }
        $id = (int) ($_GET['id'] ?? 0);
        $pdo->prepare("DELETE FROM veiculos WHERE id = ?")->execute([$id]);
        jsonResponse(['sucesso' => true]);
        break;

    default:
        jsonResponse(['erro' => 'Método não suportado'], 405);
}