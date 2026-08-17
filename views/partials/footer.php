<?php $ligasFooter = Liga::destaques(); ?>
<footer class="site-footer">
    <div class="container site-footer__grid">
        <div class="site-footer__coluna">
            <h3 class="site-footer__marca">
                <img src="<?= e(asset('img/logo-mark.svg')) ?>" alt="" width="24" height="24">
                FutebolHoje
            </h3>
            <p>O FutebolHoje reúne placar ao vivo, jogos de hoje, resultados, classificação e estatísticas de futebol de ligas do Brasil e do mundo, atualizados em tempo real e sem enrolação.</p>
        </div>

        <?php if ($ligasFooter !== []): ?>
        <div class="site-footer__coluna">
            <h3>Principais ligas</h3>
            <ul>
                <?php foreach ($ligasFooter as $liga): ?>
                <li><a href="<?= e(url('/liga/' . $liga['slug'])) ?>"><?= e($liga['nome']) ?> <span><?= e($liga['pais']) ?></span></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <div class="site-footer__coluna">
            <h3>Institucional</h3>
            <ul>
                <li><a href="<?= e(url('/ligas')) ?>">Todas as ligas</a></li>
                <li><a href="<?= e(url('/ao-vivo')) ?>">Jogos ao vivo</a></li>
                <li><a href="<?= e(url('/sobre')) ?>">Sobre</a></li>
                <li><a href="<?= e(url('/contato')) ?>">Contato</a></li>
                <li><a href="<?= e(url('/privacidade')) ?>">Privacidade</a></li>
                <li><a href="<?= e(url('/termos')) ?>">Termos de uso</a></li>
            </ul>
        </div>
    </div>
    <div class="container site-footer__base">
        <p>&copy; <?= date('Y') ?> <?= e(SITE_NAME) ?>. Dados fornecidos por API-Football. Todos os direitos reservados.</p>
    </div>
</footer>
