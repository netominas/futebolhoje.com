<?php
/** @var ?string $erro */
?>
<div class="admin-login-card">
    <h1 class="admin-login-card__titulo">Futebol<span>Hoje</span> <small>Admin</small></h1>

    <?php if ($erro !== null): ?>
    <div class="admin-alerta admin-alerta--erro"><?= e($erro) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= e(url('/admin/login')) ?>" class="admin-form">
        <label>E-mail
            <input type="email" name="email" required autofocus>
        </label>
        <label>Senha
            <input type="password" name="senha" required>
        </label>
        <button type="submit" class="btn btn-primario admin-form__botao">Entrar</button>
    </form>
</div>
