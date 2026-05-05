<?php
declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

if (session_status() !== PHP_SESSION_ACTIVE) {
	session_start();
}

require BASE_PATH . '/src/App.php';

$app = new App();
$app->run();
