<?php

declare(strict_types=1);

final class AdminUsuario
{
    public static function porEmail(string $email): ?array
    {
        $stmt = Database::getConnection()->prepare('SELECT * FROM admin_usuarios WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $usuario = $stmt->fetch();
        return $usuario ?: null;
    }

    public static function porId(int $id): ?array
    {
        $stmt = Database::getConnection()->prepare('SELECT * FROM admin_usuarios WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $usuario = $stmt->fetch();
        return $usuario ?: null;
    }

    public static function todos(): array
    {
        $stmt = Database::getConnection()->query('SELECT id, nome, email, created_at FROM admin_usuarios ORDER BY nome ASC');
        return $stmt->fetchAll();
    }

    public static function contar(): int
    {
        $stmt = Database::getConnection()->query('SELECT COUNT(*) AS total FROM admin_usuarios');
        return (int) $stmt->fetch()['total'];
    }

    public static function criar(string $nome, string $email, string $senha): int
    {
        $stmt = Database::getConnection()->prepare(
            'INSERT INTO admin_usuarios (nome, email, senha_hash) VALUES (:nome, :email, :senha_hash)'
        );
        $stmt->execute([
            'nome' => $nome,
            'email' => $email,
            'senha_hash' => password_hash($senha, PASSWORD_DEFAULT),
        ]);

        return (int) Database::getConnection()->lastInsertId();
    }

    public static function excluir(int $id): void
    {
        $stmt = Database::getConnection()->prepare('DELETE FROM admin_usuarios WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
