<section class="section">
    <div class="section-header">
        <div>
            <p class="eyebrow">Revenu</p>
            <h1><?= htmlspecialchars($income['short_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></h1>
        </div>
        <a class="button button-secondary" href="/?page=account&id=<?= $account['id'] ?>">← Retour</a>
    </div>

    <?php if (!empty($success)) : ?>
        <p class="notice notice-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if (!empty($error)) : ?>
        <p class="notice notice-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <article class="detail-card">
        <form method="post" action="/?page=income&id=<?= $income['id'] ?>" class="detail-form">
            <input type="hidden" name="action" value="update">

            <label>
                Nom court
                <input type="text" name="short_name" required value="<?= htmlspecialchars($income['short_name'], ENT_QUOTES, 'UTF-8') ?>">
            </label>

            <label>
                Description
                <textarea name="description" required><?= htmlspecialchars($income['description'], ENT_QUOTES, 'UTF-8') ?></textarea>
            </label>

            <label>
                Montant (€)
                <input type="number" step="0.01" name="amount" required value="<?= $income['amount'] ?>">
            </label>

            <label>
                Fréquence
                <input type="text" name="frequency" value="<?= htmlspecialchars($income['frequency'], ENT_QUOTES, 'UTF-8') ?>">
            </label>

            <label>
                Date de début
                <input type="date" name="start_date" value="<?= htmlspecialchars($income['start_date'], ENT_QUOTES, 'UTF-8') ?>">
            </label>

            <label>
                Date de fin
                <input type="date" name="end_date" value="<?= htmlspecialchars($income['end_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </label>

            <button class="button" type="submit">Mettre à jour</button>
        </form>

        <form method="post" action="/?page=income&id=<?= $income['id'] ?>" class="danger-form" style="margin-top:18px;">
            <input type="hidden" name="action" value="delete">
            <button class="button button-danger" type="submit" onclick="return confirm('Supprimer ce revenu ?')">Supprimer</button>
        </form>
    </article>
</section>
