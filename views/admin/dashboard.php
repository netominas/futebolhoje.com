<?php
/** @var array $totais */
/** @var array $ultimasSincronizacoes */
?>
<h1>Dashboard</h1>

<div class="admin-cards">
    <div class="admin-card"><span><?= (int) $totais['jogos_hoje'] ?></span>Jogos hoje</div>
    <div class="admin-card"><span><?= (int) $totais['ao_vivo'] ?></span>Ao vivo agora</div>
    <div class="admin-card"><span><?= (int) $totais['ligas'] ?></span>Ligas no banco</div>
    <div class="admin-card"><span><?= (int) $totais['times'] ?></span>Times no banco</div>
    <div class="admin-card"><span><?= (int) $totais['jogos'] ?></span>Jogos no banco</div>
    <div class="admin-card"><span><?= (int) $totais['ligas_destaque'] ?></span>Ligas em destaque</div>
    <div class="admin-card"><span><?= (int) $totais['times_destaque'] ?></span>Times em destaque</div>
</div>

<h2>Últimas sincronizações</h2>
<div class="admin-tabela-wrap">
<table class="admin-tabela">
    <thead>
        <tr><th>Worker</th><th>Status</th><th>Mensagem</th><th>Quando</th></tr>
    </thead>
    <tbody>
        <?php foreach ($ultimasSincronizacoes as $log): ?>
        <tr>
            <td><?= e($log['worker']) ?></td>
            <td><span class="admin-badge admin-badge--<?= $log['status'] === 'ok' ? 'ok' : 'erro' ?>"><?= e($log['status']) ?></span></td>
            <td><?= e($log['mensagem'] ?? '') ?></td>
            <td><?= e(formatarDataHora($log['created_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>
