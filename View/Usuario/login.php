<?php
session_start();
if (isset($_SESSION['usuario_id'])) {
    header('Location: ../../index.php');
    exit;
}

require_once __DIR__ . '/../../DB/Database.php';
require_once __DIR__ . '/../../Controller/UsuarioController.php';

$controller = new UsuarioController($pdo);
$erro = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = is_string($_POST['email'] ?? null) ? trim($_POST['email']) : '';
    $senha = is_string($_POST['senha'] ?? null) ? $_POST['senha'] : '';
    $usuario = $controller->autenticar($email, $senha);

    if ($usuario === null) {
        $erro = 'E-mail ou senha inválidos.';
    } else {
        session_regenerate_id(true);
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        header('Location: ../../index.php');
        exit;
    }
}

$escapar = static fn($valor): string => htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar no sistema</title>
</head>
<body>
    <h1>Entrar no sistema</h1>
    <?php if ($erro !== ''): ?><p role="alert"><?= $escapar($erro) ?></p><?php endif; ?>
    <form method="post">
        <label for="email">E-mail:</label>
        <input id="email" type="email" name="email" required value="<?= $escapar($email) ?>"><br>
        <label for="senha">Senha:</label>
        <input id="senha" type="password" name="senha" required><br>
        <button type="submit">Entrar</button>
    </form>
    <p><a href="cadastrar.php">Cadastrar novo usuário</a></p>
</body>
</html>
