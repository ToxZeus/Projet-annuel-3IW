<?php
declare(strict_types=1);

/**
 * Seeder de démo pour la soutenance.
 * Usage : php bin/seed-demo.php
 *
 * Ré-exécutable : chaque compte @budgie.local listé ci-dessous est supprimé
 * (avec ses comptes/dépenses/revenus/exceptions/partages en cascade) puis
 * recréé à l'identique, pour repartir d'un état propre et connu le jour J.
 */

define('BASE_PATH', dirname(__DIR__));

$envFile = BASE_PATH . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value, " \t\n\r\0\x0B\"'");
        putenv(trim($key) . '=' . trim($value, " \t\n\r\0\x0B\"'"));
    }
}

require BASE_PATH . '/src/Database.php';
require BASE_PATH . '/src/UserService.php';
require BASE_PATH . '/src/AccountService.php';
require BASE_PATH . '/src/ExpenseService.php';
require BASE_PATH . '/src/IncomeService.php';
require BASE_PATH . '/src/ExceptionService.php';
require BASE_PATH . '/src/ShareService.php';

$db = new Database(BASE_PATH . '/data/budgie.db');
$db->init();

$userService      = new UserService($db);
$accountService   = new AccountService($db);
$expenseService   = new ExpenseService($db);
$incomeService    = new IncomeService($db);
$exceptionService = new ExceptionService($db);
$shareService     = new ShareService($db);

$appUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost:8080', '/');

const PWD_ADMIN    = 'AdminDemo2026!';
const PWD_FREE     = 'DemoFree2026!';
const PWD_PREMIUM  = 'DemoPremium2026!';
const PWD_INACTIVE = 'Inactive2026!';
const PWD_TRASH    = 'Trash2026!';

$demoEmails = [
    'admin@budgie.local',
    'demo@budgie.local',
    'premium@budgie.local',
    'inactive@budgie.local',
    'trash@budgie.local',
];

echo "== Nettoyage des anciennes données de démo ==\n";
foreach ($demoEmails as $email) {
    $existing = $userService->findByEmail($email);
    if ($existing === null) {
        continue;
    }
    foreach ($accountService->findByUser($email) as $account) {
        $accountService->delete((int) $account['id']);
    }
    $db->exec('DELETE FROM account_shares WHERE owner_email = ? OR invited_email = ?', [$email, $email]);
    $db->exec('DELETE FROM users WHERE email = ?', [$email]);
    echo "  - supprimé : $email\n";
}

/**
 * Crée un utilisateur actif (UserService::create force is_active=false, on l'active ensuite).
 */
function seedUser(UserService $userService, Database $db, string $email, string $fullName, string $password, string $plan, bool $active, bool $admin): void
{
    $userService->create($email, $fullName, $password, '', '', $plan);
    if ($active) {
        $db->exec('UPDATE users SET is_active = 1 WHERE email = ?', [$email]);
    }
    if ($admin) {
        $db->exec('UPDATE users SET is_admin = 1 WHERE email = ?', [$email]);
    }
}

echo "\n== Création des comptes utilisateurs ==\n";

seedUser($userService, $db, 'admin@budgie.local', 'Admin Budgie', PWD_ADMIN, 'paid', true, true);
echo "  - admin@budgie.local (admin, premium, actif)\n";

seedUser($userService, $db, 'demo@budgie.local', 'Utilisateur Démo', PWD_FREE, 'free', true, false);
echo "  - demo@budgie.local (gratuit, actif, au maximum du quota)\n";

seedUser($userService, $db, 'premium@budgie.local', 'Utilisateur Premium', PWD_PREMIUM, 'paid', true, false);
echo "  - premium@budgie.local (premium, actif, quotas dépassés)\n";

seedUser($userService, $db, 'inactive@budgie.local', 'Compte Inactif', PWD_INACTIVE, 'free', false, false);
echo "  - inactive@budgie.local (gratuit, NON activé)\n";

seedUser($userService, $db, 'trash@budgie.local', 'Compte À Supprimer', PWD_TRASH, 'free', true, false);
echo "  - trash@budgie.local (gratuit, actif, à supprimer en live)\n";

