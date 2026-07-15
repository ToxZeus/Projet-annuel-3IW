<section class="hero auth-card">
    <p class="eyebrow">Budgie</p>
    <h1>Créer un compte</h1>
    <p class="lead">
        Rejoins Budgie et commence à gérer ton budget personnel.
    </p>

    <?php if (!empty($success)) : ?>
        <p class="notice notice-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if (!empty($error)) : ?>
        <p class="notice notice-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <form method="post" action="/?page=signup" class="auth-form">
        <?= $csrf_field ?>
        <label>
            Email
            <input type="email" name="email" required value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </label>

        <label>
            Nom complet
            <input type="text" name="full_name" required value="<?= htmlspecialchars($old['full_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </label>

        <label>
            Mot de passe
            <input type="password" name="password" required>
        </label>

        <label>
            Confirmer le mot de passe
            <input type="password" name="password_confirm" required>
        </label>

        <button class="button" type="submit">S'inscrire</button>
    </form>

    <p class="hint">
        Tu as déjà un compte ? <a href="/?page=login" class="link">Connecte-toi</a>
    </p>
</section>
