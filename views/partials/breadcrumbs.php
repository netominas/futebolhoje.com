<?php $itens = Seo::breadcrumbItems(); ?>
<?php if ($itens !== []): ?>
<div class="container breadcrumbs">
    <?php foreach ($itens as $i => $item): ?>
        <span><?php if ($i === count($itens) - 1): ?><?= e($item['nome']) ?><?php else: ?><a href="<?= e(url($item['url'])) ?>"><?= e($item['nome']) ?></a><?php endif; ?></span>
    <?php endforeach; ?>
</div>
<?php endif; ?>
