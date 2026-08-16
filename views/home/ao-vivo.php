<?php
/** @var array $jogosAoVivo */
?>
<section class="secao">
        <h1 class="secao__titulo"><span class="badge-ao-vivo"><span class="dot-vivo"></span> Jogos ao vivo</span></h1>

        <?php if ($jogosAoVivo === []): ?>
            <div class="vazio">Nenhum jogo ao vivo neste momento. Volte durante o horário das partidas.</div>
        <?php else: ?>
            <?php foreach ($jogosAoVivo as $grupo): ?>
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

<script>setTimeout(function () { location.reload(); }, 20000);</script>
