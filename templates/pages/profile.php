<section class="section">
    <div class="section-header">
        <div>
            <p class="eyebrow">Compte</p>
            <h1>Gérer votre profil.</h1>
        </div>
    </div>

    <?php if (!empty($success)) : ?>
        <p class="notice notice-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if (!empty($error)) : ?>
        <p class="notice notice-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <div class="detail-grid">
        <article class="detail-card">
            <h2>Informations personnelles</h2>
            <form method="post" action="/?page=profile" class="detail-form"><?= CsrfHelper::field() ?>
                <input type="hidden" name="action" value="update-name">

                <label>
                    Adresse email
                    <input type="email" value="<?= htmlspecialchars($user['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" disabled>
                </label>

                <label>
                    Nom complet
                    <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                </label>

                <button class="button" type="submit">Enregistrer</button>
            </form>
        </article>

        <article class="detail-card">
            <h2>Mot de passe</h2>
            <form method="post" action="/?page=profile" class="detail-form"><?= CsrfHelper::field() ?>
                <input type="hidden" name="action" value="update-password">

                <label>
                    Mot de passe actuel
                    <input type="password" name="current_password" required autocomplete="current-password">
                </label>

                <label>
                    Nouveau mot de passe
                    <input type="password" name="password" required autocomplete="new-password" placeholder="Budgie2026!">
                </label>

                <label>
                    Confirmer le mot de passe
                    <input type="password" name="password_confirm" required autocomplete="new-password" placeholder="Budgie2026!">
                </label>

                <button class="button" type="submit">Changer le mot de passe</button>
            </form>
        </article>

        <article class="detail-card">
            <h2>Abonnement</h2>
            <div class="card-body">
                <dl>
                    <dt>Formule actuelle</dt>
                    <dd><?= ($user['plan'] ?? 'free') === 'paid' ? 'Premium' : 'Gratuit' ?></dd>
                    <dt>Comptes bancaires</dt>
                    <dd><?= ($user['plan'] ?? 'free') === 'paid' ? 'Illimités' : '2 maximum' ?></dd>
                    <dt>Dépenses</dt>
                    <dd><?= ($user['plan'] ?? 'free') === 'paid' ? 'Illimitées' : '7 par compte' ?></dd>
                    <dt>Revenus</dt>
                    <dd><?= ($user['plan'] ?? 'free') === 'paid' ? 'Illimités' : '2 par compte' ?></dd>
                </dl>
            </div>

            <?php if (($user['plan'] ?? 'free') === 'paid') : ?>
                <form method="post" action="/?page=subscription-cancel" class="detail-form"><?= CsrfHelper::field() ?>
                    <button class="button button-danger" type="submit">Résilier premium</button>
                </form>
            <?php else : ?>
                <form method="post" action="/?page=subscription-checkout" class="detail-form"><?= CsrfHelper::field() ?>
                    <button class="button" type="submit">Souscrire à premium</button>
                </form>
            <?php endif; ?>
        </article>
    </div>
</section>