// ---------------------------------------------------------------------
// demo@budgie.local — utilisateur FREE pile au quota (2 comptes / 7 dépenses / 2 revenus)
// ---------------------------------------------------------------------
echo "\n== Données : demo@budgie.local (FREE, au quota) ==\n";

$accCourant = $accountService->create('demo@budgie.local', 'Compte courant', 'Compte principal pour dépenses journalières', 0.5, 12, 1500.0);
$accEpargne = $accountService->create('demo@budgie.local', 'Épargne', 'Compte d\'épargne pour objectif futur', 2.0, 30, 800.0);

$expenseIds = [];
foreach ([
    ['Loyer appartement', 'Paiement du loyer mensuel', 850.00, 'mensuel', null, '2026-01-01', null],
    ['Facture électricité', 'Paiement de la facture mensuelle d\'électricité', 78.90, 'mensuel', null, '2026-01-01', null],
    ['Abonnement internet', 'Frais mensuels internet et télévision', 29.99, 'mensuel', null, '2026-01-01', null],
    ['Facture mobile', 'Abonnement téléphonique mensuel', 19.90, 'mensuel', null, '2026-01-01', null],
    ['Assurance habitation', 'Paiement mensuel de l\'assurance maison', 12.50, 'mensuel', null, '2026-01-01', null],
    ['Abonnement salle de sport', 'Frais mensuels de la salle de sport', 35.00, 'mensuel', null, '2026-01-01', null],
    ['Courses supermarché', 'Achats alimentaires et ménagers', 120.50, 'ponctuel', null, '2026-07-05', null],
] as $e) {
    $expenseIds[$e[0]] = $expenseService->create($accCourant, $e[0], $e[1], $e[2], $e[3], $e[4], $e[5], $e[6]);
}

$incomeIds = [];
foreach ([
    ['Salaire', 'Virement de salaire mensuel', 2400.00, 'mensuel', null, '2026-01-01', null],
    ['Prime annuelle', 'Prime versée par l\'employeur', 500.00, 'ponctuel', null, '2026-06-15', null],
] as $i) {
    $incomeIds[$i[0]] = $incomeService->create($accCourant, $i[0], $i[1], $i[2], $i[3], $i[4], $i[5], $i[6]);
}

// Exception ponctuelle : grosse facture d'électricité en août (canicule / clim)
$exceptionService->create('expense', $expenseIds['Facture électricité'], 'Facture électricité (canicule)', 'Surconsommation climatisation', 145.00, 'ponctuel', null, '2026-08-01', null);

// Compte Épargne : volontairement sous le quota (3 dépenses / 1 revenu) pour montrer qu'on peut encore en ajouter
foreach ([
    ['Transfert épargne', 'Virement automatique mensuel vers l\'épargne', 200.00, 'mensuel', null, '2026-01-01', null],
    ['Frais de tenue de compte', 'Frais bancaires mensuels', 2.50, 'mensuel', null, '2026-01-01', null],
    ['Achat livret', 'Frais d\'ouverture produit d\'épargne', 50.00, 'ponctuel', null, '2026-03-10', null],
] as $e) {
    $expenseService->create($accEpargne, $e[0], $e[1], $e[2], $e[3], $e[4], $e[5], $e[6]);
}
$incomeService->create($accEpargne, 'Intérêts trimestriels', 'Intérêts versés chaque trimestre', 15.00, 'periodique', 3, '2026-01-01', null);

echo "  - 2 comptes (quota atteint), 'Compte courant' = 7 dépenses / 2 revenus (quota atteint), 'Épargne' sous le quota\n";
echo "  - 1 exception ponctuelle (facture électricité août)\n";

// ---------------------------------------------------------------------
// premium@budgie.local — utilisateur PAID, quotas largement dépassés
// ---------------------------------------------------------------------
echo "\n== Données : premium@budgie.local (PREMIUM, illimité) ==\n";

$pCourant = $accountService->create('premium@budgie.local', 'Compte courant', 'Compte principal', 0.3, 15, 3200.0);
$pEpargne = $accountService->create('premium@budgie.local', 'Épargne', 'Épargne de précaution', 2.5, 30, 5400.0);
$pInvest  = $accountService->create('premium@budgie.local', 'Investissement', 'Portefeuille investissement', 4.0, 30, 12000.0);
$pPro     = $accountService->create('premium@budgie.local', 'Compte pro', 'Activité freelance', 0.0, 20, 900.0);

