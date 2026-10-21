<?php
// api/usuarios.php
require_once '../config/database.php';
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_perfil'] !== 'admin') {
    jsonResponse(['erro' => 'Acesso negado. Apenas administradores.'], 403);
}

$pdo = getConnection();
$metodo = $_SERVER['REQUEST_METHOD'];

switch ($metodo) {
    case 'GET':
        // Lista todos os usuários, trazendo também o telefone se for motorista
        $sql = "SELECT u.id, u.nome, u.email, u.perfil, u.status, u.criado_em, m.contato 
                FROM usuarios u 
                LEFT JOIN motoristas m ON u.id = m.usuario_id 
                ORDER BY u.criado_em DESC";
        jsonResponse($pdo->query($sql)->fetchAll());
        break;

    case 'POST':
        // Atualizar Permissão (Perfil)
        $id = (int)($_POST['id'] ?? 0);
        $acao = $_POST['acao'] ?? '';

        if ($id <= 0) jsonResponse(['erro' => 'Usuário inválido'], 400);
        if ($id == $_SESSION['usuario_id']) jsonResponse(['erro' => 'Você não pode alterar seu próprio acesso aqui'], 400);

        if ($acao === 'mudar_perfil') {
            $novo_perfil = $_POST['perfil'] ?? '';
            if (!in_array($novo_perfil, ['admin', 'gestor', 'despachante', 'motorista'])) {
                jsonResponse(['erro' => 'Perfil inválido'], 400);
            }
            $pdo->prepare("UPDATE usuarios SET perfil = ? WHERE id = ?")->execute([$novo_perfil, $id]);
            jsonResponse(['sucesso' => true, 'msg' => 'Permissão atualizada!']);

        } elseif ($acao === 'mudar_status') {
            $novo_status = $_POST['status'] ?? '';
            if (!in_array($novo_status, ['Ativo', 'Inativo'])) {
                jsonResponse(['erro' => 'Status inválido'], 400);
            }
            $pdo->prepare("UPDATE usuarios SET status = ? WHERE id = ?")->execute([$novo_status, $id]);
            jsonResponse(['sucesso' => true, 'msg' => 'Status atualizado!']);

        } elseif ($acao === 'excluir') {
            $pdo->prepare("DELETE FROM usuarios WHERE id = ?")->execute([$id]);
            jsonResponse(['sucesso' => true, 'msg' => 'Usuário excluído!']);
        }
        break;
}