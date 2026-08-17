<?php
/** @var array $jogosHoje */
/** @var array $jogosAoVivo */
/** @var int $totalJogos */
/** @var int $totalLigas */
/** @var string $dataExtensa */
/** @var array $perguntas */
?>
<h1 class="pagina-titulo">Futebol Hoje: Jogos de Hoje ao Vivo e Resultados</h1>
<p class="pagina-intro">
    Acompanhe todos os jogos de futebol de hoje, <?= e($dataExtensa) ?>, com placar ao vivo minuto a minuto,
    horários, resultados e classificação<?= $totalLigas > 0 ? ' de ' . $totalLigas . ' ' . ($totalLigas === 1 ? 'competição' : 'competições') . ' em andamento' : '' ?>.
    Dados atualizados automaticamente, sem precisar recarregar a página.
</p>

<?php if ($jogosAoVivo !== []): ?>
    <section class="secao">
        <h2 class="secao__titulo"><span class="badge-ao-vivo"><span class="dot-vivo"></span> Ao vivo agora</span></h2>
        <div class="liga-grupo">
            <?php foreach ($jogosAoVivo as $jogo): ?>
                <?php View::partial('linha-jogo', ['jogo' => $jogo]); ?>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<section class="secao">
    <h2 class="secao__titulo">Jogos de hoje</h2>

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

<section class="secao secao-sobre-home">
    <h2 class="secao__titulo">Por que acompanhar o futebol hoje no FutebolHoje</h2>
    <p>
        O FutebolHoje reúne, em uma única página, os jogos de futebol de hoje de mais de 500 ligas —
        do Brasileirão às principais competições europeias e sul-americanas. Assim que uma partida
        começa, o placar, os gols e os cartões são atualizados automaticamente.
    </p>
    <ul class="lista-marcadores">
        <li>Jogos de hoje agrupados por liga, na ordem das competições mais acompanhadas</li>
        <li>Placar ao vivo com atualização automática durante a partida</li>
        <li><a href="<?= e(url('/ligas')) ?>">Classificação e estatísticas</a> de cada campeonato</li>
        <li>Página de cada partida com ficha do jogo, linha do tempo e estatísticas detalhadas</li>
    </ul>
</section>

<section class="secao secao-faq">
    <h2 class="secao__titulo">Perguntas frequentes sobre os jogos de hoje</h2>
    <?php foreach ($perguntas as $item): ?>
        <div class="faq-item">
            <h3><?= e($item['pergunta']) ?></h3>
            <p><?= e($item['resposta']) ?></p>
        </div>
    <?php endforeach; ?>
</section>

<?php if ($jogosAoVivo !== []): ?>
<script>setTimeout(function () { location.reload(); }, 25000);</script>
<?php endif; ?>