$pExpenseIds = [];
foreach ([
    ['Loyer', 'Paiement du loyer mensuel', 1100.00, 'mensuel', null, '2026-01-01', null],
    ['Facture électricité', 'Facture mensuelle d\'électricité', 95.00, 'mensuel', null, '2026-01-01', null],
    ['Facture eau', 'Facture mensuelle d\'eau', 35.00, 'mensuel', null, '2026-01-01', null],
    ['Internet/TV', 'Abonnement box internet', 45.00, 'mensuel', null, '2026-01-01', null],
    ['Mobile', 'Abonnement téléphonique', 25.00, 'mensuel', null, '2026-01-01', null],
    ['Assurance auto', 'Prime d\'assurance semestrielle', 480.00, 'periodique', 6, '2026-02-01', null],
    ['Courses', 'Courses alimentaires mensuelles', 380.00, 'mensuel', null, '2026-01-01', null],
    ['Essence', 'Carburant mensuel', 150.00, 'mensuel', null, '2026-01-01', null],
    ['Abonnement salle de sport', 'Abonnement salle de sport', 40.00, 'mensuel', null, '2026-01-01', null],
    ['Restaurant', 'Sortie restaurant', 60.00, 'ponctuel', null, '2026-07-12', null],
] as $e) {
    $pExpenseIds[$e[0]] = $expenseService->create($pCourant, $e[0], $e[1], $e[2], $e[3], $e[4], $e[5], $e[6]);
}

$pIncomeIds = [];
foreach ([
    ['Salaire', 'Virement de salaire mensuel', 3800.00, 'mensuel', null, '2026-01-01', null],
    ['Freelance', 'Mission freelance', 600.00, 'ponctuel', null, '2026-07-01', null],
    ['Remboursement mutuelle', 'Remboursement frais de santé', 45.00, 'ponctuel', null, '2026-06-20', null],
    ['Prime trimestrielle', 'Prime versée chaque trimestre', 300.00, 'periodique', 3, '2026-01-01', null],
] as $i) {
    $pIncomeIds[$i[0]] = $incomeService->create($pCourant, $i[0], $i[1], $i[2], $i[3], $i[4], $i[5], $i[6]);
}

foreach ([
    ['Frais de tenue de compte', 'Frais bancaires mensuels', 3.00, 'mensuel', null, '2026-01-01', null],
    ['Versement automatique', 'Virement épargne automatique', 400.00, 'mensuel', null, '2026-01-01', null],
    ['Frais divers', 'Frais de gestion', 8.00, 'ponctuel', null, '2026-04-15', null],
    ['Frais de dossier', 'Frais liés à un placement', 20.00, 'ponctuel', null, '2026-05-02', null],
    ['Assurance épargne', 'Assurance produit d\'épargne', 6.50, 'mensuel', null, '2026-01-01', null],
] as $e) {
    $expenseService->create($pEpargne, $e[0], $e[1], $e[2], $e[3], $e[4], $e[5], $e[6]);
}
foreach ([
    ['Intérêts', 'Intérêts versés chaque trimestre', 45.00, 'periodique', 3, '2026-01-01', null],
    ['Virement extra', 'Virement exceptionnel', 500.00, 'ponctuel', null, '2026-03-20', null],
    ['Prime épargne salariale', 'Participation employeur', 250.00, 'ponctuel', null, '2026-05-10', null],
] as $i) {
    $incomeService->create($pEpargne, $i[0], $i[1], $i[2], $i[3], $i[4], $i[5], $i[6]);
}

foreach ([
    ['Frais de gestion', 'Frais de gestion mensuels du portefeuille', 25.00, 'mensuel', null, '2026-01-01', null],
    ['Frais de courtage', 'Frais liés à un ordre de bourse', 10.00, 'ponctuel', null, '2026-06-05', null],
    ['Frais d\'entrée', 'Frais d\'entrée sur nouveau placement', 100.00, 'ponctuel', null, '2026-02-20', null],
] as $e) {
    $expenseService->create($pInvest, $e[0], $e[1], $e[2], $e[3], $e[4], $e[5], $e[6]);
}
$pIncomeIds['Dividendes'] = $incomeService->create($pInvest, 'Dividendes', 'Dividendes versés chaque trimestre', 220.00, 'periodique', 3, '2026-01-01', null);
$incomeService->create($pInvest, 'Plus-value', 'Vente d\'une ligne avec plus-value', 800.00, 'ponctuel', null, '2026-06-30', null);

