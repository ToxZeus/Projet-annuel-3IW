<section class="section">
    <div class="section-header">
        <div>
            <p class="eyebrow">Partage</p>
            <h1>Invitation à consulter un compte</h1>
        </div>
    </div>

    <?php if (!empty($error)) : ?>
        <p class="notice notice-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if (empty($share)) : ?>
        <p class="empty-state">Ce lien d'invitation est invalide ou a expiré.</p>
        <a class="button" href="/?page=dashboard">Retour au tableau de bord</a>
    <?php elseif ($share['status'] === 'accepted') : ?>
        <p class="notice">Cette invitation a déjà été acceptée.</p>
        <a class="button" href="/?page=account&id=<?= $share['account_id'] ?>">Voir le compte</a>
    <?php elseif (strcasecmp($share['invited_email'], $user['email'] ?? '') !== 0) : ?>
        <p class="notice notice-error">Cette invitation a été envoyée à une autre adresse email (<?= htmlspecialchars($share['invited_email'], ENT_QUOTES, 'UTF-8') ?>).</p>
    <?php else : ?>
        <article class="detail-card">
            <p>
                <strong><?= htmlspecialchars($share['owner_email'], ENT_QUOTES, 'UTF-8') ?></strong>
                vous invite à consulter, en lecture seule, le compte
                <strong><?= htmlspecialchars($share_account['short_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>.
            </p>
            <p><?= htmlspecialchars($share_account['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>

            <form method="post" action="/?page=share-accept">
                <?= $csrf_field ?>
                <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
                <button class="button" type="submit">Accepter l'invitation</button>
            </form>
        </article>
    <?php endif; ?>
</section>