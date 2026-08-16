<?php
/** @var array $ligas */
/** @var string $busca */
?>
<section class="secao">
        <h1 class="secao__titulo">Ligas e campeonatos</h1>

        <?php if ($ligas === []): ?>
            <div class="vazio">
                <?= $busca !== '' ? 'Nenhuma liga encontrada para "' . e($busca) . '".' : 'Nenhuma liga com jogos recentes.' ?>
            </div>
        <?php else: ?>
            <div class="grid-cards">
                <?php foreach ($ligas as $liga): ?>
                    <a class="card-link" href="<?= e(url('/liga/' . $liga['slug'])) ?>">
                        <?php if ($liga['logo']): ?><img src="<?= e($liga['logo']) ?>" alt="" loading="lazy"><?php endif; ?>
                        <span>
                            <?= e($liga['nome']) ?>
                            <span class="pais"><?= e($liga['pais']) ?></span>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
