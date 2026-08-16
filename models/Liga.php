<?php

declare(strict_types=1);

final class Liga
{
    public static function porSlug(string $slug): ?array
    {
        $stmt = Database::getConnection()->prepare('SELECT * FROM ligas WHERE slug = :slug LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        $liga = $stmt->fetch();
        return $liga ?: null;
    }

    public static function porId(int $id): ?array
    {
        $stmt = Database::getConnection()->prepare('SELECT * FROM ligas WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $liga = $stmt->fetch();
        return $liga ?: null;
    }

    // Ligas com jogo hoje ou nos próximos 7 dias, para listagens (home, /ligas)
    public static function comJogosProximos(int $limite = 200): array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT DISTINCT l.* FROM ligas l
             INNER JOIN jogos j ON j.liga_id = l.id
             WHERE j.data_utc >= (NOW() - INTERVAL 1 DAY) AND j.data_utc <= (NOW() + INTERVAL 7 DAY)
             ORDER BY l.pais, l.nome
             LIMIT :limite'
        );
        $stmt->bindValue('limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // IDs reais da API-Football dos campeonatos mais buscados pelo público brasileiro,
    // usados na sidebar. FIELD() mantém a ordem de destaque definida aqui.
    private const IDS_DESTAQUE = [71, 73, 13, 2, 39, 140, 135, 78, 61];

    public static function destaques(): array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT * FROM ligas WHERE id IN (' . implode(',', self::IDS_DESTAQUE) . ')
             ORDER BY FIELD(id, ' . implode(',', self::IDS_DESTAQUE) . ')'
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function buscar(string $termo, int $limite = 30): array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT * FROM ligas WHERE nome LIKE :termo OR pais LIKE :termo ORDER BY nome LIMIT :limite'
        );
        $stmt->bindValue('termo', '%' . $termo . '%');
        $stmt->bindValue('limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
