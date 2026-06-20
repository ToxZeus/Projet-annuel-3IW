<?php /** @var string $entity_type */ /** @var int $entity_id */ ?>
<section class="section">
    <div class="section-header">
        <div>
            <p class="eyebrow">Exception</p>
            <h1>Nouvelle exception</h1>
        </div>
        <a class="button button-secondary" href="/?page=<?= $entity_type === 'income' ? 'income' : 'expense' ?>&id=<?= $entity_id ?>">← Retour</a>
    </div>

    <?php if (!empty($error)) : ?>
        <p class="notice notice-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <article class="detail-card">
        <form method="post" action="/?page=exception-create" class="detail-form">
            <input type="hidden" name="entity_type" value="<?= htmlspecialchars($entity_type, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="entity_id" value="<?= (int) $entity_id ?>">

            <label>
                Nom
                <input type="text" name="name" required placeholder="Ex: Vacances estivales">
            </label>

            <label>
                Description
                <textarea name="description" placeholder="Description de l'exception"></textarea>
            </label>

            <label>
                Montant (€)
                <input type="number" name="amount" step="0.01" min="0.01" required placeholder="150.00">
            </label>

            <label>
                Fréquence
                <select name="frequency" id="frequency">
                    <option value="ponctuel">Ponctuelle</option>
                    <option value="mensuel">Tous les mois</option>
                    <option value="periodique">Tous les N mois</option>
                </select>
            </label>

            <label id="frequency-months-label" style="display:none;">
                Tous les N mois
                <input type="number" name="frequency_months" min="1" placeholder="Ex: 12">
            </label>

            <label>
                Date de début
                <input type="date" name="start_date" required value="<?= date('Y-m-d') ?>">
            </label>

            <label>
                Date de fin (optionnelle)
                <input type="date" name="end_date">
            </label>

            <button class="button" type="submit">Créer l'exception</button>
        </form>
    </article>
</section>

<script>
document.getElementById('frequency').addEventListener('change', function() {
    document.getElementById('frequency-months-label').style.display = this.value === 'periodique' ? 'flex' : 'none';
});
</script>
