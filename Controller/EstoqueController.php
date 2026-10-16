<?php
require_once __DIR__ . '/../Model/EstoqueModel.php';

class EstoqueController
{
    private EstoqueModel $estoqueModel;

    public function __construct(PDO $pdo)
    {
        $this->estoqueModel = new EstoqueModel($pdo);
    }

    public function listarProdutosOrdenados(): array
    {
        $produtos = $this->estoqueModel->buscarProdutosComEstoque();

        // Ordenação por inserção para manter a ordenação alfabética explícita no sistema.
        for ($i = 1, $total = count($produtos); $i < $total; $i++) {
            $produto = $produtos[$i];
            $j = $i - 1;
            while ($j >= 0 && strnatcasecmp($produtos[$j]['nome'], $produto['nome']) > 0) {
                $produtos[$j + 1] = $produtos[$j];
                $j--;
            }
            $produtos[$j + 1] = $produto;
        }

        return $produtos;
    }

    public function movimentar(
        string $produtoId,
        string $tipo,
        string $quantidade,
        string $data,
        string $responsavelId,
        string $responsavelNome
    ): string {
        $id = filter_var($produtoId, FILTER_VALIDATE_INT);
        $qtd = filter_var($quantidade, FILTER_VALIDATE_INT);
        $usuarioId = filter_var($responsavelId, FILTER_VALIDATE_INT);
        $responsavelNome = trim($responsavelNome);
        $dataValida = DateTime::createFromFormat('!Y-m-d', $data);

        if ($id === false || $id < 1 || $usuarioId === false || $usuarioId < 1
            || $responsavelNome === '' || !in_array($tipo, ['entrada', 'saida'], true)
            || $qtd === false || $qtd < 1 || $qtd > 2147483647 || $dataValida === false
            || $dataValida->format('Y-m-d') !== $data) {
            throw new InvalidArgumentException('Informe produto, tipo, quantidade positiva e data válidos.');
        }

        return $this->estoqueModel->movimentar($id, $tipo, $qtd, $data, $usuarioId, $responsavelNome);
    }

    public function configurarMinimo(string $produtoId, string $quantidadeMinima): void
    {
        $id = filter_var($produtoId, FILTER_VALIDATE_INT);
        $minimo = filter_var($quantidadeMinima, FILTER_VALIDATE_INT);

        if ($id === false || $id < 1 || $minimo === false || $minimo < 0 || $minimo > 2147483647) {
            throw new InvalidArgumentException('Informe um estoque mínimo igual ou maior que zero.');
        }

        $this->estoqueModel->configurarMinimo($id, $minimo);
    }

    public function historico(int $produtoId): array
    {
        return $this->estoqueModel->buscarMovimentacoes($produtoId);
    }
}
