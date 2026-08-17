<?php

declare(strict_types=1);

final class Time
{
    public static function porSlug(string $slug): ?array
    {
        $stmt = Database::getConnection()->prepare('SELECT * FROM times WHERE slug = :slug LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        $time = $stmt->fetch();
        return $time ?: null;
    }

    public static function porId(int $id): ?array
    {
        $stmt = Database::getConnection()->prepare('SELECT * FROM times WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $time = $stmt->fetch();
        return $time ?: null;
    }

    public static function proximosJogos(int $timeId, int $limite = 10): array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT j.*, l.nome AS liga_nome, l.slug AS liga_slug,
                    tm.nome AS mandante_nome, tm.logo AS mandante_logo, tm.slug AS mandante_slug,
                    tv.nome AS visitante_nome, tv.logo AS visitante_logo, tv.slug AS visitante_slug
             FROM jogos j
             INNER JOIN ligas l ON l.id = j.liga_id
             INNER JOIN times tm ON tm.id = j.mandante_id
             INNER JOIN times tv ON tv.id = j.visitante_id
             WHERE (j.mandante_id = :time_id OR j.visitante_id = :time_id2) AND j.data_utc >= NOW()
             ORDER BY j.data_utc ASC
             LIMIT :limite'
        );
        $stmt->bindValue('time_id', $timeId, PDO::PARAM_INT);
        $stmt->bindValue('time_id2', $timeId, PDO::PARAM_INT);
        $stmt->bindValue('limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function ultimosResultados(int $timeId, int $limite = 10): array
    {
        $stmt = Database::getConnection()->prepare(
            "SELECT j.*, l.nome AS liga_nome, l.slug AS liga_slug,
                    tm.nome AS mandante_nome, tm.logo AS mandante_logo, tm.slug AS mandante_slug,
                    tv.nome AS visitante_nome, tv.logo AS visitante_logo, tv.slug AS visitante_slug
             FROM jogos j
             INNER JOIN ligas l ON l.id = j.liga_id
             INNER JOIN times tm ON tm.id = j.mandante_id
             INNER JOIN times tv ON tv.id = j.visitante_id
             WHERE (j.mandante_id = :time_id OR j.visitante_id = :time_id2) AND j.status_curto IN ('FT', 'AET', 'PEN')
             ORDER BY j.data_utc DESC
             LIMIT :limite"
        );
        $stmt->bindValue('time_id', $timeId, PDO::PARAM_INT);
        $stmt->bindValue('time_id2', $timeId, PDO::PARAM_INT);
        $stmt->bindValue('limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Times marcados como destaque pelo painel admin, na ordem definida lá (sidebar)
    public static function destaques(): array
    {
        $stmt = Database::getConnection()->query(
            'SELECT * FROM times WHERE destaque = 1 ORDER BY ordem_destaque ASC'
        );
        return $stmt->fetchAll();
    }

    public static function buscar(string $termo, int $limite = 30): array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT * FROM times WHERE nome LIKE :termo ORDER BY nome LIMIT :limite'
        );
        $stmt->bindValue('termo', '%' . $termo . '%');
        $stmt->bindValue('limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // --- Painel admin ---

    public static function paginado(string $busca, int $pagina, int $porPagina = 40): array
    {
        $offset = max(0, ($pagina - 1) * $porPagina);
        $pdo = Database::getConnection();

        $where = '';
        $params = [];
        if ($busca !== '') {
            $where = 'WHERE nome LIKE :termo';
            $params['termo'] = '%' . $busca . '%';
        }

        $stmtTotal = $pdo->prepare("SELECT COUNT(*) AS total FROM times {$where}");
        $stmtTotal->execute($params);
        $total = (int) $stmtTotal->fetch()['total'];

        $stmt = $pdo->prepare(
            "SELECT * FROM times {$where}
             ORDER BY destaque DESC, ordem_destaque ASC, nome ASC
             LIMIT :offset, :porPagina"
        );
        foreach ($params as $chave => $valor) {
            $stmt->bindValue($chave, $valor);
        }
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue('porPagina', $porPagina, PDO::PARAM_INT);
        $stmt->execute();

        return ['times' => $stmt->fetchAll(), 'total' => $total];
    }

    public static function definirDestaque(int $id, bool $destaque, ?int $ordem): void
    {
        $stmt = Database::getConnection()->prepare(
            'UPDATE times SET destaque = :destaque, ordem_destaque = :ordem WHERE id = :id'
        );
        $stmt->execute([
            'destaque' => $destaque ? 1 : 0,
            'ordem' => $destaque ? $ordem : null,
            'id' => $id,
        ]);
    }

    public static function proximaOrdemDestaque(): int
    {
        $stmt = Database::getConnection()->query('SELECT COALESCE(MAX(ordem_destaque), 0) + 1 AS proxima FROM times WHERE destaque = 1');
        return (int) $stmt->fetch()['proxima'];
    }
}
