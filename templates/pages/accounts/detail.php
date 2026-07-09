<section class="section">
    <div class="section-header">
        <div>
            <p class="eyebrow">Compte</p>
            <h1><?= htmlspecialchars($account['short_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></h1>
        </div>
        <a class="button button-secondary" href="/?page=accounts">← Retour aux comptes</a>
    </div>

    <?php if (!empty($success)) : ?>
        <p class="notice notice-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if (!empty($error)) : ?>
        <p class="notice notice-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if (empty($is_owner)) : ?>
        <p class="notice">Ce compte vous a été partagé. Vous y avez accès en lecture seule.</p>
    <?php endif; ?>

    <div class="stats-grid" style="margin-bottom: 24px;">
        <article class="stat">
            <span class="stat-label">Solde actuel</span>
            <strong><?= number_format((float) ($account['balance'] ?? 0), 2, ',', ' ') ?> €</strong>
        </article>
        <article class="stat">
            <span class="stat-label">Solde initial</span>
            <strong><?= number_format((float) ($account['initial_balance'] ?? 0), 2, ',', ' ') ?> €</strong>
        </article>
        <article class="stat">
            <span class="stat-label">Taux de rémunération</span>
            <strong><?= number_format((float) ($account['interest_rate'] ?? 0), 2, ',', ' ') ?> %</strong>
        </article>
        <article class="stat">
            <span class="stat-label">Taux d'imposition</span>
            <strong><?= number_format((float) ($account['tax_rate'] ?? 0), 2, ',', ' ') ?> %</strong>
        </article>
    </div>

    <div class="detail-grid">
        <article class="detail-card">
            <h2>Informations</h2>
            <?php if (!empty($is_owner)) : ?>
            <form method="post" action="/?page=account&id=<?= $account['id'] ?>" class="detail-form">
                <input type="hidden" name="action" value="update">

                <label>
                    Nom court
                    <input type="text" name="short_name" required value="<?= htmlspecialchars($account['short_name'], ENT_QUOTES, 'UTF-8') ?>">
                </label>

                <label>
                    Description
                    <textarea name="description" required><?= htmlspecialchars($account['description'], ENT_QUOTES, 'UTF-8') ?></textarea>
                </label>

                <label>
                    Solde initial (€)
                    <input type="number" name="initial_balance" step="0.01" value="<?= number_format((float) ($account['initial_balance'] ?? $account['balance'] ?? 0), 2, '.', '') ?>">
                </label>

                <label>
                    Taux de rémunération (%)
                    <input type="number" name="interest_rate" step="0.01" min="0" max="100" value="<?= $account['interest_rate'] ?>">
                </label>

                <label>
                    Taux d'imposition (%)
                    <input type="number" name="tax_rate" step="0.01" min="0" max="100" value="<?= $account['tax_rate'] ?>">
                </label>

                <button class="button" type="submit">Mettre à jour</button>
            </form>
            <?php else : ?>
                <dl>
                    <dt>Nom court</dt>
                    <dd><?= htmlspecialchars($account['short_name'], ENT_QUOTES, 'UTF-8') ?></dd>
                    <dt>Description</dt>
                    <dd><?= htmlspecialchars($account['description'], ENT_QUOTES, 'UTF-8') ?></dd>
                    <dt>Taux de rémunération</dt>
                    <dd><?= number_format((float) $account['interest_rate'], 2, ',', ' ') ?> %</dd>
                    <dt>Taux d'imposition</dt>
                    <dd><?= number_format((float) $account['tax_rate'], 2, ',', ' ') ?> %</dd>
                </dl>
            <?php endif; ?>
        </article>

        <?php if (!empty($is_owner)) : ?>
        <article class="detail-card">
            <h2>Partage</h2>
            <p>Invitez quelqu'un à consulter ce compte en lecture seule.</p>
            <form method="post" action="/?page=account&id=<?= $account['id'] ?>" class="detail-form">
                <input type="hidden" name="action" value="share">
                <label>
                    Adresse email
                    <input type="email" name="invited_email" required placeholder="ami@exemple.com">
                </label>
                <button class="button" type="submit">Envoyer l'invitation</button>
            </form>

            <?php if (!empty($shares)) : ?>
                <div class="forecast-table-wrap" style="margin-top: 16px;">
                    <table class="forecast-table">
                        <thead>
                            <tr>
                                <th>Email</th>
                                <th>Statut</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($shares as $share) : ?>
                                <tr>
                                    <td><?= htmlspecialchars($share['invited_email'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= $share['status'] === 'accepted' ? 'Accepté' : 'En attente' ?></td>
                                    <td>
                                        <form method="post" action="/?page=account&id=<?= $account['id'] ?>" onsubmit="return confirm('Révoquer ce partage ?')">
                                            <input type="hidden" name="action" value="share-revoke">
                                            <input type="hidden" name="share_id" value="<?= $share['id'] ?>">
                                            <button class="button button-secondary" type="submit">Révoquer</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else : ?>
                <p class="empty-state">Ce compte n'est partagé avec personne.</p>
            <?php endif; ?>
        </article>

        <article class="detail-card danger">
            <h2>Danger</h2>
            <p>Une fois supprimé, le compte et tous ses données (dépenses, revenus) seront perdus.</p>
            <form method="post" action="/?page=account&id=<?= $account['id'] ?>" class="danger-form">
                <input type="hidden" name="action" value="delete">
                <button class="button button-danger" type="submit" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce compte ?')">Supprimer le compte</button>
            </form>
        </article>
        <?php endif; ?>
    </div>
</section>

<section class="section" data-search-section>
    <div class="section-header">
        <div>
            <p class="eyebrow">Dépenses</p>
            <h2>Liste des dépenses liées</h2>
        </div>
        <?php if (!empty($is_owner)) : ?>
        <a class="button" href="/?page=expense-create&account_id=<?= $account['id'] ?>">+ Nouvelle dépense</a>
        <?php endif; ?>
    </div>

    <article class="detail-card" style="margin-bottom: 24px;">
        <form method="get" action="/" class="search-form js-search-form" style="display: flex; gap: 8px; flex-wrap: wrap; align-items: flex-end;">
            <input type="hidden" name="page" value="account">
            <input type="hidden" name="id" value="<?= $account['id'] ?>">
            <label style="flex: 1; min-width: 240px;">
                Recherche dépenses
                <input type="search" class="js-search-input" name="q" value="<?= htmlspecialchars($search_query ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Nom court ou description" style="width: 100%; padding: 10px; margin-top: 6px; border: 1px solid #ccc; border-radius: 6px;">
            </label>
            <button class="button" type="submit" style="padding: 10px 16px;">Chercher</button>
        </form>
    </article>

    <?php if (empty($expenses)) : ?>
        <p class="empty-state">Aucune dépense pour ce compte.</p>
    <?php else : ?>
        <div class="accounts-grid js-search-list">
            <?php foreach ($expenses as $exp) : ?>
                <article class="account-card">
                    <h3><?= htmlspecialchars($exp['short_name'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <p><?= htmlspecialchars($exp['description'], ENT_QUOTES, 'UTF-8') ?></p>
                    <div class="card-body">
                        <dl>
                            <dt>Montant</dt>
                            <dd class="balance"><?= number_format($exp['amount'], 2, ',', ' ') ?> €</dd>
                            <dt>Fréquence</dt>
                            <dd><?= htmlspecialchars($exp['frequency'], ENT_QUOTES, 'UTF-8') ?> <?= $exp['frequency_months'] ? '(' . $exp['frequency_months'] . ' mois)' : '' ?></dd>
                            <dt>Début</dt>
                            <dd><?= htmlspecialchars($exp['start_date'], ENT_QUOTES, 'UTF-8') ?></dd>
                        </dl>
                    </div>
                    <div class="card-footer">
                        <a class="link" href="/?page=expense&id=<?= $exp['id'] ?>">Voir / Éditer</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="show-more-wrapper js-show-more hidden">
            <button class="button" type="button">Voir plus</button>
        </div>

        <p class="empty-state js-search-empty hidden">Aucune dépense trouvée.</p>
    <?php endif; ?>
</section>

<section class="section" data-search-section>
    <div class="section-header">
        <div>
            <p class="eyebrow">Revenus</p>
            <h2>Liste des revenus liés</h2>
        </div>
        <?php if (!empty($is_owner)) : ?>
        <a class="button" href="/?page=income-create&account_id=<?= $account['id'] ?>">+ Nouveau revenu</a>
        <?php endif; ?>
    </div>

    <article class="detail-card" style="margin-bottom: 24px;">
        <label style="display: flex; gap: 8px; flex-wrap: wrap; align-items: flex-end;">
            <span style="flex: 1; min-width: 240px;">
                Recherche revenus
                <input type="search" class="js-search-input" placeholder="Nom court ou description" style="width: 100%; padding: 10px; margin-top: 6px; border: 1px solid #ccc; border-radius: 6px;">
            </span>
        </label>
    </article>

    <?php if (empty($incomes)) : ?>
        <p class="empty-state">Aucun revenu pour ce compte.</p>
    <?php else : ?>
        <div class="accounts-grid js-search-list">
            <?php foreach ($incomes as $inc) : ?>
                <article class="account-card">
                    <h3><?= htmlspecialchars($inc['short_name'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <p><?= htmlspecialchars($inc['description'], ENT_QUOTES, 'UTF-8') ?></p>
                    <div class="card-body">
                        <dl>
                            <dt>Montant</dt>
                            <dd class="balance"><?= number_format($inc['amount'], 2, ',', ' ') ?> €</dd>
                            <dt>Fréquence</dt>
                            <dd><?= htmlspecialchars($inc['frequency'], ENT_QUOTES, 'UTF-8') ?> <?= $inc['frequency_months'] ? '(' . $inc['frequency_months'] . ' mois)' : '' ?></dd>
                            <dt>Début</dt>
                            <dd><?= htmlspecialchars($inc['start_date'], ENT_QUOTES, 'UTF-8') ?></dd>
                        </dl>
                    </div>
                    <div class="card-footer">
                        <a class="link" href="/?page=income&id=<?= $inc['id'] ?>">Voir / Éditer</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="show-more-wrapper js-show-more hidden">
            <button class="button" type="button">Voir plus</button>
        </div>

        <p class="empty-state js-search-empty hidden">Aucun revenu trouvé.</p>
    <?php endif; ?>
</section>