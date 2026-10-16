<?php

require_once __DIR__ . '/../../DB/Database.php';
require_once __DIR__ . '/../../Controller/UsuarioController.php';

$UsuarioController = new UsuarioController($pdo);

if(isset($_GET['id'])){
    $id = $_GET['id'];
    $usuario = $UsuarioController->deletar($id);
    header('Location: ../../index.php');
} else {
    header('Location: ../../index.php');
}
?>