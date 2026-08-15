<?php

declare(strict_types=1);

final class Classificacao
{
    // Retorna agrupado por grupo: ['' => [linhas...], 'Grupo A' => [...], ...]
    public static function porLiga(int $ligaId, int $temporada): array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT c.*, t.nome AS time_nome, t.logo AS time_logo, t.slug AS time_slug
             FROM classificacao c
             INNER JOIN times t ON t.id = c.time_id
             WHERE c.liga_id = :liga_id AND c.temporada = :temporada
             ORDER BY c.grupo, c.posicao ASC'
        );
        $stmt->execute(['liga_id' => $ligaId, 'temporada' => $temporada]);

        $agrupado = [];
        foreach ($stmt->fetchAll() as $linha) {
            $agrupado[$linha['grupo']][] = $linha;
        }

        return $agrupado;
    }
}
