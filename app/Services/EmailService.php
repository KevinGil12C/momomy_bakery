<?php

namespace App\Services;

use App\config\Config;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Service to handle Email sending using PHPMailer
 */
class EmailService
{
    private $host;
    private $user;
    private $pass;
    private $port;

    public function __construct()
    {
        $this->host = Config::HOST;
        $this->user = Config::MAIL;
        $this->pass = Config::PASSWORD;
        $this->port = Config::SMTP_PORT;
    }

    /**
     * Send email using PHPMailer
     */
    public function send($to, $subject, $body, $attachment = null, $altBody = '')
    {
        if (class_exists(PHPMailer::class)) {
            $mail = new PHPMailer(true);
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host       = $this->host;
                $mail->SMTPAuth   = true;
                $mail->Username   = $this->user;
                $mail->Password   = $this->pass;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL for port 465
                $mail->Port       = $this->port;
                $mail->CharSet    = 'UTF-8';

                // Recipients
                $mail->setFrom($this->user, Config::FROM_NAME);
                $mail->addAddress($to);

                // Attachment
                if ($attachment && file_exists($attachment)) {
                    $mail->addAttachment($attachment);
                }

                // Content
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body    = $body;
                $mail->AltBody = $altBody ?: strip_tags($body);

                $mail->send();
                return true;
            } catch (Exception $e) {
                error_log("Email sending failed: {$mail->ErrorInfo}");
                return false;
            }
        } else {
            // Fallback to mail() if PHPMailer is missing (attachments not supported in this basic fallback)
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= 'From: ' . Config::FROM_NAME . ' <' . $this->user . '>' . "\r\n";
            return mail($to, $subject, $body, $headers);
        }
    }
}
