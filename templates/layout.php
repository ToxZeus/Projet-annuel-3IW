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
            <a href="/?page=accounts">Comptes</a>
            <a href="/?page=previsions">Previsions</a>
            <a href="/?page=login">Connexion</a>
            <a href="/?page=logout">Déconnexion</a>
        </nav>
    </header>
    <main class="shell">
        <?= $content ?>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const LIST_LIMIT = 4;

        document.querySelectorAll('[data-search-section]').forEach(function (section) {
            const form = section.querySelector('.js-search-form');
            const searchInput = section.querySelector('input[type="search"].js-search-input') || form?.querySelector('input[type="search"]');
            const list = section.querySelector('.js-search-list');
            const cards = Array.from(list?.querySelectorAll('.account-card') || []);
            const showMoreWrapper = section.querySelector('.js-show-more');
            const showMoreButton = showMoreWrapper?.querySelector('button');
            const emptyMessage = section.querySelector('.js-search-empty');

            if (!searchInput || !list || cards.length === 0 || !showMoreButton || !emptyMessage) {
                return;
            }

            function updateButtonState(expanded, hasMore) {
                showMoreButton.dataset.expanded = expanded.toString();
                showMoreButton.textContent = expanded ? 'Voir moins' : 'Voir plus';
                showMoreWrapper.classList.toggle('hidden', !hasMore);
            }

            function renderList() {
                const query = searchInput.value.trim().toLowerCase();
                const matching = cards.filter(function (card) {
                    const text = (card.textContent || '').toLowerCase();
                    return query === '' || text.includes(query);
                });

                const hasMore = matching.length > LIST_LIMIT;
                let expanded = showMoreButton.dataset.expanded === 'true';
                if (!hasMore) {
                    expanded = false;
                }

                cards.forEach(function (card) {
                    card.classList.add('hidden');
                });

                matching.forEach(function (card, index) {
                    if (expanded || index < LIST_LIMIT) {
                        card.classList.remove('hidden');
                    }
                });

                updateButtonState(expanded, hasMore);

                emptyMessage.classList.toggle('hidden', matching.length !== 0);
                list.classList.toggle('hidden', matching.length === 0);
            }

            showMoreButton.addEventListener('click', function () {
                const expanded = showMoreButton.dataset.expanded === 'true';
                updateButtonState(!expanded, true);
                renderList();
            });

            searchInput.addEventListener('input', function () {
                if (showMoreButton.dataset.expanded === 'true') {
                    showMoreButton.dataset.expanded = 'false';
                }
                renderList();
            });

            showMoreButton.dataset.expanded = 'false';
            renderList();
        });
    });
    </script>
</body>
</html>
