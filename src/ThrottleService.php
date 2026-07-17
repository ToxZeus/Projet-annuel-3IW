<?php
declare(strict_types=1);

final class ThrottleService
{
    public function __construct(private Database $db)
    {
    }

    public function tooManyAttempts(string $key, int $maxAttempts, int $windowMinutes): bool
    {
        $cutoff = date('Y-m-d H:i:s', time() - $windowMinutes * 60);
        $this->db->exec('DELETE FROM auth_attempts WHERE attempted_at < ?', [$cutoff]);

        $row = $this->db->fetch(
            'SELECT COUNT(*) AS n FROM auth_attempts WHERE identifier = ? AND attempted_at >= ?',
            [$key, $cutoff]
        );

        return (int) ($row['n'] ?? 0) >= $maxAttempts;
    }

    public function hit(string $key): void
    {
        $this->db->exec(
            'INSERT INTO auth_attempts (identifier, attempted_at) VALUES (?, ?)',
            [$key, date('Y-m-d H:i:s')]
        );
    }

    public function clear(string $key): void
    {
        $this->db->exec('DELETE FROM auth_attempts WHERE identifier = ?', [$key]);
    }
}
