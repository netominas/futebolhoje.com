<?php

declare(strict_types=1);

require __DIR__ . '/../config/config.php';
require __DIR__ . '/../core/helpers.php';

spl_autoload_register(function (string $class): void {
    foreach (['core', 'models'] as $dir) {
        $file = __DIR__ . "/../{$dir}/{$class}.php";
        if (is_file($file)) {
            require $file;
            return;
        }
    }
});

function syncLog(PDO $pdo, string $worker, string $status, ?string $mensagem = null, ?int $duracaoMs = null): void
{
    $stmt = $pdo->prepare(
        'INSERT INTO sync_log (worker, status, mensagem, duracao_ms) VALUES (:worker, :status, :mensagem, :duracao_ms)'
    );
    $stmt->execute([
        'worker' => $worker,
        'status' => $status,
        'mensagem' => $mensagem !== null ? substr($mensagem, 0, 500) : null,
        'duracao_ms' => $duracaoMs,
    ]);
}
