<?php
$ligasDestaque = Liga::destaques();
$timesDestaque = Time::destaques();
?>
<aside class="sidebar">
    <?php if ($ligasDestaque !== []): ?>
    <div class="sidebar-secao">
        <h2 class="sidebar-secao__titulo">Principais ligas</h2>
        <ul class="sidebar-lista">
            <?php foreach ($ligasDestaque as $liga): ?>
            <li>
                <a href="<?= e(url('/liga/' . $liga['slug'])) ?>">
                    <?php if ($liga['logo']): ?><img src="<?= e($liga['logo']) ?>" alt="" loading="lazy"><?php endif; ?>
                    <span><?= e($liga['nome']) ?><small><?= e($liga['pais']) ?></small></span>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <?php if ($timesDestaque !== []): ?>
    <div class="sidebar-secao">
        <h2 class="sidebar-secao__titulo">Principais times</h2>
        <ul class="sidebar-lista">
            <?php foreach ($timesDestaque as $time): ?>
            <li>
                <a href="<?= e(url('/time/' . $time['slug'])) ?>">
                    <?php if ($time['logo']): ?><img src="<?= e($time['logo']) ?>" alt="" loading="lazy"><?php endif; ?>
                    <span><?= e($time['nome']) ?></span>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <div class="sidebar-secao sidebar-anuncio">
        <div class="slot-anuncio"><!-- espaço reservado para anúncio AdSense --></div>
    </div>
</aside>
