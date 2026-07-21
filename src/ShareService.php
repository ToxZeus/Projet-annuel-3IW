<?php
declare(strict_types=1);

final class ShareService
{
    private const INVITE_LIFETIME_HOURS = 24;

    public function __construct(private Database $db)
    {
    }

    public function invite(int $accountId, string $ownerEmail, string $invitedEmail): string
    {
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+' . self::INVITE_LIFETIME_HOURS . ' hours'));

        $this->db->exec(
            'INSERT INTO account_shares (account_id, owner_email, invited_email, token, status, created_at, expires_at)
             VALUES (?, ?, ?, ?, ?, ?, ?)',
            [$accountId, $ownerEmail, $invitedEmail, $token, 'pending', date('Y-m-d H:i:s'), $expiresAt]
        );

        return $token;
    }

    public function findPendingInvite(int $accountId, string $invitedEmail): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM account_shares
             WHERE account_id = ? AND invited_email = ? AND status = 'pending' AND expires_at > ?",
            [$accountId, $invitedEmail, date('Y-m-d H:i:s')]
        );
    }

    public function findByToken(string $token): ?array
    {
        return $this->db->fetch('SELECT * FROM account_shares WHERE token = ?', [$token]);
    }

    public function isExpired(array $share): bool
    {
        return !empty($share['expires_at']) && $share['expires_at'] < date('Y-m-d H:i:s');
    }

    public function accept(string $token, string $userEmail): bool
    {
        $share = $this->findByToken($token);
        if ($share === null || $share['status'] !== 'pending') {
            return false;
        }

        if ($this->isExpired($share)) {
            return false;
        }

        if (strcasecmp($share['invited_email'], $userEmail) !== 0) {
            return false;
        }

        return $this->db->exec(
            "UPDATE account_shares SET status = 'accepted', accepted_at = ? WHERE id = ?",
            [date('Y-m-d H:i:s'), $share['id']]
        ) > 0;
    }

    public function findByAccount(int $accountId): array
    {
        return $this->db->fetchAll(
            'SELECT * FROM account_shares WHERE account_id = ? ORDER BY created_at DESC',
            [$accountId]
        );
    }

    public function findAccountsSharedWithUser(string $userEmail): array
    {
        return $this->db->fetchAll(
            "SELECT account_shares.*, accounts.short_name, accounts.description, accounts.balance,
                    accounts.interest_rate, accounts.tax_rate, accounts.created_at AS account_created_at
             FROM account_shares
             INNER JOIN accounts ON accounts.id = account_shares.account_id
             WHERE account_shares.invited_email = ? AND account_shares.status = 'accepted'
             ORDER BY account_shares.accepted_at DESC",
            [$userEmail]
        );
    }

    public function hasAcceptedAccess(int $accountId, string $userEmail): bool
    {
        $row = $this->db->fetch(
            "SELECT id FROM account_shares WHERE account_id = ? AND invited_email = ? AND status = 'accepted'",
            [$accountId, $userEmail]
        );

        return $row !== null;
    }

    public function revoke(int $shareId, string $ownerEmail): bool
    {
        return $this->db->exec(
            'DELETE FROM account_shares WHERE id = ? AND owner_email = ?',
            [$shareId, $ownerEmail]
        ) > 0;
    }
}