<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin — <?= e(SITE_NAME) ?></title>
<meta name="robots" content="noindex, nofollow">
<link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>?v=<?= (int) (@filemtime(__DIR__ . '/../../assets/css/style.css') ?: time()) ?>">
<link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>?v=<?= (int) (@filemtime(__DIR__ . '/../../assets/css/admin.css') ?: time()) ?>">
</head>
<body class="admin-body">
<header class="admin-nav">
    <div class="admin-nav__inner">
        <a href="<?= e(url('/admin')) ?>" class="admin-nav__logo">FutebolHoje <span>Admin</span></a>
        <nav class="admin-nav__links">
            <a href="<?= e(url('/admin')) ?>">Dashboard</a>
            <a href="<?= e(url('/admin/ligas')) ?>">Ligas</a>
            <a href="<?= e(url('/admin/times')) ?>">Times</a>
            <a href="<?= e(url('/admin/usuarios')) ?>">Usuários</a>
        </nav>
        <form method="post" action="<?= e(url('/admin/sair')) ?>" class="admin-nav__sair">
            <input type="hidden" name="csrf" value="<?= e(AdminAuth::csrfToken()) ?>">
            <span><?= e(AdminAuth::nome() ?? '') ?></span>
            <button type="submit" class="btn-link">Sair</button>
        </form>
    </div>
</header>
<main class="admin-main">
<?= $conteudo ?>
</main>
</body>
</html>
