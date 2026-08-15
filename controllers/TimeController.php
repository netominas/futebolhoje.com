<?php

declare(strict_types=1);

final class TimeController
{
    public function detalhe(array $params): void
    {
        $time = Time::porSlug($params['slug']);
        if ($time === null) {
            http_response_code(404);
            (new PaginaController())->naoEncontrada();
            return;
        }

        $proximosJogos = Time::proximosJogos((int) $time['id']);
        $ultimosResultados = Time::ultimosResultados((int) $time['id']);

        Seo::set(
            $time['nome'] . ' - Próximos jogos e últimos resultados',
            'Próximos jogos, últimos resultados e classificação de ' . $time['nome'] . '.',
            '/time/' . $time['slug']
        );
        Seo::breadcrumbs([
            ['nome' => 'Início', 'url' => '/'],
            ['nome' => $time['nome'], 'url' => '/time/' . $time['slug']],
        ]);

        View::render('time/detalhe', [
            'time' => $time,
            'proximosJogos' => $proximosJogos,
            'ultimosResultados' => $ultimosResultados,
        ]);
    }
}
