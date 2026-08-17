<?php

declare(strict_types=1);

final class AdminAuthController
{
    public function login(): void
    {
        if (AdminAuth::logado()) {
            redirecionar('/admin');
            return;
        }

        View::render('admin/login', ['erro' => null], 'admin-login');
    }

    public function autenticar(): void
    {
        $email = trim((string) ($_POST['email'] ?? ''));
        $senha = (string) ($_POST['senha'] ?? '');
        $usuario = $email !== '' ? AdminUsuario::porEmail($email) : null;

        if ($usuario === null || !password_verify($senha, $usuario['senha_hash'])) {
            View::render('admin/login', ['erro' => 'E-mail ou senha inválidos.'], 'admin-login');
            return;
        }

        AdminAuth::login($usuario);
        redirecionar('/admin');
    }

    public function sair(): void
    {
        AdminAuth::exigirCsrf();
        AdminAuth::logout();
        redirecionar('/admin/login');
    }
}
