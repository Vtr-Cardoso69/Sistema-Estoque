<?php

if (empty($usuarios)) {
    echo "<p>Nenhum usuário encontrado!</p>";
    echo "<a href='View/Usuario/cadastrar.php'>Cadastrar</a>";
    return;
}

echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<tr><td><a href='View/Usuario/cadastrar.php'>Cadastrar</a></td></tr>";
echo "<tr><th>ID</th><th>Nome</th><th>E-mail</th><th>Ações</th></tr>";

foreach ($usuarios as $usuario) {
    $id = $usuario['id'];
    echo "<tr>";
    echo "<td>{$id}</td>";
    echo "<td>{$usuario['nome']}</td>";
    echo "<td>{$usuario['email']}</td>";
    echo "<td>
                <a href='View/Usuario/editar.php?id={$id}'>Editar</a> |
                <a href='View/Usuario/deletar.php?id={$id}' onclick=\"return confirm('Tem certeza que deseja excluir este usuário?')\">Deletar</a>
            </td>";
    echo "</tr>";
}
echo "</table>";
