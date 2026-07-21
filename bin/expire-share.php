<?php
declare(strict_types=1);

$id = $argv[1] ?? null;

if ($id === null) {
    echo "Usage : php bin/expire-share.php <id>\n";
    exit(1);
}

$pdo = new PDO('sqlite:' . __DIR__ . '/../data/budgie.db');
$stmt = $pdo->prepare("UPDATE account_shares SET expires_at = datetime('now', '-1 hour') WHERE id = ?");
$stmt->execute([(int) $id]);

echo $stmt->rowCount() > 0 ? "Ligne $id marquée comme expirée.\n" : "Aucune ligne trouvée avec l'id $id.\n";