<div class="container" style="max-width: 400px; margin-top: 50px;">
    <h1>Réinitialiser votre mot de passe</h1>

    <?php 
    $token = $_GET['token'] ?? '';
    if (empty($token)): ?>
        <div class="alert alert-danger">Token invalide.</div>
        <p><a href="/?page=login">Retour à la connexion</a></p>
    <?php else: ?>

        <?php if ($error): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success" role="alert">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="/?page=reset-password">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

            <div class="form-group mb-3">
                <label for="password">Nouveau mot de passe</label>
                <input type="password" class="form-control" id="password" name="password" 
                       placeholder="Min 8 car, 1 maj, 1 min, 1 chiffre, 1 spécial" required>
                <small class="form-text text-muted">
                    Au moins 8 caractères, 1 majuscule, 1 minuscule, 1 chiffre et 1 caractère spécial (@$!%*?&)
                </small>
            </div>

            <div class="form-group mb-3">
                <label for="password_confirm">Confirmer le mot de passe</label>
                <input type="password" class="form-control" id="password_confirm" name="password_confirm" 
                       placeholder="Répétez le mot de passe" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Réinitialiser le mot de passe</button>
        </form>

        <p class="mt-3 text-center">
            <a href="/?page=login">Retour à la connexion</a>
        </p>

    <?php endif; ?>
</div>
