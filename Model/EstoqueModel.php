<?php
class EstoqueModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function buscarProdutosComEstoque(): array
    {
        $stmt = $this->pdo->query(
            'SELECT p.id, p.nome, p.descricao, p.preco,
                    e.id AS estoque_id, COALESCE(e.quantidade, 0) AS quantidade,
                    COALESCE(e.quantidade_minima, 0) AS quantidade_minima
             FROM produtos p
             LEFT JOIN estoques e ON e.produtos_id = p.id'
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function movimentar(
        int $produtoId,
        string $tipo,
        int $quantidade,
        string $data,
        int $responsavelId,
        string $responsavelNome
    ): string {
        $this->pdo->beginTransaction();

        try {
            $produtoStmt = $this->pdo->prepare('SELECT id FROM produtos WHERE id = :id FOR UPDATE');
            $produtoStmt->execute([':id' => $produtoId]);
            if ($produtoStmt->fetchColumn() === false) {
                throw new InvalidArgumentException('Produto não encontrado.');
            }

            $estoqueStmt = $this->pdo->prepare(
                'SELECT id, quantidade, quantidade_minima FROM estoques
                 WHERE produtos_id = :produto_id ORDER BY id LIMIT 1 FOR UPDATE'
            );
            $estoqueStmt->execute([':produto_id' => $produtoId]);
            $estoque = $estoqueStmt->fetch(PDO::FETCH_ASSOC);

            if ($estoque === false) {
                if ($tipo === 'saida') {
                    throw new InvalidArgumentException('Não há estoque disponível para saída.');
                }

                $insertEstoque = $this->pdo->prepare(
                    'INSERT INTO estoques (produtos_id, quantidade, quantidade_minima)
                     VALUES (:produto_id, :quantidade, 0)'
                );
                $insertEstoque->execute([':produto_id' => $produtoId, ':quantidade' => $quantidade]);
                $novaQuantidade = $quantidade;
                $minimo = 0;
            } else {
                $estoqueAtual = (int) $estoque['quantidade'];
                if ($tipo === 'saida' && $quantidade > $estoqueAtual) {
                    throw new InvalidArgumentException('A quantidade de saída excede o estoque disponível.');
                }

                $novaQuantidade = $tipo === 'entrada'
                    ? $estoqueAtual + $quantidade
                    : $estoqueAtual - $quantidade;
                if ($novaQuantidade > 2147483647) {
                    throw new InvalidArgumentException('A quantidade resultante excede o limite do estoque.');
                }
                $minimo = (int) $estoque['quantidade_minima'];

                $updateEstoque = $this->pdo->prepare(
                    'UPDATE estoques SET quantidade = :quantidade WHERE id = :id'
                );
                $updateEstoque->execute([
                    ':quantidade' => $novaQuantidade,
                    ':id' => $estoque['id'],
                ]);
            }

            $movimentoStmt = $this->pdo->prepare(
                'INSERT INTO movimentacoes_estoque
                    (produtos_id, tipo, quantidade, data_movimentacao, usuario_id, responsavel)
                 VALUES (:produto_id, :tipo, :quantidade, :data, :usuario_id, :responsavel)'
            );
            $movimentoStmt->execute([
                ':produto_id' => $produtoId,
                ':tipo' => $tipo,
                ':quantidade' => $quantidade,
                ':data' => $data,
                ':usuario_id' => $responsavelId,
                ':responsavel' => $responsavelNome,
            ]);

            $this->pdo->commit();
            if ($tipo === 'saida' && $novaQuantidade < $minimo) {
                return "Atenção: estoque abaixo do mínimo configurado ({$novaQuantidade} de {$minimo}).";
            }

            return 'Movimentação registrada com sucesso.';
        } catch (Throwable $erro) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $erro;
        }
    }

    public function configurarMinimo(int $produtoId, int $quantidadeMinima): void
    {
        $produtoStmt = $this->pdo->prepare('SELECT id FROM produtos WHERE id = :id');
        $produtoStmt->execute([':id' => $produtoId]);
        if ($produtoStmt->fetchColumn() === false) {
            throw new InvalidArgumentException('Produto não encontrado.');
        }

        $estoqueStmt = $this->pdo->prepare('SELECT id FROM estoques WHERE produtos_id = :produto_id ORDER BY id LIMIT 1');
        $estoqueStmt->execute([':produto_id' => $produtoId]);
        $estoqueId = $estoqueStmt->fetchColumn();

        if ($estoqueId === false) {
            $stmt = $this->pdo->prepare(
                'INSERT INTO estoques (produtos_id, quantidade, quantidade_minima)
                 VALUES (:produto_id, 0, :minimo)'
            );
            $stmt->execute([':produto_id' => $produtoId, ':minimo' => $quantidadeMinima]);
            return;
        }

        $stmt = $this->pdo->prepare('UPDATE estoques SET quantidade_minima = :minimo WHERE id = :id');
        $stmt->execute([':minimo' => $quantidadeMinima, ':id' => $estoqueId]);
    }

    public function buscarMovimentacoes(int $produtoId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT tipo, quantidade, data_movimentacao, criado_em, responsavel
             FROM movimentacoes_estoque
             WHERE produtos_id = :produto_id
             ORDER BY data_movimentacao DESC, id DESC'
        );
        $stmt->execute([':produto_id' => $produtoId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
