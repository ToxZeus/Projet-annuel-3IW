<section class="hero auth-card">
    <p class="eyebrow">Connexion</p>
    <h1>Accéder à l'espace personnel.</h1>

    <?php if (!empty($error)) : ?>
        <p class="notice notice-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if (!empty($success)) : ?>
        <p class="notice notice-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <form class="auth-form" method="post" action="/?page=login">
        <label>
            Adresse email
            <input type="email" name="email" required autocomplete="email" placeholder="demo@budgie.local">
        </label>

        <label>
            Mot de passe
            <input type="password" name="password" required autocomplete="current-password" placeholder="BudgieDemo2026!">
        </label>

        <button class="button" type="submit">Se connecter</button>
    </form>

    <p class="hint"><a href="/?page=forgot-password" class="link">Mot de passe oublié ?</a></p>
    <p class="hint">Pas encore inscrit ? <a href="/?page=signup" class="link">Crée un compte</a></p>
    <p class="hint" style="font-size: 0.85rem; color: var(--muted);">Compte de démo : demo@budgie.local / BudgieDemo2026!</p>
</section>