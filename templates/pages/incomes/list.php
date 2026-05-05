<section class="section">
    <div class="section-header">
        <div>
            <p class="eyebrow">Revenus</p>
            <h1>Tous les revenus</h1>
        </div>
    </div>

    <?php if (empty($incomes)) : ?>
        <p class="empty-state">Aucun revenu trouvé.</p>
    <?php else : ?>
        <div class="accounts-grid">
            <?php foreach ($incomes as $inc) : ?>
                <article class="account-card">
                    <h3><?= htmlspecialchars($inc['short_name'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <p><?= htmlspecialchars($inc['description'], ENT_QUOTES, 'UTF-8') ?></p>
                    <div class="card-body">
                        <dl>
                            <dt>Montant</dt>
                            <dd class="balance"><?= number_format($inc['amount'], 2, ',', ' ') ?> €</dd>
                            <dt>Compte</dt>
                            <dd><?= htmlspecialchars($inc['account_id'], ENT_QUOTES, 'UTF-8') ?></dd>
                        </dl>
                    </div>
                    <div class="card-footer">
                        <a class="link" href="/?page=income&id=<?= $inc['id'] ?>">Voir / Éditer</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
