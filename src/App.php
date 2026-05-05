<?php
declare(strict_types=1);

final class App
{
    private Database $db;
    private AccountService $accountService;

    public function __construct()
    {
        $this->db = new Database(BASE_PATH . '/data/budgie.db');
        $this->db->init();
        $this->accountService = new AccountService($this->db);
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
        ];

        if (!isset($routes[$page])) {
            http_response_code(404);
            $page = 'home';
        }

        $route = $routes[$page];

        if (in_array($page, ['dashboard', 'accounts', 'account', 'account-create']) && !$this->isAuthenticated()) {
            header('Location: /?page=login');
            exit;
        }

        if ($page === 'account' && !isset($_GET['id'])) {
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
