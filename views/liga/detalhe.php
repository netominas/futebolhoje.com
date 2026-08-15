<?php
/** @var array $liga */
/** @var int $temporada */
/** @var array $rodadas */
/** @var ?string $rodadaSelecionada */
/** @var array $jogos */
/** @var array $classificacao */
?>
<div class="container">
    <section class="secao">
        <h1 class="secao__titulo">
            <?php if ($liga['logo']): ?><img src="<?= e($liga['logo']) ?>" alt="" style="width:28px;height:28px;object-fit:contain;"><?php endif; ?>
            <?= e($liga['nome']) ?> <span class="pais" style="font-weight:500;color:var(--cor-texto-suave);">— <?= e($liga['pais']) ?></span>
        </h1>
    </section>

    <?php if ($rodadas !== []): ?>
    <div class="abas-rodada">
        <?php foreach ($rodadas as $rodada): ?>
            <a class="<?= $rodada === $rodadaSelecionada ? 'ativa' : '' ?>" href="<?= e(url('/liga/' . $liga['slug'] . '?rodada=' . urlencode($rodada))) ?>"><?= e($rodada) ?></a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <section class="secao">
        <h2 class="secao__titulo">Jogos</h2>
        <?php if ($jogos === []): ?>
            <div class="vazio">Nenhum jogo cadastrado para esta rodada ainda.</div>
        <?php else: ?>
            <div class="liga-grupo">
                <?php foreach ($jogos as $jogo): ?>
                    <?php View::partial('linha-jogo', ['jogo' => $jogo]); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <?php if ($classificacao !== []): ?>
    <section class="secao">
        <h2 class="secao__titulo">Classificação</h2>
        <?php foreach ($classificacao as $grupoNome => $linhas): ?>
            <?php if ($grupoNome !== ''): ?><h3><?= e($grupoNome) ?></h3><?php endif; ?>
            <div style="overflow-x:auto;">
            <table class="tabela-classificacao">
                <thead>
                    <tr>
                        <th>#</th>
                        <th style="text-align:left;">Time</th>
                        <th>P</th>
                        <th>J</th>
                        <th>V</th>
                        <th>E</th>
                        <th>D</th>
                        <th>GP</th>
                        <th>GC</th>
                        <th>SG</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($linhas as $linha): ?>
                    <tr>
                        <td><?= (int) $linha['posicao'] ?></td>
                        <td class="time-nome">
                            <a href="<?= e(url('/time/' . $linha['time_slug'])) ?>">
                                <?php if ($linha['time_logo']): ?><img src="<?= e($linha['time_logo']) ?>" alt="" loading="lazy"><?php endif; ?>
                                <?= e($linha['time_nome']) ?>
                            </a>
                        </td>
                        <td class="pontos"><?= (int) $linha['pontos'] ?></td>
                        <td><?= (int) $linha['jogos'] ?></td>
                        <td><?= (int) $linha['vitorias'] ?></td>
                        <td><?= (int) $linha['empates'] ?></td>
                        <td><?= (int) $linha['derrotas'] ?></td>
                        <td><?= (int) $linha['gols_pro'] ?></td>
                        <td><?= (int) $linha['gols_contra'] ?></td>
                        <td><?= (int) $linha['saldo_gols'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        <?php endforeach; ?>
    </section>
    <?php endif; ?>
</div>
