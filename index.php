<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: View/Usuario/login.php');
    exit;
}

$nomeUsuario = htmlspecialchars($_SESSION['usuario_nome'] ?? 'Usuário', ENT_QUOTES, 'UTF-8');
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel Principal</title>
</head>
<body>
    <!-- 5.1.1. Exibir nome do usuário logado -->
    <h1>Bem-vindo, <?= $nomeUsuario ?>!</h1>

    <nav>
        <ul>
            <!-- 5.1.3. Link para Cadastro de Produto (Item 6) -->
            <li><a href="View/Produto/listar.php">Cadastro de Produto</a></li>

            <!-- 5.1.4. Link para Gestão de Estoque (Item 7) -->
            <li><a href="View/Estoque/gerenciar.php">Gestão de Estoque</a></li>
            
            <!-- 5.1.2. Link/Botão de Logout -->
            <li><a href="logout.php" style="color: red;">Sair (Logout)</a></li>
        </ul>
    </nav>
</body>
</html>