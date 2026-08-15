<?php
/** @var string $termo */
/** @var array $ligas */
/** @var array $times */
?>
<div class="container">
    <section class="secao">
        <h1 class="secao__titulo">Resultados para "<?= e($termo) ?>"</h1>

        <?php if ($termo === ''): ?>
            <div class="vazio">Digite o nome de um time ou liga na busca acima.</div>
        <?php elseif ($ligas === [] && $times === []): ?>
            <div class="vazio">Nada encontrado para "<?= e($termo) ?>".</div>
        <?php else: ?>
            <?php if ($ligas !== []): ?>
                <h2>Ligas</h2>
                <div class="grid-cards">
                    <?php foreach ($ligas as $liga): ?>
                        <a class="card-link" href="<?= e(url('/liga/' . $liga['slug'])) ?>">
                            <?php if ($liga['logo']): ?><img src="<?= e($liga['logo']) ?>" alt=""><?php endif; ?>
                            <span><?= e($liga['nome']) ?><span class="pais"><?= e($liga['pais']) ?></span></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($times !== []): ?>
                <h2>Times</h2>
                <div class="grid-cards">
                    <?php foreach ($times as $time): ?>
                        <a class="card-link" href="<?= e(url('/time/' . $time['slug'])) ?>">
                            <?php if ($time['logo']): ?><img src="<?= e($time['logo']) ?>" alt=""><?php endif; ?>
                            <span><?= e($time['nome']) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </section>
</div>
