<?php
/** @var array $times */
/** @var int $total */
/** @var string $busca */
/** @var int $pagina */
$voltarAtual = $_SERVER['REQUEST_URI'];
$totalPaginas = max(1, (int) ceil($total / 40));
?>
<h1>Times</h1>
<p class="admin-legenda">Times marcados como destaque aparecem na sidebar, na ordem definida abaixo.</p>

<form method="get" class="admin-busca">
    <input type="search" name="busca" value="<?= e($busca) ?>" placeholder="Buscar por nome...">
    <button type="submit" class="btn btn-primario">Buscar</button>
</form>

<div class="admin-tabela-wrap">
<table class="admin-tabela">
    <thead>
        <tr><th></th><th>Time</th><th>Destaque</th><th>Ordem</th><th></th></tr>
    </thead>
    <tbody>
        <?php foreach ($times as $time): ?>
        <tr>
            <td><?php if ($time['logo']): ?><img src="<?= e($time['logo']) ?>" alt="" class="admin-logo" loading="lazy"><?php endif; ?></td>
            <td><?= e($time['nome']) ?></td>
            <td><?= $time['destaque'] ? 'Sim' : '—' ?></td>
            <td>
                <?php if ($time['destaque']): ?>
                <form method="post" action="<?= e(url('/admin/times/' . $time['id'] . '/ordem')) ?>" class="admin-form-inline">
                    <input type="hidden" name="csrf" value="<?= e(AdminAuth::csrfToken()) ?>">
                    <input type="hidden" name="voltar" value="<?= e($voltarAtual) ?>">
                    <input type="number" name="ordem" value="<?= (int) $time['ordem_destaque'] ?>" min="1" class="admin-input-numero">
                    <button type="submit" class="btn-link">salvar</button>
                </form>
                <?php endif; ?>
            </td>
            <td>
                <?php if ($time['destaque']): ?>
                <form method="post" action="<?= e(url('/admin/times/' . $time['id'] . '/remover')) ?>">
                    <input type="hidden" name="csrf" value="<?= e(AdminAuth::csrfToken()) ?>">
                    <input type="hidden" name="voltar" value="<?= e($voltarAtual) ?>">
                    <button type="submit" class="btn-link btn-link--remover">remover destaque</button>
                </form>
                <?php else: ?>
                <form method="post" action="<?= e(url('/admin/times/' . $time['id'] . '/destacar')) ?>">
                    <input type="hidden" name="csrf" value="<?= e(AdminAuth::csrfToken()) ?>">
                    <input type="hidden" name="voltar" value="<?= e($voltarAtual) ?>">
                    <button type="submit" class="btn-link">destacar</button>
                </form>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>

<?php if ($totalPaginas > 1): ?>
<div class="admin-paginacao">
    <?php for ($p = 1; $p <= $totalPaginas; $p++): ?>
        <a class="<?= $p === $pagina ? 'ativa' : '' ?>" href="<?= e(url('/admin/times?busca=' . urlencode($busca) . '&pagina=' . $p)) ?>"><?= $p ?></a>
    <?php endfor; ?>
</div>
<?php endif; ?>
