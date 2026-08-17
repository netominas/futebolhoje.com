<?php

declare(strict_types=1);

// Roda a cada poucas horas via cron. Em vez de atualizar classificação de TODAS as ligas
// (custaria 1 request por liga+temporada, caro com milhares de ligas), só re-sincroniza
// ligas que tiveram algum jogo encerrado recentemente — é ali que a tabela muda de fato.
// Ex. crontab: 15 */3 * * * php /caminho/futebolhoje/workers/sync_standings.php

require __DIR__ . '/bootstrap.php';

$inicio = microtime(true);
$pdo = Database::getConnection();

$stmt = $pdo->query(
    "SELECT DISTINCT liga_id, temporada FROM jogos
     WHERE status_curto IN ('FT', 'AET', 'PEN')
       AND updated_at >= (NOW() - INTERVAL 4 HOUR)"
);
$combinacoes = $stmt->fetchAll();

$stmtUpsert = $pdo->prepare(
    'INSERT INTO classificacao (
        liga_id, temporada, grupo, time_id, posicao, pontos, jogos, vitorias, empates, derrotas,
        gols_pro, gols_contra, saldo_gols, forma
    ) VALUES (
        :liga_id, :temporada, :grupo, :time_id, :posicao, :pontos, :jogos, :vitorias, :empates, :derrotas,
        :gols_pro, :gols_contra, :saldo, :forma
    )
    ON DUPLICATE KEY UPDATE
        posicao = VALUES(posicao), pontos = VALUES(pontos), jogos = VALUES(jogos),
        vitorias = VALUES(vitorias), empates = VALUES(empates), derrotas = VALUES(derrotas),
        gols_pro = VALUES(gols_pro), gols_contra = VALUES(gols_contra), saldo_gols = VALUES(saldo_gols),
        forma = VALUES(forma)'
);

$totalLigas = 0;
$totalErros = 0;

foreach ($combinacoes as $combo) {
    $ligaId = (int) $combo['liga_id'];
    $temporada = (int) $combo['temporada'];

    // Com todas as ligas cobertas, uma leva pode ter centenas de combinações liga+temporada
    // pra atualizar de uma vez. Sem pausa entre elas, isso estoura o limite de 300
    // requests/minuto da API (foi o que aconteceu). 220ms de pausa = ~270/min, com folga.
    usleep(220000);

    // Uma liga com formato inesperado (ex: chave eliminatória sem tabela de pontos) não pode
    // derrubar a sincronização das outras 261 ligas da leva.
    try {
        $grupos = ApiFootball::standings($ligaId, $temporada);
        if ($grupos === []) {
            continue;
        }

        // A API retorna standings[0].league.standings como lista de grupos (ex: fase de grupos),
        // cada grupo é uma lista de times.
        $tabelasPorGrupo = $grupos[0]['league']['standings'] ?? [];

        foreach ($tabelasPorGrupo as $indiceGrupo => $tabela) {
            foreach ($tabela as $linha) {
                if (empty($linha['team']['id'])) {
                    continue;
                }

                SyncHelpers::upsertTime($pdo, $linha['team']);

                $stmtUpsert->execute([
                    'liga_id' => $ligaId,
                    'temporada' => $temporada,
                    'grupo' => $linha['group'] ?? (string) $indiceGrupo,
                    'time_id' => $linha['team']['id'],
                    'posicao' => $linha['rank'],
                    'pontos' => $linha['points'],
                    'jogos' => $linha['all']['played'],
                    'vitorias' => $linha['all']['win'],
                    'empates' => $linha['all']['draw'],
                    'derrotas' => $linha['all']['lose'],
                    'gols_pro' => $linha['all']['goals']['for'],
                    'gols_contra' => $linha['all']['goals']['against'],
                    'saldo' => $linha['goalsDiff'],
                    'forma' => $linha['form'] ?? null,
                ]);
            }
        }

        $totalLigas++;
    } catch (Throwable $e) {
        $totalErros++;
        syncLog($pdo, 'sync_standings', 'erro', "liga {$ligaId}/{$temporada}: " . $e->getMessage());
    }
}

syncLog($pdo, 'sync_standings', 'ok', "{$totalLigas} ligas atualizadas, {$totalErros} erros", (int) ((microtime(true) - $inicio) * 1000));
echo "OK: {$totalLigas} ligas atualizadas, {$totalErros} erros\n";
