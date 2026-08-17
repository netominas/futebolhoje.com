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

// Painel admin
$router->get('/admin/login', fn () => (new AdminAuthController())->login());
$router->post('/admin/login', fn () => (new AdminAuthController())->autenticar());
$router->post('/admin/sair', fn () => (new AdminAuthController())->sair());

$router->get('/admin', fn () => (new AdminController())->dashboard());

$router->get('/admin/ligas', fn () => (new AdminLigaController())->index());
$router->post('/admin/ligas/{id}/destacar', fn (array $p) => (new AdminLigaController())->destacar($p));
$router->post('/admin/ligas/{id}/remover', fn (array $p) => (new AdminLigaController())->remover($p));
$router->post('/admin/ligas/{id}/ordem', fn (array $p) => (new AdminLigaController())->reordenar($p));

$router->get('/admin/times', fn () => (new AdminTimeController())->index());
$router->post('/admin/times/{id}/destacar', fn (array $p) => (new AdminTimeController())->destacar($p));
$router->post('/admin/times/{id}/remover', fn (array $p) => (new AdminTimeController())->remover($p));
$router->post('/admin/times/{id}/ordem', fn (array $p) => (new AdminTimeController())->reordenar($p));

$router->get('/admin/usuarios', fn () => (new AdminUsuarioController())->index());
$router->post('/admin/usuarios', fn () => (new AdminUsuarioController())->criar());
$router->post('/admin/usuarios/{id}/excluir', fn (array $p) => (new AdminUsuarioController())->excluir($p));
