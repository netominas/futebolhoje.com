<?php

declare(strict_types=1);

$router = new Router();

$router->get('/', fn () => (new HomeController())->index());
$router->get('/ao-vivo', fn () => (new HomeController())->aoVivo());

$router->get('/ligas', fn () => (new LigaController())->index());
$router->get('/liga/{slug}', fn (array $p) => (new LigaController())->detalhe($p));

$router->get('/jogo/{id}/{slug}', fn (array $p) => (new JogoController())->detalhe($p));

$router->get('/time/{slug}', fn (array $p) => (new TimeController())->detalhe($p));

$router->get('/busca', fn () => (new BuscaController())->index());

$router->get('/sobre', fn () => (new PaginaController())->sobre());
$router->get('/contato', fn () => (new PaginaController())->contato());
$router->get('/privacidade', fn () => (new PaginaController())->privacidade());
$router->get('/termos', fn () => (new PaginaController())->termos());

$router->get('/sitemap.xml', fn () => (new SitemapController())->index());
