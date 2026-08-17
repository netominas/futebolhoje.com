<?php

declare(strict_types=1);

final class HomeController
{
    public function index(): void
    {
        $jogosHoje = Jogo::hoje();
        $jogosAoVivo = Jogo::aoVivo();
        $jogosPorLiga = $this->agruparPorLiga($jogosHoje);

        $totalJogos = count($jogosHoje);
        $totalLigas = count($jogosPorLiga);
        $dataExtensa = formatarDataExtensa();

        Seo::set(
            'Futebol Hoje: Jogos de Hoje ao Vivo e Resultados',
            "Veja todos os jogos de futebol de hoje, {$dataExtensa}: placar ao vivo, horários, resultados e classificação de centenas de ligas do Brasil e do mundo, atualizados em tempo real.",
            '/'
        );
        Seo::addJsonLd([
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => SITE_NAME,
            'url' => url('/'),
            'description' => SITE_DESCRIPTION,
        ]);
        Seo::addJsonLd([
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array_map(
                static fn (array $item): array => [
                    '@type' => 'Question',
                    'name' => $item['pergunta'],
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item['resposta']],
                ],
                $this->perguntasFrequentes()
            ),
        ]);

        View::render('home/index', [
            'jogosHoje' => $jogosPorLiga,
            'jogosAoVivo' => $jogosAoVivo,
            'totalJogos' => $totalJogos,
            'totalLigas' => $totalLigas,
            'dataExtensa' => $dataExtensa,
            'perguntas' => $this->perguntasFrequentes(),
        ]);
    }

    /** @return array<int, array{pergunta: string, resposta: string}> */
    private function perguntasFrequentes(): array
    {
        return [
            [
                'pergunta' => 'Quais jogos de futebol tem hoje?',
                'resposta' => 'O FutebolHoje lista automaticamente todos os jogos do dia assim que a tabela é divulgada pelas confederações, cobrindo o Brasileirão e centenas de ligas nacionais e continentais ao redor do mundo.',
            ],
            [
                'pergunta' => 'Onde ver o placar dos jogos de hoje ao vivo?',
                'resposta' => 'Nesta página, na seção "Ao vivo agora": o placar, o tempo de jogo e os principais lances são atualizados automaticamente enquanto a partida acontece, sem precisar recarregar a página.',
            ],
            [
                'pergunta' => 'Que horas começam os jogos de futebol hoje?',
                'resposta' => 'O horário de cada partida aparece ao lado do confronto na listagem de jogos de hoje, já convertido para o horário local.',
            ],
            [
                'pergunta' => 'O FutebolHoje cobre outras ligas além do futebol brasileiro?',
                'resposta' => 'Sim. Além do Brasileirão Série A, Série B e Copa do Brasil, o site acompanha competições europeias como Champions League, Premier League, La Liga, Serie A italiana e Bundesliga, além de ligas sul-americanas e de outros continentes.',
            ],
        ];
    }

    public function aoVivo(): void
    {
        $jogosAoVivo = Jogo::aoVivo();

        Seo::set(
            'Jogos ao vivo agora',
            'Acompanhe o placar dos jogos de futebol que estão rolando agora, em tempo real.',
            '/ao-vivo'
        );
        Seo::breadcrumbs([
            ['nome' => 'Início', 'url' => '/'],
            ['nome' => 'Ao vivo', 'url' => '/ao-vivo'],
        ]);

        View::render('home/ao-vivo', [
            'jogosAoVivo' => $this->agruparPorLiga($jogosAoVivo),
        ]);
    }

    private function agruparPorLiga(array $jogos): array
    {
        $agrupado = [];
        foreach ($jogos as $jogo) {
            $agrupado[$jogo['liga_id']]['liga'] = [
                'id' => $jogo['liga_id'],
                'nome' => $jogo['liga_nome'],
                'slug' => $jogo['liga_slug'],
                'logo' => $jogo['liga_logo'],
                'pais' => $jogo['liga_pais'],
            ];
            $agrupado[$jogo['liga_id']]['jogos'][] = $jogo;
        }

        return $agrupado;
    }
}
