<?php
declare(strict_types=1);

final class UserService
{
    public function __construct(private Database $db)
    {
    }

    public function create(string $email, string $fullName, string $password, string $verificationToken = '', string $tokenExpiry = '', string $plan = 'free'): int
    {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $now = date('Y-m-d H:i:s');
        $plan = in_array($plan, ['free', 'paid'], true) ? $plan : 'free';

        $this->db->exec(
            'INSERT INTO users (email, full_name, password_hash, plan, is_active, verification_token, token_expiry, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            [$email, $fullName, $passwordHash, $plan, false, $verificationToken ?: null, $tokenExpiry ?: null, $now]
        );

        return (int) $this->db->lastInsertId();
    }

    public function findByEmail(string $email): ?array
    {
        return $this->db->fetch('SELECT * FROM users WHERE email = ?', [$email]);
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetch('SELECT * FROM users WHERE id = ?', [$id]);
    }

    public function verifyCredentials(string $email, string $password): ?array
    {
        $user = $this->findByEmail($email);

        if ($user === null) {
            return null;
        }

        if (!$user['is_active']) {
            return null; // Compte non activé
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

    public function activateAccount(string $token): bool
    {
        $user = $this->db->fetch(
            'SELECT id, token_expiry FROM users WHERE verification_token = ? LIMIT 1',
            [$token]
        );

        if (!$user) {
            return false;
        }

        // Vérifier que le token n'a pas expiré
        if (strtotime($user['token_expiry'] ?? '0') < time()) {
            return false;
        }

        $this->db->exec(
            'UPDATE users SET is_active = true, verification_token = NULL, token_expiry = NULL WHERE id = ?',
            [$user['id']]
        );

        return true;
    }

    public function generateResetToken(string $email): ?array
    {
        $user = $this->findByEmail($email);
        if (!$user) {
            return null;
        }

        $resetToken = bin2hex(random_bytes(32));
        $resetTokenExpiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        $this->db->exec(
            'UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?',
            [$resetToken, $resetTokenExpiry, $user['id']]
        );

        return ['reset_token' => $resetToken, 'user_id' => $user['id']];
    }

    public function resetPassword(string $token, string $newPassword): bool
    {
        $user = $this->db->fetch(
            'SELECT id, reset_token_expiry FROM users WHERE reset_token = ? LIMIT 1',
            [$token]
        );

        if (!$user) {
            return false;
        }

        // Vérifier que le token n'a pas expiré
        if (strtotime($user['reset_token_expiry'] ?? '0') < time()) {
            return false;
        }

        $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);

        $this->db->exec(
            'UPDATE users SET password_hash = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?',
            [$newPasswordHash, $user['id']]
        );

        return true;
    }

    public function update(int $id, string $fullName): bool
    {
        $now = date('Y-m-d H:i:s');
        $this->db->exec(
            'UPDATE users SET full_name = ?, updated_at = ? WHERE id = ?',
            [$fullName, $now, $id]
        );

        return true;
    }

    public function updatePlan(string $email, string $plan): bool
    {
        $plan = in_array($plan, ['free', 'paid'], true) ? $plan : 'free';

        return $this->db->exec(
            'UPDATE users SET plan = ?, updated_at = ? WHERE email = ?',
            [$plan, date('Y-m-d H:i:s'), $email]
        ) > 0;
    }
}
