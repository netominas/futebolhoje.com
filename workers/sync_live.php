<?php

declare(strict_types=1);

// Cron só roda a cada 1 min no mínimo, mas queremos placar "ao vivo" mais próximo de tempo real.
// Solução: o script fica rodando ~55s, atualizando a cada 15s (4 ciclos), e o cron dispara ele
// de novo no minuto seguinte. Ex. crontab: * * * * * php /caminho/futebolhoje/workers/sync_live.php
//
// Busca eventos/estatísticas só dos jogos ao vivo (poucos por vez), não de todos os jogos do dia.

require __DIR__ . '/bootstrap.php';

$pdo = Database::getConnection();
$duracaoTotalSegundos = 55;
$intervaloSegundos = 15;
$fimEm = time() + $duracaoTotalSegundos;

do {
    $inicioCiclo = microtime(true);

    try {
        $jogosAoVivo = ApiFootball::fixturesLive();

        foreach ($jogosAoVivo as $item) {
            $jogoId = SyncHelpers::upsertJogo($pdo, $item);

            $eventos = ApiFootball::fixtureEvents($jogoId);
            SyncHelpers::sincronizarEventos($pdo, $jogoId, $eventos);

            $estatisticas = ApiFootball::fixtureStatistics($jogoId);
            SyncHelpers::sincronizarEstatisticas($pdo, $jogoId, $estatisticas);
        }

        syncLog($pdo, 'sync_live', 'ok', count($jogosAoVivo) . ' jogos ao vivo', (int) ((microtime(true) - $inicioCiclo) * 1000));
        echo 'OK: ' . count($jogosAoVivo) . " jogos ao vivo\n";
    } catch (Throwable $e) {
        syncLog($pdo, 'sync_live', 'erro', $e->getMessage(), (int) ((microtime(true) - $inicioCiclo) * 1000));
        fwrite(STDERR, 'Erro: ' . $e->getMessage() . "\n");
    }

    $restante = $fimEm - time();
    if ($restante > $intervaloSegundos) {
        sleep($intervaloSegundos);
    }
} while (time() < $fimEm);
