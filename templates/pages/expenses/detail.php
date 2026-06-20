<?php
$frequency = $old['frequency'] ?? ($expense['frequency'] ?? 'ponctuel');
$frequencyMonths = $old['frequencyMonths'] ?? ($expense['frequency_months'] ?? '');
?>

<section class="section">
    <div class="section-header">
        <div>
            <p class="eyebrow">Dépense</p>
            <h1><?= htmlspecialchars($expense['short_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></h1>
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
        <form method="post" action="/?page=expense&id=<?= $expense['id'] ?>" class="detail-form">
            <input type="hidden" name="action" value="update">

            <label>
                Nom court
                <input type="text" name="short_name" required value="<?= htmlspecialchars($old['shortName'] ?? $expense['short_name'], ENT_QUOTES, 'UTF-8') ?>">
            </label>

            <label>
                Description
                <textarea name="description" required><?= htmlspecialchars($old['description'] ?? $expense['description'], ENT_QUOTES, 'UTF-8') ?></textarea>
            </label>

            <label>
                Montant (€)
                <input type="number" step="0.01" name="amount" required value="<?= htmlspecialchars((string) ($old['amount'] ?? $expense['amount']), ENT_QUOTES, 'UTF-8') ?>">
            </label>

            <label>
                Fréquence
                <select name="frequency" data-frequency-select>
                    <option value="ponctuel" <?= $frequency === 'ponctuel' ? 'selected' : '' ?>>Ponctuel</option>
                    <option value="mensuel" <?= $frequency === 'mensuel' ? 'selected' : '' ?>>Tous les 1 mois</option>
                    <option value="periodic" <?= $frequency === 'periodic' ? 'selected' : '' ?>>Tous les N mois</option>
                </select>
            </label>

            <label data-frequency-months>
                Tous les N mois
                <input type="number" name="frequency_months" min="1" value="<?= htmlspecialchars((string) $frequencyMonths, ENT_QUOTES, 'UTF-8') ?>">
            </label>

            <label>
                Date de début
                <input type="date" name="start_date" value="<?= htmlspecialchars($old['startDate'] ?? $expense['start_date'], ENT_QUOTES, 'UTF-8') ?>">
            </label>

            <label>
                Date de fin
                <input type="date" name="end_date" value="<?= htmlspecialchars($old['endDate'] ?? ($expense['end_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </label>

            <button class="button" type="submit">Mettre à jour</button>
        </form>

        <form method="post" action="/?page=expense&id=<?= $expense['id'] ?>" class="danger-form" style="margin-top:18px;">
            <input type="hidden" name="action" value="delete">
            <button class="button button-danger" type="submit" onclick="return confirm('Supprimer cette dépense ?')">Supprimer</button>
        </form>
    </article>
</section>

<section class="section">
    <div class="section-header">
        <div>
            <p class="eyebrow">Dépense</p>
            <h1><?= htmlspecialchars($expense['short_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></h1>
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
        <form method="post" action="/?page=expense&id=<?= $expense['id'] ?>" class="detail-form">
            <input type="hidden" name="action" value="update">

            <label>
                Nom court
                <input type="text" name="short_name" required value="<?= htmlspecialchars($expense['short_name'], ENT_QUOTES, 'UTF-8') ?>">
            </label>

            <label>
                Description
                <textarea name="description" required><?= htmlspecialchars($expense['description'], ENT_QUOTES, 'UTF-8') ?></textarea>
            </label>

            <label>
                Montant (€)
                <input type="number" step="0.01" name="amount" required value="<?= $expense['amount'] ?>">
            </label>

            <label>
                Fréquence
                <input type="text" name="frequency" value="<?= htmlspecialchars($expense['frequency'], ENT_QUOTES, 'UTF-8') ?>">
            </label>

            <label>
                Date de début
                <input type="date" name="start_date" value="<?= htmlspecialchars($expense['start_date'], ENT_QUOTES, 'UTF-8') ?>">
            </label>

            <label>
                Date de fin
                <input type="date" name="end_date" value="<?= htmlspecialchars($expense['end_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </label>

            <button class="button" type="submit">Mettre à jour</button>
        </form>

        <form method="post" action="/?page=expense&id=<?= $expense['id'] ?>" class="danger-form" style="margin-top:18px;">
            <input type="hidden" name="action" value="delete">
            <button class="button button-danger" type="submit" onclick="return confirm('Supprimer cette dépense ?')">Supprimer</button>
        </form>
    </article>
</section>
<section class="section" style="padding-top: 0;">
    <div class="section-header">
        <div>
            <p class="eyebrow">Exceptions</p>
            <h2>Exceptions liées à cette dépense</h2>
        </div>
        <a class="button" href="/?page=exception-create&type=expense&entity_id=<?= $expense['id'] ?>">+ Nouvelle exception</a>
    </div>

    <?php if (empty($exceptions)) : ?>
        <p class="empty-state">Aucune exception pour cette dépense.</p>
    <?php else : ?>
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
    <?php endif; ?>
</section>
