<?php

declare(strict_types=1);

final class JogoConteudo
{
    public static function porJogoId(int $jogoId): ?array
    {
        $stmt = Database::getConnection()->prepare('SELECT * FROM jogo_conteudo WHERE jogo_id = :jogo_id LIMIT 1');
        $stmt->execute(['jogo_id' => $jogoId]);
        $linha = $stmt->fetch();
        return $linha ?: null;
    }

    public static function salvar(int $jogoId, string $tipo, string $conteudoHtml): void
    {
        $stmt = Database::getConnection()->prepare(
            'INSERT INTO jogo_conteudo (jogo_id, tipo, conteudo_html)
             VALUES (:jogo_id, :tipo, :conteudo_html)
             ON DUPLICATE KEY UPDATE tipo = VALUES(tipo), conteudo_html = VALUES(conteudo_html), gerado_em = NOW()'
        );
        $stmt->execute([
            'jogo_id' => $jogoId,
            'tipo' => $tipo,
            'conteudo_html' => $conteudoHtml,
        ]);
    }

    // Jogos finalizados em ligas com conteudo_ia=1 que ainda nao tem conteudo do tipo 'ia' salvo
    public static function pendentesIa(int $limite = 20): array
    {
        $stmt = Database::getConnection()->prepare(
            "SELECT j.id FROM jogos j
             INNER JOIN ligas l ON l.id = j.liga_id
             LEFT JOIN jogo_conteudo jc ON jc.jogo_id = j.id AND jc.tipo = 'ia'
             WHERE l.conteudo_ia = 1
               AND j.status_curto IN ('FT', 'AET', 'PEN')
               AND jc.jogo_id IS NULL
             ORDER BY j.data_utc DESC
             LIMIT :limite"
        );
        $stmt->bindValue('limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return array_map('intval', array_column($stmt->fetchAll(), 'id'));
    }
}
