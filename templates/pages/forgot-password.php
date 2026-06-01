<div class="container" style="max-width: 400px; margin-top: 50px;">
    <h1>Réinitialiser votre mot de passe</h1>

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

    <form method="POST" action="/?page=forgot-password">
        <div class="form-group mb-3">
            <label for="email">Adresse email</label>
            <input type="email" class="form-control" id="email" name="email" 
                   placeholder="vous@example.com" required autocomplete="email">
        </div>

        <button type="submit" class="btn btn-primary w-100">Envoyer le lien de réinitialisation</button>
    </form>

    <p class="mt-3 text-center">
        <a href="/?page=login">Retour à la connexion</a>
    </p>
</div>
