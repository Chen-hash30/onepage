<?php

namespace App\Core;

class Mailer {
    public static function sendNotification($to, $subject, $message) {
        $method = Config::get('mail.method', 'phpmail');

        if ($method === 'smtp') {
            return self::sendViaSMTP($to, $subject, $message);
        } else {
            return self::sendViaPHP($to, $subject, $message);
        }
    }

    private static function sendViaPHP($to, $subject, $message) {

        $fromEmail = Config::get('mail.from_email', 'noreply@yourdomain.com');
        $fromName = Config::get('mail.from_name', 'OnePage System');

        $headers = "From: {$fromName} <{$fromEmail}>\r\n";
        $headers .= "Reply-To: {$fromEmail}\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        $result = mail($to, $subject, $message, $headers);

        return $result;
    }

    private static function sendViaSMTP($to, $subject, $message) {

        $smtpHost = Config::get('mail.smtp.host', '');
        $smtpPort = Config::get('mail.smtp.port', 587);
        $smtpUsername = Config::get('mail.smtp.username', '');
        $smtpPassword = Config::get('mail.smtp.password', '');
        $smtpEncryption = Config::get('mail.smtp.encryption', 'tls');
        $fromEmail = Config::get('mail.from_email', 'noreply@yourdomain.com');
        $fromName = Config::get('mail.from_name', 'OnePage System');


        if (empty($smtpHost) || empty($smtpUsername) || empty($smtpPassword)) {
            return self::sendViaPHP($to, $subject, $message);
        }

        // Create socket connection
        $socket = fsockopen(
            ($smtpEncryption === 'ssl' ? 'ssl://' : '') . $smtpHost,
            $smtpPort,
            $errno,
            $errstr,
            30
        );

        if (!$socket) {
            return false;
        }


        // SMTP conversation
        $responses = [];
        $responses[] = self::readSMTPResponse($socket);

        // EHLO
        fwrite($socket, "EHLO " . $_SERVER['SERVER_NAME'] . "\r\n");
        $responses[] = self::readSMTPResponse($socket);

        // STARTTLS if needed
        if ($smtpEncryption === 'tls') {
            fwrite($socket, "STARTTLS\r\n");
            $responses[] = self::readSMTPResponse($socket);
            stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            // Re-EHLO after TLS
            fwrite($socket, "EHLO " . $_SERVER['SERVER_NAME'] . "\r\n");
            $responses[] = self::readSMTPResponse($socket);
        }

        // AUTH LOGIN
        fwrite($socket, "AUTH LOGIN\r\n");
        $responses[] = self::readSMTPResponse($socket);

        fwrite($socket, base64_encode($smtpUsername) . "\r\n");
        $responses[] = self::readSMTPResponse($socket);

        fwrite($socket, base64_encode($smtpPassword) . "\r\n");
        $responses[] = self::readSMTPResponse($socket);

        // MAIL FROM
        fwrite($socket, "MAIL FROM:<{$fromEmail}>\r\n");
        $responses[] = self::readSMTPResponse($socket);

        // RCPT TO
        fwrite($socket, "RCPT TO:<{$to}>\r\n");
        $responses[] = self::readSMTPResponse($socket);

        // DATA
        fwrite($socket, "DATA\r\n");
        $responses[] = self::readSMTPResponse($socket);

        // Email content
        $emailContent = "From: {$fromName} <{$fromEmail}>\r\n";
        $emailContent .= "To: {$to}\r\n";
        $emailContent .= "Subject: {$subject}\r\n";
        $emailContent .= "Content-Type: text/html; charset=UTF-8\r\n";
        $emailContent .= "X-Mailer: OnePage SMTP\r\n";
        $emailContent .= "\r\n";
        $emailContent .= $message;
        $emailContent .= "\r\n.\r\n";

        fwrite($socket, $emailContent);
        $responses[] = self::readSMTPResponse($socket);

        // QUIT
        fwrite($socket, "QUIT\r\n");
        $responses[] = self::readSMTPResponse($socket);

        fclose($socket);

        // Check if all responses indicate success (start with 2 or 3)
        foreach ($responses as $response) {
            if (!preg_match('/^[23]/', $response)) {
                return false;
            }
        }

        return true;
    }

    private static function readSMTPResponse($socket) {
        $response = '';
        while ($line = fgets($socket, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) === ' ') {
                break;
            }
        }
        return $response;
    }
}
