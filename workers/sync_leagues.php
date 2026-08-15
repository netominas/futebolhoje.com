<?php

declare(strict_types=1);

// Roda 1x/dia via cron. Uma única chamada à API traz todas as ligas + temporadas disponíveis.
// Ex. crontab: 0 4 * * * php /caminho/futebolhoje/workers/sync_leagues.php

require __DIR__ . '/bootstrap.php';

$inicio = microtime(true);
$pdo = Database::getConnection();

try {
    $ligas = ApiFootball::leagues();

    $stmtLiga = $pdo->prepare(
        'INSERT INTO ligas (id, nome, tipo, pais, pais_codigo, logo, bandeira_pais, slug, temporada_atual)
         VALUES (:id, :nome, :tipo, :pais, :pais_codigo, :logo, :bandeira, :slug, :temporada_atual)
         ON DUPLICATE KEY UPDATE
            nome = VALUES(nome), tipo = VALUES(tipo), pais = VALUES(pais), pais_codigo = VALUES(pais_codigo),
            logo = VALUES(logo), bandeira_pais = VALUES(bandeira_pais), temporada_atual = VALUES(temporada_atual)'
    );

    $stmtTemporada = $pdo->prepare(
        'INSERT INTO liga_temporadas (liga_id, temporada, data_inicio, data_fim, atual)
         VALUES (:liga_id, :temporada, :inicio, :fim, :atual)
         ON DUPLICATE KEY UPDATE data_inicio = VALUES(data_inicio), data_fim = VALUES(data_fim), atual = VALUES(atual)'
    );

    $total = 0;
    foreach ($ligas as $item) {
        $liga = $item['league'];
        $pais = $item['country'];

        $temporadaAtual = null;
        foreach ($item['seasons'] as $temporada) {
            if (!empty($temporada['current'])) {
                $temporadaAtual = $temporada['year'];
            }
        }

        $stmtLiga->execute([
            'id' => $liga['id'],
            'nome' => $liga['name'],
            'tipo' => $liga['type'] === 'Cup' ? 'Cup' : 'League',
            'pais' => $pais['name'] ?? '',
            'pais_codigo' => $pais['code'] ?? null,
            'logo' => $liga['logo'] ?? null,
            'bandeira' => $pais['flag'] ?? null,
            'slug' => slugify($liga['name'] . '-' . ($pais['name'] ?? '')) . '-' . $liga['id'],
            'temporada_atual' => $temporadaAtual,
        ]);

        foreach ($item['seasons'] as $temporada) {
            $stmtTemporada->execute([
                'liga_id' => $liga['id'],
                'temporada' => $temporada['year'],
                'inicio' => $temporada['start'] ?? null,
                'fim' => $temporada['end'] ?? null,
                'atual' => !empty($temporada['current']) ? 1 : 0,
            ]);
        }

        $total++;
    }

    syncLog($pdo, 'sync_leagues', 'ok', "{$total} ligas sincronizadas", (int) ((microtime(true) - $inicio) * 1000));
    echo "OK: {$total} ligas sincronizadas\n";
} catch (Throwable $e) {
    syncLog($pdo, 'sync_leagues', 'erro', $e->getMessage(), (int) ((microtime(true) - $inicio) * 1000));
    fwrite(STDERR, 'Erro: ' . $e->getMessage() . "\n");
    exit(1);
}
