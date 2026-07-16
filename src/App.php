<?php
declare(strict_types=1);

final class App
{
    private const FREE_ACCOUNT_LIMIT = 2;
    private const FREE_EXPENSE_LIMIT_PER_ACCOUNT = 7;
    private const FREE_INCOME_LIMIT_PER_ACCOUNT = 2;

    private Database $db;
    private UserService $userService;
    private AccountService $accountService;
    private ExpenseService $expenseService;
    private IncomeService $incomeService;
    private ExceptionService $exceptionService;
    private ShareService $shareService;

    public function __construct()
    {
        $this->db = new Database(BASE_PATH . '/data/budgie.db');
        $this->db->init();
        $this->userService = new UserService($this->db);
        $this->accountService = new AccountService($this->db);
        $this->expenseService = new ExpenseService($this->db);
        $this->incomeService = new IncomeService($this->db);
        $this->exceptionService = new ExceptionService($this->db);
        $this->shareService = new ShareService($this->db);
        $this->seedDemoExpenses();
    }

    public function run(): void
    {
        $page = $_GET['page'] ?? 'home';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$this->isValidCsrfToken()) {
            http_response_code(403);
            echo 'Requête invalide (jeton CSRF manquant ou expiré).';
            exit;
        }

        if ($page === 'signup' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleSignup();
            return;
        }

        if ($page === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleLogin();
            return;
        }

        if ($page === 'profile' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleProfilePost();
            return;
        }

        if ($page === 'subscription-checkout' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleSubscriptionCheckout();
            return;
        }

        if ($page === 'subscription-cancel' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleSubscriptionCancel();
            return;
        }

        if ($page === 'subscription-success') {
            $this->handleSubscriptionSuccess();
            return;
        }

        if ($page === 'logout') {
            $this->handleLogout();
            return;
        }

