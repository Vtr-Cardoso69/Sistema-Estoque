<?php
class ProdutoModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function buscarTodos(string $termo = ''): array
    {
        if ($termo === '') {
            $stmt = $this->pdo->query('SELECT * FROM produtos ORDER BY nome');
        } else {
            $stmt = $this->pdo->prepare(
                'SELECT * FROM produtos
                 WHERE nome LIKE :nome OR descricao LIKE :descricao OR CAST(preco AS CHAR) LIKE :preco
                 ORDER BY nome'
            );
            $likeTermo = '%' . $termo . '%';
            $stmt->execute([
                ':nome' => $likeTermo,
                ':descricao' => $likeTermo,
                ':preco' => $likeTermo,
            ]);
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarProduto(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM produtos WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $produto = $stmt->fetch(PDO::FETCH_ASSOC);

        return $produto === false ? null : $produto;
    }

    public function cadastrar(string $nome, string $descricao, int $preco): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO produtos (nome, descricao, preco) VALUES (:nome, :descricao, :preco)'
        );
        $stmt->execute([
            ':nome' => $nome,
            ':descricao' => $descricao,
            ':preco' => $preco,
        ]);
    }

    public function editar(string $nome, string $descricao, int $preco, int $id): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE produtos SET nome = :nome, descricao = :descricao, preco = :preco WHERE id = :id'
        );
        $stmt->execute([
            ':nome' => $nome,
            ':descricao' => $descricao,
            ':preco' => $preco,
            ':id' => $id,
        ]);
    }

    public function deletar(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM produtos WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }
}
