<?php
declare(strict_types=1);

final class Database
{
    private ?PDO $connection = null;

    public function __construct(private string $dbPath)
    {
    }

    public function connect(): PDO
    {
        if ($this->connection !== null) {
            return $this->connection;
        }

        $this->connection = new PDO(
            'sqlite:' . $this->dbPath,
            null,
            null,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        return $this->connection;
    }

    public function init(): void
    {
        $pdo = $this->connect();

        $pdo->exec('
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                email TEXT NOT NULL UNIQUE,
                full_name TEXT NOT NULL,
                password_hash TEXT NOT NULL,
                plan TEXT NOT NULL DEFAULT \'free\',
                is_active BOOLEAN DEFAULT FALSE,
                verification_token TEXT,
                token_expiry TEXT,
                reset_token TEXT,
                reset_token_expiry TEXT,
                stripe_customer_id TEXT,
                stripe_subscription_id TEXT,
                created_at TEXT NOT NULL,
                updated_at TEXT
            )
        ');

        $userColumns = $pdo->query('PRAGMA table_info(users)')->fetchAll(PDO::FETCH_ASSOC);
        $existingUserColumns = [];
        foreach ($userColumns as $column) {
            $existingUserColumns[] = $column['name'] ?? '';
        }

        if (!in_array('plan', $existingUserColumns, true)) {
            $pdo->exec("ALTER TABLE users ADD COLUMN plan TEXT NOT NULL DEFAULT 'free'");
        }

        if (!in_array('stripe_customer_id', $existingUserColumns, true)) {
            $pdo->exec('ALTER TABLE users ADD COLUMN stripe_customer_id TEXT');
        }

        if (!in_array('stripe_subscription_id', $existingUserColumns, true)) {
            $pdo->exec('ALTER TABLE users ADD COLUMN stripe_subscription_id TEXT');
        }
        if (!in_array('is_admin', $existingUserColumns, true)) {
            $pdo->exec("ALTER TABLE users ADD COLUMN is_admin BOOLEAN NOT NULL DEFAULT 0");
        }
        $pdo->exec('
            CREATE TABLE IF NOT EXISTS accounts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_email TEXT NOT NULL,
                short_name TEXT NOT NULL,
                description TEXT NOT NULL,
                created_at TEXT NOT NULL,
                interest_rate REAL NOT NULL DEFAULT 0,
                tax_rate REAL NOT NULL DEFAULT 0,
                balance REAL NOT NULL DEFAULT 0,
                UNIQUE(user_email, short_name),
                FOREIGN KEY (user_email) REFERENCES users(email)
            )
        ');

        $pdo->exec('
            CREATE TABLE IF NOT EXISTS expenses (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                account_id INTEGER NOT NULL,
                short_name TEXT NOT NULL,
                description TEXT NOT NULL,
                amount REAL NOT NULL,
                frequency TEXT NOT NULL,
                frequency_months INTEGER DEFAULT NULL,
                start_date TEXT NOT NULL,
                end_date TEXT DEFAULT NULL,
                FOREIGN KEY (account_id) REFERENCES accounts(id)
            )
        ');

        $pdo->exec('
            CREATE TABLE IF NOT EXISTS incomes (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                account_id INTEGER NOT NULL,
                short_name TEXT NOT NULL,
                description TEXT NOT NULL,
                amount REAL NOT NULL,
                frequency TEXT NOT NULL,
                frequency_months INTEGER DEFAULT NULL,
                start_date TEXT NOT NULL,
                end_date TEXT DEFAULT NULL,
                FOREIGN KEY (account_id) REFERENCES accounts(id)
            )
        ');
        $pdo->exec('
            CREATE TABLE IF NOT EXISTS exceptions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                entity_type TEXT NOT NULL,
                entity_id INTEGER NOT NULL,
                name TEXT NOT NULL,
                description TEXT NOT NULL DEFAULT \'\',
                amount REAL NOT NULL,
                frequency TEXT NOT NULL DEFAULT \'ponctuel\',
                frequency_months INTEGER DEFAULT NULL,
                start_date TEXT NOT NULL,
                end_date TEXT DEFAULT NULL
            )
        ');
    
    }

    public function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute($params);

        return $stmt;
    }

    public function exec(string $sql, array $params = []): int
    {
        return $this->query($sql, $params)->rowCount();
    }

    public function fetch(string $sql, array $params = []): ?array
    {
        $result = $this->query($sql, $params)->fetch(PDO::FETCH_ASSOC);

        return $result === false ? null : $result;
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function lastInsertId(): string
    {
        return $this->connect()->lastInsertId();
    }
}
