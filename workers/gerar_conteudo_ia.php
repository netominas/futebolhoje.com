<?php

declare(strict_types=1);

// Gera conteúdo com IA (Claude) para jogos já finalizados nas ligas marcadas como
// "Conteúdo IA" no painel admin. Roda depois que o jogo termina — nunca antes — pra
// sempre trabalhar em cima do resultado e das estatísticas reais (grounding), evitando
// que a IA especule sobre um jogo que ainda não aconteceu.
// Ex. crontab: */20 * * * * php /caminho/futebolhoje/workers/gerar_conteudo_ia.php

require __DIR__ . '/bootstrap.php';

const SISTEMA_PROMPT = <<<'TXT'
Você é um redator esportivo brasileiro. Escreva uma análise curta e objetiva sobre a
partida de futebol descrita pelo usuário, usando SOMENTE os fatos fornecidos — nunca
invente jogadores, minutos, placares ou estatísticas que não estejam nos dados.

Responda em português do Brasil, em HTML simples (apenas as tags <h3>, <p>, <ul>, <li>
e <strong> — nunca <html>, <head>, <body> ou blocos de código).

Estrutura exigida:
1. Um parágrafo de abertura com o resultado e o contexto da partida.
2. <h3>Como foi o jogo</h3> narrando os principais momentos a partir dos eventos
   fornecidos (gols, cartões, viradas).
3. <h3>Destaques da partida</h3> com uma lista <ul> de 2 a 4 destaques concretos
   (gols, cartões ou uma estatística relevante).

Tom jornalístico, direto, sem clichês, sem opinião pessoal. Não inclua ficha técnica
(árbitro, estádio, data) — isso já é exibido separadamente na página.
TXT;

$inicio = microtime(true);
$pdo = Database::getConnection();

try {
    $idsPendentes = JogoConteudo::pendentesIa(15);
    $totalGerados = 0;
    $totalErros = 0;

    foreach ($idsPendentes as $jogoId) {
        try {
            $jogo = Jogo::porId($jogoId);
            if ($jogo === null) {
                continue;
            }

            $eventos = Jogo::eventos($jogoId);
            $estatisticas = Jogo::estatisticas($jogoId, (int) $jogo['mandante_id'], (int) $jogo['visitante_id']);

            $resumo = ConteudoJogo::resumoParaPrompt($jogo, $eventos, $estatisticas);
            $textoIa = ClaudeApi::gerarTexto($resumo, 900, SISTEMA_PROMPT);

            // Remove blocos de código markdown, caso o modelo envolva a resposta em ```html
            $textoIa = trim(preg_replace('/^```(?:html)?\s*|\s*```$/i', '', $textoIa) ?? $textoIa);

            $html = $textoIa . ConteudoJogo::fichaDoJogo($jogo);
            JogoConteudo::salvar($jogoId, 'ia', $html);
            $totalGerados++;
        } catch (Throwable $e) {
            $totalErros++;
            syncLog($pdo, 'gerar_conteudo_ia', 'erro', "jogo {$jogoId}: " . $e->getMessage());
        }
    }

    syncLog($pdo, 'gerar_conteudo_ia', 'ok', "{$totalGerados} conteúdos gerados, {$totalErros} erros", (int) ((microtime(true) - $inicio) * 1000));
    echo "OK: {$totalGerados} conteúdos gerados, {$totalErros} erros\n";
} catch (Throwable $e) {
    syncLog($pdo, 'gerar_conteudo_ia', 'erro', $e->getMessage(), (int) ((microtime(true) - $inicio) * 1000));
    fwrite(STDERR, 'Erro: ' . $e->getMessage() . "\n");
    exit(1);
}
