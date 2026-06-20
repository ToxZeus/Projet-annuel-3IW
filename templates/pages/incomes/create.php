<?php
$frequency = $old['frequency'] ?? 'ponctuel';
$frequencyMonths = $old['frequencyMonths'] ?? '';
?>

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
                <input type="text" name="short_name" required value="<?= htmlspecialchars($old['shortName'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </label>

            <label>
                Description
                <textarea name="description" required><?= htmlspecialchars($old['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </label>

            <label>
                Montant (€)
                <input type="number" step="0.01" name="amount" required value="<?= htmlspecialchars((string) ($old['amount'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </label>

            <div class="frequency-group">
                <label>
                    Fréquence
                    <select name="frequency" data-frequency-select>
                        <option value="ponctuel" <?= $frequency === 'ponctuel' ? 'selected' : '' ?>>Ponctuel</option>
                        <option value="mensuel" <?= $frequency === 'mensuel' ? 'selected' : '' ?>>Tous les mois</option>
                        <option value="periodic" <?= $frequency === 'periodic' ? 'selected' : '' ?>>Périodique (tous les N mois)</option>
                    </select>
                </label>

                <label data-frequency-months<?= $frequency !== 'periodic' ? ' class="hidden"' : '' ?>>
                    Tous les combien de mois ?
                    <input type="number" name="frequency_months" min="2" value="<?= htmlspecialchars((string) $frequencyMonths, ENT_QUOTES, 'UTF-8') ?>">
                </label>
            </div>

            <label>
                Date de début
                <input type="date" name="start_date" value="<?= htmlspecialchars($old['startDate'] ?? date('Y-m-d'), ENT_QUOTES, 'UTF-8') ?>">
            </label>

            <label<?= $frequency === 'ponctuel' ? ' class="hidden"' : '' ?> data-frequency-enddate>
                Date de fin
                <input type="date" name="end_date" value="<?= htmlspecialchars($old['endDate'] ?? '', ENT_QUOTES, 'UTF-8') ?>"<?= $frequency === 'ponctuel' ? ' disabled' : '' ?>>
            </label>

            <button class="button" type="submit">Créer le revenu</button>
        </form>
    </article>
</section>
