<?php
declare(strict_types=1);

final class ExceptionService
{
    /** @var array<string, array> Cache par requête HTTP pour éviter de relire les mêmes exceptions (prévisions : 12 mois × N entrées) */
    private array $entityCache = [];

    public function __construct(private Database $db)
    {
    }

    public function findByEntity(string $type, int $entityId): array
    {
        $cacheKey = $type . ':' . $entityId;
        if (!isset($this->entityCache[$cacheKey])) {
            $this->entityCache[$cacheKey] = $this->db->fetchAll(
                'SELECT * FROM exceptions WHERE entity_type = ? AND entity_id = ? ORDER BY start_date ASC',
                [$type, $entityId]
            );
        }

        return $this->entityCache[$cacheKey];
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetch('SELECT * FROM exceptions WHERE id = ?', [$id]);
    }

    public function create(
        string $type,
        int $entityId,
        string $name,
        string $description,
        float $amount,
        string $frequency,
        ?int $frequencyMonths,
        string $startDate,
        ?string $endDate
    ): int {
        $this->db->exec(
            'INSERT INTO exceptions (entity_type, entity_id, name, description, amount, frequency, frequency_months, start_date, end_date)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [$type, $entityId, $name, $description, $amount, $frequency, $frequencyMonths, $startDate, $endDate]
        );
        $this->entityCache = [];

        return (int) $this->db->lastInsertId();
    }

    public function update(
        int $id,
        string $name,
        string $description,
        float $amount,
        string $frequency,
        ?int $frequencyMonths,
        string $startDate,
        ?string $endDate
    ): bool {
        $this->entityCache = [];

        return $this->db->exec(
            'UPDATE exceptions SET name = ?, description = ?, amount = ?, frequency = ?, frequency_months = ?, start_date = ?, end_date = ? WHERE id = ?',
            [$name, $description, $amount, $frequency, $frequencyMonths, $startDate, $endDate, $id]
        ) > 0;
    }

    public function delete(int $id): bool
    {
        $this->entityCache = [];

        return $this->db->exec('DELETE FROM exceptions WHERE id = ?', [$id]) > 0;
    }

    public function getEffectiveAmount(float $baseAmount, string $type, int $entityId, string $month): float
    {
        $exceptions = $this->findByEntity($type, $entityId);
        $targetStart = $month . '-01';
        $targetEnd   = date('Y-m-t', strtotime($targetStart));

        foreach ($exceptions as $exc) {
            $excStart = $exc['start_date'];
            $excEnd   = $exc['end_date'] ?? null;
            $freq     = strtolower($exc['frequency']);

            if ($excStart > $targetEnd) continue;
            if ($excEnd !== null && $excEnd < $targetStart) continue;

            if ($freq === 'ponctuel') {
                if ($excStart >= $targetStart && $excStart <= $targetEnd) {
                    return (float) $exc['amount'];
                }
            } elseif ($freq === 'mensuel') {
                return (float) $exc['amount'];
            } elseif ($freq === 'periodique' || $freq === 'periodic') {
                $monthsInterval = (int) ($exc['frequency_months'] ?? 1);
                if ($monthsInterval <= 0) $monthsInterval = 1;
                $startMonthIndex  = (int) date('Y', strtotime($excStart)) * 12 + (int) date('n', strtotime($excStart));
                $targetMonthIndex = (int) substr($month, 0, 4) * 12 + (int) substr($month, 5, 2);
                $delta = $targetMonthIndex - $startMonthIndex;
                if ($delta >= 0 && $delta % $monthsInterval === 0) {
                    return (float) $exc['amount'];
                }
            }
        }

        return $baseAmount;
    }
}