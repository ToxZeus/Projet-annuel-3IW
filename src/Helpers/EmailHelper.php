<?php
declare(strict_types=1);

final class EmailHelper
{
    private static function getMailer(): array
    {
        return [
            'from_email' => getenv('SMTP_FROM_EMAIL') ?: 'noreply@budgie.local',
            'from_name' => getenv('SMTP_FROM_NAME') ?: 'Budgie',
            'use_smtp' => (bool)getenv('SMTP_HOST'),
            'host' => getenv('SMTP_HOST') ?: 'localhost',
            'port' => (int)(getenv('SMTP_PORT') ?: 1025),
            'username' => getenv('SMTP_USER') ?: '',
            'password' => getenv('SMTP_PASSWORD') ?: '',
        ];
    }

    public static function sendActivation(string $email, string $firstname, string $token): bool
    {
        $config = self::getMailer();
        $activationUrl = getenv('APP_URL') ?: 'http://localhost:8000';
        $activationLink = $activationUrl . '/?page=activate&token=' . $token;

        $subject = 'Confirmez votre adresse email - Budgie';
        $htmlBody = "
            <h2>Bienvenue sur Budgie !</h2>
            <p>Bonjour $firstname,</p>
            <p>Merci de vous être inscrit sur Budgie. Pour activer votre compte, veuillez cliquer sur le lien ci-dessous :</p>
            <p><a href=\"$activationLink\" style=\"background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;\">Activer mon compte</a></p>
            <p>Ce lien expire dans 24 heures.</p>
            <p>Ou copiez ce lien : $activationLink</p>
        ";

        $textBody = "Bonjour $firstname,\n\nMerci de vous être inscrit sur Budgie. Cliquez sur ce lien pour activer votre compte :\n$activationLink\n\nCe lien expire dans 24 heures.";

        return self::sendEmail($email, $subject, $htmlBody, $textBody);
    }

    public static function sendPasswordReset(string $email, string $firstname, string $token): bool
    {
        $config = self::getMailer();
        $resetUrl = getenv('APP_URL') ?: 'http://localhost:8000';
        $resetLink = $resetUrl . '/?page=reset-password&token=' . $token;

        $subject = 'Réinitialiser votre mot de passe - Budgie';
        $htmlBody = "
            <h2>Réinitialisation de mot de passe</h2>
            <p>Bonjour $firstname,</p>
            <p>Vous avez demandé la réinitialisation de votre mot de passe. Cliquez sur le lien ci-dessous :</p>
            <p><a href=\"$resetLink\" style=\"background-color: #2196F3; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;\">Réinitialiser mon mot de passe</a></p>
            <p>Ce lien expire dans 15 minutes.</p>
            <p>Ou copiez ce lien : $resetLink</p>
        ";

        $textBody = "Bonjour $firstname,\n\nCliquez sur ce lien pour réinitialiser votre mot de passe :\n$resetLink\n\nCe lien expire dans 15 minutes.";

        return self::sendEmail($email, $subject, $htmlBody, $textBody);
    }

    private static function sendEmail(string $to, string $subject, string $htmlBody, string $textBody): bool
    {
        $config = self::getMailer();

        $headers = "From: {$config['from_name']} <{$config['from_email']}>\r\n";
        $headers .= "Reply-To: {$config['from_email']}\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        try {
            $result = mail($to, $subject, $htmlBody, $headers);
            if (!$result) {
                error_log("Erreur d'envoi d'email à $to : " . error_get_last()['message']);
            }
            return $result;
        } catch (Throwable $e) {
            error_log("Exception lors de l'envoi d'email : " . $e->getMessage());
            return false;
        }
    }
}
