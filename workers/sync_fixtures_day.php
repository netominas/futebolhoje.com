<?php

declare(strict_types=1);

// Roda a cada 10-15 min via cron. Um único request por dia (?date=YYYY-MM-DD) traz TODOS os
// jogos de TODAS as ligas naquele dia, então cobrir "todas as ligas" não multiplica o custo.
// Sincroniza ontem/hoje/amanhã/depois de amanhã para pegar jogos que viram a virada da meia-noite
// e a agenda dos próximos dias.
// Ex. crontab: */15 * * * * php /caminho/futebolhoje/workers/sync_fixtures_day.php

require __DIR__ . '/bootstrap.php';

$inicio = microtime(true);
$pdo = Database::getConnection();

try {
    $totalJogos = 0;

    for ($offset = -1; $offset <= 2; $offset++) {
        $data = date('Y-m-d', strtotime("{$offset} day"));
        $fixtures = ApiFootball::fixturesByDate($data);

        foreach ($fixtures as $item) {
            SyncHelpers::upsertJogo($pdo, $item);
            $totalJogos++;
        }
    }

    syncLog($pdo, 'sync_fixtures_day', 'ok', "{$totalJogos} jogos sincronizados", (int) ((microtime(true) - $inicio) * 1000));
    echo "OK: {$totalJogos} jogos sincronizados\n";
} catch (Throwable $e) {
    syncLog($pdo, 'sync_fixtures_day', 'erro', $e->getMessage(), (int) ((microtime(true) - $inicio) * 1000));
    fwrite(STDERR, 'Erro: ' . $e->getMessage() . "\n");
    exit(1);
}
