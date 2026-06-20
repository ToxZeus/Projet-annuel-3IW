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

        if ($config['use_smtp']) {
            return self::sendSmtpEmail($config, $to, $subject, $htmlBody, $textBody);
        }

        $headers = "From: {$config['from_name']} <{$config['from_email']}>\r\n";
        $headers .= "Reply-To: {$config['from_email']}\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        try {
            $result = mail($to, $subject, $htmlBody, $headers);
            if (!$result) {
                $lastError = error_get_last();
                $errorMessage = is_array($lastError) && isset($lastError['message'])
                    ? $lastError['message']
                    : 'unknown mail error';

                error_log("Erreur d'envoi d'email à $to : " . $errorMessage);
            }
            return $result;
        } catch (Throwable $e) {
            error_log("Exception lors de l'envoi d'email : " . $e->getMessage());
            return false;
        }
    }

    private static function sendSmtpEmail(array $config, string $to, string $subject, string $htmlBody, string $textBody): bool
    {
        $host = $config['host'];
        $port = $config['port'];
        $fromEmail = $config['from_email'];
        $fromName = $config['from_name'];

        if ($port === 465) {
            $socket = @stream_socket_client("ssl://{$host}:{$port}", $errorCode, $errorMessage, 10);
        } else {
            $socket = @stream_socket_client("tcp://{$host}:{$port}", $errorCode, $errorMessage, 10);
        }

        if (!$socket) {
            error_log("Impossible de se connecter au serveur SMTP {$host}:{$port} - {$errorMessage}");
            return false;
        }

        try {
            self::smtpReadResponse($socket, [220]);
            self::smtpSendCommand($socket, 'EHLO localhost', [250]);

            if ($port === 587) {
                self::smtpSendCommand($socket, 'STARTTLS', [220]);
                stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                self::smtpSendCommand($socket, 'EHLO localhost', [250]);
            }

            if ($config['username'] !== '') {
                self::smtpSendCommand($socket, 'AUTH LOGIN', [334]);
                self::smtpSendCommand($socket, base64_encode($config['username']), [334]);
                self::smtpSendCommand($socket, base64_encode($config['password']), [235]);
            }

            self::smtpSendCommand($socket, 'MAIL FROM:<' . $fromEmail . '>', [250]);
            self::smtpSendCommand($socket, 'RCPT TO:<' . $to . '>', [250, 251]);
            self::smtpSendCommand($socket, 'DATA', [354]);

            $message = self::buildEmailMessage($fromEmail, $fromName, $to, $subject, $htmlBody, $textBody);
            fwrite($socket, $message . "\r\n.\r\n");
            self::smtpReadResponse($socket, [250]);
            self::smtpSendCommand($socket, 'QUIT', [221]);

            fclose($socket);
            return true;
        } catch (Throwable $e) {
            error_log('Erreur SMTP : ' . $e->getMessage());
            fclose($socket);
            return false;
        }
    }

    private static function buildEmailMessage(string $fromEmail, string $fromName, string $to, string $subject, string $htmlBody, string $textBody): string
    {
        $boundary = '=_budgie_' . bin2hex(random_bytes(8));

        return implode("\r\n", [
            'From: ' . $fromName . ' <' . $fromEmail . '>',
            'To: ' . $to,
            'Subject: ' . $subject,
            'MIME-Version: 1.0',
            'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
            '',
            '--' . $boundary,
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
            '',
            $textBody,
            '',
            '--' . $boundary,
            'Content-Type: text/html; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
            '',
            $htmlBody,
            '',
            '--' . $boundary . '--',
        ]);
    }

    private static function smtpSendCommand($socket, string $command, array $expectedCodes): void
    {
        fwrite($socket, $command . "\r\n");
        self::smtpReadResponse($socket, $expectedCodes);
    }

    private static function smtpReadResponse($socket, array $expectedCodes): string
    {
        $response = '';

        while (($line = fgets($socket, 515)) !== false) {
            $response .= $line;
            if (preg_match('/^(\d{3})([\s-])/', $line, $matches) !== 1) {
                continue;
            }

            if ($matches[2] === ' ') {
                $code = (int) $matches[1];
                if (!in_array($code, $expectedCodes, true)) {
                    throw new RuntimeException('Réponse SMTP inattendue: ' . trim($response));
                }

                return $response;
            }
        }

        throw new RuntimeException('Aucune réponse SMTP reçue.');
    }
}
