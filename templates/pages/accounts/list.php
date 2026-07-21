<section class="section">
    <div class="section-header">
        <div>
            <p class="eyebrow">Comptes</p>
            <h1>Gérer vos comptes.</h1>
        </div>
        <a class="button" href="/?page=account-create">+ Nouveau compte</a>
    </div>

    <?php require BASE_PATH . '/templates/partials/flash.php'; ?>

    <?php if (empty($accounts)) : ?>
        <p class="empty-state">Aucun compte créé. <a href="/?page=account-create">Créer votre premier compte.</a></p>
    <?php else : ?>
        <div class="accounts-grid">
            <?php foreach ($accounts as $account) : ?>
                <article class="account-card">
                    <div class="card-header">
                        <div>
                            <h3><?= htmlspecialchars($account['short_name'], ENT_QUOTES, 'UTF-8') ?></h3>
                            <p><?= htmlspecialchars($account['description'], ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                    </div>

                    <div class="card-body">
                        <dl>
                            <dt>Solde</dt>
                            <dd class="balance"><?= number_format($account['balance'], 2, ',', ' ') ?> €</dd>
                            <dt>Solde initial</dt>
                            <dd><?= number_format((float) ($account['initial_balance'] ?? 0), 2, ',', ' ') ?> €</dd>
                            <dt>Taux de rémunération</dt>
                            <dd><?= number_format($account['interest_rate'], 2, ',', ' ') ?> %</dd>
                            <dt>Taux d'imposition</dt>
                            <dd><?= number_format($account['tax_rate'], 2, ',', ' ') ?> %</dd>
                            <dt>Date de création</dt>
                            <dd><?= $account['created_at'] ?></dd>
                        </dl>
                    </div>

                    <div class="card-footer">
                        <a class="link" href="/?page=account&id=<?= $account['id'] ?>">Voir détails</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php if (!empty($shared_accounts)) : ?>
<section class="section" style="padding-top: 0;">
    <div class="section-header">
        <div>
            <p class="eyebrow">Partagés avec moi</p>
            <h2>Comptes partagés (lecture seule)</h2>
        </div>
    </div>

    <div class="accounts-grid">
        <?php foreach ($shared_accounts as $account) : ?>
            <article class="account-card">
                <div class="card-header">
                    <div>
                        <h3><?= htmlspecialchars($account['short_name'], ENT_QUOTES, 'UTF-8') ?></h3>
                        <p><?= htmlspecialchars($account['description'], ENT_QUOTES, 'UTF-8') ?></p>
                        <p class="eyebrow">
                            Partagé par <?= htmlspecialchars($account['owner_email'], ENT_QUOTES, 'UTF-8') ?>
                        </p>
                        <span class="badge badge-readonly">
                            <svg class="badge-icon" viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                <rect x="4" y="10" width="16" height="10" rx="2"></rect>
                                <path d="M7 10V7a5 5 0 0 1 10 0v3"></path>
                            </svg>
                            Lecture seule
                        </span>
                    </div>
                </div>

                <div class="card-body">
                    <dl>
                        <dt>Solde</dt>
                        <dd class="balance"><?= number_format($account['balance'], 2, ',', ' ') ?> €</dd>
                        <dt>Taux de rémunération</dt>
                        <dd><?= number_format($account['interest_rate'], 2, ',', ' ') ?> %</dd>
                        <dt>Taux d'imposition</dt>
                        <dd><?= number_format($account['tax_rate'], 2, ',', ' ') ?> %</dd>
                    </dl>
                </div>

                <div class="card-footer">
                    <a class="link" href="/?page=account&id=<?= $account['id'] ?>">Voir détails</a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>