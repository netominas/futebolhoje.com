<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login — Admin <?= e(SITE_NAME) ?></title>
<meta name="robots" content="noindex, nofollow">
<link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>?v=<?= (int) (@filemtime(__DIR__ . '/../../assets/css/style.css') ?: time()) ?>">
<link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>?v=<?= (int) (@filemtime(__DIR__ . '/../../assets/css/admin.css') ?: time()) ?>">
</head>
<body class="admin-body admin-body--login">
<main class="admin-login-wrap">
<?= $conteudo ?>
</main>
</body>
</html>
