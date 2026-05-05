<?php
declare(strict_types=1);

final class App
{
    private Database $db;
    private AccountService $accountService;
    private ExpenseService $expenseService;
    private IncomeService $incomeService;

    public function __construct()
    {
        $this->db = new Database(BASE_PATH . '/data/budgie.db');
        $this->db->init();
        $this->accountService = new AccountService($this->db);
        $this->expenseService = new ExpenseService($this->db);
        $this->incomeService = new IncomeService($this->db);
    }

    public function run(): void
    {
        $page = $_GET['page'] ?? 'home';

        if ($page === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleLogin();
            return;
        }

        if ($page === 'logout') {
            $this->handleLogout();
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
            'login' => [
                'title' => 'Budgie | Connexion',
                'template' => 'pages/login.php',
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

        if (in_array($page, ['dashboard', 'accounts', 'account', 'account-create', 'expenses', 'expense', 'expense-create', 'incomes', 'income', 'income-create', 'previsions']) && !$this->isAuthenticated()) {
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

        if ($page === 'accounts') {
            $data['accounts'] = $this->accountService->findByUser($this->currentUser()['email']);
        } elseif ($page === 'account' && isset($_GET['id'])) {
            $account = $this->accountService->findById((int) $_GET['id']);
            if (!$account) {
                http_response_code(404);
                return;
            }
            $data['account'] = $account;
            $data['expenses'] = $this->expenseService->findByAccount($account['id']);
            $data['incomes'] = $this->incomeService->findByAccount($account['id']);
        } elseif ($page === 'expenses') {
            $accounts = $this->accountService->findByUser($this->currentUser()['email']);
            $allExpenses = [];
            foreach ($accounts as $account) {
                $allExpenses = array_merge($allExpenses, $this->expenseService->findByAccount($account['id']));
            }
            $data['expenses'] = $allExpenses;
        } elseif ($page === 'expense' && isset($_GET['id'])) {
            $expense = $this->expenseService->findById((int) $_GET['id']);
            if (!$expense) {
                http_response_code(404);
                return;
            }
            $data['expense'] = $expense;
            $data['account'] = $this->accountService->findById($expense['account_id']);
        } elseif ($page === 'expense-create' && isset($_GET['account_id'])) {
            $account = $this->accountService->findById((int) $_GET['account_id']);
            if (!$account) {
                http_response_code(404);
                return;
            }
            $data['account'] = $account;
        } elseif ($page === 'incomes') {
            $accounts = $this->accountService->findByUser($this->currentUser()['email']);
            $allIncomes = [];
            foreach ($accounts as $account) {
                $allIncomes = array_merge($allIncomes, $this->incomeService->findByAccount($account['id']));
            }
            $data['incomes'] = $allIncomes;
        } elseif ($page === 'income' && isset($_GET['id'])) {
            $income = $this->incomeService->findById((int) $_GET['id']);
            if (!$income) {
                http_response_code(404);
                return;
            }
            $data['income'] = $income;
            $data['account'] = $this->accountService->findById($income['account_id']);
        } elseif ($page === 'income-create' && isset($_GET['account_id'])) {
            $account = $this->accountService->findById((int) $_GET['account_id']);
            if (!$account) {
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

        $content = $this->render($route['template'], $data);

        unset($_SESSION['flash_error'], $_SESSION['flash_success']);

        echo $this->renderLayout($route['title'], $content);
    }

    private function handleLogin(): void
    {
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($this->attemptLogin($email, $password)) {
            $_SESSION['flash_success'] = 'Connexion réussie.';
            header('Location: /?page=dashboard');
            exit;
        }

        $_SESSION['flash_error'] = 'Identifiants invalides.';
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

    private function attemptLogin(string $email, string $password): bool
    {
        $user = $this->demoUser();

        if ($email !== $user['email']) {
            return false;
        }

        if (!password_verify($password, $user['password_hash'])) {
            return false;
        }

        $_SESSION['user'] = [
            'email' => $user['email'],
            'full_name' => $user['full_name'],
        ];

        return true;
    }

    private function demoUser(): array
    {
        return [
            'email' => 'demo@budgie.local',
            'full_name' => 'Utilisateur démo',
            'password_hash' => password_hash('BudgieDemo2026!', PASSWORD_DEFAULT),
        ];
    }

    private function isAuthenticated(): bool
    {
        return isset($_SESSION['user']);
    }

    private function currentUser(): ?array
    {
        return $_SESSION['user'] ?? null;
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

        $account = $this->accountService->findById($accountId);
        if (!$account || $account['user_email'] !== $this->currentUser()['email']) {
            $_SESSION['flash_error'] = 'Compte introuvable ou accès non autorisé.';
            header('Location: /?page=accounts');
            exit;
        }

        if (empty($shortName) || $amount <= 0) {
            $_SESSION['flash_error'] = 'Le nom et le montant sont obligatoires.';
            header('Location: /?page=account&id=' . $accountId);
            exit;
        }

        try {
            $this->expenseService->create($accountId, $shortName, $description, $amount, $frequency, $frequencyMonths, $startDate, $endDate);
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

        if (empty($shortName) || $amount <= 0) {
            $_SESSION['flash_error'] = 'Le nom et le montant sont obligatoires.';
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

        $account = $this->accountService->findById($accountId);
        if (!$account || $account['user_email'] !== $this->currentUser()['email']) {
            $_SESSION['flash_error'] = 'Compte introuvable ou accès non autorisé.';
            header('Location: /?page=accounts');
            exit;
        }

        if (empty($shortName) || $amount <= 0) {
            $_SESSION['flash_error'] = 'Le nom et le montant sont obligatoires.';
            header('Location: /?page=account&id=' . $accountId);
            exit;
        }

        try {
            $this->incomeService->create($accountId, $shortName, $description, $amount, $frequency, $frequencyMonths, $startDate, $endDate);
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

        if (empty($shortName) || $amount <= 0) {
            $_SESSION['flash_error'] = 'Le nom et le montant sont obligatoires.';
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

        $startBalance = (float) $account['balance'];
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

    private function render(string $template, array $data = []): string
    {
        extract($data, EXTR_SKIP);

        ob_start();
        require BASE_PATH . '/templates/' . $template;

        return (string) ob_get_clean();
    }

    private function renderLayout(string $title, string $content): string
    {
        ob_start();
        require BASE_PATH . '/templates/layout.php';

        return (string) ob_get_clean();
    }
}
