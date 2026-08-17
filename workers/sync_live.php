<?php

declare(strict_types=1);

// Cron só roda a cada 1 min no mínimo, mas queremos placar "ao vivo" mais próximo de tempo real.
// Solução: o script fica rodando ~55s, atualizando a cada 15s (4 ciclos), e o cron dispara ele
// de novo no minuto seguinte. Ex. crontab: * * * * * php /caminho/futebolhoje/workers/sync_live.php
//
// Só atualiza placar/status/minuto (1 request cobre TODOS os jogos ao vivo do mundo). Eventos e
// estatísticas detalhados de cada partida são buscados sob demanda pelo JogoController quando
// alguém abre aquela página — com todas as ligas cobertas, pode haver ~100 jogos ao vivo ao mesmo
// tempo, e buscar o detalhe de cada um aqui estouraria tempo de execução e cota da API.

require __DIR__ . '/bootstrap.php';

// Trava por arquivo: se uma execução anterior ainda estiver rodando (ex: a API respondeu
// devagar e passou do minuto), o cron seguinte sai na hora em vez de rodar em paralelo e
// dobrar o consumo de requests.
$lock = fopen(sys_get_temp_dir() . '/futebolhoje_sync_live.lock', 'c');
if ($lock === false || !flock($lock, LOCK_EX | LOCK_NB)) {
    exit(0);
}

$pdo = Database::getConnection();
$duracaoTotalSegundos = 55;
$intervaloSegundos = 15;
$fimEm = time() + $duracaoTotalSegundos;

do {
    $inicioCiclo = microtime(true);

    try {
        $jogosAoVivo = ApiFootball::fixturesLive();

        foreach ($jogosAoVivo as $item) {
            SyncHelpers::upsertJogo($pdo, $item);
        }

        syncLog($pdo, 'sync_live', 'ok', count($jogosAoVivo) . ' jogos ao vivo', (int) ((microtime(true) - $inicioCiclo) * 1000));
        echo 'OK: ' . count($jogosAoVivo) . " jogos ao vivo\n";
    } catch (Throwable $e) {
        syncLog($pdo, 'sync_live', 'erro', $e->getMessage(), (int) ((microtime(true) - $inicioCiclo) * 1000));
        fwrite(STDERR, 'Erro: ' . $e->getMessage() . "\n");
    }

    // Dorme o que faltar (nunca mais que o intervalo). Sem isso, quando sobra menos
    // tempo que o intervalo o loop nem dormia nem saía — ficava martelando a API em
    // sequência até o fim da janela (foi o que estourou a cota de requests).
    $restante = $fimEm - time();
    if ($restante <= 0) {
        break;
    }
    sleep(min($intervaloSegundos, $restante));
} while (time() < $fimEm);
