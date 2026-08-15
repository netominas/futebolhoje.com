<?php

declare(strict_types=1);

final class BuscaController
{
    public function index(): void
    {
        $termo = trim((string) ($_GET['q'] ?? ''));
        $ligas = $termo !== '' ? Liga::buscar($termo, 15) : [];
        $times = $termo !== '' ? Time::buscar($termo, 15) : [];

        Seo::set('Buscar times e ligas', 'Busque por times e ligas de futebol.', '/busca');

        View::render('paginas/busca', [
            'termo' => $termo,
            'ligas' => $ligas,
            'times' => $times,
        ]);
    }
}
