<?php

declare(strict_types=1);

// Gera o conteúdo textual padrão (sem IA) da página de um jogo, só com os dados já
// sincronizados no banco — usado em todos os jogos que não estão em uma liga com
// "Conteúdo IA" ativado no painel admin.
final class ConteudoJogo
{
    public static function gerarPadrao(array $jogo, array $eventos): string
    {
        $html = '<p>' . self::paragrafoIntro($jogo) . '</p>';

        $narrativa = self::paragrafoComoFoi($eventos);
        if ($narrativa !== '') {
            $html .= '<h3>Como foi o jogo</h3><p>' . $narrativa . '</p>';
        }

        $html .= self::fichaDoJogo($jogo);

        return $html;
    }

    private static function paragrafoIntro(array $jogo): string
    {
        $mandante = e($jogo['mandante_nome']);
        $visitante = e($jogo['visitante_nome']);
        $competicao = self::textoCompeticao($jogo);
        $local = $jogo['estadio_nome'] ? ', no ' . e($jogo['estadio_nome']) : '';

        if (jogoEstaAoVivo($jogo['status_curto'])) {
            return "{$mandante} e {$visitante} se enfrentam agora{$local}, {$competicao}. O placar é atualizado automaticamente nesta página.";
        }

        if (in_array($jogo['status_curto'], ['FT', 'AET', 'PEN'], true)) {
            $golsMandante = (int) $jogo['gols_mandante'];
            $golsVisitante = (int) $jogo['gols_visitante'];
            $data = e(formatarDataHora($jogo['data_utc']));

            if ($golsMandante > $golsVisitante) {
                $resultado = "{$mandante} venceu {$visitante} por {$golsMandante} a {$golsVisitante}";
            } elseif ($golsVisitante > $golsMandante) {
                $resultado = "{$visitante} venceu {$mandante} por {$golsVisitante} a {$golsMandante}, atuando fora de casa";
            } else {
                $resultado = "{$mandante} e {$visitante} empataram por {$golsMandante} a {$golsMandante}";
            }

            return "{$resultado}, em partida realizada em {$data}{$local}, {$competicao}.";
        }

        if (in_array($jogo['status_curto'], ['PST', 'CANC', 'ABD'], true)) {
            return "A partida entre {$mandante} e {$visitante}{$local}, {$competicao}, foi " .
                e(strtolower(statusJogoLabel($jogo['status_curto'], null))) . '.';
        }

        $data = e(formatarDataHora($jogo['data_utc']));
        return "{$mandante} e {$visitante} se enfrentam em {$data}{$local}, {$competicao}.";
    }

    private static function textoCompeticao(array $jogo): string
    {
        if ($jogo['rodada']) {
            return 'válido pela ' . e($jogo['rodada']) . ' de ' . e($jogo['liga_nome']);
        }

        return 'válido pela competição ' . e($jogo['liga_nome']);
    }

    private static function paragrafoComoFoi(array $eventos): string
    {
        $frases = [];

        foreach ($eventos as $evento) {
            $minuto = $evento['minuto'] !== null ? (int) $evento['minuto'] : null;
            if ($minuto === null) {
                continue;
            }

            $extra = $evento['minuto_extra'] ? '+' . (int) $evento['minuto_extra'] : '';
            $time = e($evento['time_nome'] ?? '');
            $jogador = e($evento['jogador'] ?? '');

            if ($evento['tipo'] === 'Goal') {
                $detalhe = (string) ($evento['detalhe'] ?? '');

                // A API-Football usa o tipo "Goal" também para cobranças de pênalti que o
                // jogador perdeu (detalhe "Missed Penalty") — não houve gol nenhum, então
                // narra como perda de pênalti em vez de gol marcado.
                if (str_contains($detalhe, 'Missed Penalty')) {
                    $frases[] = $jogador !== ''
                        ? "Aos {$minuto}{$extra}' minutos, {$jogador} perdeu um pênalti para o {$time}."
                        : "Aos {$minuto}{$extra}' minutos, o {$time} perdeu um pênalti.";
                    continue;
                }

                $tipoGol = match (true) {
                    str_contains($detalhe, 'Penalty') => ' (pênalti)',
                    str_contains($detalhe, 'Own Goal') => ' (gol contra)',
                    default => '',
                };
                $assist = $evento['jogador_assistencia']
                    ? ', com assistência de ' . e($evento['jogador_assistencia'])
                    : '';
                // A API-Football às vezes não informa o nome do jogador; nesse caso descreve
                // o lance sem sujeito em vez de deixar a frase com espaço duplo.
                $frases[] = $jogador !== ''
                    ? "Aos {$minuto}{$extra}' minutos, {$jogador} marcou para o {$time}{$tipoGol}{$assist}."
                    : "Aos {$minuto}{$extra}' minutos, o {$time} marcou{$tipoGol}{$assist}.";
            } elseif ($evento['tipo'] === 'Card' && str_contains((string) ($evento['detalhe'] ?? ''), 'Red')) {
                $frases[] = $jogador !== ''
                    ? "Aos {$minuto}{$extra}' minutos, {$jogador} ({$time}) foi expulso de campo."
                    : "Aos {$minuto}{$extra}' minutos, um jogador do {$time} foi expulso de campo.";
            }
        }

        return implode(' ', $frases);
    }

