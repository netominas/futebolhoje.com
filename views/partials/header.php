<header class="site-header">
    <div class="container site-header__bar">
        <a href="<?= e(url('/')) ?>" class="site-header__logo">
            <img src="<?= e(asset('img/logo-mark.svg')) ?>" alt="" width="30" height="30" class="site-header__logo-mark">
            Futebol<span>Hoje</span>
        </a>
        <nav class="site-nav">
            <a href="<?= e(url('/')) ?>">Início</a>
            <a href="<?= e(url('/ao-vivo')) ?>" class="site-nav__ao-vivo"><span class="dot-vivo"></span> Ao vivo</a>
            <a href="<?= e(url('/ligas')) ?>">Ligas</a>
        </nav>
        <form class="busca-form" action="<?= e(url('/busca')) ?>" method="get">
            <input type="search" name="q" placeholder="Buscar time ou liga..." value="<?= e($_GET['q'] ?? '') ?>">
        </form>
    </div>
</header>
