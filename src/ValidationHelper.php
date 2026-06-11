<?php
declare(strict_types=1);

final class ValidationHelper
{
    public static function cleanEmail(string $email): string
    {
        return strtolower(trim($email));
    }

    public static function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function validatePassword(string $password): bool
    {
        // Au moins 8 caractères, 1 minuscule, 1 majuscule, 1 chiffre, 1 caractère spécial
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password) === 1;
    }

    public static function validateMinLength(string $value, int $min): bool
    {
        return strlen(trim($value)) >= $min;
    }

    public static function cleanName(string $name): string
    {
        $cleanName = trim(strip_tags($name));

        return ucfirst(strtolower($cleanName));
    }

    public static function sanitizeString(string $str): string
    {
        return trim(htmlspecialchars($str, ENT_QUOTES, 'UTF-8'));
    }
}
