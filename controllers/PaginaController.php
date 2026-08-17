<?php

declare(strict_types=1);

final class PaginaController
{
    public function naoEncontrada(): void
    {
        Seo::set('Página não encontrada', 'A página que você procura não existe ou foi movida.');
        View::render('erros/404');
    }

    public function sobre(): void
    {
        Seo::set('Sobre o ' . SITE_NAME, 'Conheça o ' . SITE_NAME . ', portal com os jogos de futebol de hoje, placar ao vivo, resultados e estatísticas de centenas de ligas.', '/sobre');
        View::render('paginas/sobre');
    }

    public function contato(): void
    {
        Seo::set('Contato', 'Fale com o ' . SITE_NAME . '.', '/contato');
        View::render('paginas/contato');
    }

    public function privacidade(): void
    {
        Seo::set('Política de Privacidade', 'Política de privacidade do ' . SITE_NAME . '.', '/privacidade');
        View::render('paginas/privacidade');
    }

    public function termos(): void
    {
        Seo::set('Termos de Uso', 'Termos de uso do ' . SITE_NAME . '.', '/termos');
        View::render('paginas/termos');
    }
}
