<?php
/** @var array $ligas */
/** @var int $total */
/** @var string $busca */
/** @var int $pagina */
$voltarAtual = $_SERVER['REQUEST_URI'];
$totalPaginas = max(1, (int) ceil($total / 40));
?>
<h1>Ligas</h1>
<p class="admin-legenda">Ligas marcadas como destaque aparecem na sidebar, footer e no topo da home, na ordem definida abaixo.</p>

<form method="get" class="admin-busca">
    <input type="search" name="busca" value="<?= e($busca) ?>" placeholder="Buscar por nome ou país...">
    <button type="submit" class="btn btn-primario">Buscar</button>
</form>

<div class="admin-tabela-wrap">
<table class="admin-tabela">
    <thead>
        <tr><th></th><th>Liga</th><th>País</th><th>Destaque</th><th>Ordem</th><th></th></tr>
    </thead>
    <tbody>
        <?php foreach ($ligas as $liga): ?>
        <tr>
            <td><?php if ($liga['logo']): ?><img src="<?= e($liga['logo']) ?>" alt="" class="admin-logo" loading="lazy"><?php endif; ?></td>
            <td><?= e($liga['nome']) ?></td>
            <td><?= e($liga['pais']) ?></td>
            <td><?= $liga['destaque'] ? 'Sim' : '—' ?></td>
            <td>
                <?php if ($liga['destaque']): ?>
                <form method="post" action="<?= e(url('/admin/ligas/' . $liga['id'] . '/ordem')) ?>" class="admin-form-inline">
                    <input type="hidden" name="csrf" value="<?= e(AdminAuth::csrfToken()) ?>">
                    <input type="hidden" name="voltar" value="<?= e($voltarAtual) ?>">
                    <input type="number" name="ordem" value="<?= (int) $liga['ordem_destaque'] ?>" min="1" class="admin-input-numero">
                    <button type="submit" class="btn-link">salvar</button>
                </form>
                <?php endif; ?>
            </td>
            <td>
                <?php if ($liga['destaque']): ?>
                <form method="post" action="<?= e(url('/admin/ligas/' . $liga['id'] . '/remover')) ?>">
                    <input type="hidden" name="csrf" value="<?= e(AdminAuth::csrfToken()) ?>">
                    <input type="hidden" name="voltar" value="<?= e($voltarAtual) ?>">
                    <button type="submit" class="btn-link btn-link--remover">remover destaque</button>
                </form>
                <?php else: ?>
                <form method="post" action="<?= e(url('/admin/ligas/' . $liga['id'] . '/destacar')) ?>">
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
        <a class="<?= $p === $pagina ? 'ativa' : '' ?>" href="<?= e(url('/admin/ligas?busca=' . urlencode($busca) . '&pagina=' . $p)) ?>"><?= $p ?></a>
    <?php endfor; ?>
</div>
<?php endif; ?>
