<?php
$frequency = $old['frequency'] ?? ($income['frequency'] ?? 'ponctuel');
$frequencyMonths = $old['frequencyMonths'] ?? ($income['frequency_months'] ?? '');
?>

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
                <input type="text" name="short_name" required value="<?= htmlspecialchars($old['shortName'] ?? $income['short_name'], ENT_QUOTES, 'UTF-8') ?>">
            </label>

            <label>
                Description
                <textarea name="description" required><?= htmlspecialchars($old['description'] ?? $income['description'], ENT_QUOTES, 'UTF-8') ?></textarea>
            </label>

            <label>
                Montant (€)
                <input type="number" step="0.01" name="amount" required value="<?= htmlspecialchars((string) ($old['amount'] ?? $income['amount']), ENT_QUOTES, 'UTF-8') ?>">
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
                <input type="date" name="start_date" value="<?= htmlspecialchars($old['startDate'] ?? $income['start_date'], ENT_QUOTES, 'UTF-8') ?>">
            </label>

            <label<?= $frequency === 'ponctuel' ? ' class="hidden"' : '' ?> data-frequency-enddate>
                Date de fin
                <input type="date" name="end_date" value="<?= htmlspecialchars($old['endDate'] ?? ($income['end_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"<?= $frequency === 'ponctuel' ? ' disabled' : '' ?>>
            </label>

            <button class="button" type="submit">Mettre à jour</button>
        </form>

        <form method="post" action="/?page=income&id=<?= $income['id'] ?>" class="danger-form" style="margin-top:18px;">
            <input type="hidden" name="action" value="delete">
            <button class="button button-danger" type="submit" onclick="return confirm('Supprimer ce revenu ?')">Supprimer</button>
        </form>
    </article>
</section>

<?php if (!empty($exceptions)) : ?>
<section class="section" style="padding-top: 0;">
    <div class="section-header">
        <div>
            <p class="eyebrow">Exceptions</p>
            <h2>Exceptions liées à ce revenu</h2>
        </div>
        <a class="button" href="/?page=exception-create&type=income&entity_id=<?= $income['id'] ?>">+ Nouvelle exception</a>
    </div>

    <div class="accounts-grid">
        <?php foreach ($exceptions as $exc) : ?>
            <article class="account-card">
                <h3><?= htmlspecialchars($exc['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                <p><?= htmlspecialchars($exc['description'], ENT_QUOTES, 'UTF-8') ?></p>
                <div class="card-body">
                    <dl>
                        <dt>Montant exception</dt>
                        <dd class="balance"><?= number_format((float) $exc['amount'], 2, ',', ' ') ?> €</dd>
                        <dt>Fréquence</dt>
                        <dd><?= htmlspecialchars($exc['frequency'], ENT_QUOTES, 'UTF-8') ?> <?= $exc['frequency_months'] ? '(' . $exc['frequency_months'] . ' mois)' : '' ?></dd>
                        <dt>Début</dt>
                        <dd><?= htmlspecialchars($exc['start_date'], ENT_QUOTES, 'UTF-8') ?></dd>
                        <dt>Fin</dt>
                        <dd><?= htmlspecialchars($exc['end_date'] ?? 'Indéfinie', ENT_QUOTES, 'UTF-8') ?></dd>
                    </dl>
                </div>
                <div class="card-footer">
                    <a class="link" href="/?page=exception&id=<?= $exc['id'] ?>">Voir / Éditer</a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<?php else : ?>
<section class="section" style="padding-top: 0;">
    <div class="section-header">
        <div>
            <p class="eyebrow">Exceptions</p>
            <h2>Exceptions liées à ce revenu</h2>
        </div>
        <a class="button" href="/?page=exception-create&type=income&entity_id=<?= $income['id'] ?>">+ Nouvelle exception</a>
    </div>
    <p class="empty-state">Aucune exception pour ce revenu.</p>
</section>
<?php endif; ?>
