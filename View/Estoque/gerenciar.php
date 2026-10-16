<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../Usuario/login.php');
    exit;
}

require_once __DIR__ . '/../../DB/Database.php';
require_once __DIR__ . '/../../Controller/EstoqueController.php';

$controller = new EstoqueController($pdo);
$erro = '';
$mensagem = $_SESSION['mensagem_estoque'] ?? '';
unset($_SESSION['mensagem_estoque']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (($_POST['acao'] ?? '') === 'configurar_minimo') {
            $produtoId = is_string($_POST['produto_id'] ?? null) ? $_POST['produto_id'] : '';
            $minimo = is_string($_POST['quantidade_minima'] ?? null) ? $_POST['quantidade_minima'] : '';
            $controller->configurarMinimo($produtoId, $minimo);
            $_SESSION['mensagem_estoque'] = 'Estoque mínimo atualizado.';
        } elseif (($_POST['acao'] ?? '') === 'movimentar') {
            $produtoId = is_string($_POST['produto_id'] ?? null) ? $_POST['produto_id'] : '';
            $tipo = is_string($_POST['tipo'] ?? null) ? $_POST['tipo'] : '';
            $quantidade = is_string($_POST['quantidade'] ?? null) ? $_POST['quantidade'] : '';
            $data = is_string($_POST['data_movimentacao'] ?? null) ? $_POST['data_movimentacao'] : '';
            $_SESSION['mensagem_estoque'] = $controller->movimentar(
                $produtoId,
                $tipo,
                $quantidade,
                $data,
                is_scalar($_SESSION['usuario_id'] ?? null) ? (string) $_SESSION['usuario_id'] : '',
                is_string($_SESSION['usuario_nome'] ?? null) ? $_SESSION['usuario_nome'] : ''
            );
        } else {
            throw new InvalidArgumentException('Ação inválida.');
        }

        header('Location: gerenciar.php');
        exit;
    } catch (InvalidArgumentException $exception) {
        $erro = $exception->getMessage();
    }
}

$produtos = $controller->listarProdutosOrdenados();
$escapar = static fn($valor): string => htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Estoque</title>
</head>
<body>
    <h1>Gestão de Estoque</h1>
    <p><a href="../../index.php">Voltar ao início</a></p>
    <?php if ($erro !== ''): ?><p role="alert"><?= $escapar($erro) ?></p><?php endif; ?>
    <?php if ($mensagem !== ''): ?>
        <p><?= $escapar($mensagem) ?></p>
        <script>alert(<?= json_encode($mensagem, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>);</script>
    <?php endif; ?>

    <?php if ($produtos === []): ?>
        <p>Cadastre produtos antes de movimentar o estoque.</p>
    <?php else: ?>
        <table border="1" cellpadding="5" cellspacing="0">
            <thead>
                <tr><th>Produto</th><th>Estoque atual</th><th>Estoque mínimo</th><th>Configurar mínimo</th><th>Movimentação</th><th>Histórico</th></tr>
            </thead>
            <tbody>
            <?php foreach ($produtos as $produto): ?>
                <tr>
                    <td><?= $escapar($produto['nome']) ?></td>
                    <td><?= $escapar($produto['quantidade']) ?></td>
                    <td><?= $escapar($produto['quantidade_minima']) ?></td>
                    <td>
                        <form method="post">
                            <input type="hidden" name="acao" value="configurar_minimo">
                            <input type="hidden" name="produto_id" value="<?= $escapar($produto['id']) ?>">
                            <label>
                                Mínimo:
                                <input type="number" name="quantidade_minima" min="0" step="1"
                                       value="<?= $escapar($produto['quantidade_minima']) ?>" required>
                            </label>
                            <button type="submit">Salvar</button>
                        </form>
                    </td>
                    <td>
                        <form method="post">
                            <input type="hidden" name="acao" value="movimentar">
                            <input type="hidden" name="produto_id" value="<?= $escapar($produto['id']) ?>">
                            <label>
                                Tipo:
                                <select name="tipo" required>
                                    <option value="entrada">Entrada</option>
                                    <option value="saida">Saída</option>
                                </select>
                            </label>
                            <label>
                                Quantidade:
                                <input type="number" name="quantidade" min="1" step="1" required>
                            </label>
                            <label>
                                Data:
                                <input type="date" name="data_movimentacao" required value="<?= date('Y-m-d') ?>">
                            </label>
                            <button type="submit">Registrar</button>
                        </form>
                    </td>
                    <td><a href="historico.php?id=<?= $escapar($produto['id']) ?>">Ver histórico</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>
