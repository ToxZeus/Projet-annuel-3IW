<section class="section" data-search-section>
    <div class="section-header">
        <div>
            <p class="eyebrow">Revenus</p>
            <h1>Tous les revenus</h1>
        </div>
    </div>

    <article class="detail-card" style="margin-bottom: 24px;">
        <form method="get" action="/" class="search-form js-search-form" style="display: flex; gap: 8px; align-items: flex-end; flex-wrap: wrap;">
            <input type="hidden" name="page" value="incomes">
            <label style="flex: 1; min-width: 240px;">
                Recherche
                <input type="search" class="js-search-input" name="q" value="<?= htmlspecialchars($_GET['q'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Nom court ou description" style="width: 100%; padding: 10px; margin-top: 6px; border: 1px solid #ccc; border-radius: 6px;">
            </label>
            <button class="button" type="submit" style="padding: 10px 16px;">Chercher</button>
        </form>
    </article>

    <?php if (empty($incomes)) : ?>
        <p class="empty-state">Aucun revenu trouvé.</p>
    <?php else : ?>
        <div class="accounts-grid js-search-list">
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

        <div class="show-more-wrapper js-show-more hidden">
            <button class="button" type="button">Voir plus</button>
        </div>

        <p class="empty-state js-search-empty hidden">Aucun revenu trouvé.</p>
    <?php endif; ?>
</section>
