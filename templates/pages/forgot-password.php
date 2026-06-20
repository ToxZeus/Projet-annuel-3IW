<section class="hero auth-card">
    <p class="eyebrow">Mot de passe oublié</p>
    <h1>Recevoir un lien de réinitialisation.</h1>
    <p class="lead">
        Indique l'adresse email associée à ton compte. Si elle existe, un lien de réinitialisation sera envoyé.
    </p>

    <?php if (!empty($error)) : ?>
        <p class="notice notice-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if (!empty($success)) : ?>
        <p class="notice notice-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <form class="auth-form" method="post" action="/?page=forgot-password">
        <label>
            Adresse email
            <input type="email" name="email" required autocomplete="email" placeholder="demo@budgie.local" value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </label>

        <button class="button" type="submit">Envoyer le lien</button>
    </form>

    <p class="hint"><a href="/?page=login" class="link">Retour à la connexion</a></p>
</section>
