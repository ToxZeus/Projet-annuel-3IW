<?php
declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

if (session_status() !== PHP_SESSION_ACTIVE) {
	session_start();
}

require BASE_PATH . '/src/App.php';
require BASE_PATH . '/src/Database.php';
require BASE_PATH . '/src/UserService.php';
require BASE_PATH . '/src/AccountService.php';
require BASE_PATH . '/src/ExpenseService.php';
require BASE_PATH . '/src/IncomeService.php';

$app = new App();
$app->run();
