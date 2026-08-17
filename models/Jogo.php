<?php

declare(strict_types=1);

final class Jogo
{
    private const SELECT_BASE = "SELECT j.*, l.nome AS liga_nome, l.slug AS liga_slug, l.logo AS liga_logo, l.pais AS liga_pais,
                    tm.nome AS mandante_nome, tm.logo AS mandante_logo, tm.slug AS mandante_slug,
                    tv.nome AS visitante_nome, tv.logo AS visitante_logo, tv.slug AS visitante_slug
             FROM jogos j
             INNER JOIN ligas l ON l.id = j.liga_id
             INNER JOIN times tm ON tm.id = j.mandante_id
             INNER JOIN times tv ON tv.id = j.visitante_id";

    // Ligas em destaque (definidas no painel admin) aparecem primeiro, na ordem configurada lá;
    // as demais seguem por país/nome como antes.
    private const ORDER_DESTAQUE = 'l.destaque DESC, l.ordem_destaque ASC, l.pais ASC, l.nome ASC, j.data_utc ASC';

    public static function hoje(): array
    {
        $stmt = Database::getConnection()->prepare(
            self::SELECT_BASE . ' WHERE DATE(j.data_utc) = CURDATE()
             ORDER BY ' . self::ORDER_DESTAQUE
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function aoVivo(): array
    {
        $stmt = Database::getConnection()->prepare(
            self::SELECT_BASE . " WHERE j.status_curto IN ('1H', 'HT', '2H', 'ET', 'BT', 'P')
             ORDER BY " . self::ORDER_DESTAQUE
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function porId(int $id): ?array
    {
        $stmt = Database::getConnection()->prepare(self::SELECT_BASE . ' WHERE j.id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $jogo = $stmt->fetch();
        return $jogo ?: null;
    }

    public static function porLiga(int $ligaId, int $temporada, ?string $rodada = null): array
    {
        $sql = self::SELECT_BASE . ' WHERE j.liga_id = :liga_id AND j.temporada = :temporada';
        $params = ['liga_id' => $ligaId, 'temporada' => $temporada];

        if ($rodada !== null) {
            $sql .= ' AND j.rodada = :rodada';
            $params['rodada'] = $rodada;
        }

        $sql .= ' ORDER BY j.data_utc ASC';

        $stmt = Database::getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function rodadasDaLiga(int $ligaId, int $temporada): array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT rodada FROM jogos WHERE liga_id = :liga_id AND temporada = :temporada
             AND rodada IS NOT NULL
             GROUP BY rodada
             ORDER BY MIN(data_utc)'
        );
        $stmt->execute(['liga_id' => $ligaId, 'temporada' => $temporada]);
        return array_column($stmt->fetchAll(), 'rodada');
    }

    public static function eventos(int $jogoId): array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT ev.*, t.nome AS time_nome, t.logo AS time_logo
             FROM jogo_eventos ev
             LEFT JOIN times t ON t.id = ev.time_id
             WHERE ev.jogo_id = :jogo_id
             ORDER BY ev.ordem ASC'
        );
        $stmt->execute(['jogo_id' => $jogoId]);
        return $stmt->fetchAll();
    }

    // Retorna ['mandante' => [tipo => valor, ...], 'visitante' => [...]]
    public static function estatisticas(int $jogoId, int $mandanteId, int $visitanteId): array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT time_id, tipo, valor FROM jogo_estatisticas WHERE jogo_id = :jogo_id'
        );
        $stmt->execute(['jogo_id' => $jogoId]);

        $resultado = ['mandante' => [], 'visitante' => []];
        foreach ($stmt->fetchAll() as $linha) {
            $chave = (int) $linha['time_id'] === $mandanteId ? 'mandante' : 'visitante';
            $resultado[$chave][$linha['tipo']] = $linha['valor'];
        }

        return $resultado;
    }
}
