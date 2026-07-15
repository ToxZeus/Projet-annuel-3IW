<?php /** @var array $exception */ ?>
<section class="section">
    <div class="section-header">
        <div>
            <p class="eyebrow">Exception</p>
            <h1><?= htmlspecialchars($exception['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></h1>
        </div>
        <a class="button button-secondary" href="/?page=<?= $exception['entity_type'] === 'income' ? 'income' : 'expense' ?>&id=<?= $exception['entity_id'] ?>">← Retour</a>
    </div>

    <?php if (!empty($success)) : ?>
        <p class="notice notice-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if (!empty($error)) : ?>
        <p class="notice notice-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <div class="detail-grid">
        <article class="detail-card">
            <h2>Modifier l'exception</h2>
            <form method="post" action="/?page=exception&id=<?= $exception['id'] ?>" class="detail-form">
                <?= $csrf_field ?>
                <input type="hidden" name="action" value="update">

                <label>
                    Nom
                    <input type="text" name="name" required value="<?= htmlspecialchars($exception['name'], ENT_QUOTES, 'UTF-8') ?>">
                </label>

                <label>
                    Description
                    <textarea name="description"><?= htmlspecialchars($exception['description'], ENT_QUOTES, 'UTF-8') ?></textarea>
                </label>

                <label>
                    Montant (€)
                    <input type="number" name="amount" step="0.01" min="0.01" required value="<?= $exception['amount'] ?>">
                </label>

                <label>
                    Fréquence
                    <select name="frequency" id="frequency">
                        <option value="ponctuel" <?= $exception['frequency'] === 'ponctuel' ? 'selected' : '' ?>>Ponctuelle</option>
                        <option value="mensuel" <?= $exception['frequency'] === 'mensuel' ? 'selected' : '' ?>>Tous les mois</option>
                        <option value="periodique" <?= $exception['frequency'] === 'periodique' ? 'selected' : '' ?>>Tous les N mois</option>
                    </select>
                </label>

                <label id="frequency-months-label" style="display:<?= $exception['frequency'] === 'periodique' ? 'flex' : 'none' ?>;">
                    Tous les N mois
                    <input type="number" name="frequency_months" min="1" value="<?= $exception['frequency_months'] ?? '' ?>">
                </label>

                <label>
                    Date de début
                    <input type="date" name="start_date" required value="<?= htmlspecialchars($exception['start_date'], ENT_QUOTES, 'UTF-8') ?>">
                </label>

                <label>
                    Date de fin (optionnelle)
                    <input type="date" name="end_date" value="<?= htmlspecialchars($exception['end_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </label>

                <button class="button" type="submit">Mettre à jour</button>
            </form>
        </article>

        <article class="detail-card danger">
            <h2>Danger</h2>
            <p>Supprimer cette exception restaurera le montant original pour les périodes concernées.</p>
            <form method="post" action="/?page=exception&id=<?= $exception['id'] ?>" class="danger-form">
                <?= $csrf_field ?>
                <input type="hidden" name="action" value="delete">
                <button class="button button-danger" type="submit" onclick="return confirm('Supprimer cette exception ?')">Supprimer</button>
            </form>
        </article>
    </div>
</section>

<script>
document.getElementById('frequency').addEventListener('change', function() {
    document.getElementById('frequency-months-label').style.display = this.value === 'periodique' ? 'flex' : 'none';
});
</script>
