<?php $ligasFooter = Liga::destaques(); ?>
<footer class="site-footer">
    <div class="container site-footer__grid">
        <div class="site-footer__coluna">
            <h3>FutebolHoje</h3>
            <p>Placar ao vivo, resultados, classificação e estatísticas de todas as ligas de futebol, em tempo real.</p>
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
