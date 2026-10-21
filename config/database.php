<?php
// config/database.php
session_start();

define('DB_HOST', 'localhost');
define('DB_NAME', 'senn_logistica');
define('DB_USER', 'root');
define('DB_PASS', ''); // XAMPP padrão

function getConnection() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        http_response_code(500);
        die(json_encode(['erro' => 'Erro de conexão: ' . $e->getMessage()]));
    }
}

// Helper: verifica se usuário está logado
function requireAuth() {
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: ../index.php');
        exit;
    }
}

// Helper: resposta JSON padronizada
function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}