<?php

declare(strict_types=1);

final class SitemapController
{
    public function index(): void
    {
        header('Content-Type: application/xml; charset=UTF-8');

        $pdo = Database::getConnection();
        $ligas = $pdo->query('SELECT slug, updated_at FROM ligas')->fetchAll();
        $times = $pdo->query('SELECT slug, updated_at FROM times')->fetchAll();
        $jogos = $pdo->query(
            "SELECT id, mandante_id, visitante_id, updated_at,
                    (SELECT nome FROM times WHERE id = jogos.mandante_id) AS mandante_nome,
                    (SELECT nome FROM times WHERE id = jogos.visitante_id) AS visitante_nome
             FROM jogos WHERE data_utc >= (NOW() - INTERVAL 7 DAY)"
        )->fetchAll();

        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        echo $this->url(url('/'), null);
        echo $this->url(url('/ligas'), null);
        echo $this->url(url('/ao-vivo'), null);

        foreach ($ligas as $liga) {
            echo $this->url(url('/liga/' . $liga['slug']), $liga['updated_at']);
        }

        foreach ($times as $time) {
            echo $this->url(url('/time/' . $time['slug']), $time['updated_at']);
        }

        foreach ($jogos as $jogo) {
            $slug = jogoSlug((string) $jogo['mandante_nome'], (string) $jogo['visitante_nome']);
            echo $this->url(url('/jogo/' . $jogo['id'] . '/' . $slug), $jogo['updated_at']);
        }

        echo '</urlset>';
    }

    private function url(string $loc, ?string $lastmod): string
    {
        $xml = "<url><loc>" . e($loc) . "</loc>";
        if ($lastmod !== null) {
            $xml .= '<lastmod>' . date('c', strtotime($lastmod)) . '</lastmod>';
        }
        $xml .= "</url>\n";
        return $xml;
    }
}
