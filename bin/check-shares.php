<?php
declare(strict_types=1);

$pdo = new PDO('sqlite:' . __DIR__ . '/../data/budgie.db');

echo "--- Colonnes de account_shares ---\n";
foreach ($pdo->query('PRAGMA table_info(account_shares)') as $row) {
    echo $row['name'] . "\n";
}

echo "\n--- Contenu de account_shares ---\n";
foreach ($pdo->query('SELECT id, invited_email, status, created_at, expires_at FROM account_shares') as $row) {
    print_r($row);
}