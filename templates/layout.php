<?php
declare(strict_types=1);
$isAuthenticated = !empty($user);
$displayName = $isAuthenticated ? ($user['full_name'] ?? $user['email'] ?? 'Utilisateur') : null;
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
            <?php if ($isAuthenticated) : ?>
                <a href="/?page=dashboard">Espace perso</a>
                <a href="/?page=accounts">Comptes</a>
                <a href="/?page=previsions">Previsions</a>
                <a href="/?page=profile">Profil</a>
                <?php if (($user['plan'] ?? 'free') !== 'paid') : ?>
                    <a href="/?page=subscriptions">Devenir premium</a>
                <?php endif; ?>
                <span class="topbar-user">
                    Connecté : <?= htmlspecialchars((string) $displayName, ENT_QUOTES, 'UTF-8') ?>
                    <?php if (($user['plan'] ?? 'free') === 'paid') : ?>
                        <span class="premium-badge" title="Compte premium">PRO</span>
                    <?php endif; ?>
                </span>
                <a href="/?page=logout">Déconnexion</a>
            <?php else : ?>
                <a href="/?page=login">Connexion</a>
            <?php endif; ?>
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

        document.querySelectorAll('[data-frequency-select]').forEach(function (select) {
            const form = select.closest('form');
            const monthsField = form?.querySelector('[data-frequency-months]');
            const monthsInput = monthsField?.querySelector('input[name="frequency_months"]');
            const endDateField = form?.querySelector('[data-frequency-enddate]');

            function updateFrequency() {
                const isPonctuel = select.value === 'ponctuel';
                const needsMonths = select.value === 'periodic';

                if (monthsField && monthsInput) {
                    monthsField.classList.toggle('hidden', !needsMonths);
                    monthsInput.required = needsMonths;
                    monthsInput.disabled = !needsMonths;
                    if (!needsMonths) {
                        monthsInput.value = '';
                    }
                }

                if (endDateField) {
                    endDateField.classList.toggle('hidden', isPonctuel);
                    const endDateInput = endDateField.querySelector('input');
                    if (endDateInput) {
                        endDateInput.disabled = isPonctuel;
                        if (isPonctuel) endDateInput.value = '';
                    }
                }
            }

            select.addEventListener('change', updateFrequency);
            updateFrequency();
        });
    });
    </script>
</body>
</html>
