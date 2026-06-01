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
        return $this->db->exec('DELETE FROM incomes WHERE id = ?', [$id]) > 0;
    }
}
