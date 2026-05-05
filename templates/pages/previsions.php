<section class="section">
    <div class="section-header">
        <div>
            <p class="eyebrow">Previsions</p>
            <h1>Previsions mensuelles</h1>
        </div>
    </div>

    <article class="detail-card" style="margin-bottom: 24px;">
        <form method="get" action="/" class="detail-form">
            <input type="hidden" name="page" value="previsions">

            <label>
                Mois analyse
                <input type="month" name="month" value="<?= htmlspecialchars($selected_month ?? date('Y-m'), ENT_QUOTES, 'UTF-8') ?>">
            </label>

            <button class="button" type="submit">Mettre a jour</button>
        </form>
    </article>

    <div class="stats-grid">
        <article class="stat">
            <span class="stat-label">Solde debut</span>
            <strong><?= number_format((float) ($totals['start_balance'] ?? 0), 2, ',', ' ') ?> EUR</strong>
        </article>
        <article class="stat">
            <span class="stat-label">Revenus du mois</span>
            <strong><?= number_format((float) ($totals['incomes'] ?? 0), 2, ',', ' ') ?> EUR</strong>
        </article>
        <article class="stat">
            <span class="stat-label">Depenses du mois</span>
            <strong><?= number_format((float) ($totals['expenses'] ?? 0), 2, ',', ' ') ?> EUR</strong>
        </article>
        <article class="stat">
            <span class="stat-label">Interets nets</span>
            <strong><?= number_format((float) ($totals['interest'] ?? 0), 2, ',', ' ') ?> EUR</strong>
        </article>
        <article class="stat">
            <span class="stat-label">Solde projete fin de mois</span>
            <strong><?= number_format((float) ($totals['projected_balance'] ?? 0), 2, ',', ' ') ?> EUR</strong>
        </article>
    </div>
</section>

<section class="section" style="padding-top: 0;">
    <?php if (empty($forecast_rows)) : ?>
        <p class="empty-state">Aucun compte disponible pour calculer les previsions.</p>
    <?php else : ?>
        <article class="detail-card">
            <h2>Detail par compte</h2>
            <div class="forecast-table-wrap">
                <table class="forecast-table">
                    <thead>
                        <tr>
                            <th>Compte</th>
                            <th>Solde debut</th>
                            <th>Revenus</th>
                            <th>Depenses</th>
                            <th>Interets nets</th>
                            <th>Solde projete</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($forecast_rows as $row) : ?>
                            <tr>
                                <td>
                                    <a class="link" href="/?page=account&id=<?= $row['account_id'] ?>"><?= htmlspecialchars((string) $row['account_name'], ENT_QUOTES, 'UTF-8') ?></a>
                                </td>
                                <td><?= number_format((float) $row['start_balance'], 2, ',', ' ') ?> EUR</td>
                                <td><?= number_format((float) $row['incomes'], 2, ',', ' ') ?> EUR</td>
                                <td><?= number_format((float) $row['expenses'], 2, ',', ' ') ?> EUR</td>
                                <td><?= number_format((float) $row['interest'], 2, ',', ' ') ?> EUR</td>
                                <td><?= number_format((float) $row['projected_balance'], 2, ',', ' ') ?> EUR</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </article>
    <?php endif; ?>
</section>
