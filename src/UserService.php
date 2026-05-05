<?php
declare(strict_types=1);

final class UserService
{
    public function __construct(private Database $db)
    {
    }

    public function create(string $email, string $fullName, string $password): int
    {
        $this->db->exec(
            'INSERT INTO users (email, full_name, password_hash, created_at)
             VALUES (?, ?, ?, ?)',
            [$email, $fullName, password_hash($password, PASSWORD_DEFAULT), date('Y-m-d H:i:s')]
        );

        return (int) $this->db->lastInsertId();
    }

    public function findByEmail(string $email): ?array
    {
        return $this->db->fetch('SELECT * FROM users WHERE email = ?', [$email]);
    }

    public function verifyCredentials(string $email, string $password): ?array
    {
        $user = $this->findByEmail($email);

        if ($user === null) {
            return null;
        }

        if (!password_verify($password, $user['password_hash'])) {
            return null;
        }

        return $user;
    }

    public function existsByEmail(string $email): bool
    {
        return $this->findByEmail($email) !== null;
    }
}
