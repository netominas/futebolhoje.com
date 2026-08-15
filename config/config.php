<?php

declare(strict_types=1);

// Preencha com os dados reais obtidos no painel ServerAvatar (Site "futebolhoje" > Database)
// antes do deploy. Em produção, prefira definir essas variáveis de ambiente em vez de
// hardcodar valores neste arquivo.
define('DB_HOST', getenv('FUTEBOLHOJE_DB_HOST') ?: '127.0.0.1');
define('DB_NAME', getenv('FUTEBOLHOJE_DB_NAME') ?: 'futebolhoje');
define('DB_USER', getenv('FUTEBOLHOJE_DB_USER') ?: 'futebolhoje_user');
define('DB_PASS', getenv('FUTEBOLHOJE_DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

define('SITE_NAME', 'FutebolHoje');
define('SITE_URL', getenv('FUTEBOLHOJE_SITE_URL') ?: 'https://futebolhoje.com');
define('SITE_DESCRIPTION', 'Placar ao vivo, resultados, classificação e estatísticas de todas as ligas de futebol, em tempo real.');

// Publisher ID do Google AdSense (ca-pub-XXXXXXXXXXXXXXXX), preencher quando aprovado
define('ADSENSE_PUBLISHER_ID', getenv('FUTEBOLHOJE_ADSENSE_ID') ?: '');

// Credenciais da API-Football (api-sports.io). Host padrão é a API direta (v3.football.api-sports.io);
// se a assinatura for via RapidAPI, defina FUTEBOLHOJE_API_FOOTBALL_HOST=api-football-v1.p.rapidapi.com
define('API_FOOTBALL_KEY', getenv('FUTEBOLHOJE_API_FOOTBALL_KEY') ?: '');
define('API_FOOTBALL_HOST', getenv('FUTEBOLHOJE_API_FOOTBALL_HOST') ?: 'v3.football.api-sports.io');

// true em desenvolvimento para exibir erros na tela; false em produção
define('APP_DEBUG', filter_var(getenv('FUTEBOLHOJE_DEBUG') ?: 'false', FILTER_VALIDATE_BOOLEAN));

error_reporting(APP_DEBUG ? E_ALL : 0);
ini_set('display_errors', APP_DEBUG ? '1' : '0');

date_default_timezone_set('America/Sao_Paulo');
