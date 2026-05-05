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

    <p class="hint">Compte de démonstration: demo@budgie.local / BudgieDemo2026!</p>
</section>