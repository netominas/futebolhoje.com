<?php

declare(strict_types=1);

final class SyncHelpers
{
    public static function upsertEstadio(PDO $pdo, ?array $venue): ?int
    {
        if ($venue === null || empty($venue['id'])) {
            return null;
        }

        $stmt = $pdo->prepare(
            'INSERT INTO estadios (id, nome, cidade, capacidade)
             VALUES (:id, :nome, :cidade, :capacidade)
             ON DUPLICATE KEY UPDATE nome = VALUES(nome), cidade = VALUES(cidade), capacidade = VALUES(capacidade)'
        );
        $stmt->execute([
            'id' => $venue['id'],
            'nome' => $venue['name'] ?? null,
            'cidade' => $venue['city'] ?? null,
            'capacidade' => $venue['capacity'] ?? null,
        ]);

        return (int) $venue['id'];
    }

    public static function upsertTime(PDO $pdo, array $team): int
    {
        $slugBase = slugify((string) $team['name']);
        $stmt = $pdo->prepare(
            'INSERT INTO times (id, nome, logo, slug)
             VALUES (:id, :nome, :logo, :slug)
             ON DUPLICATE KEY UPDATE nome = VALUES(nome), logo = VALUES(logo)'
        );
        $stmt->execute([
            'id' => $team['id'],
            'nome' => $team['name'],
            'logo' => $team['logo'] ?? null,
            'slug' => $slugBase . '-' . $team['id'],
        ]);

        return (int) $team['id'];
    }

    // Garante que a liga exista mesmo se o worker de fixtures rodar antes do sync_leagues
    // (evita violar a FK jogos.liga_id -> ligas.id).
    public static function upsertLigaMinima(PDO $pdo, array $league): void
    {
        $stmt = $pdo->prepare(
            'INSERT IGNORE INTO ligas (id, nome, tipo, pais, slug, temporada_atual)
             VALUES (:id, :nome, :tipo, :pais, :slug, :temporada)'
        );
        $stmt->execute([
            'id' => $league['id'],
            'nome' => $league['name'] ?? ('Liga ' . $league['id']),
            'tipo' => 'League',
            'pais' => $league['country'] ?? '',
            'slug' => slugify(($league['name'] ?? 'liga') . '-' . ($league['country'] ?? '')) . '-' . $league['id'],
            'temporada' => $league['season'] ?? null,
        ]);
    }

    public static function upsertJogo(PDO $pdo, array $item): int
    {
        $fixture = $item['fixture'];
        $league = $item['league'];
        $teams = $item['teams'];
        $goals = $item['goals'];
        $score = $item['score'];

        self::upsertLigaMinima($pdo, $league);
        $estadioId = self::upsertEstadio($pdo, $fixture['venue'] ?? null);
        $mandanteId = self::upsertTime($pdo, $teams['home']);
        $visitanteId = self::upsertTime($pdo, $teams['away']);

        $stmt = $pdo->prepare(
            'INSERT INTO jogos (
                id, liga_id, temporada, rodada, data_utc, status_curto, status_longo, minuto,
                mandante_id, visitante_id, gols_mandante, gols_visitante,
                gols_mandante_intervalo, gols_visitante_intervalo, estadio_id, arbitro
            ) VALUES (
                :id, :liga_id, :temporada, :rodada, :data_utc, :status_curto, :status_longo, :minuto,
                :mandante_id, :visitante_id, :gols_mandante, :gols_visitante,
                :gols_mandante_ht, :gols_visitante_ht, :estadio_id, :arbitro
            )
            ON DUPLICATE KEY UPDATE
                status_curto = VALUES(status_curto),
                status_longo = VALUES(status_longo),
                minuto = VALUES(minuto),
                gols_mandante = VALUES(gols_mandante),
                gols_visitante = VALUES(gols_visitante),
                gols_mandante_intervalo = VALUES(gols_mandante_intervalo),
                gols_visitante_intervalo = VALUES(gols_visitante_intervalo),
                rodada = VALUES(rodada)'
        );

        $stmt->execute([
            'id' => $fixture['id'],
            'liga_id' => $league['id'],
            'temporada' => $league['season'],
            'rodada' => $league['round'] ?? null,
            'data_utc' => date('Y-m-d H:i:s', strtotime($fixture['date'])),
            'status_curto' => $fixture['status']['short'] ?? 'NS',
            'status_longo' => $fixture['status']['long'] ?? null,
            'minuto' => $fixture['status']['elapsed'] ?? null,
            'mandante_id' => $mandanteId,
            'visitante_id' => $visitanteId,
            'gols_mandante' => $goals['home'],
            'gols_visitante' => $goals['away'],
            'gols_mandante_ht' => $score['halftime']['home'] ?? null,
            'gols_visitante_ht' => $score['halftime']['away'] ?? null,
            'estadio_id' => $estadioId,
            'arbitro' => $fixture['referee'] ?? null,
        ]);

        return (int) $fixture['id'];
    }

    public static function sincronizarEventos(PDO $pdo, int $jogoId, array $eventos): void
    {
        $pdo->prepare('DELETE FROM jogo_eventos WHERE jogo_id = :jogo_id')->execute(['jogo_id' => $jogoId]);

        $stmt = $pdo->prepare(
            'INSERT INTO jogo_eventos (
                jogo_id, minuto, minuto_extra, time_id, jogador, jogador_assistencia, tipo, detalhe, comentario, ordem
            ) VALUES (
                :jogo_id, :minuto, :minuto_extra, :time_id, :jogador, :assistencia, :tipo, :detalhe, :comentario, :ordem
            )'
        );

        foreach ($eventos as $ordem => $evento) {
            $stmt->execute([
                'jogo_id' => $jogoId,
                'minuto' => $evento['time']['elapsed'] ?? null,
                'minuto_extra' => $evento['time']['extra'] ?? null,
                'time_id' => $evento['team']['id'] ?? null,
                'jogador' => $evento['player']['name'] ?? null,
                'assistencia' => $evento['assist']['name'] ?? null,
                'tipo' => $evento['type'] ?? '',
                'detalhe' => $evento['detail'] ?? null,
                'comentario' => $evento['comments'] ?? null,
                'ordem' => $ordem,
            ]);
        }
    }

    public static function sincronizarEstatisticas(PDO $pdo, int $jogoId, array $estatisticasPorTime): void
    {
        $stmt = $pdo->prepare(
            'INSERT INTO jogo_estatisticas (jogo_id, time_id, tipo, valor)
             VALUES (:jogo_id, :time_id, :tipo, :valor)
             ON DUPLICATE KEY UPDATE valor = VALUES(valor)'
        );

        foreach ($estatisticasPorTime as $bloco) {
            $timeId = $bloco['team']['id'] ?? null;
            if ($timeId === null) {
                continue;
            }

            foreach ($bloco['statistics'] as $stat) {
                $stmt->execute([
                    'jogo_id' => $jogoId,
                    'time_id' => $timeId,
                    'tipo' => $stat['type'],
                    'valor' => $stat['value'] !== null ? (string) $stat['value'] : null,
                ]);
            }
        }
    }
}
