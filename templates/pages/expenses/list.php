<section class="section" data-search-section>
    <div class="section-header">
        <div>
            <p class="eyebrow">Dépenses</p>
            <h1>Toutes les dépenses</h1>
        </div>
    </div>

    <article class="detail-card" style="margin-bottom: 24px;">
        <form method="get" action="/" class="search-form js-search-form" style="display: flex; gap: 8px; align-items: flex-end; flex-wrap: wrap;">
            <input type="hidden" name="page" value="expenses">
            <label style="flex: 1; min-width: 240px;">
                Recherche
                <input type="search" class="js-search-input" name="q" value="<?= htmlspecialchars($_GET['q'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Nom court ou description" style="width: 100%; padding: 10px; margin-top: 6px; border: 1px solid #ccc; border-radius: 6px;">
            </label>
            <button class="button" type="submit" style="padding: 10px 16px;">Chercher</button>
        </form>
    </article>

    <?php if (empty($expenses)) : ?>
        <p class="empty-state">Aucune dépense trouvée.</p>
    <?php else : ?>
        <div class="accounts-grid js-search-list">
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

        <div class="show-more-wrapper js-show-more hidden">
            <button class="button" type="button">Voir plus</button>
        </div>

        <p class="empty-state js-search-empty hidden">Aucune dépense trouvée.</p>
    <?php endif; ?>
</section>
