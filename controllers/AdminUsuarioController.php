<?php

declare(strict_types=1);

final class AdminUsuarioController
{
    public function index(): void
    {
        AdminAuth::exigirLogin();

        View::render('admin/usuarios', [
            'usuarios' => AdminUsuario::todos(),
            'erro' => null,
        ], 'admin');
    }

    public function criar(): void
    {
        AdminAuth::exigirLogin();
        AdminAuth::exigirCsrf();

        $nome = trim((string) ($_POST['nome'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $senha = (string) ($_POST['senha'] ?? '');

        $erro = null;
        if ($nome === '' || $email === '' || $senha === '') {
            $erro = 'Preencha nome, e-mail e senha.';
        } elseif (strlen($senha) < 8) {
            $erro = 'A senha precisa ter pelo menos 8 caracteres.';
        } elseif (AdminUsuario::porEmail($email) !== null) {
            $erro = 'Já existe um usuário com esse e-mail.';
        }

        if ($erro !== null) {
            View::render('admin/usuarios', ['usuarios' => AdminUsuario::todos(), 'erro' => $erro], 'admin');
            return;
        }

        AdminUsuario::criar($nome, $email, $senha);
        redirecionar('/admin/usuarios');
    }

    public function excluir(array $params): void
    {
        AdminAuth::exigirLogin();
        AdminAuth::exigirCsrf();

        $id = (int) $params['id'];

        if (AdminUsuario::contar() <= 1) {
            View::render('admin/usuarios', [
                'usuarios' => AdminUsuario::todos(),
                'erro' => 'Não é possível excluir o último usuário administrador.',
            ], 'admin');
            return;
        }

        AdminUsuario::excluir($id);
        redirecionar('/admin/usuarios');
    }
}
