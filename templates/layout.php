<?php
declare(strict_types=1);
?><!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
    <header class="topbar">
        <a class="brand" href="/?page=home">Budgie</a>
        <nav class="topbar-nav">
            <a href="/?page=home">Accueil</a>
            <a href="/?page=dashboard">Espace perso</a>
            <a href="/?page=login">Connexion</a>
            <a href="/?page=logout">Déconnexion</a>
        </nav>
    </header>
    <main class="shell">
        <?= $content ?>
    </main>
</body>
</html>
