<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?= Seo::renderHead() ?>
<link rel="icon" type="image/svg+xml" href="<?= e(asset('img/favicon.svg')) ?>">
<link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
<?php if (ADSENSE_PUBLISHER_ID !== ''): ?>
<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=<?= e(ADSENSE_PUBLISHER_ID) ?>" crossorigin="anonymous"></script>
<?php endif; ?>
</head>
<body>
<?php View::partial('header'); ?>
<?php View::partial('breadcrumbs'); ?>
<main>
<div class="container layout-grid">
    <div class="conteudo-principal">
        <?= $conteudo ?>
    </div>
    <?php View::partial('sidebar'); ?>
</div>
</main>
<?php View::partial('footer'); ?>
</body>
</html>
