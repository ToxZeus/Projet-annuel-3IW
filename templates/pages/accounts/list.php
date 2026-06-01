<section class="section">
    <div class="section-header">
        <div>
            <p class="eyebrow">Comptes</p>
            <h1>Gérer vos comptes.</h1>
        </div>
        <a class="button" href="/?page=account-create">+ Nouveau compte</a>
    </div>

    <?php if (!empty($success)) : ?>
        <p class="notice notice-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if (!empty($error)) : ?>
        <p class="notice notice-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

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
