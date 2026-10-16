<?php
session_start();
require_once __DIR__ . '/../../DB/Database.php';
require_once __DIR__ . '/../../Controller/UsuarioController.php';

if (isset($_SESSION['usuario_id'])) {
    header('Location: ../../index.php');
    exit;
}

$controller = new UsuarioController($pdo);
$erro = '';
$nome = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = is_string($_POST['nome'] ?? null) ? $_POST['nome'] : '';
    $email = is_string($_POST['email'] ?? null) ? $_POST['email'] : '';
    $senha = is_string($_POST['senha'] ?? null) ? $_POST['senha'] : '';

    try {
        $id = $controller->cadastrar($nome, $email, $senha);
        session_regenerate_id(true);
        $_SESSION['usuario_id'] = $id;
        $_SESSION['usuario_nome'] = trim($nome);
        header('Location: ../../index.php');
        exit;
    } catch (InvalidArgumentException $exception) {
        $erro = $exception->getMessage();
    } catch (PDOException $exception) {
        $erro = 'Não foi possível cadastrar. Verifique se o e-mail já está cadastrado e tente novamente.';
    }
}

$escapar = static fn($valor): string => htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Usuário</title>
</head>
<body>
    <h1>Cadastrar Usuário</h1>
    <?php if ($erro !== ''): ?><p role="alert"><?= $escapar($erro) ?></p><?php endif; ?>
    <form method="post">
        <label for="nome">Nome:</label>
        <input id="nome" type="text" name="nome" maxlength="255" required value="<?= $escapar($nome) ?>"><br>
        <label for="email">E-mail:</label>
        <input id="email" type="email" name="email" maxlength="255" required value="<?= $escapar($email) ?>"><br>
        <label for="senha">Senha (números):</label>
        <input id="senha" type="password" name="senha" inputmode="numeric" pattern="[0-9]+" required><br>
        <button type="submit">Cadastrar e entrar</button>
    </form>
    <p><a href="login.php">Já tenho cadastro</a></p>
</body>
</html>
