<?php
// api/motoristas.php
require_once '../config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    jsonResponse(['erro' => 'Não autenticado'], 401);
}

$pdo = getConnection();
$metodo = $_SERVER['REQUEST_METHOD'];

switch ($metodo) {
    case 'GET':
        $sql = "SELECT m.*, v.placa AS veiculo_placa, u.email 
                FROM motoristas m 
                LEFT JOIN veiculos v ON m.veiculo_id = v.id 
                LEFT JOIN usuarios u ON m.usuario_id = u.id
                ORDER BY m.criado_em DESC";
        jsonResponse($pdo->query($sql)->fetchAll());
        break;

    case 'POST':
        if (!in_array($_SESSION['usuario_perfil'], ['admin', 'gestor'])) {
            jsonResponse(['erro' => 'Sem permissão'], 403);
        }

        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $contato = trim($_POST['contato'] ?? '');
        $categoria_cnh = $_POST['categoria_cnh'] ?? 'B';
        $validade_cnh = $_POST['validade_cnh'] ?? '';
        $disponibilidade = $_POST['disponibilidade'] ?? 'Disponível';
        $veiculo_id = !empty($_POST['veiculo_id']) ? (int) $_POST['veiculo_id'] : null;
        $senha = $_POST['senha'] ?? '123456'; // Senha padrão se não for enviada

        // Validações
        if (empty($nome) || empty($email) || empty($contato) || empty($validade_cnh)) {
            jsonResponse(['erro' => 'Preencha todos os campos obrigatórios, incluindo e-mail'], 400);
        }

        // Verifica se e-mail já existe
        $stmtCheck = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmtCheck->execute([$email]);
        if ($stmtCheck->fetch()) {
            jsonResponse(['erro' => 'Este e-mail já está em uso'], 409);
        }

        try {
            // Inicia uma transação (se algo falhar, cancela tudo)
            $pdo->beginTransaction();

            // 1. Cria o Login (Tabela Usuarios)
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
            $sqlUser = "INSERT INTO usuarios (nome, email, senha, perfil) VALUES (?, ?, ?, 'motorista')";
            $stmtUser = $pdo->prepare($sqlUser);
            $stmtUser->execute([$nome, $email, $senhaHash]);
            $usuario_id = $pdo->lastInsertId();

            // 2. Cria os dados do motorista (Tabela Motoristas)
            $sqlMotorista = "INSERT INTO motoristas (usuario_id, nome, contato, categoria_cnh, validade_cnh, disponibilidade, veiculo_id)
                             VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmtMotorista = $pdo->prepare($sqlMotorista);
            $stmtMotorista->execute([$usuario_id, $nome, $contato, $categoria_cnh, $validade_cnh, $disponibilidade, $veiculo_id]);

            $pdo->commit();

            jsonResponse(['sucesso' => true, 'id' => $pdo->lastInsertId()], 201);
        } catch (Exception $e) {
            $pdo->rollBack();
            jsonResponse(['erro' => 'Erro ao cadastrar motorista e login: ' . $e->getMessage()], 500);
        }
        break;

    default:
        jsonResponse(['erro' => 'Método não suportado'], 405);
}