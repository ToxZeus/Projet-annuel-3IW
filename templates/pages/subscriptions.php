<section class="section">
    <div class="section-header">
        <div>
            <p class="eyebrow">Abonnements</p>
            <h1>Choisir une formule.</h1>
        </div>
    </div>

    <?php if (!empty($success)) : ?>
        <p class="notice notice-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if (!empty($error)) : ?>
        <p class="notice notice-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <div class="accounts-grid">
        <article class="account-card">
            <div class="card-header">
                <div>
                    <h3>Gratuit</h3>
                    <p>Formule actuelle pour demarrer.</p>
                </div>
            </div>
            <div class="card-body">
                <dl>
                    <dt>Comptes bancaires</dt>
                    <dd>2 maximum</dd>
                    <dt>Depenses</dt>
                    <dd>7 par compte</dd>
                    <dt>Revenus</dt>
                    <dd>2 par compte</dd>
                </dl>
            </div>
        </article>

        <article class="account-card">
            <div class="card-header">
                <div>
                    <h3>Premium</h3>
                    <p>Formule payante avec limites levees.</p>
                </div>
            </div>
            <div class="card-body">
                <dl>
                    <dt>Comptes bancaires</dt>
                    <dd>Illimites</dd>
                    <dt>Depenses</dt>
                    <dd>Illimitees</dd>
                    <dt>Revenus</dt>
                    <dd>Illimites</dd>
                </dl>
            </div>
            <div class="card-footer">
                <?php if (($user['plan'] ?? 'free') === 'paid') : ?>
                    <span class="notice notice-success">Vous etes premium.</span>
                <?php else : ?>
                    <form method="post" action="/?page=subscription-checkout">
                        <button class="button" type="submit">Devenir premium</button>
                    </form>
                <?php endif; ?>
            </div>
        </article>
    </div>
</section>
