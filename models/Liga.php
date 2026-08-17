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

    // Ligas marcadas como destaque pelo painel admin, na ordem definida lá (sidebar, footer, home)
    public static function destaques(): array
    {
        $stmt = Database::getConnection()->query(
            'SELECT * FROM ligas WHERE destaque = 1 ORDER BY ordem_destaque ASC'
        );
        return $stmt->fetchAll();
    }

    public static function buscar(string $termo, int $limite = 30): array
    {
        // :termo_nome e :termo_pais em vez de reaproveitar o mesmo nome duas vezes — com
        // PDO::ATTR_EMULATE_PREPARES=false o driver não aceita placeholder nomeado repetido.
        $stmt = Database::getConnection()->prepare(
            'SELECT * FROM ligas WHERE nome LIKE :termo_nome OR pais LIKE :termo_pais ORDER BY nome LIMIT :limite'
        );
        $stmt->bindValue('termo_nome', '%' . $termo . '%');
        $stmt->bindValue('termo_pais', '%' . $termo . '%');
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
            $where = 'WHERE nome LIKE :termo_nome OR pais LIKE :termo_pais';
            $params['termo_nome'] = '%' . $busca . '%';
            $params['termo_pais'] = '%' . $busca . '%';
        }

        $stmtTotal = $pdo->prepare("SELECT COUNT(*) AS total FROM ligas {$where}");
        $stmtTotal->execute($params);
        $total = (int) $stmtTotal->fetch()['total'];

        $stmt = $pdo->prepare(
            "SELECT * FROM ligas {$where}
             ORDER BY destaque DESC, ordem_destaque ASC, pais ASC, nome ASC
             LIMIT :offset, :porPagina"
        );
        foreach ($params as $chave => $valor) {
            $stmt->bindValue($chave, $valor);
        }
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue('porPagina', $porPagina, PDO::PARAM_INT);
        $stmt->execute();

        return ['ligas' => $stmt->fetchAll(), 'total' => $total];
    }

    public static function definirDestaque(int $id, bool $destaque, ?int $ordem): void
    {
        $stmt = Database::getConnection()->prepare(
            'UPDATE ligas SET destaque = :destaque, ordem_destaque = :ordem WHERE id = :id'
        );
        $stmt->execute([
            'destaque' => $destaque ? 1 : 0,
            'ordem' => $destaque ? $ordem : null,
            'id' => $id,
        ]);
    }

    public static function proximaOrdemDestaque(): int
    {
        $stmt = Database::getConnection()->query('SELECT COALESCE(MAX(ordem_destaque), 0) + 1 AS proxima FROM ligas WHERE destaque = 1');
        return (int) $stmt->fetch()['proxima'];
    }

    public static function definirConteudoIa(int $id, bool $ativo): void
    {
        $stmt = Database::getConnection()->prepare('UPDATE ligas SET conteudo_ia = :ativo WHERE id = :id');
        $stmt->execute(['ativo' => $ativo ? 1 : 0, 'id' => $id]);
    }

    public static function comConteudoIa(): array
    {
        $stmt = Database::getConnection()->query('SELECT id FROM ligas WHERE conteudo_ia = 1');
        return array_map('intval', array_column($stmt->fetchAll(), 'id'));
    }
}
