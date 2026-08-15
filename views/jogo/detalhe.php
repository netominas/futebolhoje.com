<?php
/** @var array $jogo */
/** @var array $eventos */
/** @var array $estatisticas */
$aoVivo = jogoEstaAoVivo($jogo['status_curto']);
$encerrado = in_array($jogo['status_curto'], ['FT', 'AET', 'PEN'], true);

function iconeEvento(string $tipo, ?string $detalhe): string
{
    if ($tipo === 'Card') {
        return $detalhe !== null && str_contains($detalhe, 'Red') ? '🟥' : '🟨';
    }

    return match ($tipo) {
        'Goal' => '⚽',
        'subst' => '🔁',
        'Var' => '📺',
        default => '•',
    };
}
?>
<div class="container">

    <div class="placar-hero">
        <div class="placar-hero__liga">
            <a href="<?= e(url('/liga/' . $jogo['liga_slug'])) ?>" style="color:#a9b2c3;"><?= e($jogo['liga_nome']) ?></a>
            <?= $jogo['rodada'] ? '— ' . e($jogo['rodada']) : '' ?>
        </div>
        <div class="placar-hero__times">
            <div class="placar-hero__time">
                <?php if ($jogo['mandante_logo']): ?><img src="<?= e($jogo['mandante_logo']) ?>" alt=""><?php endif; ?>
                <span><a href="<?= e(url('/time/' . $jogo['mandante_slug'])) ?>" style="color:#fff;"><?= e($jogo['mandante_nome']) ?></a></span>
            </div>
            <div class="placar-hero__marcador">
                <?= $jogo['gols_mandante'] !== null ? (int) $jogo['gols_mandante'] : '-' ?>
                &ndash;
                <?= $jogo['gols_visitante'] !== null ? (int) $jogo['gols_visitante'] : '-' ?>
            </div>
            <div class="placar-hero__time">
                <?php if ($jogo['visitante_logo']): ?><img src="<?= e($jogo['visitante_logo']) ?>" alt=""><?php endif; ?>
                <span><a href="<?= e(url('/time/' . $jogo['visitante_slug'])) ?>" style="color:#fff;"><?= e($jogo['visitante_nome']) ?></a></span>
            </div>
        </div>
        <div class="placar-hero__status">
            <?php if ($jogo['status_curto'] === 'NS'): ?>
                <?= e(formatarDataHora($jogo['data_utc'])) ?>
            <?php else: ?>
                <?= e(statusJogoLabel($jogo['status_curto'], $jogo['minuto'] !== null ? (int) $jogo['minuto'] : null)) ?>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($eventos !== []): ?>
    <section class="secao">
        <h2 class="secao__titulo">Linha do tempo</h2>
        <div class="cartao-vivo">
            <?php foreach ($eventos as $evento): ?>
                <div class="timeline-evento">
                    <div class="timeline-evento__minuto"><?= (int) $evento['minuto'] ?>'<?= $evento['minuto_extra'] ? '+' . (int) $evento['minuto_extra'] : '' ?></div>
                    <div class="timeline-evento__icone"><?= iconeEvento($evento['tipo'], $evento['detalhe']) ?></div>
                    <div>
                        <strong><?= e($evento['jogador'] ?? '') ?></strong>
                        <?php if ($evento['jogador_assistencia']): ?> (assist. <?= e($evento['jogador_assistencia']) ?>)<?php endif; ?>
                        — <?= e($evento['time_nome'] ?? '') ?>
                        <?php if ($evento['detalhe']): ?><span style="color:var(--cor-texto-suave);"> · <?= e($evento['detalhe']) ?></span><?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <?php if ($estatisticas['mandante'] !== [] || $estatisticas['visitante'] !== []): ?>
    <section class="secao">
        <h2 class="secao__titulo">Estatísticas</h2>
        <div class="cartao-vivo">
            <?php foreach ($estatisticas['mandante'] as $tipo => $valorMandante):
                $valorVisitante = $estatisticas['visitante'][$tipo] ?? '0';
                $numMandante = (float) preg_replace('/[^0-9.]/', '', (string) $valorMandante) ?: 0;
                $numVisitante = (float) preg_replace('/[^0-9.]/', '', (string) $valorVisitante) ?: 0;
                $total = $numMandante + $numVisitante;
                $percMandante = $total > 0 ? ($numMandante / $total) * 100 : 50;
            ?>
                <div class="stat-linha">
                    <div class="stat-linha__valores">
                        <span><?= e((string) $valorMandante) ?></span>
                        <span style="color:var(--cor-texto-suave);font-weight:600;"><?= e($tipo) ?></span>
                        <span><?= e((string) $valorVisitante) ?></span>
                    </div>
                    <div class="stat-linha__barra">
                        <span style="width:<?= $percMandante ?>%;"></span>
                        <span style="width:<?= 100 - $percMandante ?>%;"></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

</div>

<?php if ($aoVivo): ?>
<script>setTimeout(function () { location.reload(); }, 20000);</script>
<?php endif; ?>
