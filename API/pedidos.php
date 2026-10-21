<?php
// api/pedidos.php
require_once '../config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    jsonResponse(['erro' => 'Não autenticado'], 401);
}

$pdo = getConnection();
$metodo = $_SERVER['REQUEST_METHOD'];

switch ($metodo) {
    case 'GET':
        jsonResponse($pdo->query("SELECT * FROM pedidos ORDER BY prioridade = 'Alta' DESC, criado_em DESC")->fetchAll());
        break;

    case 'POST':
        if (!in_array($_SESSION['usuario_perfil'], ['admin', 'despachante'])) {
            jsonResponse(['erro' => 'Sem permissão'], 403);
        }

        // ==========================================
        // IMPORTAÇÃO VIA CSV
        // ==========================================
        if (isset($_FILES['arquivo_csv']) && $_FILES['arquivo_csv']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['arquivo_csv']['tmp_name'];
            $fileName = $_FILES['arquivo_csv']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            if ($fileExtension !== 'csv') {
                jsonResponse(['erro' => 'Por favor, envie um arquivo .csv válido'], 400);
            }

            $handle = fopen($fileTmpPath, 'r');
            if ($handle === false) {
                jsonResponse(['erro' => 'Erro ao abrir o arquivo'], 500);
            }

            // Ignora a primeira linha (cabeçalho)
            fgetcsv($handle, 1000, ';'); 

            $inseridos = 0;
            $sql = "INSERT INTO pedidos (codigo, cliente, endereco, peso_kg, volume_m3, prioridade, janela_inicio, janela_fim) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);

            $pdo->beginTransaction();

            while (($row = fgetcsv($handle, 1000, ';')) !== FALSE) {
                // Formato esperado: Cliente;Endereco;Peso;Volume;Prioridade;JanelaInicio;JanelaFim
                if (count($row) >= 7) {
                    $cliente = trim($row[0]);
                    $endereco = trim($row[1]);
                    $peso = (float)str_replace(',', '.', trim($row[2]));
                    $volume = (float)str_replace(',', '.', trim($row[3]));
                    $prioridade = in_array(trim($row[4]), ['Baixa', 'Normal', 'Alta']) ? trim($row[4]) : 'Normal';
                    $inicio = trim($row[5]); 
                    $fim = trim($row[6]);    
                    $codigo = 'PED-' . strtoupper(substr(uniqid(), -6));

                    $stmt->execute([$codigo, $cliente, $endereco, $peso, $volume, $prioridade, $inicio, $fim]);
                    $inseridos++;
                }
            }
            fclose($handle);
            $pdo->commit();

            jsonResponse(['sucesso' => true, 'mensagem' => "$inseridos pedidos importados com sucesso!"]);
        } 
        // ==========================================
        // CADASTRO MANUAL
        // ==========================================
        else {
            $cliente = trim($_POST['cliente'] ?? '');
            $endereco = trim($_POST['endereco'] ?? '');
            $peso = (float) ($_POST['peso_kg'] ?? 0);
            $volume = (float) ($_POST['volume_m3'] ?? 0);
            $prioridade = $_POST['prioridade'] ?? 'Normal';
            $inicio = $_POST['janela_inicio'] ?? '';
            $fim = $_POST['janela_fim'] ?? '';

            // Validação
            if (empty($cliente) || empty($endereco) || empty($inicio) || empty($fim)) {
                jsonResponse(['erro' => 'Preencha todos os campos obrigatórios'], 400);
            }
            if ($peso <= 0 || $volume <= 0) {
                jsonResponse(['erro' => 'Peso e Volume devem ser maiores que zero'], 400);
            }
            if (strtotime($fim) <= strtotime($inicio)) {
                jsonResponse(['erro' => 'A janela final deve ser após a inicial'], 400);
            }

            $codigo = 'PED-' . strtoupper(substr(uniqid(), -6));
            $sql = "INSERT INTO pedidos (codigo, cliente, endereco, peso_kg, volume_m3, prioridade, janela_inicio, janela_fim) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $pdo->prepare($sql)->execute([$codigo, $cliente, $endereco, $peso, $volume, $prioridade, $inicio, $fim]);

            jsonResponse(['sucesso' => true, 'mensagem' => 'Pedido criado com sucesso!', 'codigo' => $codigo], 201);
        }
        break;

    default:
        jsonResponse(['erro' => 'Método não suportado'], 405);
}