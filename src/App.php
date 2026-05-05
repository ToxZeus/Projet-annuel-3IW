<?php
declare(strict_types=1);

final class App
{
    public function run(): void
    {
        $page = $_GET['page'] ?? 'home';

        $routes = [
            'home' => [
                'title' => 'Budgie | Ton partenaire financier personnel',
                'template' => 'pages/home.php',
            ],
        ];

        if (!isset($routes[$page])) {
            http_response_code(404);
            $page = 'home';
        }

        $route = $routes[$page];
        $content = $this->render($route['template'], [
            'page' => $page,
        ]);

        echo $this->renderLayout($route['title'], $content);
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
