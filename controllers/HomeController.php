<?php

declare(strict_types=1);

final class HomeController
{
    public function index(): void
    {
        $jogosHoje = Jogo::hoje();
        $jogosAoVivo = Jogo::aoVivo();

        Seo::set(
            SITE_NAME . ' - Placar ao vivo, resultados e classificação de futebol',
            SITE_DESCRIPTION,
            '/'
        );
        Seo::addJsonLd([
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => SITE_NAME,
            'url' => url('/'),
            'description' => SITE_DESCRIPTION,
        ]);

        View::render('home/index', [
            'jogosHoje' => $this->agruparPorLiga($jogosHoje),
            'jogosAoVivo' => $jogosAoVivo,
        ]);
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
