<section class="section">
    <div class="section-header">
        <div>
            <p class="eyebrow">Administration</p>
            <h1>Tableau de bord admin.</h1>
        </div>
    </div>
    <?php if (!empty($success)) : ?>
        <p class="notice notice-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <?php if (!empty($error)) : ?>
        <p class="notice notice-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <div class="stats-grid" style="margin-bottom: 32px;">
        <article class="stat">
            <span class="stat-label">Utilisateurs total</span>
            <strong><?= (int) ($stats['total_users'] ?? 0) ?></strong>
        </article>
        <article class="stat">
            <span class="stat-label">Comptes premium</span>
            <strong><?= (int) ($stats['premium_users'] ?? 0) ?></strong>
        </article>
        <article class="stat">
            <span class="stat-label">Comptes bancaires</span>
            <strong><?= (int) ($stats['total_accounts'] ?? 0) ?></strong>
        </article>
        <article class="stat">
            <span class="stat-label">Dépenses enregistrées</span>
            <strong><?= (int) ($stats['total_expenses'] ?? 0) ?></strong>
        </article>
        <article class="stat">
            <span class="stat-label">Revenus enregistrés</span>
            <strong><?= (int) ($stats['total_incomes'] ?? 0) ?></strong>
        </article>
    </div>
</section>
<section class="section" style="padding-top: 0;">
    <div class="section-header">
        <div>
            <p class="eyebrow">Utilisateurs</p>
            <h2>Gestion des utilisateurs</h2>
        </div>
    </div>
    <?php if (empty($users)) : ?>
        <p class="empty-state">Aucun utilisateur trouvé.</p>
    <?php else : ?>
        <article class="detail-card">
            <div class="forecast-table-wrap">
                <table class="forecast-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Email</th>
                            <th>Nom</th>
                            <th>Plan</th>
                            <th>Actif</th>
                            <th>Inscrit le</th>
                            <th>Comptes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u) : ?>
                            <tr>
                                <td><?= (int) $u['id'] ?></td>
                                <td><?= htmlspecialchars((string) $u['email'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) $u['full_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <?php if ($u['plan'] === 'paid') : ?>
                                        <span class="premium-badge">PRO</span>
                                    <?php else : ?>
                                        Gratuit
                                    <?php endif; ?>
                                </td>
                                <td><?= $u['is_active'] ? 'OUI' : 'NON' ?></td>
                                <td><?= htmlspecialchars((string) ($u['created_at'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= (int) ($u['nb_accounts'] ?? 0) ?></td>
                                <td style="display:flex; gap:6px; flex-wrap:wrap;">
                                    <?php if ((string) $u['email'] !== (string) ($current_admin_email ?? '')) : ?>
                                        <?php if ($u['plan'] === 'paid') : ?>
                                            <form method="post" action="/?page=admin"><?= CsrfHelper::field() ?>
                                                <input type="hidden" name="action" value="set-plan">
                                                <input type="hidden" name="target_email" value="<?= htmlspecialchars((string) $u['email'], ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="plan" value="free">
                                                <button class="button button-secondary" type="submit">Rétrograder</button>
                                            </form>
                                        <?php else : ?>
                                            <form method="post" action="/?page=admin"><?= CsrfHelper::field() ?>
                                                <input type="hidden" name="action" value="set-plan">
                                                <input type="hidden" name="target_email" value="<?= htmlspecialchars((string) $u['email'], ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="plan" value="paid">
                                                <button class="button" type="submit">Mettre premium</button>
                                            </form>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <?php if ((string) $u['email'] !== (string) ($current_admin_email ?? '')) : ?>
                                        <form method="post" action="/?page=admin" onsubmit="return confirm('Confirmer ?')"><?= CsrfHelper::field() ?>
                                            <input type="hidden" name="action" value="toggle-active">
                                            <input type="hidden" name="target_email" value="<?= htmlspecialchars((string) $u['email'], ENT_QUOTES, 'UTF-8') ?>">
                                            <button class="button button-secondary" type="submit">
                                                <?= $u['is_active'] ? 'Désactiver' : 'Activer' ?>
                                            </button>
                                        </form>
                                        <form method="post" action="/?page=admin" onsubmit="return confirm('Supprimer cet utilisateur ?')"><?= CsrfHelper::field() ?>
                                            <input type="hidden" name="action" value="delete-user">
                                            <input type="hidden" name="target_email" value="<?= htmlspecialchars((string) $u['email'], ENT_QUOTES, 'UTF-8') ?>">
                                            <button class="button button-danger" type="submit">Supprimer</button>
                                        </form>
                                    <?php else : ?>
                                        <span>Vous</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </article>
    <?php endif; ?>
</section>
