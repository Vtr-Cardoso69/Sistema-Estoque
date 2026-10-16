<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../Usuario/login.php');
    exit;
}

require_once __DIR__ . '/../../DB/Database.php';
require_once __DIR__ . '/../../Controller/ProdutoController.php';

$controller = new ProdutoController($pdo);
$termoBusca = $_GET['busca'] ?? '';
$termo = is_string($termoBusca) ? trim($termoBusca) : '';
$produtos = $controller->listar($termo);
$mensagem = $_SESSION['mensagem_produto'] ?? '';
unset($_SESSION['mensagem_produto']);
$escapar = static fn($valor): string => htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Produto</title>
</head>
<body>
    <h1>Cadastro de Produto</h1>
    <p><a href="../../index.php">Voltar ao início</a></p>
    <?php if ($mensagem !== ''): ?><p><?= $escapar($mensagem) ?></p><?php endif; ?>

    <form method="get">
        <label for="busca">Buscar produto:</label>
        <input id="busca" name="busca" type="search" value="<?= $escapar($termo) ?>">
        <button type="submit">Buscar</button>
        <a href="listar.php">Limpar</a>
    </form>
    <p><a href="cadastrar.php">Cadastrar novo produto</a></p>

    <?php if ($produtos === []): ?>
        <p>Nenhum produto encontrado.</p>
    <?php else: ?>
        <table border="1" cellpadding="5" cellspacing="0">
            <thead>
                <tr><th>ID</th><th>Nome</th><th>Descrição</th><th>Preço</th><th>Ações</th></tr>
            </thead>
            <tbody>
            <?php foreach ($produtos as $produto): ?>
                <tr>
                    <td><?= $escapar($produto['id']) ?></td>
                    <td><?= $escapar($produto['nome']) ?></td>
                    <td><?= $escapar($produto['descricao']) ?></td>
                    <td><?= $escapar($produto['preco']) ?></td>
                    <td>
                        <a href="editar.php?id=<?= $escapar($produto['id']) ?>">Editar</a>
                        <form method="post" action="deletar.php" style="display:inline"
                              onsubmit="return confirm('Deseja excluir este produto?')">
                            <input type="hidden" name="id" value="<?= $escapar($produto['id']) ?>">
                            <button type="submit">Excluir</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>
