<section class="section">
    <div class="section-header">
        <div>
            <p class="eyebrow">Prévisions</p>
            <h1>Prévisions mensuelles</h1>
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

<?php if (!empty($chart_labels)) : ?>
<section class="section" style="padding-top: 0;">
    <div class="charts-grid">
        <article class="detail-card">
            <h2>Évolution du solde (12 mois)</h2>
            <canvas id="chartBalance" height="120"></canvas>
        </article>
        <article class="detail-card">
            <h2>Revenus vs Dépenses (12 mois)</h2>
            <canvas id="chartIncomesExpenses" height="120"></canvas>
        </article>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
(function () {
    const labels = <?= json_encode($chart_labels) ?>;
    const balances = <?= json_encode($chart_balances) ?>;
    const incomes = <?= json_encode($chart_incomes) ?>;
    const expenses = <?= json_encode($chart_expenses) ?>;

    const green = '#2f5d50';
    const greenLight = 'rgba(47,93,80,0.15)';
    const red = '#c0392b';
    const redLight = 'rgba(192,57,43,0.15)';

    new Chart(document.getElementById('chartBalance'), {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'Solde projeté (€)',
                data: balances,
                borderColor: green,
                backgroundColor: greenLight,
                fill: true,
                tension: 0.3,
                pointRadius: 4,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { ticks: { callback: v => v.toLocaleString('fr-FR') + ' €' } } }
        }
    });

    new Chart(document.getElementById('chartIncomesExpenses'), {
        type: 'bar',
        data: {
            labels,
            datasets: [
                { label: 'Revenus (€)', data: incomes, backgroundColor: greenLight, borderColor: green, borderWidth: 1 },
                { label: 'Dépenses (€)', data: expenses, backgroundColor: redLight, borderColor: red, borderWidth: 1 }
            ]
        },
        options: {
            responsive: true,
            scales: { y: { ticks: { callback: v => v.toLocaleString('fr-FR') + ' €' } } }
        }
    });
})();
</script>
<?php endif; ?>

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
