<section class="hero">
    <p class="eyebrow">Budgie</p>
    <h1>Ton partenaire financier personnel.</h1>
    <p class="lead">
        Une base simple pour suivre comptes, dépenses, revenus et prévisions sans connexion bancaire directe.
    </p>
    <div class="actions">
        <?php if (!empty($user)) : ?>
            <a class="button" href="/?page=dashboard">Voir l'espace perso</a>
            <a class="button button-secondary" href="/?page=logout">Se déconnecter</a>
        <?php else : ?>
            <a class="button" href="/?page=login">Se connecter</a>
            <a class="button button-secondary" href="/?page=signup">Créer un compte</a>
        <?php endif; ?>
    </div>
</section>