    // Resumo em texto simples dos dados reais do jogo, usado como grounding no prompt
    // da IA — assim a IA só analisa/narra, nunca inventa placar, jogador ou minuto.
    public static function resumoParaPrompt(array $jogo, array $eventos, array $estatisticas): string
    {
        $linhas = [];
        $linhas[] = "Confronto: {$jogo['mandante_nome']} {$jogo['gols_mandante']} x {$jogo['gols_visitante']} {$jogo['visitante_nome']}";
        $linhas[] = "Competição: {$jogo['liga_nome']} ({$jogo['liga_pais']})" . ($jogo['rodada'] ? ", {$jogo['rodada']}" : '');
        $linhas[] = 'Data: ' . formatarDataHora($jogo['data_utc']);
        if ($jogo['estadio_nome']) {
            $linhas[] = "Estádio: {$jogo['estadio_nome']}" . ($jogo['estadio_cidade'] ? " — {$jogo['estadio_cidade']}" : '');
        }
        if ($jogo['arbitro']) {
            $linhas[] = "Árbitro: {$jogo['arbitro']}";
        }

        if ($eventos !== []) {
            $linhas[] = '';
            $linhas[] = 'Eventos (minuto - tipo - jogador - time - detalhe):';
            foreach ($eventos as $evento) {
                $minuto = $evento['minuto'] !== null ? $evento['minuto'] . "'" : '?';
                $assist = $evento['jogador_assistencia'] ? " (assistência: {$evento['jogador_assistencia']})" : '';
                $linhas[] = "{$minuto} - {$evento['tipo']} - {$evento['jogador']} - {$evento['time_nome']} - {$evento['detalhe']}{$assist}";
            }
        }

        if ($estatisticas['mandante'] !== [] || $estatisticas['visitante'] !== []) {
            $linhas[] = '';
            $linhas[] = 'Estatísticas (' . $jogo['mandante_nome'] . ' x ' . $jogo['visitante_nome'] . '):';
            foreach ($estatisticas['mandante'] as $tipo => $valorMandante) {
                $valorVisitante = $estatisticas['visitante'][$tipo] ?? '-';
                $linhas[] = "{$tipo}: {$valorMandante} x {$valorVisitante}";
            }
        }

        return implode("\n", $linhas);
    }

    // Pública: reaproveitada também pelo worker de conteúdo IA (dados factuais nunca
    // saem do código — só a análise/narrativa é escrita pela IA).
    public static function fichaDoJogo(array $jogo): string
    {
        $itens = [];
        $itens[] = ['Competição', e($jogo['liga_nome']) . ($jogo['liga_pais'] ? ' (' . e($jogo['liga_pais']) . ')' : '')];

        if ($jogo['rodada']) {
            $itens[] = ['Rodada', e($jogo['rodada'])];
        }

        $itens[] = ['Data e hora', e(formatarDataHora($jogo['data_utc']))];

        if ($jogo['estadio_nome']) {
            $local = $jogo['estadio_nome'] . ($jogo['estadio_cidade'] ? ' — ' . $jogo['estadio_cidade'] : '');
            $itens[] = ['Estádio', e($local)];
        }

        if ($jogo['arbitro']) {
            $itens[] = ['Árbitro', e($jogo['arbitro'])];
        }

        $html = '<h3>Ficha do jogo</h3><ul class="ficha-jogo">';
        foreach ($itens as [$rotulo, $valor]) {
            $html .= "<li><strong>{$rotulo}:</strong> {$valor}</li>";
        }
        $html .= '</ul>';

        return $html;
    }
}
