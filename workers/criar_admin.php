<?php

declare(strict_types=1);

// Cria (ou atualiza a senha de) um usuário administrador. Rodar manualmente uma vez por SSH:
// php workers/criar_admin.php "Nome" email@exemplo.com "senha-forte"

require __DIR__ . '/bootstrap.php';

[, $nome, $email, $senha] = $argv + [null, null, null, null];

if ($nome === null || $email === null || $senha === null) {
    fwrite(STDERR, "Uso: php criar_admin.php \"Nome\" email@exemplo.com \"senha\"\n");
    exit(1);
}

if (strlen($senha) < 8) {
    fwrite(STDERR, "A senha precisa ter pelo menos 8 caracteres.\n");
    exit(1);
}

$pdo = Database::getConnection();
$existente = AdminUsuario::porEmail($email);

if ($existente !== null) {
    $stmt = $pdo->prepare('UPDATE admin_usuarios SET nome = :nome, senha_hash = :senha_hash WHERE id = :id');
    $stmt->execute([
        'nome' => $nome,
        'senha_hash' => password_hash($senha, PASSWORD_DEFAULT),
        'id' => $existente['id'],
    ]);
    echo "Usuário {$email} atualizado.\n";
} else {
    AdminUsuario::criar($nome, $email, $senha);
    echo "Usuário {$email} criado.\n";
}
