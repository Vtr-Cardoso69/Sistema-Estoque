<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../Usuario/login.php');
    exit;
}

require_once __DIR__ . '/../../DB/Database.php';
require_once __DIR__ . '/../../Controller/ProdutoController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
    if ($id !== false && $id !== null && $id > 0) {
        $controller = new ProdutoController($pdo);
        $controller->deletar($id);
        $_SESSION['mensagem_produto'] = 'Produto excluído com sucesso.';
    }
}

header('Location: listar.php');
exit;
