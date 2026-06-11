<section class="section">
    <div class="section-header">
        <div>
            <p class="eyebrow">Nouveau revenu</p>
            <h1>Créer un revenu pour <?= htmlspecialchars($account['short_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></h1>
        </div>
        <a class="button button-secondary" href="/?page=account&id=<?= $account['id'] ?>">← Retour</a>
    </div>

    <article class="detail-card">
        <form method="post" action="/?page=incomes" class="detail-form">
            <input type="hidden" name="account_id" value="<?= $account['id'] ?>">

            <label>
                Nom court
                <input type="text" name="short_name" required>
            </label>

            <label>
                Description
                <textarea name="description" required></textarea>
            </label>

            <label>
                Montant (€)
                <input type="number" step="0.01" name="amount" required>
            </label>

            <label>
                Fréquence
                <select name="frequency">
                    <option value="ponctuel">Ponctuel</option>
                    <option value="mensuel">Tous les 1 mois</option>
                    <option value="periodic">Tous les N mois</option>
                </select>
            </label>

            <label>
                Tous les N mois (si applicable)
                <input type="number" name="frequency_months" min="1">
            </label>

            <label>
                Date de début
                <input type="date" name="start_date" value="<?= date('Y-m-d') ?>">
            </label>

            <label>
                Date de fin
                <input type="date" name="end_date">
            </label>

            <button class="button" type="submit">Créer le revenu</button>
        </form>
    </article>
</section>
