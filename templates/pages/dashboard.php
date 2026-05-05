<section class="hero dashboard-card">
    <p class="eyebrow">Espace personnel</p>
    <h1>Bienvenue<?= !empty($user['full_name']) ? ', ' . htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8') : '' ?>.</h1>
    <p class="lead">
        Cette zone sera connectée à la vraie base de données pour gérer les comptes, dépenses, revenus et prévisions.
    </p>

    <div class="stats-grid">
        <article class="stat">
            <span class="stat-label">Comptes</span>
            <strong>0</strong>
        </article>
        <article class="stat">
            <span class="stat-label">Dépenses</span>
            <strong>0</strong>
        </article>
        <article class="stat">
            <span class="stat-label">Revenus</span>
            <strong>0</strong>
        </article>
    </div>
</section>