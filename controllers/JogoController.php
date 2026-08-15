<?php

declare(strict_types=1);

final class JogoController
{
    public function detalhe(array $params): void
    {
        $id = (int) $params['id'];
        $jogo = Jogo::porId($id);

        if ($jogo === null) {
            http_response_code(404);
            (new PaginaController())->naoEncontrada();
            return;
        }

        if (jogoEstaAoVivo($jogo['status_curto']) && $this->eventosDesatualizados($jogo['eventos_atualizados_em'])) {
            $this->atualizarEventosEEstatisticas($id);
        }

        $eventos = Jogo::eventos($id);
        $estatisticas = Jogo::estatisticas($id, (int) $jogo['mandante_id'], (int) $jogo['visitante_id']);

        $titulo = $jogo['mandante_nome'] . ' x ' . $jogo['visitante_nome'];
        $slugCanonico = jogoSlug($jogo['mandante_nome'], $jogo['visitante_nome']);

        Seo::set(
            $titulo . ' - ' . $jogo['liga_nome'] . ' - Placar ao vivo',
            'Acompanhe o placar, estatísticas e eventos de ' . $titulo . ' pela ' . $jogo['liga_nome'] . '.',
            '/jogo/' . $id . '/' . $slugCanonico
        );
        Seo::breadcrumbs([
            ['nome' => 'Início', 'url' => '/'],
            ['nome' => $jogo['liga_nome'], 'url' => '/liga/' . $jogo['liga_slug']],
            ['nome' => $titulo, 'url' => '/jogo/' . $id . '/' . $slugCanonico],
        ]);
        Seo::addJsonLd([
            '@context' => 'https://schema.org',
            '@type' => 'SportsEvent',
            'name' => $titulo,
            'startDate' => date('c', strtotime($jogo['data_utc'])),
            'eventStatus' => in_array($jogo['status_curto'], ['FT', 'AET', 'PEN'], true)
                ? 'https://schema.org/EventCompleted'
                : (in_array($jogo['status_curto'], ['PST', 'CANC'], true) ? 'https://schema.org/EventPostponed' : 'https://schema.org/EventScheduled'),
            'competitor' => [
                ['@type' => 'SportsTeam', 'name' => $jogo['mandante_nome']],
                ['@type' => 'SportsTeam', 'name' => $jogo['visitante_nome']],
            ],
        ]);

        View::render('jogo/detalhe', [
            'jogo' => $jogo,
            'eventos' => $eventos,
            'estatisticas' => $estatisticas,
        ]);
    }

    private function eventosDesatualizados(?string $atualizadoEm): bool
    {
        if ($atualizadoEm === null) {
            return true;
        }

        return strtotime($atualizadoEm) < time() - 20;
    }

    // Busca eventos/estatísticas na hora, só para quem realmente abre a página do jogo — evita
    // ter que sincronizar isso pra todos os jogos ao vivo do mundo em segundo plano (ver sync_live.php).
    private function atualizarEventosEEstatisticas(int $jogoId): void
    {
        try {
            $pdo = Database::getConnection();
            SyncHelpers::sincronizarEventos($pdo, $jogoId, ApiFootball::fixtureEvents($jogoId));
            SyncHelpers::sincronizarEstatisticas($pdo, $jogoId, ApiFootball::fixtureStatistics($jogoId));
            SyncHelpers::marcarEventosAtualizados($pdo, $jogoId);
        } catch (Throwable $e) {
            // Se a API falhar aqui, a página ainda renderiza com os últimos dados salvos no banco.
        }
    }
}
