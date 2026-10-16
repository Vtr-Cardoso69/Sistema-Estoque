<?php

$dsn = "mysql:host=localhost;dbname=saep_db;charset=utf8mb4";
$usuario = "root";
$senha = "";

$pdo = new PDO($dsn, $usuario, $senha, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);
?>