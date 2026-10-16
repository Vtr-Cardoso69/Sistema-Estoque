<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../Usuario/login.php');
    exit;
}

require_once __DIR__ . '/../../DB/Database.php';
require_once __DIR__ . '/../../Controller/ProdutoController.php';
require_once __DIR__ . '/../../Controller/EstoqueController.php';

$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
if ($id === false || $id === null || $id < 1) {
    header('Location: gerenciar.php');
    exit;
}

$produto = (new ProdutoController($pdo))->buscarProduto($id);
if ($produto === null) {
    http_response_code(404);
    exit('Produto não encontrado.');
}

$movimentacoes = (new EstoqueController($pdo))->historico($id);
$escapar = static fn($valor): string => htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico de Estoque</title>
</head>
<body>
    <h1>Histórico de <?= $escapar($produto['nome']) ?></h1>
    <p><a href="gerenciar.php">Voltar à gestão de estoque</a></p>

    <?php if ($movimentacoes === []): ?>
        <p>Nenhuma movimentação registrada.</p>
    <?php else: ?>
        <table border="1" cellpadding="5" cellspacing="0">
            <thead><tr><th>Data da movimentação</th><th>Tipo</th><th>Quantidade</th><th>Responsável</th><th>Registrado em</th></tr></thead>
            <tbody>
            <?php foreach ($movimentacoes as $movimentacao): ?>
                <tr>
                    <td><?= $escapar($movimentacao['data_movimentacao']) ?></td>
                    <td><?= $escapar(ucfirst($movimentacao['tipo'])) ?></td>
                    <td><?= $escapar($movimentacao['quantidade']) ?></td>
                    <td><?= $escapar($movimentacao['responsavel']) ?></td>
                    <td><?= $escapar($movimentacao['criado_em']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>
