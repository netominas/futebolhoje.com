<?php
/** @var array $time */
/** @var array $proximosJogos */
/** @var array $ultimosResultados */
?>
<div class="container">
    <section class="secao">
        <h1 class="secao__titulo">
            <?php if ($time['logo']): ?><img src="<?= e($time['logo']) ?>" alt="" style="width:32px;height:32px;object-fit:contain;"><?php endif; ?>
            <?= e($time['nome']) ?>
        </h1>
    </section>

    <section class="secao">
        <h2 class="secao__titulo">Próximos jogos</h2>
        <?php if ($proximosJogos === []): ?>
            <div class="vazio">Nenhum jogo agendado.</div>
        <?php else: ?>
            <div class="liga-grupo">
                <?php foreach ($proximosJogos as $jogo): ?>
                    <?php View::partial('linha-jogo', ['jogo' => $jogo]); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section class="secao">
        <h2 class="secao__titulo">Últimos resultados</h2>
        <?php if ($ultimosResultados === []): ?>
            <div class="vazio">Nenhum resultado recente.</div>
        <?php else: ?>
            <div class="liga-grupo">
                <?php foreach ($ultimosResultados as $jogo): ?>
                    <?php View::partial('linha-jogo', ['jogo' => $jogo]); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>
