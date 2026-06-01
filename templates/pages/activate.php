<h1>Activation du compte</h1>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <p><a href="/?page=login">Retour à la connexion</a></p>
<?php elseif ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <p><a href="/?page=login">Aller à la connexion</a></p>
<?php else: ?>
    <p>Vérification de votre lien d'activation...</p>
<?php endif; ?>
