<?php
require_once __DIR__ . '/../Model/ProdutoModel.php';

class ProdutoController
{
    private ProdutoModel $produtoModel;

    public function __construct(PDO $pdo)
    {
        $this->produtoModel = new ProdutoModel($pdo);
    }

    public function listar(string $termo = ''): array
    {
        return $this->produtoModel->buscarTodos($termo);
    }

    public function buscarProduto(int $id): ?array
    {
        return $this->produtoModel->buscarProduto($id);
    }

    public function cadastrar(string $nome, string $descricao, string $preco): void
    {
        $this->validar($nome, $descricao, $preco);
        $this->produtoModel->cadastrar(trim($nome), trim($descricao), (int) $preco);
    }

    public function editar(string $nome, string $descricao, string $preco, int $id): void
    {
        $this->validar($nome, $descricao, $preco);
        if ($this->buscarProduto($id) === null) {
            throw new InvalidArgumentException('Produto não encontrado.');
        }

        $this->produtoModel->editar(trim($nome), trim($descricao), (int) $preco, $id);
    }

    public function deletar(int $id): void
    {
        $this->produtoModel->deletar($id);
    }

    private function validar(string $nome, string $descricao, string $preco): void
    {
        $nome = trim($nome);
        $descricao = trim($descricao);
        $precoValidado = filter_var($preco, FILTER_VALIDATE_INT);

        if ($nome === '' || $descricao === '' || $preco === '') {
            throw new InvalidArgumentException('Preencha todos os campos.');
        }
        if (mb_strlen($nome) > 255 || mb_strlen($descricao) > 255) {
            throw new InvalidArgumentException('Nome e descrição devem ter no máximo 255 caracteres.');
        }
        if ($precoValidado === false || $precoValidado < 0 || $precoValidado > 2147483647) {
            throw new InvalidArgumentException('Informe um preço inteiro entre zero e 2.147.483.647.');
        }
    }
}
