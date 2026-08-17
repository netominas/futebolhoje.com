<?php
/** @var array $usuarios */
/** @var ?string $erro */
?>
<h1>Usuários administradores</h1>

<?php if ($erro !== null): ?>
<div class="admin-alerta admin-alerta--erro"><?= e($erro) ?></div>
<?php endif; ?>

<div class="admin-tabela-wrap">
<table class="admin-tabela">
    <thead>
        <tr><th>Nome</th><th>E-mail</th><th>Criado em</th><th></th></tr>
    </thead>
    <tbody>
        <?php foreach ($usuarios as $usuario): ?>
        <tr>
            <td><?= e($usuario['nome']) ?></td>
            <td><?= e($usuario['email']) ?></td>
            <td><?= e(formatarDataHora($usuario['created_at'])) ?></td>
            <td>
                <form method="post" action="<?= e(url('/admin/usuarios/' . $usuario['id'] . '/excluir')) ?>" onsubmit="return confirm('Excluir este usuário?');">
                    <input type="hidden" name="csrf" value="<?= e(AdminAuth::csrfToken()) ?>">
                    <button type="submit" class="btn-link btn-link--remover">excluir</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>

<h2>Novo usuário</h2>
<form method="post" action="<?= e(url('/admin/usuarios')) ?>" class="admin-form admin-form--largura-limitada">
    <input type="hidden" name="csrf" value="<?= e(AdminAuth::csrfToken()) ?>">
    <label>Nome
        <input type="text" name="nome" required>
    </label>
    <label>E-mail
        <input type="email" name="email" required>
    </label>
    <label>Senha
        <input type="password" name="senha" required minlength="8">
    </label>
    <button type="submit" class="btn btn-primario admin-form__botao">Criar usuário</button>
</form>
