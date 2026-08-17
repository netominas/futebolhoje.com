<?php

declare(strict_types=1);

final class AdminController
{
    public function dashboard(): void
    {
        AdminAuth::exigirLogin();

        $pdo = Database::getConnection();
        $totais = $pdo->query(
            'SELECT (SELECT COUNT(*) FROM ligas) AS ligas,
                    (SELECT COUNT(*) FROM times) AS times,
                    (SELECT COUNT(*) FROM jogos) AS jogos,
                    (SELECT COUNT(*) FROM jogos WHERE DATE(data_utc) = CURDATE()) AS jogos_hoje,
                    (SELECT COUNT(*) FROM jogos WHERE status_curto IN (\'1H\',\'HT\',\'2H\',\'ET\',\'BT\',\'P\')) AS ao_vivo,
                    (SELECT COUNT(*) FROM ligas WHERE destaque = 1) AS ligas_destaque,
                    (SELECT COUNT(*) FROM times WHERE destaque = 1) AS times_destaque'
        )->fetch();

        $ultimasSincronizacoes = $pdo->query(
            'SELECT worker, status, mensagem, created_at FROM sync_log ORDER BY id DESC LIMIT 10'
        )->fetchAll();

        View::render('admin/dashboard', [
            'totais' => $totais,
            'ultimasSincronizacoes' => $ultimasSincronizacoes,
        ], 'admin');
    }
}
