<?php
require_once __DIR__ . '/../Model/UsuarioModel.php';
class UsuarioController {
    private $usuarioModel;
   
    public function __construct($pdo) {
        $this->usuarioModel = new UsuarioModel($pdo);

    }
    public function listar() {
        $usuarios = $this->usuarioModel->buscarTodos();
        require_once __DIR__ . '/../View/Usuario/listar.php';
        return;
    }

    public function buscarUsuario($id){
        $usuario = $this->usuarioModel->buscarUsuario($id);
        return $usuario;
    }

    public function cadastrar($nome, $email, $senha){
        $nome = trim($nome);
        $email = trim($email);
        $senha = trim($senha);
        if ($nome === '' || mb_strlen($nome) > 255) {
            throw new InvalidArgumentException('Informe um nome com até 255 caracteres.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 255) {
            throw new InvalidArgumentException('Informe um e-mail válido.');
        }
        $senhaValidada = $this->normalizarSenha($senha);
        if ($senhaValidada === null) {
            throw new InvalidArgumentException('A senha deve conter apenas números.');
        }

        return $this->usuarioModel->cadastrar($nome, $email, $senhaValidada);
    }

    public function autenticar(string $email, string $senha): ?array
    {
        $senhaValidada = $this->normalizarSenha($senha);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $senhaValidada === null) {
            return null;
        }

        return $this->usuarioModel->autenticar(trim($email), $senhaValidada);
    }

    private function normalizarSenha(string $senha): ?string
    {
        if (!preg_match('/^\d+$/', $senha)) {
            return null;
        }

        $senha = ltrim($senha, '0');
        $senha = $senha === '' ? '0' : $senha;

        if (strlen($senha) > 10 || (strlen($senha) === 10 && strcmp($senha, '2147483647') > 0)) {
            return null;
        }

        return $senha;
    }
    
    public function editar($nome,$email, $senha, $id){
        $this->usuarioModel->editar($nome, $email, $senha, $id);

    }

    public function deletar($id){
        $usuario = $this->usuarioModel->deletar($id);
        return $usuario;
    }

}





