<?php
declare(strict_types=1);

final class App
{
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
        ];

        if (!isset($routes[$page])) {
            http_response_code(404);
            $page = 'home';
        }

        $route = $routes[$page];

        if ($page === 'dashboard' && !$this->isAuthenticated()) {
            header('Location: /?page=login');
            exit;
        }

        $content = $this->render($route['template'], [
            'page' => $page,
            'user' => $this->currentUser(),
            'error' => $_SESSION['flash_error'] ?? null,
            'success' => $_SESSION['flash_success'] ?? null,
        ]);

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
