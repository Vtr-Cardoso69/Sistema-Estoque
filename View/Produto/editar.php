<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../Usuario/login.php');
    exit;
}

require_once __DIR__ . '/../../DB/Database.php';
require_once __DIR__ . '/../../Controller/ProdutoController.php';

$id = filter_var($_GET['id'] ?? $_POST['id'] ?? null, FILTER_VALIDATE_INT);
if ($id === false || $id === null || $id < 1) {
    header('Location: listar.php');
    exit;
}

$controller = new ProdutoController($pdo);
$produto = $controller->buscarProduto($id);
if ($produto === null) {
    http_response_code(404);
    exit('Produto não encontrado.');
}

$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = is_string($_POST['nome'] ?? null) ? $_POST['nome'] : '';
    $descricao = is_string($_POST['descricao'] ?? null) ? $_POST['descricao'] : '';
    $preco = is_string($_POST['preco'] ?? null) ? $_POST['preco'] : '';

    try {
        $controller->editar($nome, $descricao, $preco, $id);
        $_SESSION['mensagem_produto'] = 'Produto atualizado com sucesso.';
        header('Location: listar.php');
        exit;
    } catch (InvalidArgumentException $exception) {
        $erro = $exception->getMessage();
        $produto['nome'] = $nome;
        $produto['descricao'] = $descricao;
        $produto['preco'] = $preco;
    }
}
$escapar = static fn($valor): string => htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Produto</title>
</head>
<body>
    <h1>Editar Produto</h1>
    <p><a href="listar.php">Voltar à lista de produtos</a></p>
    <?php if ($erro !== ''): ?><p role="alert"><?= $escapar($erro) ?></p><?php endif; ?>
    <form method="post">
        <input type="hidden" name="id" value="<?= $escapar($id) ?>">
        <label for="nome">Nome:</label>
        <input id="nome" name="nome" type="text" maxlength="255" required value="<?= $escapar($produto['nome']) ?>"><br>
        <label for="descricao">Descrição:</label>
        <input id="descricao" name="descricao" type="text" maxlength="255" required value="<?= $escapar($produto['descricao']) ?>"><br>
        <label for="preco">Preço (valor inteiro):</label>
        <input id="preco" name="preco" type="number" min="0" step="1" required value="<?= $escapar($produto['preco']) ?>"><br>
        <button type="submit">Salvar alterações</button>
    </form>
</body>
</html>
