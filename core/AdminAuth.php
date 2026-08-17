<?php

declare(strict_types=1);

final class AdminAuth
{
    public static function login(array $usuario): void
    {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $usuario['id'];
        $_SESSION['admin_nome'] = $usuario['nome'];
    }

    public static function logout(): void
    {
        $_SESSION = [];
        session_destroy();
    }

    public static function logado(): bool
    {
        return isset($_SESSION['admin_id']);
    }

    public static function usuarioId(): ?int
    {
        return $_SESSION['admin_id'] ?? null;
    }

    public static function nome(): ?string
    {
        return $_SESSION['admin_nome'] ?? null;
    }

    public static function exigirLogin(): void
    {
        if (!self::logado()) {
            redirecionar('/admin/login');
            exit;
        }
    }

    public static function csrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    // Chamar no início de todo handler POST do admin. Encerra a requisição se o token não bater.
    public static function exigirCsrf(): void
    {
        $token = $_POST['csrf'] ?? null;
        if (!is_string($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(419);
            exit('Sessão expirada, volte e tente novamente.');
        }
    }
}
