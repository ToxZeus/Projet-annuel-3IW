<?php
declare(strict_types=1);

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

if (session_status() !== PHP_SESSION_ACTIVE) {
	session_start();
}

require BASE_PATH . '/src/App.php';
require BASE_PATH . '/src/Database.php';
require BASE_PATH . '/src/ValidationHelper.php';
require BASE_PATH . '/src/Helpers/EmailHelper.php';
require BASE_PATH . '/src/UserService.php';
require BASE_PATH . '/src/AccountService.php';
require BASE_PATH . '/src/ExpenseService.php';
require BASE_PATH . '/src/IncomeService.php';
require BASE_PATH . '/src/ExceptionService.php';

$app = new App();
$app->run();
