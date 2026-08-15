<?php

declare(strict_types=1);

final class LigaController
{
    public function index(): void
    {
        $busca = trim((string) ($_GET['busca'] ?? ''));
        $ligas = $busca !== '' ? Liga::buscar($busca) : Liga::comJogosProximos();

        Seo::set(
            'Todas as ligas e campeonatos de futebol',
            'Lista completa de ligas, copas e campeonatos de futebol cobertos pelo ' . SITE_NAME . '.',
            '/ligas'
        );
        Seo::breadcrumbs([
            ['nome' => 'Início', 'url' => '/'],
            ['nome' => 'Ligas', 'url' => '/ligas'],
        ]);

        View::render('liga/index', [
            'ligas' => $ligas,
            'busca' => $busca,
        ]);
    }

    public function detalhe(array $params): void
    {
        $liga = Liga::porSlug($params['slug']);
        if ($liga === null) {
            http_response_code(404);
            (new PaginaController())->naoEncontrada();
            return;
        }

        $temporada = (int) $liga['temporada_atual'];
        $rodadas = Jogo::rodadasDaLiga((int) $liga['id'], $temporada);
        $rodadaSelecionada = $_GET['rodada'] ?? ($rodadas[array_key_last($rodadas)] ?? null);

        $jogos = Jogo::porLiga((int) $liga['id'], $temporada, $rodadaSelecionada);
        $classificacao = Classificacao::porLiga((int) $liga['id'], $temporada);

        Seo::set(
            $liga['nome'] . ' - Tabela, jogos e classificação',
            'Classificação, próximos jogos e resultados de ' . $liga['nome'] . ' (' . $liga['pais'] . ').',
            '/liga/' . $liga['slug']
        );
        Seo::breadcrumbs([
            ['nome' => 'Início', 'url' => '/'],
            ['nome' => 'Ligas', 'url' => '/ligas'],
            ['nome' => $liga['nome'], 'url' => '/liga/' . $liga['slug']],
        ]);

        View::render('liga/detalhe', [
            'liga' => $liga,
            'temporada' => $temporada,
            'rodadas' => $rodadas,
            'rodadaSelecionada' => $rodadaSelecionada,
            'jogos' => $jogos,
            'classificacao' => $classificacao,
        ]);
    }
}
