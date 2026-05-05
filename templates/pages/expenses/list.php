<section class="section">
    <div class="section-header">
        <div>
            <p class="eyebrow">Dépenses</p>
            <h1>Toutes les dépenses</h1>
        </div>
    </div>

    <?php if (empty($expenses)) : ?>
        <p class="empty-state">Aucune dépense trouvée.</p>
    <?php else : ?>
        <div class="accounts-grid">
            <?php foreach ($expenses as $exp) : ?>
                <article class="account-card">
                    <h3><?= htmlspecialchars($exp['short_name'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <p><?= htmlspecialchars($exp['description'], ENT_QUOTES, 'UTF-8') ?></p>
                    <div class="card-body">
                        <dl>
                            <dt>Montant</dt>
                            <dd class="balance"><?= number_format($exp['amount'], 2, ',', ' ') ?> €</dd>
                            <dt>Compte</dt>
                            <dd><?= htmlspecialchars($exp['account_id'], ENT_QUOTES, 'UTF-8') ?></dd>
                        </dl>
                    </div>
                    <div class="card-footer">
                        <a class="link" href="/?page=expense&id=<?= $exp['id'] ?>">Voir / Éditer</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
