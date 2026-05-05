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
