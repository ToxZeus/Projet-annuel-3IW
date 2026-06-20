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

    public function __construct()
    {
        $this->db = new Database(BASE_PATH . '/data/budgie.db');
        $this->db->init();
        $this->userService = new UserService($this->db);
        $this->accountService = new AccountService($this->db);
        $this->expenseService = new ExpenseService($this->db);
        $this->incomeService = new IncomeService($this->db);
        $this->seedDemoExpenses();
    }

    public function run(): void
    {
        $page = $_GET['page'] ?? 'home';

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
            }
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
        ];

        if (!isset($routes[$page])) {
            http_response_code(404);
            $page = 'home';
        }

        $route = $routes[$page];

        if (in_array($page, ['dashboard', 'accounts', 'account', 'account-create', 'expenses', 'expense', 'expense-create', 'incomes', 'income', 'income-create', 'previsions', 'subscriptions', 'profile']) && !$this->isAuthenticated()) {
            header('Location: /?page=login');
            exit;
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
} else
        if ($page === 'accounts') {
            $data['accounts'] = $this->withComputedBalances($this->accountService->findByUser($this->currentUser()['email']));
        } elseif ($page === 'account' && isset($_GET['id'])) {
            $account = $this->accountService->findById((int) $_GET['id']);
            if (!$account || $account['user_email'] !== $this->currentUser()['email']) {
                http_response_code(404);
                return;
            }
            $account = $this->withComputedBalance($account);
            $searchQuery = trim((string) ($_GET['q'] ?? ''));
            $expenses = $this->expenseService->findByAccount($account['id']);
            if ($searchQuery !== '') {
                $expenses = array_values(array_filter($expenses, function (array $expense) use ($searchQuery) {
                    return stripos((string) $expense['short_name'], $searchQuery) !== false
                        || stripos((string) $expense['description'], $searchQuery) !== false;
                }));
            }

            $data['account'] = $account;
            $data['expenses'] = $expenses;
            $data['incomes'] = $this->incomeService->findByAccount($account['id']);
            $data['search_query'] = $searchQuery;
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
            if (!$account || $account['user_email'] !== $this->currentUser()['email']) {
                http_response_code(404);
                return;
            }
            $data['expense'] = $expense;
            $data['account'] = $account;
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
            if (!$account || $account['user_email'] !== $this->currentUser()['email']) {
                http_response_code(404);
                return;
            }
            $data['income'] = $income;
            $data['account'] = $account;
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

            $data['selected_month'] = $selectedMonth;
            $data['forecast_rows'] = $forecastRows;
            $data['totals'] = $totals;
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

        // Validation
        if (empty($email) || empty($fullName) || empty($password)) {
            $_SESSION['flash_error'] = 'Tous les champs sont obligatoires.';
            $this->flashOldInput('signup', ['email' => $email, 'full_name' => $fullName]);
            header('Location: /?page=signup');
            exit;
        }

        if (!ValidationHelper::validateEmail($email)) {
            $_SESSION['flash_error'] = 'Email invalide.';
            $this->flashOldInput('signup', ['email' => $email, 'full_name' => $fullName]);
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
            // Générer un token d'activation
            $verificationToken = bin2hex(random_bytes(32));
            $tokenExpiry = date('Y-m-d H:i:s', strtotime('+24 hours'));

            // Créer l'utilisateur avec le token
            $this->userService->create($email, $fullName, $password, $verificationToken, $tokenExpiry, $plan);

            // Envoyer l'email d'activation
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

        $user = $this->userService->verifyCredentials($email, $password);
        if ($user !== null) {
            $_SESSION['user'] = [
                'email' => $user['email'],
                'full_name' => $user['full_name'],
                'plan' => $user['plan'] ?? 'free',
                'stripe_customer_id' => $user['stripe_customer_id'] ?? null,
                'stripe_subscription_id' => $user['stripe_subscription_id'] ?? null,
            ];
            $_SESSION['flash_success'] = 'Connexion réussie.';
            header('Location: /?page=dashboard');
            exit;
        }

        $_SESSION['flash_error'] = 'Identifiants invalides.';
        $this->flashOldInput('login', ['email' => $email]);
        header('Location: /?page=login');
        exit;
    }

    private function handleLogout(): void
    {
        unset($_SESSION['user']);
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

        // Toujours afficher un message de succès pour des raisons de sécurité
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

    private function seedDemoUser(): void
    {
        if (!$this->userService->existsByEmail('demo@budgie.local')) {
            try {
                // Créer un utilisateur démo activé directement (sans token)
                $this->userService->create('demo@budgie.local', 'Utilisateur démo', 'BudgieDemo2026!');
                
                // Activer le compte démo en supprimant le token d'activation
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

    private function currentUser(): ?array
    {
        return $_SESSION['user'] ?? null;
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
                $taxRate
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

        if (empty($shortName) || empty($description)) {
            $_SESSION['flash_error'] = 'Tous les champs obligatoires doivent être remplis.';
            header('Location: /?page=account&id=' . $id);
            exit;
        }

        if ($this->accountService->update($id, $shortName, $description, $interestRate, $taxRate)) {
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
            $_SESSION['flash_error'] = 'Indiquez le nombre de mois pour cette frequence.';
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
            $_SESSION['flash_error'] = 'Indiquez le nombre de mois pour cette frequence.';
            header('Location: /?page=expense&id=' . $id);
            exit;
        }

        if ($this->expenseService->update($id, $shortName, $description, $amount, $frequency, $frequencyMonths, $startDate, $endDate)) {
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
            $_SESSION['flash_error'] = 'Indiquez le nombre de mois pour cette frequence.';
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
            $_SESSION['flash_error'] = 'Indiquez le nombre de mois pour cette frequence.';
            header('Location: /?page=income&id=' . $id);
            exit;
        }

        if ($this->incomeService->update($id, $shortName, $description, $amount, $frequency, $frequencyMonths, $startDate, $endDate)) {
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
            $balance += $this->amountUntilDate($income, $cutoffDate);
        }

        foreach ($this->expenseService->findByAccount((int) $account['id']) as $expense) {
            $balance -= $this->amountUntilDate($expense, $cutoffDate);
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
                $incomeTotal += (float) $income['amount'];
            }
        }

        $expenseTotal = 0.0;
        foreach ($expenses as $expense) {
            if ($this->occursInMonth($expense, $month)) {
                $expenseTotal += (float) $expense['amount'];
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

        if ($frequency === 'periodic' || $frequency === 'periodique' || $frequency === 'periodique') {
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

    private function amountUntilDate(array $entry, DateTimeImmutable $cutoffDate): float
    {
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

        $amount = (float) $entry['amount'];
        $frequency = strtolower(trim((string) ($entry['frequency'] ?? 'ponctuel')));

        if ($frequency === 'ponctuel') {
            return $amount;
        }

        if ($frequency === 'mensuel') {
            return $amount * $this->countMonthlyOccurrences($startDate, $effectiveEndDate, 1);
        }

        if ($frequency === 'periodic' || $frequency === 'periodique') {
            $monthsInterval = (int) ($entry['frequency_months'] ?? 1);
            if ($monthsInterval <= 0) {
                $monthsInterval = 1;
            }

            return $amount * $this->countMonthlyOccurrences($startDate, $effectiveEndDate, $monthsInterval);
        }

        return $amount;
    }

    private function countMonthlyOccurrences(DateTimeImmutable $startDate, DateTimeImmutable $endDate, int $monthsInterval): int
    {
        $count = 0;
        $startMonthIndex = ((int) $startDate->format('Y')) * 12 + ((int) $startDate->format('n'));
        $endMonthIndex = ((int) $endDate->format('Y')) * 12 + ((int) $endDate->format('n'));
        $startDay = (int) $startDate->format('j');

        for ($monthIndex = $startMonthIndex; $monthIndex <= $endMonthIndex; $monthIndex += $monthsInterval) {
            $year = intdiv($monthIndex - 1, 12);
            $month = (($monthIndex - 1) % 12) + 1;
            $monthStart = DateTimeImmutable::createFromFormat('Y-m-d', sprintf('%04d-%02d-01', $year, $month));
            if (!$monthStart) {
                continue;
            }

            $lastDay = (int) $monthStart->modify('last day of this month')->format('j');
            $occurrenceDay = min($startDay, $lastDay);
            $occurrenceDate = $monthStart->setDate($year, $month, $occurrenceDay);

            if ($occurrenceDate >= $startDate && $occurrenceDate <= $endDate) {
                $count++;
            }
        }

        return $count;
    }

    private function render(string $template, array $data = []): string
    {
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