        if ($page === 'activate') {
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $this->handleActivateGet();
            }
            return;
        }

        if ($page === 'forgot-password' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleForgotPasswordPost();
            return;
        }

        if ($page === 'reset-password' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleResetPasswordPost();
            return;
        }

        if ($page === 'exception-create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleExceptionCreate();
            return;
        }

        if ($page === 'exception' && isset($_GET['id']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? null;
            if ($action === 'update') {
                $this->handleExceptionUpdate((int) $_GET['id']);
                return;
            } elseif ($action === 'delete') {
                $this->handleExceptionDelete((int) $_GET['id']);
                return;
            }
        }

        if ($page === 'admin' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleAdminPost();
            return;
        }

        if ($page === 'accounts' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleAccountCreate();
            return;
        }

        if ($page === 'account' && isset($_GET['id']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? null;
            if ($action === 'update') {
                $this->handleAccountUpdate((int) $_GET['id']);
                return;
            } elseif ($action === 'delete') {
                $this->handleAccountDelete((int) $_GET['id']);
                return;
            } elseif ($action === 'share') {
                $this->handleAccountShareInvite((int) $_GET['id']);
                return;
            } elseif ($action === 'share-revoke') {
                $this->handleAccountShareRevoke((int) $_GET['id']);
                return;
            }
        }

        if ($page === 'share-accept' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleShareAccept();
            return;
        }
        if ($page === 'expenses' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleExpenseCreate();
            return;
        }

        if ($page === 'expense' && isset($_GET['id']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? null;
            if ($action === 'update') {
                $this->handleExpenseUpdate((int) $_GET['id']);
                return;
            } elseif ($action === 'delete') {
                $this->handleExpenseDelete((int) $_GET['id']);
                return;
            }
        }

        if ($page === 'incomes' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleIncomeCreate();
            return;
        }

        if ($page === 'income' && isset($_GET['id']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? null;
            if ($action === 'update') {
                $this->handleIncomeUpdate((int) $_GET['id']);
                return;
            } elseif ($action === 'delete') {
                $this->handleIncomeDelete((int) $_GET['id']);
                return;
            }
        }

        $routes = [
            'home' => [
                'title' => 'Budgie | Ton partenaire financier personnel',
                'template' => 'pages/home.php',
            ],
            'signup' => [
                'title' => 'Budgie | Inscription',
                'template' => 'pages/signup.php',
            ],
            'login' => [
                'title' => 'Budgie | Connexion',
                'template' => 'pages/login.php',
            ],
            'activate' => [
                'title' => 'Budgie | Activation du compte',
                'template' => 'pages/activate.php',
            ],
            'forgot-password' => [
                'title' => 'Budgie | Mot de passe oublié',
                'template' => 'pages/forgot-password.php',
            ],
            'reset-password' => [
                'title' => 'Budgie | Réinitialiser le mot de passe',
                'template' => 'pages/reset-password.php',
            ],
            'subscriptions' => [
                'title' => 'Budgie | Abonnements',
                'template' => 'pages/subscriptions.php',
            ],
            'profile' => [
                'title' => 'Budgie | Compte',
                'template' => 'pages/profile.php',
            ],
            'dashboard' => [
                'title' => 'Budgie | Tableau de bord',
                'template' => 'pages/dashboard.php',
            ],
            'accounts' => [
                'title' => 'Budgie | Comptes',
                'template' => 'pages/accounts/list.php',
            ],
            'account' => [
                'title' => 'Budgie | Compte',
                'template' => 'pages/accounts/detail.php',
            ],
            'account-create' => [
                'title' => 'Budgie | Nouveau compte',
                'template' => 'pages/accounts/create.php',
            ],
            'expenses' => [
                'title' => 'Budgie | Dépenses',
                'template' => 'pages/expenses/list.php',
            ],
            'expense' => [
                'title' => 'Budgie | Dépense',
                'template' => 'pages/expenses/detail.php',
            ],
            'expense-create' => [
                'title' => 'Budgie | Nouvelle dépense',
                'template' => 'pages/expenses/create.php',
            ],
            'incomes' => [
                'title' => 'Budgie | Revenus',
                'template' => 'pages/incomes/list.php',
            ],
            'income' => [
                'title' => 'Budgie | Revenu',
                'template' => 'pages/incomes/detail.php',
            ],
            'income-create' => [
                'title' => 'Budgie | Nouveau revenu',
                'template' => 'pages/incomes/create.php',
            ],
            'previsions' => [
                'title' => 'Budgie | Prévisions',
                'template' => 'pages/previsions.php',
            ],
            'admin' => [
                'title' => 'Budgie | Administration',
                'template' => 'pages/admin.php',
            ],
            'exception-create' => [
                'title' => 'Budgie | Nouvelle exception',
                'template' => 'pages/exceptions/create.php',
            ],
            'exception' => [
                'title' => 'Budgie | Exception',
                'template' => 'pages/exceptions/detail.php',
            ],
            'share-accept' => [
                'title' => 'Budgie | Invitation de partage',
                'template' => 'pages/share-accept.php',
            ],
        ];

        if (!isset($routes[$page])) {
            http_response_code(404);
            $page = 'home';
        }

        $route = $routes[$page];

    if (in_array($page, ['dashboard', 'accounts', 'account', 'account-create', 'expenses', 'expense', 'expense-create', 'incomes', 'income', 'income-create', 'previsions', 'subscriptions', 'profile', 'admin', 'exception', 'exception-create', 'share-accept']) && !$this->isAuthenticated()) {
            if ($page === 'share-accept' && isset($_GET['token'])) {
                $_SESSION['pending_share_token'] = (string) $_GET['token'];
                $_SESSION['flash_error'] = 'Connectez-vous (ou inscrivez-vous avec cette même adresse email) pour accepter l\'invitation.';
            }
            header('Location: /?page=login');
            exit;
        }

        if ($page === 'admin' && !($this->currentUser()['is_admin'] ?? false)) {
            http_response_code(403);
            echo $this->renderLayout(
                'Budgie | Accès refusé',
                '<section class="section"><p class="notice notice-error">Accès refusé. Vous n\'êtes pas administrateur.</p></section>',
                $this->currentUser()
            );
            return;
        }

        if ($page === 'account' && !isset($_GET['id'])) {
            http_response_code(400);
            return;
        }

        if ($page === 'expense' && !isset($_GET['id'])) {
            http_response_code(400);
            return;
        }

        if ($page === 'income' && !isset($_GET['id'])) {
            http_response_code(400);
            return;
        }

        $data = [
            'page' => $page,
            'user' => $this->currentUser(),
            'error' => $_SESSION['flash_error'] ?? null,
            'success' => $_SESSION['flash_success'] ?? null,
        ];

        if ($this->isAuthenticated()) {
            $data['user'] = $this->userService->findByEmail($this->currentUser()['email']) ?? $this->currentUser();
        }

        if ($page === 'dashboard') {
            $accounts = $this->accountService->findByUser($this->currentUser()['email']);
            $accounts = $this->withComputedBalances($accounts);
            $totalExpenses = 0;
            $totalIncomes  = 0;
            foreach ($accounts as $acc) {
                $totalExpenses += count($this->expenseService->findByAccount($acc['id']));
                $totalIncomes  += count($this->incomeService->findByAccount($acc['id']));
            }
            $data['nb_accounts'] = count($accounts);
            $data['nb_expenses'] = $totalExpenses;
            $data['nb_incomes']  = $totalIncomes;
} elseif ($page === 'accounts') {
            $data['accounts'] = $this->withComputedBalances($this->accountService->findByUser($this->currentUser()['email']));
            $data['shared_accounts'] = $this->withComputedBalances(array_map(
                fn (array $row): array => [
                    'id' => $row['account_id'],
                    'short_name' => $row['short_name'],
                    'description' => $row['description'],
                    'balance' => $row['balance'],
                    'interest_rate' => $row['interest_rate'],
                    'tax_rate' => $row['tax_rate'],
                    'created_at' => $row['account_created_at'],
                    'owner_email' => $row['owner_email'],
                ],
                $this->shareService->findAccountsSharedWithUser($this->currentUser()['email'])
            ));
        } elseif ($page === 'account' && isset($_GET['id'])) {
            $account = $this->accountService->findById((int) $_GET['id']);
            $isOwner = $account !== null && $account['user_email'] === $this->currentUser()['email'];
            $hasSharedAccess = !$isOwner && $account !== null
                && $this->shareService->hasAcceptedAccess($account['id'], $this->currentUser()['email']);

            if (!$account || (!$isOwner && !$hasSharedAccess)) {
                http_response_code(404);
                return;
            }
            $account = $this->withComputedBalance($account);
            $data['is_owner'] = $isOwner;
            if ($isOwner) {
                $data['shares'] = $this->shareService->findByAccount($account['id']);
            }
            $searchQuery = trim((string) ($_GET['q'] ?? ''));
            $expenses = $this->expenseService->findByAccount($account['id']);
            if ($searchQuery !== '') {
                $expenses = array_values(array_filter($expenses, function (array $expense) use ($searchQuery) {
                    return stripos((string) $expense['short_name'], $searchQuery) !== false
                        || stripos((string) $expense['description'], $searchQuery) !== false;
                }));
            }

            $searchQueryIncome = trim((string) ($_GET['qr'] ?? ''));
            $incomes = $this->incomeService->findByAccount($account['id']);
            if ($searchQueryIncome !== '') {
                $incomes = array_values(array_filter($incomes, function (array $income) use ($searchQueryIncome) {
                    return stripos((string) $income['short_name'], $searchQueryIncome) !== false
                        || stripos((string) $income['description'], $searchQueryIncome) !== false;
                }));
            }

            $data['account'] = $account;
            $data['expenses'] = $expenses;
            $data['incomes'] = $incomes;
            $data['search_query'] = $searchQuery;
            $data['search_query_income'] = $searchQueryIncome;
        } elseif ($page === 'expenses') {
            $allExpenses = $this->expenseService->findByUser($this->currentUser()['email']);
            $searchQuery = trim((string) ($_GET['q'] ?? ''));
            if ($searchQuery !== '') {
                $allExpenses = array_values(array_filter($allExpenses, function (array $expense) use ($searchQuery) {
                    return stripos((string) $expense['short_name'], $searchQuery) !== false
                        || stripos((string) $expense['description'], $searchQuery) !== false;
                }));
            }
            $data['expenses'] = $allExpenses;
            $data['search_query'] = $searchQuery;
       } elseif ($page === 'expense' && isset($_GET['id'])) {
            $expense = $this->expenseService->findById((int) $_GET['id']);
            if (!$expense) {
                http_response_code(404);
                return;
            }
            $account = $this->accountService->findById((int) $expense['account_id']);
            $isOwner = $account !== null && $account['user_email'] === $this->currentUser()['email'];
            $hasSharedAccess = !$isOwner && $account !== null
                && $this->shareService->hasAcceptedAccess($account['id'], $this->currentUser()['email']);
            if (!$account || (!$isOwner && !$hasSharedAccess)) {
                http_response_code(404);
                return;
            }
            $data['expense'] = $expense;
            $data['account'] = $account;
            $data['is_owner'] = $isOwner;
            $data['exceptions'] = $this->exceptionService->findByEntity('expense', (int) $expense['id']);
        } elseif ($page === 'expense-create' && isset($_GET['account_id'])) {
            $account = $this->accountService->findById((int) $_GET['account_id']);
            if (!$account || $account['user_email'] !== $this->currentUser()['email']) {
                http_response_code(404);
                return;
            }
            $data['account'] = $account;
        } elseif ($page === 'incomes') {
            $data['incomes'] = $this->incomeService->findByUser($this->currentUser()['email']);
       } elseif ($page === 'income' && isset($_GET['id'])) {
            $income = $this->incomeService->findById((int) $_GET['id']);
            if (!$income) {
                http_response_code(404);
                return;
            }
            $account = $this->accountService->findById((int) $income['account_id']);
            $isOwner = $account !== null && $account['user_email'] === $this->currentUser()['email'];
            $hasSharedAccess = !$isOwner && $account !== null
                && $this->shareService->hasAcceptedAccess($account['id'], $this->currentUser()['email']);
            if (!$account || (!$isOwner && !$hasSharedAccess)) {
                http_response_code(404);
                return;
            }
            $data['income'] = $income;
            $data['account'] = $account;
            $data['is_owner'] = $isOwner;
            $data['exceptions'] = $this->exceptionService->findByEntity('income', (int) $income['id']);
        } elseif ($page === 'income-create' && isset($_GET['account_id'])) {
            $account = $this->accountService->findById((int) $_GET['account_id']);
            if (!$account || $account['user_email'] !== $this->currentUser()['email']) {
                http_response_code(404);
                return;
            }
            $data['account'] = $account;
        } elseif ($page === 'previsions') {
            $selectedMonth = trim((string) ($_GET['month'] ?? date('Y-m')));
            if (!preg_match('/^\d{4}-\d{2}$/', $selectedMonth)) {
                $selectedMonth = date('Y-m');
            }

            $accounts = $this->accountService->findByUser($this->currentUser()['email']);
            $forecastRows = [];
            $totals = [
                'start_balance' => 0.0,
                'incomes' => 0.0,
                'expenses' => 0.0,
                'interest' => 0.0,
                'projected_balance' => 0.0,
            ];

            foreach ($accounts as $account) {
                $forecast = $this->buildMonthlyForecast($account, $selectedMonth);
                $forecastRows[] = $forecast;
                $totals['start_balance'] += $forecast['start_balance'];
                $totals['incomes'] += $forecast['incomes'];
                $totals['expenses'] += $forecast['expenses'];
                $totals['interest'] += $forecast['interest'];
                $totals['projected_balance'] += $forecast['projected_balance'];
            }

            $chartLabels = [];
            $chartBalances = [];
            $chartIncomes = [];
            $chartExpenses = [];
            $monthStart = DateTimeImmutable::createFromFormat('Y-m-d', $selectedMonth . '-01');
            for ($i = 0; $i < 12; $i++) {
                $m = $monthStart->modify("+{$i} months")->format('Y-m');
                $monthTotals = ['projected_balance' => 0.0, 'incomes' => 0.0, 'expenses' => 0.0];
                foreach ($accounts as $account) {
                    $f = $this->buildMonthlyForecast($account, $m);
                    $monthTotals['projected_balance'] += $f['projected_balance'];
                    $monthTotals['incomes'] += $f['incomes'];
                    $monthTotals['expenses'] += $f['expenses'];
                }
                $chartLabels[] = $m;
                $chartBalances[] = round($monthTotals['projected_balance'], 2);
                $chartIncomes[] = round($monthTotals['incomes'], 2);
                $chartExpenses[] = round($monthTotals['expenses'], 2);
            }

            $data['selected_month'] = $selectedMonth;
            $data['forecast_rows'] = $forecastRows;
            $data['totals'] = $totals;
            $data['chart_labels'] = $chartLabels;
            $data['chart_balances'] = $chartBalances;
            $data['chart_incomes'] = $chartIncomes;
            $data['chart_expenses'] = $chartExpenses;
        } elseif ($page === 'exception-create') {
            $type     = trim((string) ($_GET['type'] ?? 'expense'));
            $entityId = (int) ($_GET['entity_id'] ?? 0);
            $data['entity_type'] = $type;
            $data['entity_id']   = $entityId;
        } elseif ($page === 'exception' && isset($_GET['id'])) {
            $exception = $this->exceptionService->findById((int) $_GET['id']);
            if (!$exception) {
                http_response_code(404);
                return;
            }
            $owned = $exception['entity_type'] === 'income'
                ? $this->incomeService->findById((int) $exception['entity_id'])
                : $this->expenseService->findById((int) $exception['entity_id']);
            $account = $owned ? $this->accountService->findById((int) $owned['account_id']) : null;
            if (!$account || $account['user_email'] !== $this->currentUser()['email']) {
                http_response_code(403);
                return;
            }
            $data['exception'] = $exception;
        } elseif ($page === 'admin') {
            $allUsers = $this->db->fetchAll(
                'SELECT u.*, (SELECT COUNT(*) FROM accounts a WHERE a.user_email = u.email) AS nb_accounts
                FROM users u ORDER BY u.created_at DESC'
            );
            $stats = [
                'total_users'    => count($allUsers),
                'premium_users'  => count(array_filter($allUsers, fn($u) => ($u['plan'] ?? 'free') === 'paid')),
                'total_accounts' => (int) ($this->db->fetch('SELECT COUNT(*) AS n FROM accounts')['n'] ?? 0),
                'total_expenses' => (int) ($this->db->fetch('SELECT COUNT(*) AS n FROM expenses')['n'] ?? 0),
                'total_incomes'  => (int) ($this->db->fetch('SELECT COUNT(*) AS n FROM incomes')['n'] ?? 0),
            ];
           $data['users']               = $allUsers;
            $data['stats']               = $stats;
            $data['current_admin_email'] = $this->currentUser()['email'];
        } elseif ($page === 'share-accept') {
            $token = trim((string) ($_GET['token'] ?? ''));
            $share = $token !== '' ? $this->shareService->findByToken($token) : null;
            $data['token'] = $token;
            $data['share'] = $share;
            if ($share !== null) {
                $data['share_account'] = $this->accountService->findById((int) $share['account_id']);
            }
        }
        $oldInputKey = $this->oldInputKey($page);
        $data['old'] = $_SESSION['old_input'][$oldInputKey] ?? [];

        $content = $this->render($route['template'], $data);

        unset($_SESSION['flash_error'], $_SESSION['flash_success']);
        unset($_SESSION['old_input'][$oldInputKey]);

        echo $this->renderLayout($route['title'], $content, $data['user']);
    }

    private function handleSignup(): void
    {
        $email = ValidationHelper::cleanEmail($_POST['email'] ?? '');
        $fullName = ValidationHelper::cleanName($_POST['full_name'] ?? '');
        $password = (string) ($_POST['password'] ?? '');
        $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');
        $plan = 'free';

        $this->flashOldInput('signup', ['email' => $email, 'full_name' => $fullName]);

        if (empty($email) || empty($fullName) || empty($password)) {
            $_SESSION['flash_error'] = 'Tous les champs sont obligatoires.';
            header('Location: /?page=signup');
            exit;
        }

        if (!ValidationHelper::validateEmail($email)) {
            $_SESSION['flash_error'] = 'Email invalide.';
            header('Location: /?page=signup');
            exit;
        }

        if (!ValidationHelper::validatePassword($password)) {
            $_SESSION['flash_error'] = 'Le mot de passe doit avoir au moins 8 caractères, 1 majuscule, 1 minuscule, 1 chiffre et 1 caractère spécial.';
            header('Location: /?page=signup');
            exit;
        }

        if ($password !== $passwordConfirm) {
            $_SESSION['flash_error'] = 'Les mots de passe ne correspondent pas.';
            header('Location: /?page=signup');
            exit;
        }

        if ($this->userService->existsByEmail($email)) {
            $_SESSION['flash_error'] = 'Cet email est déjà utilisé.';
            header('Location: /?page=signup');
            exit;
        }

        try {
            $verificationToken = bin2hex(random_bytes(32));
            $tokenExpiry = date('Y-m-d H:i:s', strtotime('+24 hours'));

            $this->userService->create($email, $fullName, $password, $verificationToken, $tokenExpiry, $plan);

            EmailHelper::sendActivation($email, explode(' ', $fullName)[0], $verificationToken);
            unset($_SESSION['old_input']['signup']);

            $_SESSION['flash_success'] = 'Inscription réussie. Veuillez confirmer votre adresse email en cliquant sur le lien reçu.';
            header('Location: /?page=login');
            exit;
        } catch (Exception $e) {
            error_log('Signup error: ' . $e->getMessage());
            $_SESSION['flash_error'] = 'Erreur lors de l\'inscription.';
            header('Location: /?page=signup');
            exit;
        }
    }

    private function handleLogin(): void
    {
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        $attemptsKey = 'login_attempts_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
        $_SESSION[$attemptsKey] ??= ['count' => 0, 'until' => 0];

        if ($_SESSION[$attemptsKey]['until'] > time()) {
            $_SESSION['flash_error'] = 'Trop de tentatives. Réessayez dans quelques minutes.';
            header('Location: /?page=login');
            exit;
        }

        $user = $this->userService->verifyCredentials($email, $password);

        if ($user === null) {
            $_SESSION[$attemptsKey]['count']++;
            if ($_SESSION[$attemptsKey]['count'] >= 5) {
                $_SESSION[$attemptsKey]['until'] = time() + 900;
                $_SESSION[$attemptsKey]['count'] = 0;
            }
            $_SESSION['flash_error'] = 'Identifiants invalides.';
            $this->flashOldInput('login', ['email' => $email]);
            header('Location: /?page=login');
            exit;
        }

        unset($_SESSION[$attemptsKey]);
        session_regenerate_id(true);

        $_SESSION['user'] = [
            'email' => $user['email'],
            'full_name' => $user['full_name'],
            'plan' => $user['plan'] ?? 'free',
            'is_admin' => (bool) ($user['is_admin'] ?? false),
            'stripe_customer_id' => $user['stripe_customer_id'] ?? null,
            'stripe_subscription_id' => $user['stripe_subscription_id'] ?? null,
        ];
        $_SESSION['flash_success'] = 'Connexion réussie.';

        if (!empty($_SESSION['pending_share_token'])) {
            $token = $_SESSION['pending_share_token'];
            unset($_SESSION['pending_share_token']);
            header('Location: /?page=share-accept&token=' . urlencode($token));
            exit;
        }

        header('Location: /?page=dashboard');
        exit;
    }

    private function handleLogout(): void
    {
        $_SESSION = [];
        session_destroy();
        session_start();
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['flash_success'] = 'Déconnexion réussie.';
        header('Location: /?page=login');
        exit;
    }

    private function handleProfilePost(): void
    {
        if (!$this->isAuthenticated()) {
            $_SESSION['flash_error'] = 'Vous devez être connecté.';
            header('Location: /?page=login');
            exit;
        }

        $action = $_POST['action'] ?? '';
        if ($action === 'update-name') {
            $fullName = ValidationHelper::cleanName($_POST['full_name'] ?? '');
            if ($fullName === '') {
                $_SESSION['flash_error'] = 'Le nom est obligatoire.';
                header('Location: /?page=profile');
                exit;
            }

            $storedUser = $this->userService->findByEmail($this->currentUser()['email']);
            if ($storedUser === null) {
                $_SESSION['flash_error'] = 'Utilisateur introuvable.';
                header('Location: /?page=profile');
                exit;
            }

            $this->userService->update((int) $storedUser['id'], $fullName);
            $_SESSION['user']['full_name'] = $fullName;
            $_SESSION['flash_success'] = 'Nom mis à jour.';
            header('Location: /?page=profile');
            exit;
        }

        if ($action === 'update-password') {
            $currentPassword = (string) ($_POST['current_password'] ?? '');
            $password = (string) ($_POST['password'] ?? '');
            $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');

            if (!$this->userService->verifyPassword($this->currentUser()['email'], $currentPassword)) {
                $_SESSION['flash_error'] = 'Mot de passe actuel incorrect.';
                header('Location: /?page=profile');
                exit;
            }

            if (!ValidationHelper::validatePassword($password)) {
                $_SESSION['flash_error'] = 'Le nouveau mot de passe doit avoir au moins 8 caractères, 1 majuscule, 1 minuscule, 1 chiffre et 1 caractère spécial.';
                header('Location: /?page=profile');
                exit;
            }

            if ($password !== $passwordConfirm) {
                $_SESSION['flash_error'] = 'Les mots de passe ne correspondent pas.';
                header('Location: /?page=profile');
                exit;
            }

            $this->userService->updatePasswordByEmail($this->currentUser()['email'], $password);
            $_SESSION['flash_success'] = 'Mot de passe mis à jour.';
            header('Location: /?page=profile');
            exit;
        }

        $_SESSION['flash_error'] = 'Action inconnue.';
        header('Location: /?page=profile');
        exit;
    }

    private function handleSubscriptionCheckout(): void
    {
        if (!$this->isAuthenticated()) {
            $_SESSION['flash_error'] = 'Vous devez être connecté.';
            header('Location: /?page=login');
            exit;
        }

        if (!$this->isFreeUser()) {
            $_SESSION['flash_success'] = 'Votre compte est déjà premium.';
            header('Location: /?page=subscriptions');
            exit;
        }

        $secretKey = getenv('STRIPE_SECRET_KEY') ?: '';
        $priceId = getenv('STRIPE_PREMIUM_PRICE_ID') ?: '';
        $appUrl = rtrim(getenv('APP_URL') ?: $this->baseUrl(), '/');

        if ($secretKey === '' || $priceId === '') {
            $_SESSION['flash_error'] = 'Stripe n\'est pas configuré. Renseignez STRIPE_SECRET_KEY et STRIPE_PREMIUM_PRICE_ID.';
            header('Location: /?page=subscriptions');
            exit;
        }

        $session = $this->createStripeCheckoutSession($secretKey, [
            'mode' => 'subscription',
            'line_items[0][price]' => $priceId,
            'line_items[0][quantity]' => '1',
            'customer_email' => $this->currentUser()['email'],
            'client_reference_id' => $this->currentUser()['email'],
            'success_url' => $appUrl . '/?page=subscription-success&session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $appUrl . '/?page=subscriptions',
        ]);

        if (!isset($session['url'])) {
            $_SESSION['flash_error'] = $session['error'] ?? 'Impossible de créer la session Stripe.';
            header('Location: /?page=subscriptions');
            exit;
        }

        header('Location: ' . $session['url']);
        exit;
    }

    private function handleSubscriptionSuccess(): void
    {
        if (!$this->isAuthenticated()) {
            $_SESSION['flash_error'] = 'Vous devez être connecté.';
            header('Location: /?page=login');
            exit;
        }

        $secretKey = getenv('STRIPE_SECRET_KEY') ?: '';
        $sessionId = trim((string) ($_GET['session_id'] ?? ''));

        if ($secretKey === '' || $sessionId === '') {
            $_SESSION['flash_error'] = 'Validation Stripe impossible.';
            header('Location: /?page=subscriptions');
            exit;
        }

        $session = $this->retrieveStripeCheckoutSession($secretKey, $sessionId);
        if (($session['payment_status'] ?? '') !== 'paid' || ($session['client_reference_id'] ?? '') !== $this->currentUser()['email']) {
            $_SESSION['flash_error'] = 'Paiement Stripe non confirmé.';
            header('Location: /?page=subscriptions');
            exit;
        }

        $this->userService->updatePlan($this->currentUser()['email'], 'paid');
        $this->userService->updateStripeSubscription(
            $this->currentUser()['email'],
            isset($session['customer']) ? (string) $session['customer'] : null,
            isset($session['subscription']) ? (string) $session['subscription'] : null
        );
        $_SESSION['user']['plan'] = 'paid';
        $_SESSION['user']['stripe_customer_id'] = $session['customer'] ?? null;
        $_SESSION['user']['stripe_subscription_id'] = $session['subscription'] ?? null;
        $_SESSION['flash_success'] = 'Votre compte est maintenant premium.';
        header('Location: /?page=profile');
        exit;
    }

    private function handleSubscriptionCancel(): void
    {
        if (!$this->isAuthenticated()) {
            $_SESSION['flash_error'] = 'Vous devez être connecté.';
            header('Location: /?page=login');
            exit;
        }

        $storedUser = $this->userService->findByEmail($this->currentUser()['email']);
        if (($storedUser['plan'] ?? 'free') !== 'paid') {
            $_SESSION['flash_success'] = 'Votre compte est déjà en formule gratuite.';
            header('Location: /?page=profile');
            exit;
        }

        $subscriptionId = (string) ($storedUser['stripe_subscription_id'] ?? '');
        $secretKey = getenv('STRIPE_SECRET_KEY') ?: '';
        if ($subscriptionId !== '' && $secretKey !== '') {
            $response = $this->cancelStripeSubscription($secretKey, $subscriptionId);
            if (isset($response['error'])) {
                $_SESSION['flash_error'] = $response['error'];
                header('Location: /?page=profile');
                exit;
            }
        }

        $this->userService->updatePlan($this->currentUser()['email'], 'free');
        $this->userService->clearStripeSubscription($this->currentUser()['email']);
        $_SESSION['user']['plan'] = 'free';
        $_SESSION['user']['stripe_subscription_id'] = null;
        $_SESSION['flash_success'] = 'Abonnement premium résilié. Votre compte est repassé en gratuit.';
        header('Location: /?page=profile');
        exit;
    }

    private function handleActivateGet(): void
    {
        $token = trim((string) ($_GET['token'] ?? ''));

        if (empty($token)) {
            $_SESSION['flash_error'] = 'Token invalide.';
            header('Location: /?page=login');
            exit;
        }

        if ($this->userService->activateAccount($token)) {
            $_SESSION['flash_success'] = 'Votre compte a été activé. Vous pouvez maintenant vous connecter.';
            header('Location: /?page=login');
            exit;
        }

        $_SESSION['flash_error'] = 'Le lien d\'activation a expiré ou est invalide.';
        header('Location: /?page=login');
        exit;
    }

    private function handleForgotPasswordPost(): void
    {
        $email = ValidationHelper::cleanEmail($_POST['email'] ?? '');
        $this->flashOldInput('forgot-password', ['email' => $email]);

        if (empty($email)) {
            $_SESSION['flash_error'] = 'Veuillez entrer votre adresse email.';
            header('Location: /?page=forgot-password');
            exit;
        }

        $result = $this->userService->generateResetToken($email);
        if ($result) {
            $user = $this->userService->findByEmail($email);
            EmailHelper::sendPasswordReset($email, explode(' ', $user['full_name'])[0], $result['reset_token']);
        }

        $_SESSION['flash_success'] = 'Si cette adresse email existe, un lien de réinitialisation a été envoyé.';
        header('Location: /?page=login');
        exit;
    }

    private function handleResetPasswordPost(): void
    {
        $token = trim((string) ($_POST['token'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');

        if (empty($token) || empty($password) || empty($passwordConfirm)) {
            $_SESSION['flash_error'] = 'Tous les champs sont obligatoires.';
            header('Location: /?page=reset-password&token=' . urlencode($token));
            exit;
        }

        if (!ValidationHelper::validatePassword($password)) {
            $_SESSION['flash_error'] = 'Le mot de passe doit avoir au moins 8 caractères, 1 majuscule, 1 minuscule, 1 chiffre et 1 caractère spécial.';
            header('Location: /?page=reset-password&token=' . urlencode($token));
            exit;
        }

        if ($password !== $passwordConfirm) {
            $_SESSION['flash_error'] = 'Les mots de passe ne correspondent pas.';
            header('Location: /?page=reset-password&token=' . urlencode($token));
            exit;
        }

        if ($this->userService->resetPassword($token, $password)) {
            $_SESSION['flash_success'] = 'Votre mot de passe a été réinitialisé. Vous pouvez maintenant vous connecter.';
            header('Location: /?page=login');
            exit;
        }

        $_SESSION['flash_error'] = 'Le lien de réinitialisation a expiré ou est invalide.';
        header('Location: /?page=forgot-password');
        exit;
    }

    private function handleExceptionCreate(): void
    {
        if (!$this->isAuthenticated()) {
            http_response_code(403);
            exit;
        }

        $type        = trim((string) ($_POST['entity_type'] ?? 'expense'));
        $entityId    = (int) ($_POST['entity_id'] ?? 0);

        $owned = $type === 'income'
            ? $this->incomeService->findById($entityId)
            : $this->expenseService->findById($entityId);
        if (!$owned) {
            http_response_code(404);
            exit;
        }
        $account = $this->accountService->findById((int) $owned['account_id']);
        if (!$account || $account['user_email'] !== $this->currentUser()['email']) {
            http_response_code(403);
            exit;
        }

        $name        = trim((string) ($_POST['name'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $amount      = (float) ($_POST['amount'] ?? 0);
        $frequency   = trim((string) ($_POST['frequency'] ?? 'ponctuel'));
        $frequencyMonths = isset($_POST['frequency_months']) && $_POST['frequency_months'] !== '' ? (int) $_POST['frequency_months'] : null;
        $startDate   = trim((string) ($_POST['start_date'] ?? date('Y-m-d')));
        $endDate     = trim((string) ($_POST['end_date'] ?? '')) ?: null;

        if (empty($name) || $amount <= 0) {
            $_SESSION['flash_error'] = 'Le nom et le montant sont obligatoires.';
            header('Location: /?page=exception-create&type=' . $type . '&entity_id=' . $entityId);
            exit;
        }

        $this->exceptionService->create($type, $entityId, $name, $description, $amount, $frequency, $frequencyMonths, $startDate, $endDate);
        $_SESSION['flash_success'] = 'Exception créée avec succès.';
        $redirectPage = $type === 'income' ? 'income' : 'expense';
        header('Location: /?page=' . $redirectPage . '&id=' . $entityId);
        exit;
    }

    private function handleExceptionUpdate(int $id): void
    {
        if (!$this->isAuthenticated()) {
            http_response_code(403);
            exit;
        }

        $exception = $this->exceptionService->findById($id);
        if (!$exception) {
            http_response_code(404);
            exit;
        }

        $owned = $exception['entity_type'] === 'income'
            ? $this->incomeService->findById((int) $exception['entity_id'])
            : $this->expenseService->findById((int) $exception['entity_id']);
        $account = $owned ? $this->accountService->findById((int) $owned['account_id']) : null;
        if (!$account || $account['user_email'] !== $this->currentUser()['email']) {
            http_response_code(403);
            exit;
        }

        $name        = trim((string) ($_POST['name'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $amount      = (float) ($_POST['amount'] ?? 0);
        $frequency   = trim((string) ($_POST['frequency'] ?? 'ponctuel'));
        $frequencyMonths = isset($_POST['frequency_months']) && $_POST['frequency_months'] !== '' ? (int) $_POST['frequency_months'] : null;
        $startDate   = trim((string) ($_POST['start_date'] ?? date('Y-m-d')));
        $endDate     = trim((string) ($_POST['end_date'] ?? '')) ?: null;
        if (empty($name) || $amount <= 0) {
            $_SESSION['flash_error'] = 'Le nom et le montant sont obligatoires.';
            header('Location: /?page=exception&id=' . $id);
            exit;
        }

        $this->exceptionService->update($id, $name, $description, $amount, $frequency, $frequencyMonths, $startDate, $endDate);
        $_SESSION['flash_success'] = 'Exception mise à jour.';
        header('Location: /?page=exception&id=' . $id);
        exit;
    }

    private function handleExceptionDelete(int $id): void
    {
        if (!$this->isAuthenticated()) {
            http_response_code(403);
            exit;
        }

        $exception = $this->exceptionService->findById($id);
        if (!$exception) {
            http_response_code(404);
            exit;
        }

        $type     = $exception['entity_type'];
        $entityId = $exception['entity_id'];

        $owned = $type === 'income'
            ? $this->incomeService->findById((int) $entityId)
            : $this->expenseService->findById((int) $entityId);
        $account = $owned ? $this->accountService->findById((int) $owned['account_id']) : null;
        if (!$account || $account['user_email'] !== $this->currentUser()['email']) {
            http_response_code(403);
            exit;
        }

        $this->exceptionService->delete($id);
        $_SESSION['flash_success'] = 'Exception supprimée.';
        $redirectPage = $type === 'income' ? 'income' : 'expense';
        header('Location: /?page=' . $redirectPage . '&id=' . $entityId);
        exit;
    }

    private function handleAdminPost(): void
    {
        if (!$this->isAuthenticated() || !($this->currentUser()['is_admin'] ?? false)) {
            http_response_code(403);
            exit;
        }

        $action      = trim((string) ($_POST['action'] ?? ''));
        $targetEmail = trim((string) ($_POST['target_email'] ?? ''));

        if ($targetEmail === '') {
            $_SESSION['flash_error'] = 'Email cible manquant.';
            header('Location: /?page=admin');
            exit;
        }

        $targetUser = $this->userService->findByEmail($targetEmail);
        if (!$targetUser) {
            $_SESSION['flash_error'] = 'Utilisateur introuvable.';
            header('Location: /?page=admin');
            exit;
        }

        if ($action === 'set-plan') {
            $plan = in_array($_POST['plan'] ?? '', ['free', 'paid'], true) ? $_POST['plan'] : 'free';
            $this->userService->updatePlan($targetEmail, $plan);
            $_SESSION['flash_success'] = 'Plan mis à jour.';
        } elseif ($action === 'toggle-active') {
            if ($targetEmail === $this->currentUser()['email']) {
                $_SESSION['flash_error'] = 'Vous ne pouvez pas modifier votre propre statut.';
                header('Location: /?page=admin');
                exit;
            }
            $newState = $targetUser['is_active'] ? 0 : 1;
            $this->db->exec('UPDATE users SET is_active = ? WHERE email = ?', [$newState, $targetEmail]);
            $_SESSION['flash_success'] = 'Statut mis à jour.';
        } elseif ($action === 'delete-user') {
            if ($targetEmail === $this->currentUser()['email']) {
                $_SESSION['flash_error'] = 'Vous ne pouvez pas supprimer votre propre compte.';
                header('Location: /?page=admin');
                exit;
            }
            $accounts = $this->accountService->findByUser($targetEmail);
            foreach ($accounts as $acc) {
                $this->db->exec('DELETE FROM exceptions WHERE entity_id IN (SELECT id FROM expenses WHERE account_id = ?)', [$acc['id']]);
                $this->db->exec('DELETE FROM exceptions WHERE entity_id IN (SELECT id FROM incomes WHERE account_id = ?)', [$acc['id']]);
                $this->db->exec('DELETE FROM expenses WHERE account_id = ?', [$acc['id']]);
                $this->db->exec('DELETE FROM incomes WHERE account_id = ?', [$acc['id']]);
                $this->db->exec('DELETE FROM account_shares WHERE account_id = ?', [$acc['id']]);
            }
            $this->db->exec('DELETE FROM account_shares WHERE owner_email = ?', [$targetEmail]);
            $this->db->exec('DELETE FROM account_shares WHERE invited_email = ?', [$targetEmail]);
            $this->db->exec('DELETE FROM accounts WHERE user_email = ?', [$targetEmail]);
            $this->db->exec('DELETE FROM users WHERE email = ?', [$targetEmail]);
            $_SESSION['flash_success'] = 'Utilisateur supprimé.';
        } else {
            $_SESSION['flash_error'] = 'Action inconnue.';
        }

        header('Location: /?page=admin');
        exit;
    }

    private function handleAccountCreate(): void
    {
        if (!$this->isAuthenticated()) {
            $_SESSION['flash_error'] = 'Vous devez être connecté.';
            header('Location: /?page=login');
            exit;
        }

        $shortName = trim((string) ($_POST['short_name'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $interestRate = (float) ($_POST['interest_rate'] ?? 0);
        $taxRate = (float) ($_POST['tax_rate'] ?? 0);
        $initialBalance = (float) ($_POST['initial_balance'] ?? 0);

        if (empty($shortName) || empty($description)) {
            $_SESSION['flash_error'] = 'Tous les champs obligatoires doivent être remplis.';
            header('Location: /?page=accounts');
            exit;
        }

        try {
            if ($this->isFreeUser() && $this->accountService->countByUser($this->currentUser()['email']) >= self::FREE_ACCOUNT_LIMIT) {
                $_SESSION['flash_error'] = 'La formule gratuite permet de créer 2 comptes bancaires maximum.';
                header('Location: /?page=accounts');
                exit;
            }

            $this->accountService->create(
                $this->currentUser()['email'],
                $shortName,
                $description,
                $interestRate,
                $taxRate,
                $initialBalance
            );
            $_SESSION['flash_success'] = 'Compte créé avec succès.';
        } catch (Exception $e) {
            $_SESSION['flash_error'] = 'Erreur lors de la création du compte.';
        }

        header('Location: /?page=accounts');
        exit;
    }

    private function handleAccountUpdate(int $id): void
    {
        $account = $this->accountService->findById($id);
        if (!$account || $account['user_email'] !== $this->currentUser()['email']) {
            http_response_code(403);
            exit;
        }

        $shortName = trim((string) ($_POST['short_name'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $interestRate = (float) ($_POST['interest_rate'] ?? 0);
        $taxRate = (float) ($_POST['tax_rate'] ?? 0);
        $initialBalance = (float) ($_POST['initial_balance'] ?? 0);

        if (empty($shortName) || empty($description)) {
            $_SESSION['flash_error'] = 'Tous les champs obligatoires doivent être remplis.';
            header('Location: /?page=account&id=' . $id);
            exit;
        }

        if ($this->accountService->update($id, $shortName, $description, $interestRate, $taxRate, $initialBalance)) {
            $_SESSION['flash_success'] = 'Compte mis à jour avec succès.';
        } else {
            $_SESSION['flash_error'] = 'Erreur lors de la mise à jour du compte.';
        }

        header('Location: /?page=account&id=' . $id);
        exit;
    }

    private function handleAccountDelete(int $id): void
    {
        $account = $this->accountService->findById($id);
        if (!$account || $account['user_email'] !== $this->currentUser()['email']) {
            http_response_code(403);
            exit;
        }

       if ($this->accountService->delete($id)) {
            $_SESSION['flash_success'] = 'Compte supprimé avec succès.';
        } else {
            $_SESSION['flash_error'] = 'Erreur lors de la suppression du compte.';
        }

        header('Location: /?page=accounts');
        exit;
    }

    private function handleAccountShareInvite(int $accountId): void
    {
        $account = $this->accountService->findById($accountId);
        if (!$account || $account['user_email'] !== $this->currentUser()['email']) {
            http_response_code(403);
            exit;
        }

        $invitedEmail = ValidationHelper::cleanEmail($_POST['invited_email'] ?? '');

        if ($invitedEmail === '' || !ValidationHelper::validateEmail($invitedEmail)) {
            $_SESSION['flash_error'] = 'Adresse email invalide.';
            header('Location: /?page=account&id=' . $accountId);
            exit;
        }

        if (strcasecmp($invitedEmail, $this->currentUser()['email']) === 0) {
            $_SESSION['flash_error'] = 'Vous ne pouvez pas partager un compte avec vous-même.';
            header('Location: /?page=account&id=' . $accountId);
            exit;
        }

        if ($this->shareService->findPendingInvite($accountId, $invitedEmail) !== null) {
            $_SESSION['flash_error'] = 'Une invitation est déjà en attente pour cette adresse.';
            header('Location: /?page=account&id=' . $accountId);
            exit;
        }

        try {
            $token = $this->shareService->invite($accountId, $this->currentUser()['email'], $invitedEmail);

            EmailHelper::sendShareInvitation(
                $invitedEmail,
                $this->currentUser()['full_name'] ?? $this->currentUser()['email'],
                $account['short_name'],
                $token
            );

            $_SESSION['flash_success'] = 'Invitation envoyée à ' . $invitedEmail . '.';
        } catch (Exception $e) {
            error_log('Share invite error: ' . $e->getMessage());
            $_SESSION['flash_error'] = 'Erreur lors de l\'envoi de l\'invitation.';
        }

        header('Location: /?page=account&id=' . $accountId);
        exit;
    }

    private function handleAccountShareRevoke(int $accountId): void
    {
        $account = $this->accountService->findById($accountId);
        if (!$account || $account['user_email'] !== $this->currentUser()['email']) {
            http_response_code(403);
            exit;
        }

        $shareId = (int) ($_POST['share_id'] ?? 0);

        if ($this->shareService->revoke($shareId, $this->currentUser()['email'])) {
            $_SESSION['flash_success'] = 'Partage révoqué.';
        } else {
            $_SESSION['flash_error'] = 'Erreur lors de la révocation du partage.';
        }

        header('Location: /?page=account&id=' . $accountId);
        exit;
    }

    private function handleShareAccept(): void
    {
        if (!$this->isAuthenticated()) {
            header('Location: /?page=login');
            exit;
        }

        $token = trim((string) ($_POST['token'] ?? ''));
        $share = $this->shareService->findByToken($token);

        if ($share === null) {
            $_SESSION['flash_error'] = 'Invitation introuvable ou déjà traitée.';
            header('Location: /?page=dashboard');
            exit;
        }

        if (strcasecmp($share['invited_email'], $this->currentUser()['email']) !== 0) {
            $_SESSION['flash_error'] = 'Cette invitation a été envoyée à une autre adresse email.';
            header('Location: /?page=dashboard');
            exit;
        }

        if ($this->shareService->accept($token, $this->currentUser()['email'])) {
            $_SESSION['flash_success'] = 'Vous avez maintenant accès à ce compte en lecture seule.';
            header('Location: /?page=account&id=' . $share['account_id']);
            exit;
        }

        $_SESSION['flash_error'] = 'Cette invitation n\'est plus valide.';
        header('Location: /?page=dashboard');
        exit;
    }

    private function handleExpenseCreate(): void
    {
        if (!$this->isAuthenticated()) {
            $_SESSION['flash_error'] = 'Vous devez être connecté.';
            header('Location: /?page=login');
            exit;
        }

        $accountId = (int) ($_POST['account_id'] ?? 0);
        $shortName = trim((string) ($_POST['short_name'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $amount = (float) ($_POST['amount'] ?? 0);
        $frequency = trim((string) ($_POST['frequency'] ?? 'ponctuel'));
        $frequencyMonths = isset($_POST['frequency_months']) && $_POST['frequency_months'] !== '' ? (int) $_POST['frequency_months'] : null;
        $startDate = trim((string) ($_POST['start_date'] ?? date('Y-m-d')));
        $endDate = trim((string) ($_POST['end_date'] ?? '')) ?: null;

        $oldInput = compact('shortName', 'description', 'amount', 'frequency', 'frequencyMonths', 'startDate', 'endDate');
        $this->flashOldInput('expense-create:' . $accountId, $oldInput);

        $account = $this->accountService->findById($accountId);
        if (!$account || $account['user_email'] !== $this->currentUser()['email']) {
            $_SESSION['flash_error'] = 'Compte introuvable ou accès non autorisé.';
            header('Location: /?page=accounts');
            exit;
        }

        if (empty($shortName) || $amount <= 0) {
            $_SESSION['flash_error'] = 'Le nom et le montant sont obligatoires.';
            header('Location: /?page=expense-create&account_id=' . $accountId);
            exit;
        }

        if ($frequency !== 'periodic') {
            $frequencyMonths = null;
        } elseif ($frequencyMonths === null || $frequencyMonths < 1) {
            $_SESSION['flash_error'] = 'Indiquez le nombre de mois pour cette fréquence.';
            header('Location: /?page=expense-create&account_id=' . $accountId);
            exit;
        }

        if ($this->isFreeUser() && $this->expenseService->countByAccount($accountId) >= self::FREE_EXPENSE_LIMIT_PER_ACCOUNT) {
            $_SESSION['flash_error'] = 'La formule gratuite permet de créer 7 dépenses maximum par compte.';
            header('Location: /?page=account&id=' . $accountId);
            exit;
        }

        try {
            $this->expenseService->create($accountId, $shortName, $description, $amount, $frequency, $frequencyMonths, $startDate, $endDate);
            unset($_SESSION['old_input']['expense-create:' . $accountId]);
            $_SESSION['flash_success'] = 'Dépense créée avec succès.';
        } catch (Exception $e) {
            $_SESSION['flash_error'] = 'Erreur lors de la création de la dépense.';
        }

        header('Location: /?page=account&id=' . $accountId);
        exit;
    }

    private function handleExpenseUpdate(int $id): void
    {
        $expense = $this->expenseService->findById($id);
        if (!$expense) {
            http_response_code(404);
            exit;
        }

        $account = $this->accountService->findById((int) $expense['account_id']);
        if (!$account || $account['user_email'] !== $this->currentUser()['email']) {
            http_response_code(403);
            exit;
        }

        $shortName = trim((string) ($_POST['short_name'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $amount = (float) ($_POST['amount'] ?? 0);
        $frequency = trim((string) ($_POST['frequency'] ?? 'ponctuel'));
        $frequencyMonths = isset($_POST['frequency_months']) && $_POST['frequency_months'] !== '' ? (int) $_POST['frequency_months'] : null;
        $startDate = trim((string) ($_POST['start_date'] ?? date('Y-m-d')));
        $endDate = trim((string) ($_POST['end_date'] ?? '')) ?: null;

        $oldInput = compact('shortName', 'description', 'amount', 'frequency', 'frequencyMonths', 'startDate', 'endDate');
        $this->flashOldInput('expense:' . $id, $oldInput);

        if (empty($shortName) || $amount <= 0) {
            $_SESSION['flash_error'] = 'Le nom et le montant sont obligatoires.';
            header('Location: /?page=expense&id=' . $id);
            exit;
        }

        if ($frequency !== 'periodic') {
            $frequencyMonths = null;
        } elseif ($frequencyMonths === null || $frequencyMonths < 1) {
            $_SESSION['flash_error'] = 'Indiquez le nombre de mois pour cette fréquence.';
            header('Location: /?page=expense&id=' . $id);
            exit;
        }

        if ($this->expenseService->update($id, $shortName, $description, $amount, $frequency, $frequencyMonths, $startDate, $endDate)) {
            unset($_SESSION['old_input']['expense:' . $id]);
            $_SESSION['flash_success'] = 'Dépense mise à jour.';
        } else {
            $_SESSION['flash_error'] = 'Erreur lors de la mise à jour.';
        }

        header('Location: /?page=expense&id=' . $id);
        exit;
    }

    private function handleExpenseDelete(int $id): void
    {
        $expense = $this->expenseService->findById($id);
        if (!$expense) {
            http_response_code(404);
            exit;
        }

        $account = $this->accountService->findById((int) $expense['account_id']);
        if (!$account || $account['user_email'] !== $this->currentUser()['email']) {
            http_response_code(403);
            exit;
        }

        if ($this->expenseService->delete($id)) {
            $_SESSION['flash_success'] = 'Dépense supprimée.';
        } else {
            $_SESSION['flash_error'] = 'Erreur lors de la suppression.';
        }

        header('Location: /?page=account&id=' . $account['id']);
        exit;
    }

    private function handleIncomeCreate(): void
    {
        if (!$this->isAuthenticated()) {
            $_SESSION['flash_error'] = 'Vous devez être connecté.';
            header('Location: /?page=login');
            exit;
        }

        $accountId = (int) ($_POST['account_id'] ?? 0);
        $shortName = trim((string) ($_POST['short_name'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $amount = (float) ($_POST['amount'] ?? 0);
        $frequency = trim((string) ($_POST['frequency'] ?? 'ponctuel'));
        $frequencyMonths = isset($_POST['frequency_months']) && $_POST['frequency_months'] !== '' ? (int) $_POST['frequency_months'] : null;
        $startDate = trim((string) ($_POST['start_date'] ?? date('Y-m-d')));
        $endDate = trim((string) ($_POST['end_date'] ?? '')) ?: null;

        $oldInput = compact('shortName', 'description', 'amount', 'frequency', 'frequencyMonths', 'startDate', 'endDate');
        $this->flashOldInput('income-create:' . $accountId, $oldInput);

        $account = $this->accountService->findById($accountId);
        if (!$account || $account['user_email'] !== $this->currentUser()['email']) {
            $_SESSION['flash_error'] = 'Compte introuvable ou accès non autorisé.';
            header('Location: /?page=accounts');
            exit;
        }

        if (empty($shortName) || $amount <= 0) {
            $_SESSION['flash_error'] = 'Le nom et le montant sont obligatoires.';
            header('Location: /?page=income-create&account_id=' . $accountId);
            exit;
        }

        if ($frequency !== 'periodic') {
            $frequencyMonths = null;
        } elseif ($frequencyMonths === null || $frequencyMonths < 1) {
            $_SESSION['flash_error'] = 'Indiquez le nombre de mois pour cette fréquence.';
            header('Location: /?page=income-create&account_id=' . $accountId);
            exit;
        }

        if ($this->isFreeUser() && $this->incomeService->countByAccount($accountId) >= self::FREE_INCOME_LIMIT_PER_ACCOUNT) {
            $_SESSION['flash_error'] = 'La formule gratuite permet de créer 2 revenus maximum par compte.';
            header('Location: /?page=account&id=' . $accountId);
            exit;
        }

        try {
            $this->incomeService->create($accountId, $shortName, $description, $amount, $frequency, $frequencyMonths, $startDate, $endDate);
            unset($_SESSION['old_input']['income-create:' . $accountId]);
            $_SESSION['flash_success'] = 'Revenu créé avec succès.';
        } catch (Exception $e) {
            $_SESSION['flash_error'] = 'Erreur lors de la création du revenu.';
        }

        header('Location: /?page=account&id=' . $accountId);
        exit;
    }

    private function handleIncomeUpdate(int $id): void
    {
        $income = $this->incomeService->findById($id);
        if (!$income) {
            http_response_code(404);
            exit;
        }

        $account = $this->accountService->findById((int) $income['account_id']);
        if (!$account || $account['user_email'] !== $this->currentUser()['email']) {
            http_response_code(403);
            exit;
        }

        $shortName = trim((string) ($_POST['short_name'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $amount = (float) ($_POST['amount'] ?? 0);
        $frequency = trim((string) ($_POST['frequency'] ?? 'ponctuel'));
        $frequencyMonths = isset($_POST['frequency_months']) && $_POST['frequency_months'] !== '' ? (int) $_POST['frequency_months'] : null;
        $startDate = trim((string) ($_POST['start_date'] ?? date('Y-m-d')));
        $endDate = trim((string) ($_POST['end_date'] ?? '')) ?: null;

        $oldInput = compact('shortName', 'description', 'amount', 'frequency', 'frequencyMonths', 'startDate', 'endDate');
        $this->flashOldInput('income:' . $id, $oldInput);

        if (empty($shortName) || $amount <= 0) {
            $_SESSION['flash_error'] = 'Le nom et le montant sont obligatoires.';
            header('Location: /?page=income&id=' . $id);
            exit;
        }

        if ($frequency !== 'periodic') {
            $frequencyMonths = null;
        } elseif ($frequencyMonths === null || $frequencyMonths < 1) {
            $_SESSION['flash_error'] = 'Indiquez le nombre de mois pour cette fréquence.';
            header('Location: /?page=income&id=' . $id);
            exit;
        }

        if ($this->incomeService->update($id, $shortName, $description, $amount, $frequency, $frequencyMonths, $startDate, $endDate)) {
            unset($_SESSION['old_input']['income:' . $id]);
            $_SESSION['flash_success'] = 'Revenu mis à jour.';
        } else {
            $_SESSION['flash_error'] = 'Erreur lors de la mise à jour.';
        }

        header('Location: /?page=income&id=' . $id);
        exit;
    }
    private function handleIncomeDelete(int $id): void
    {
        $income = $this->incomeService->findById($id);
        if (!$income) {
            http_response_code(404);
            exit;
        }

        $account = $this->accountService->findById((int) $income['account_id']);
        if (!$account || $account['user_email'] !== $this->currentUser()['email']) {
            http_response_code(403);
            exit;
        }

        if ($this->incomeService->delete($id)) {
            $_SESSION['flash_success'] = 'Revenu supprimé.';
        } else {
            $_SESSION['flash_error'] = 'Erreur lors de la suppression.';
        }

        header('Location: /?page=account&id=' . $account['id']);
        exit;
    }

    private function flashOldInput(string $key, array $input): void
    {
        $_SESSION['old_input'][$key] = $input;
    }

    private function oldInputKey(string $page): string
    {
        if (in_array($page, ['expense', 'income', 'account'], true) && isset($_GET['id'])) {
            return $page . ':' . (int) $_GET['id'];
        }

        if (in_array($page, ['expense-create', 'income-create'], true) && isset($_GET['account_id'])) {
            return $page . ':' . (int) $_GET['account_id'];
        }

        return $page;
    }

    private function seedDemoUser(): void
    {
        if (!$this->userService->existsByEmail('demo@budgie.local')) {
            try {
                $this->userService->create('demo@budgie.local', 'Utilisateur démo', 'BudgieDemo2026!');

                $user = $this->userService->findByEmail('demo@budgie.local');
                if ($user && !$user['is_active']) {
                    $this->db->exec(
                        'UPDATE users SET is_active = true, verification_token = NULL, token_expiry = NULL WHERE id = ?',
                        [$user['id']]
                    );
                }
            } catch (Exception $e) {
                // Silently fail if demo user already exists
            }
        }

        $this->db->exec('UPDATE users SET plan = ? WHERE email = ?', ['free', 'demo@budgie.local']);
    }

    private function seedDemoExpenses(): void
    {
        $user = $this->userService->findByEmail('demo@budgie.local');
        if (!$user) {
            return;
        }

        $accounts = $this->accountService->findByUser($user['email']);
        if (empty($accounts)) {
            $this->accountService->create($user['email'], 'Compte courant', 'Compte principal pour dépenses journalières', 0.0, 0.0);
            $this->accountService->create($user['email'], 'Épargne', 'Compte d\'épargne pour objectif futur', 0.0, 0.0);
            $accounts = $this->accountService->findByUser($user['email']);
        }

        $totalExpenses = 0;
        foreach ($accounts as $account) {
            $totalExpenses += count($this->expenseService->findByAccount((int) $account['id']));
        }

        if ($totalExpenses > 0) {
            return;
        }

        $sampleExpenses = [
            ['account' => 'Compte courant', 'short_name' => 'Courses supermarché', 'description' => 'Achats alimentaires et ménagers', 'amount' => 120.50, 'frequency' => 'ponctuel', 'frequency_months' => null, 'start_date' => '2026-05-10', 'end_date' => null],
            ['account' => 'Compte courant', 'short_name' => 'Facture électricité', 'description' => 'Paiement de la facture mensuelle d\'électricité', 'amount' => 78.90, 'frequency' => 'mensuel', 'frequency_months' => null, 'start_date' => '2026-05-01', 'end_date' => null],
            ['account' => 'Compte courant', 'short_name' => 'Abonnement internet', 'description' => 'Frais mensuels internet et télévision', 'amount' => 29.99, 'frequency' => 'mensuel', 'frequency_months' => null, 'start_date' => '2026-05-01', 'end_date' => null],
            ['account' => 'Compte courant', 'short_name' => 'Loyer appartement', 'description' => 'Paiement du loyer mensuel', 'amount' => 850.00, 'frequency' => 'mensuel', 'frequency_months' => null, 'start_date' => '2026-05-01', 'end_date' => null],
            ['account' => 'Compte courant', 'short_name' => 'Café', 'description' => 'Achat d\'un café et viennoiserie', 'amount' => 4.50, 'frequency' => 'ponctuel', 'frequency_months' => null, 'start_date' => '2026-05-15', 'end_date' => null],
            ['account' => 'Compte courant', 'short_name' => 'Facture mobile', 'description' => 'Abonnement téléphonique mensuel', 'amount' => 19.90, 'frequency' => 'mensuel', 'frequency_months' => null, 'start_date' => '2026-05-01', 'end_date' => null],
            ['account' => 'Compte courant', 'short_name' => 'Cadeau anniversaire', 'description' => 'Achat cadeau pour un anniversaire', 'amount' => 60.00, 'frequency' => 'ponctuel', 'frequency_months' => null, 'start_date' => '2026-05-20', 'end_date' => null],
            ['account' => 'Compte courant', 'short_name' => 'Abonnement salle', 'description' => 'Frais mensuels de la salle de sport', 'amount' => 35.00, 'frequency' => 'mensuel', 'frequency_months' => null, 'start_date' => '2026-05-01', 'end_date' => null],
            ['account' => 'Compte courant', 'short_name' => 'Assurance habitation', 'description' => 'Paiement mensuel de l\'assurance maison', 'amount' => 12.50, 'frequency' => 'mensuel', 'frequency_months' => null, 'start_date' => '2026-05-01', 'end_date' => null],
            ['account' => 'Compte courant', 'short_name' => 'Pass transport', 'description' => 'Pass mensuel pour les transports publics', 'amount' => 45.00, 'frequency' => 'mensuel', 'frequency_months' => null, 'start_date' => '2026-05-01', 'end_date' => null],
            ['account' => 'Épargne', 'short_name' => 'Transfert épargne', 'description' => 'Transfert occasionnel vers le compte épargne', 'amount' => 150.00, 'frequency' => 'ponctuel', 'frequency_months' => null, 'start_date' => '2026-05-05', 'end_date' => null],
            ['account' => 'Épargne', 'short_name' => 'Versement objectif vacances', 'description' => 'Mise de côté pour les vacances', 'amount' => 200.00, 'frequency' => 'ponctuel', 'frequency_months' => null, 'start_date' => '2026-05-12', 'end_date' => null],
            ['account' => 'Épargne', 'short_name' => 'Cadeau noël', 'description' => 'Épargne pour cadeau de fin d\'année', 'amount' => 100.00, 'frequency' => 'ponctuel', 'frequency_months' => null, 'start_date' => '2026-05-18', 'end_date' => null],
        ];

        $accountMap = [];
        foreach ($accounts as $account) {
            $accountMap[$account['short_name']] = (int) $account['id'];
        }

        foreach ($sampleExpenses as $expense) {
            if (!isset($accountMap[$expense['account']])) {
                continue;
            }
            $this->expenseService->create(
                $accountMap[$expense['account']],
                $expense['short_name'],
                $expense['description'],
                $expense['amount'],
                $expense['frequency'],
                $expense['frequency_months'],
                $expense['start_date'],
                $expense['end_date']
            );
        }
    }

    private function isAuthenticated(): bool
    {
        return isset($_SESSION['user']);
    }

    private function isValidCsrfToken(): bool
    {
        $sessionToken = $_SESSION['csrf_token'] ?? '';
        $sentToken = $_POST['csrf_token'] ?? '';
        return $sessionToken !== '' && hash_equals($sessionToken, $sentToken);
    }

    private function csrfField(): string
    {
        $token = htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8');
        return '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }

    private function currentUser(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    private function isFreeUser(): bool
    {
        $user = $this->currentUser();
        if ($user === null) {
            return true;
        }

        if (isset($user['plan'])) {
            return $user['plan'] !== 'paid';
        }

        $storedUser = $this->userService->findByEmail((string) $user['email']);

        return ($storedUser['plan'] ?? 'free') !== 'paid';
    }

    private function createStripeCheckoutSession(string $secretKey, array $params): array
    {
        return $this->stripeRequest($secretKey, 'POST', 'https://api.stripe.com/v1/checkout/sessions', $params);
    }

    private function retrieveStripeCheckoutSession(string $secretKey, string $sessionId): array
    {
        return $this->stripeRequest($secretKey, 'GET', 'https://api.stripe.com/v1/checkout/sessions/' . rawurlencode($sessionId));
    }

    private function cancelStripeSubscription(string $secretKey, string $subscriptionId): array
    {
        return $this->stripeRequest($secretKey, 'DELETE', 'https://api.stripe.com/v1/subscriptions/' . rawurlencode($subscriptionId));
    }

    private function stripeRequest(string $secretKey, string $method, string $url, array $params = []): array
    {
        $options = [
            'http' => [
                'method' => $method,
                'header' => "Authorization: Bearer {$secretKey}\r\n",
                'ignore_errors' => true,
            ],
        ];

        if ($method === 'POST') {
            $options['http']['header'] .= "Content-Type: application/x-www-form-urlencoded\r\n";
            $options['http']['content'] = http_build_query($params);
        }

        $response = file_get_contents($url, false, stream_context_create($options));
        if ($response === false) {
            return ['error' => 'Stripe ne répond pas.'];
        }

        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            return ['error' => 'Réponse Stripe invalide.'];
        }

        if (isset($decoded['error']['message'])) {
            return ['error' => $decoded['error']['message']];
        }

        return $decoded;
    }

    private function baseUrl(): string
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8080';

        return $scheme . '://' . $host;
    }

    private function withComputedBalances(array $accounts): array
    {
        return array_map(fn (array $account): array => $this->withComputedBalance($account), $accounts);
    }

    private function withComputedBalance(array $account): array
    {
        $account['initial_balance'] = (float) ($account['balance'] ?? 0);
        $account['balance'] = $this->computeCurrentBalance($account);

        return $account;
    }

    private function computeCurrentBalance(array $account): float
    {
        return $this->computeBalanceUntil($account, new DateTimeImmutable('today'));
    }

    private function computeBalanceUntil(array $account, DateTimeImmutable $cutoffDate): float
    {
        $balance = (float) ($account['balance'] ?? 0);

        foreach ($this->incomeService->findByAccount((int) $account['id']) as $income) {
        $balance += $this->amountUntilDate($income, $cutoffDate, 'income');
        }

        foreach ($this->expenseService->findByAccount((int) $account['id']) as $expense) {
            $balance -= $this->amountUntilDate($expense, $cutoffDate, 'expense');
        }

        return $balance;
    }

    private function buildMonthlyForecast(array $account, string $month): array
    {
        $incomes = $this->incomeService->findByAccount((int) $account['id']);
        $expenses = $this->expenseService->findByAccount((int) $account['id']);

        $incomeTotal = 0.0;
        foreach ($incomes as $income) {
            if ($this->occursInMonth($income, $month)) {
                $incomeTotal += $this->exceptionService->getEffectiveAmount(
                    (float) $income['amount'],
                    'income',
                    (int) $income['id'],
                    $month
                );
            }
        }

        $expenseTotal = 0.0;
        foreach ($expenses as $expense) {
            if ($this->occursInMonth($expense, $month)) {
                $expenseTotal += $this->exceptionService->getEffectiveAmount(
                    (float) $expense['amount'],
                    'expense',
                    (int) $expense['id'],
                    $month
                );
            }
        }

        $monthStart = DateTimeImmutable::createFromFormat('Y-m-d', $month . '-01');
        $startBalance = $monthStart
            ? $this->computeBalanceUntil($account, $monthStart->modify('-1 day'))
            : (float) $account['balance'];
        $annualRate = ((float) $account['interest_rate']) / 100;
        $taxRate = ((float) $account['tax_rate']) / 100;
        $interest = $startBalance * ($annualRate / 12) * (1 - $taxRate);
        $projectedBalance = $startBalance + $incomeTotal - $expenseTotal + $interest;

        return [
            'account_id' => (int) $account['id'],
            'account_name' => $account['short_name'],
            'start_balance' => $startBalance,
            'incomes' => $incomeTotal,
            'expenses' => $expenseTotal,
            'interest' => $interest,
            'projected_balance' => $projectedBalance,
        ];
    }

    private function occursInMonth(array $entry, string $month): bool
    {
        $targetMonthStart = DateTimeImmutable::createFromFormat('Y-m-d', $month . '-01');
        if (!$targetMonthStart) {
            return false;
        }
        $targetMonthEnd = $targetMonthStart->modify('last day of this month');

        $startDate = DateTimeImmutable::createFromFormat('Y-m-d', (string) $entry['start_date']);
        if (!$startDate) {
            return false;
        }

        $endDate = null;
        if (!empty($entry['end_date'])) {
            $parsedEndDate = DateTimeImmutable::createFromFormat('Y-m-d', (string) $entry['end_date']);
            if ($parsedEndDate) {
                $endDate = $parsedEndDate;
            }
        }

        if ($startDate > $targetMonthEnd) {
            return false;
        }

        if ($endDate !== null && $endDate < $targetMonthStart) {
            return false;
        }

        $frequency = strtolower(trim((string) ($entry['frequency'] ?? 'ponctuel')));

        if ($frequency === 'ponctuel') {
            return $startDate >= $targetMonthStart && $startDate <= $targetMonthEnd;
        }

        if ($frequency === 'mensuel') {
            return true;
        }

        if ($frequency === 'periodic' || $frequency === 'periodique') {
            $monthsInterval = (int) ($entry['frequency_months'] ?? 1);
            if ($monthsInterval <= 0) {
                $monthsInterval = 1;
            }

            $startMonthIndex = ((int) $startDate->format('Y')) * 12 + ((int) $startDate->format('n'));
            $targetMonthIndex = ((int) $targetMonthStart->format('Y')) * 12 + ((int) $targetMonthStart->format('n'));
            $delta = $targetMonthIndex - $startMonthIndex;

            return $delta >= 0 && ($delta % $monthsInterval === 0);
        }

        return true;
    }

private function amountUntilDate(array $entry, DateTimeImmutable $cutoffDate, string $entityType = 'expense'): float    {
        $startDate = DateTimeImmutable::createFromFormat('Y-m-d', (string) $entry['start_date']);
        if (!$startDate || $startDate > $cutoffDate) {
            return 0.0;
        }

        $effectiveEndDate = $cutoffDate;
        if (!empty($entry['end_date'])) {
            $endDate = DateTimeImmutable::createFromFormat('Y-m-d', (string) $entry['end_date']);
            if ($endDate && $endDate < $effectiveEndDate) {
                $effectiveEndDate = $endDate;
            }
        }

        if ($effectiveEndDate < $startDate) {
            return 0.0;
        }

       $baseAmount = (float) $entry['amount'];
$frequency  = strtolower(trim((string) ($entry['frequency'] ?? 'ponctuel')));
$entityId   = (int) $entry['id'];

if ($frequency === 'ponctuel') {
    $month = $startDate->format('Y-m');
    return $this->exceptionService->getEffectiveAmount($baseAmount, $entityType, $entityId, $month);
}

$monthsInterval = 1;
if ($frequency === 'periodic' || $frequency === 'periodique') {
    $monthsInterval = (int) ($entry['frequency_months'] ?? 1);
    if ($monthsInterval <= 0) {
        $monthsInterval = 1;
    }
}

$total = 0.0;
$startMonthIndex = ((int) $startDate->format('Y')) * 12 + ((int) $startDate->format('n'));
$endMonthIndex   = ((int) $effectiveEndDate->format('Y')) * 12 + ((int) $effectiveEndDate->format('n'));
$startDay = (int) $startDate->format('j');

for ($monthIndex = $startMonthIndex; $monthIndex <= $endMonthIndex; $monthIndex += $monthsInterval) {
    $year  = intdiv($monthIndex - 1, 12);
    $month = (($monthIndex - 1) % 12) + 1;
    $monthStart = DateTimeImmutable::createFromFormat('Y-m-d', sprintf('%04d-%02d-01', $year, $month));
    if (!$monthStart) {
        continue;
    }

    $lastDay = (int) $monthStart->modify('last day of this month')->format('j');
    $occurrenceDay  = min($startDay, $lastDay);
    $occurrenceDate = $monthStart->setDate($year, $month, $occurrenceDay);

    if ($occurrenceDate >= $startDate && $occurrenceDate <= $effectiveEndDate) {
        $monthKey = sprintf('%04d-%02d', $year, $month);
        $total += $this->exceptionService->getEffectiveAmount($baseAmount, $entityType, $entityId, $monthKey);
    }
}

        return $total;
    }

    private function render(string $template, array $data = []): string
    {
        $data['csrf_field'] = $data['csrf_field'] ?? $this->csrfField();
        extract($data, EXTR_SKIP);

        ob_start();
        require BASE_PATH . '/templates/' . $template;

        return (string) ob_get_clean();
    }

    private function renderLayout(string $title, string $content, ?array $user): string
    {
        ob_start();
        require BASE_PATH . '/templates/layout.php';

        return (string) ob_get_clean();
    }
}