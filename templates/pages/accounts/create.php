<section class="section">
    <div class="section-header">
        <div>
            <p class="eyebrow">Nouveau compte</p>
            <h1>Créer un compte.</h1>
        </div>
        <a class="button button-secondary" href="/?page=accounts">← Retour aux comptes</a>
    </div>

    <?php if (!empty($error)) : ?>
        <p class="notice notice-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <article class="detail-card">
        <form method="post" action="/?page=accounts" class="detail-form">
            <label>
                Nom court
                <input type="text" name="short_name" required placeholder="Ex. Compte courant">
            </label>

            <label>
                Description
                <textarea name="description" required placeholder="Ex. Compte courant individuel"></textarea>
            </label>

            <label>
                Taux de rémunération (%)
                <input type="number" name="interest_rate" step="0.01" min="0" max="100" value="0">
            </label>

            <label>
                Taux d'imposition (%)
                <input type="number" name="tax_rate" step="0.01" min="0" max="100" value="0">
            </label>

            <button class="button" type="submit">Créer le compte</button>
        </form>
    </article>
</section>
