<?php
declare(strict_types=1);

final class IncomeService
{
    public function __construct(private Database $db)
    {
    }

    public function create(int $accountId, string $shortName, string $description, float $amount, string $frequency, ?int $frequencyMonths, string $startDate, ?string $endDate): int
    {
        $this->db->exec(
            'INSERT INTO incomes (account_id, short_name, description, amount, frequency, frequency_months, start_date, end_date)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            [$accountId, $shortName, $description, $amount, $frequency, $frequencyMonths, $startDate, $endDate]
        );

        return (int) $this->db->lastInsertId();
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetch('SELECT * FROM incomes WHERE id = ?', [$id]);
    }

    public function findByAccount(int $accountId): array
    {
        return $this->db->fetchAll('SELECT * FROM incomes WHERE account_id = ? ORDER BY start_date DESC', [$accountId]);
    }

    public function findByUser(string $userEmail): array
    {
        return $this->db->fetchAll(
            'SELECT i.*, a.short_name AS account_short_name
             FROM incomes i
             INNER JOIN accounts a ON a.id = i.account_id
             WHERE a.user_email = ?
             ORDER BY i.start_date DESC',
            [$userEmail]
        );
    }

    public function countByAccount(int $accountId): int
    {
        $row = $this->db->fetch('SELECT COUNT(*) AS total FROM incomes WHERE account_id = ?', [$accountId]);

        return (int) ($row['total'] ?? 0);
    }

    public function update(int $id, string $shortName, string $description, float $amount, string $frequency, ?int $frequencyMonths, string $startDate, ?string $endDate): bool
    {
        return $this->db->exec(
            'UPDATE incomes SET short_name = ?, description = ?, amount = ?, frequency = ?, frequency_months = ?, start_date = ?, end_date = ? WHERE id = ?',
            [$shortName, $description, $amount, $frequency, $frequencyMonths, $startDate, $endDate, $id]
        ) > 0;
    }

    public function delete(int $id): bool
    {
        $this->db->exec("DELETE FROM exceptions WHERE entity_type = 'income' AND entity_id = ?", [$id]);

        return $this->db->exec('DELETE FROM incomes WHERE id = ?', [$id]) > 0;
    }
}
