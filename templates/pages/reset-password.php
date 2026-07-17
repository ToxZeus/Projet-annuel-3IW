<?php $token = $_GET['token'] ?? ''; ?>

<section class="hero auth-card">
    <p class="eyebrow">Réinitialisation</p>
    <h1>Choisir un nouveau mot de passe.</h1>

    <?php if (empty($token)) : ?>
        <p class="notice notice-error">Token invalide.</p>
        <p class="hint"><a href="/?page=login" class="link">Retour à la connexion</a></p>
    <?php else : ?>
        <p class="lead">
            Utilise un mot de passe sécurisé avec au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.
        </p>

        <?php if (!empty($error)) : ?>
            <p class="notice notice-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <?php if (!empty($success)) : ?>
            <p class="notice notice-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <form class="auth-form" method="post" action="/?page=reset-password"><?= CsrfHelper::field() ?>
            <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">

            <label>
                Nouveau mot de passe
                <input type="password" name="password" required autocomplete="new-password" placeholder="Budgie2026!">
            </label>

            <label>
                Confirmer le mot de passe
                <input type="password" name="password_confirm" required autocomplete="new-password" placeholder="Budgie2026!">
            </label>

            <button class="button" type="submit">Réinitialiser le mot de passe</button>
        </form>

        <p class="hint"><a href="/?page=login" class="link">Retour à la connexion</a></p>
    <?php endif; ?>
</section>
