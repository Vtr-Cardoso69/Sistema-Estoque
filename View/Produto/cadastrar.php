<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../Usuario/login.php');
    exit;
}

require_once __DIR__ . '/../../DB/Database.php';
require_once __DIR__ . '/../../Controller/ProdutoController.php';

$controller = new ProdutoController($pdo);
$erro = '';
$nome = '';
$descricao = '';
$preco = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = is_string($_POST['nome'] ?? null) ? $_POST['nome'] : '';
    $descricao = is_string($_POST['descricao'] ?? null) ? $_POST['descricao'] : '';
    $preco = is_string($_POST['preco'] ?? null) ? $_POST['preco'] : '';

    try {
        $controller->cadastrar($nome, $descricao, $preco);
        $_SESSION['mensagem_produto'] = 'Produto cadastrado com sucesso.';
        header('Location: listar.php');
        exit;
    } catch (InvalidArgumentException $exception) {
        $erro = $exception->getMessage();
    }
}
$escapar = static fn($valor): string => htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Produto</title>
</head>
<body>
    <h1>Cadastrar Produto</h1>
    <p><a href="listar.php">Voltar à lista de produtos</a></p>
    <?php if ($erro !== ''): ?><p role="alert"><?= $escapar($erro) ?></p><?php endif; ?>
    <form method="post">
        <label for="nome">Nome:</label>
        <input id="nome" name="nome" type="text" maxlength="255" required value="<?= $escapar($nome) ?>"><br>
        <label for="descricao">Descrição:</label>
        <input id="descricao" name="descricao" type="text" maxlength="255" required value="<?= $escapar($descricao) ?>"><br>
        <label for="preco">Preço (valor inteiro):</label>
        <input id="preco" name="preco" type="number" min="0" step="1" required value="<?= $escapar($preco) ?>"><br>
        <button type="submit">Cadastrar</button>
    </form>
</body>
</html>