foreach ([
    ['Frais bancaires pro', 'Frais de tenue de compte professionnel', 15.00, 'mensuel', null, '2026-01-01', null],
    ['Logiciel de facturation', 'Abonnement outil de facturation', 12.00, 'mensuel', null, '2026-01-01', null],
] as $e) {
    $expenseService->create($pPro, $e[0], $e[1], $e[2], $e[3], $e[4], $e[5], $e[6]);
}
$incomeService->create($pPro, 'Facture client', 'Paiement d\'une mission freelance', 1200.00, 'ponctuel', null, '2026-07-10', null);

// Exceptions couvrant les 3 fréquences, sur une dépense ET un revenu
$exceptionService->create('expense', $pExpenseIds['Loyer'], 'Loyer renégocié', 'Nouveau loyer après négociation', 1050.00, 'mensuel', null, '2026-09-01', null);
$exceptionService->create('expense', $pExpenseIds['Courses'], 'Courses (rentrée)', 'Gros arrivage de rentrée', 520.00, 'ponctuel', null, '2026-08-01', null);
$exceptionService->create('income', $pIncomeIds['Prime trimestrielle'], 'Prime trimestrielle revalorisée', 'Nouvelle grille de prime', 350.00, 'periodique', 3, '2026-04-01', null);

echo "  - 4 comptes (au-delà du quota gratuit de 2)\n";
echo "  - 'Compte courant' = 10 dépenses / 4 revenus (au-delà des quotas 7 / 2)\n";
echo "  - 3 exceptions : mensuelle (loyer), ponctuelle (courses), périodique (prime)\n";

// Partage de comptes : un déjà accepté, un en attente (pour démo live)
$shareService->invite($pCourant, 'premium@budgie.local', 'demo@budgie.local');
$acceptedShare = $db->fetch(
    "SELECT token FROM account_shares WHERE account_id = ? AND invited_email = ? ORDER BY id DESC LIMIT 1",
    [$pCourant, 'demo@budgie.local']
);
$shareService->accept((string) $acceptedShare['token'], 'demo@budgie.local');

$pendingToken = $shareService->invite($pEpargne, 'premium@budgie.local', 'demo@budgie.local');

echo "  - 'Compte courant' partagé avec demo@budgie.local (déjà accepté)\n";
echo "  - 'Épargne' partagé avec demo@budgie.local (INVITATION EN ATTENTE, voir lien ci-dessous)\n";

// ---------------------------------------------------------------------
// trash@budgie.local — juste de quoi prouver la suppression en cascade
// ---------------------------------------------------------------------
$trashAcc = $accountService->create('trash@budgie.local', 'Compte test', 'Compte de test à supprimer', 0, 0, 100.0);
$expenseService->create($trashAcc, 'Dépense test', 'Dépense de test', 10.00, 'ponctuel', null, '2026-07-01', null);

echo "\n== Terminé ==\n";
echo "\nComptes disponibles pour la soutenance :\n";
printf("  %-24s %-22s %s\n", 'Email', 'Mot de passe', 'Rôle');
printf("  %-24s %-22s %s\n", 'admin@budgie.local', PWD_ADMIN, 'Admin + Premium');
printf("  %-24s %-22s %s\n", 'demo@budgie.local', PWD_FREE, 'Gratuit, au quota');
printf("  %-24s %-22s %s\n", 'premium@budgie.local', PWD_PREMIUM, 'Premium, quotas dépassés');
printf("  %-24s %-22s %s\n", 'inactive@budgie.local', PWD_INACTIVE, 'Non activé (démo admin)');
printf("  %-24s %-22s %s\n", 'trash@budgie.local', PWD_TRASH, 'À supprimer en démo admin');

echo "\nLien d'invitation en attente (compte 'Épargne' de premium@budgie.local, à accepter en étant connecté en demo@budgie.local) :\n";
echo "  $appUrl/?page=share-accept&token=$pendingToken\n";
