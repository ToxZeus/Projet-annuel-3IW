<section class="hero dashboard-card">
    <p class="eyebrow">Espace personnel</p>
    <h1>Bienvenue<?= !empty($user['full_name']) ? ', ' . htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8') : '' ?>.</h1>
    <p class="lead">
        Voici un aperçu de votre situation financière actuelle.
    </p>
    <div class="stats-grid">
        <article class="stat">
            <span class="stat-label">Comptes</span>
            <strong><?= $nb_accounts ?? 0 ?></strong>
        </article>
        <article class="stat">
            <span class="stat-label">Dépenses</span>
            <strong><?= $nb_expenses ?? 0 ?></strong>
        </article>
        <article class="stat">
            <span class="stat-label">Revenus</span>
            <strong><?= $nb_incomes ?? 0 ?></strong>
        </article>
    </div>
    <div class="actions" style="margin-top: 2rem;">
        <a href="/?page=accounts" class="button">Gérer mes comptes</a>
        <a href="/?page=previsions" class="button button-secondary">Voir les prévisions</a>
    </div>
</section>