<?php
declare(strict_types=1);

final class AccountService
{
    public function __construct(private Database $db)
    {
    }

    public function create(string $userEmail, string $shortName, string $description, float $interestRate = 0, float $taxRate = 0, float $initialBalance = 0.0): int
    {
        $this->db->exec(
            'INSERT INTO accounts (user_email, short_name, description, created_at, interest_rate, tax_rate, balance)
             VALUES (?, ?, ?, ?, ?, ?, ?)',
            [$userEmail, $shortName, $description, date('Y-m-d'), $interestRate, $taxRate, $initialBalance]
        );

        return (int) $this->db->lastInsertId();
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetch('SELECT * FROM accounts WHERE id = ?', [$id]);
    }

    public function findByUser(string $userEmail): array
    {
        return $this->db->fetchAll('SELECT * FROM accounts WHERE user_email = ? ORDER BY created_at DESC', [$userEmail]);
    }

    public function countByUser(string $userEmail): int
    {
        $row = $this->db->fetch('SELECT COUNT(*) AS total FROM accounts WHERE user_email = ?', [$userEmail]);

        return (int) ($row['total'] ?? 0);
    }

    public function update(int $id, string $shortName, string $description, float $interestRate, float $taxRate, float $initialBalance): bool
    {
        return $this->db->exec(
            'UPDATE accounts SET short_name = ?, description = ?, interest_rate = ?, tax_rate = ?, balance = ? WHERE id = ?',
            [$shortName, $description, $interestRate, $taxRate, $initialBalance, $id]
        ) > 0;
    }

    public function delete(int $id): bool
    {
        $this->db->exec(
            "DELETE FROM exceptions WHERE entity_type = 'expense' AND entity_id IN (SELECT id FROM expenses WHERE account_id = ?)",
            [$id]
        );
        $this->db->exec(
            "DELETE FROM exceptions WHERE entity_type = 'income' AND entity_id IN (SELECT id FROM incomes WHERE account_id = ?)",
            [$id]
        );
        $this->db->exec('DELETE FROM expenses WHERE account_id = ?', [$id]);
        $this->db->exec('DELETE FROM incomes WHERE account_id = ?', [$id]);
        $this->db->exec('DELETE FROM account_shares WHERE account_id = ?', [$id]);

        return $this->db->exec('DELETE FROM accounts WHERE id = ?', [$id]) > 0;
    }

    public function updateBalance(int $id, float $balance): bool
    {
        return $this->db->exec('UPDATE accounts SET balance = ? WHERE id = ?', [$balance, $id]) > 0;
    }
}
