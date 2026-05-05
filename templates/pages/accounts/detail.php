<section class="section">
    <div class="section-header">
        <div>
            <p class="eyebrow">Compte</p>
            <h1><?= htmlspecialchars($account['short_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></h1>
        </div>
        <a class="button button-secondary" href="/?page=accounts">← Retour aux comptes</a>
    </div>

    <?php if (!empty($success)) : ?>
        <p class="notice notice-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if (!empty($error)) : ?>
        <p class="notice notice-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <div class="detail-grid">
        <article class="detail-card">
            <h2>Informations</h2>
            <form method="post" action="/?page=account&id=<?= $account['id'] ?>" class="detail-form">
                <input type="hidden" name="action" value="update">

                <label>
                    Nom court
                    <input type="text" name="short_name" required value="<?= htmlspecialchars($account['short_name'], ENT_QUOTES, 'UTF-8') ?>">
                </label>

                <label>
                    Description
                    <textarea name="description" required><?= htmlspecialchars($account['description'], ENT_QUOTES, 'UTF-8') ?></textarea>
                </label>

                <label>
                    Taux de rémunération (%)
                    <input type="number" name="interest_rate" step="0.01" min="0" max="100" value="<?= $account['interest_rate'] ?>">
                </label>

                <label>
                    Taux d'imposition (%)
                    <input type="number" name="tax_rate" step="0.01" min="0" max="100" value="<?= $account['tax_rate'] ?>">
                </label>

                <button class="button" type="submit">Mettre à jour</button>
            </form>
        </article>

        <article class="detail-card danger">
            <h2>Danger</h2>
            <p>Une fois supprimé, le compte et tous ses données (dépenses, revenus) seront perdus.</p>
            <form method="post" action="/?page=account&id=<?= $account['id'] ?>" class="danger-form">
                <input type="hidden" name="action" value="delete">
                <button class="button button-danger" type="submit" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce compte ?')">Supprimer le compte</button>
            </form>
        </article>
    </div>
</section>

<section class="section">
    <div class="section-header">
        <div>
            <p class="eyebrow">Dépenses</p>
            <h2>Liste des dépenses liées</h2>
        </div>
        <a class="button" href="/?page=expense-create&account_id=<?= $account['id'] ?>">+ Nouvelle dépense</a>
    </div>

    <?php if (empty($expenses)) : ?>
        <p class="empty-state">Aucune dépense pour ce compte.</p>
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
                            <dt>Fréquence</dt>
                            <dd><?= htmlspecialchars($exp['frequency'], ENT_QUOTES, 'UTF-8') ?> <?= $exp['frequency_months'] ? '(' . $exp['frequency_months'] . ' mois)' : '' ?></dd>
                            <dt>Début</dt>
                            <dd><?= htmlspecialchars($exp['start_date'], ENT_QUOTES, 'UTF-8') ?></dd>
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

<section class="section">
    <div class="section-header">
        <div>
            <p class="eyebrow">Revenus</p>
            <h2>Liste des revenus liés</h2>
        </div>
        <a class="button" href="/?page=income-create&account_id=<?= $account['id'] ?>">+ Nouveau revenu</a>
    </div>

    <?php if (empty($incomes)) : ?>
        <p class="empty-state">Aucun revenu pour ce compte.</p>
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
                            <dt>Fréquence</dt>
                            <dd><?= htmlspecialchars($inc['frequency'], ENT_QUOTES, 'UTF-8') ?> <?= $inc['frequency_months'] ? '(' . $inc['frequency_months'] . ' mois)' : '' ?></dd>
                            <dt>Début</dt>
                            <dd><?= htmlspecialchars($inc['start_date'], ENT_QUOTES, 'UTF-8') ?></dd>
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
