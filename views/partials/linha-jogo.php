<?php
/** @var array $jogo */
$aoVivo = jogoEstaAoVivo($jogo['status_curto']);
$slug = jogoSlug($jogo['mandante_nome'], $jogo['visitante_nome']);
?>
<a class="linha-jogo" href="<?= e(url('/jogo/' . $jogo['id'] . '/' . $slug)) ?>">
    <div class="linha-jogo__status<?= $aoVivo ? ' ao-vivo' : '' ?>">
        <?php if ($jogo['status_curto'] === 'NS'): ?>
            <?= e(formatarHora($jogo['data_utc'])) ?>
        <?php else: ?>
            <?= e(statusJogoLabel($jogo['status_curto'], $jogo['minuto'] !== null ? (int) $jogo['minuto'] : null)) ?>
        <?php endif; ?>
    </div>
    <div class="linha-jogo__times">
        <div class="linha-jogo__time">
            <?php if ($jogo['mandante_logo']): ?><img src="<?= e($jogo['mandante_logo']) ?>" alt="" loading="lazy"><?php endif; ?>
            <?= e($jogo['mandante_nome']) ?>
        </div>
        <div class="linha-jogo__time">
            <?php if ($jogo['visitante_logo']): ?><img src="<?= e($jogo['visitante_logo']) ?>" alt="" loading="lazy"><?php endif; ?>
            <?= e($jogo['visitante_nome']) ?>
        </div>
    </div>
    <div class="linha-jogo__placar">
        <span><?= $jogo['gols_mandante'] !== null ? (int) $jogo['gols_mandante'] : '-' ?></span>
        <span><?= $jogo['gols_visitante'] !== null ? (int) $jogo['gols_visitante'] : '-' ?></span>
    </div>
</a>
