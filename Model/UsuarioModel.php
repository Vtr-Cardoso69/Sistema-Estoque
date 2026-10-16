<?php
class UsuarioModel {
    private $pdo;
    public function __construct(PDO $pdo){
        $this->pdo = $pdo;
    }

    public function buscarTodos(){
        $stmt = $this->pdo->query("SELECT * FROM usuarios");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function buscarUsuario($id){
        $stmt = $this->pdo->query("SELECT * FROM usuarios WHERE id = $id");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function cadastrar($nome, $email, $senha) {
        $sql = "INSERT INTO usuarios (nome, email, senha) VALUES (:nome, :email, :senha)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':nome' => $nome,
            ':email' => $email,
            ':senha' => $senha
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function autenticar(string $email, string $senha): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, nome FROM usuarios
             WHERE email = :email AND CAST(senha AS CHAR) = :senha
             LIMIT 1'
        );
        $stmt->execute([
            ':email' => $email,
            ':senha' => $senha,
        ]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        return $usuario === false ? null : $usuario;
    }
    public function editar($nome, $email, $senha, $id) {
        $sql = "UPDATE usuarios SET nome=?, email=?, senha=? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$nome, $email, $senha, $id]);
    }

    public function deletar($id) {
        $sql = "DELETE FROM usuarios WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }
    
}