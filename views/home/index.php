<?php
/** @var array $jogosHoje */
/** @var array $jogosAoVivo */
?>
<div class="container">

    <?php if ($jogosAoVivo !== []): ?>
    <section class="secao">
        <h1 class="secao__titulo"><span class="badge-ao-vivo"><span class="dot-vivo"></span> Ao vivo agora</span></h1>
        <div class="liga-grupo">
            <?php foreach ($jogosAoVivo as $jogo): ?>
                <?php View::partial('linha-jogo', ['jogo' => $jogo]); ?>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <section class="secao">
        <h1 class="secao__titulo">Jogos de hoje</h1>

        <?php if ($jogosHoje === []): ?>
            <div class="vazio">Nenhum jogo programado para hoje.</div>
        <?php else: ?>
            <?php foreach ($jogosHoje as $grupo): ?>
                <div class="liga-grupo">
                    <div class="liga-grupo__cabecalho">
                        <?php if ($grupo['liga']['logo']): ?><img src="<?= e($grupo['liga']['logo']) ?>" alt="" loading="lazy"><?php endif; ?>
                        <a href="<?= e(url('/liga/' . $grupo['liga']['slug'])) ?>"><?= e($grupo['liga']['nome']) ?></a>
                        <span class="pais">— <?= e($grupo['liga']['pais']) ?></span>
                    </div>
                    <?php foreach ($grupo['jogos'] as $jogo): ?>
                        <?php View::partial('linha-jogo', ['jogo' => $jogo]); ?>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

</div>

<?php if ($jogosAoVivo !== []): ?>
<script>setTimeout(function () { location.reload(); }, 25000);</script>
<?php endif; ?>
