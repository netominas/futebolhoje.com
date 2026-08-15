<footer class="site-footer">
    <div class="container">
        <div class="site-footer__links">
            <a href="<?= e(url('/ligas')) ?>">Ligas</a>
            <a href="<?= e(url('/ao-vivo')) ?>">Ao vivo</a>
            <a href="<?= e(url('/sobre')) ?>">Sobre</a>
            <a href="<?= e(url('/contato')) ?>">Contato</a>
            <a href="<?= e(url('/privacidade')) ?>">Privacidade</a>
            <a href="<?= e(url('/termos')) ?>">Termos de uso</a>
        </div>
        <p>&copy; <?= date('Y') ?> <?= e(SITE_NAME) ?>. Dados fornecidos por API-Football. Todos os direitos reservados.</p>
    </div>
</footer>
