<?php

namespace Controller;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require_once("module/phpmailer/vendor/autoload.php");

class MailController{
    /** Default value: SMTP::DEBUG_OFF */
    public static int $smtp_debug = SMTP::DEBUG_OFF;
    /** Default value: true */
    public static bool $isSMTP = true;
    /** Default value: "smtp.gmail.com" */
    public static string $host = "smtp.gmail.com";
    public static $username = null;
    public static $password = null;
    /** Default value: PHPMailer::ENCRYPTION_SMTPS */
    public static $SMTP_secure = PHPMailer::ENCRYPTION_SMTPS;
    /** Default value: 465 */
    public static int $port = 465;


    public static function sendMailTo(string $mail_to, string $subject, string $body, string $alt_body){
        $mail = new PHPMailer(true);

        try {
            $mail->SMTPDebug = self::$smtp_debug;
            if (self::$isSMTP)
                $mail->isSMTP();
            $mail->Host = self::$host;
            $mail->SMTPAuth = true;
            $mail->Username = self::$username;
            $mail->Password = self::$password;
            $mail->SMTPSecure = self::$SMTP_secure;
            $mail->Port = self::$port;

            $mail->setFrom(self::$username, strchr(self::$username, '@', true));
            $mail->addAddress($mail_to);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->AltBody = $alt_body;

            $mail->send();
        } catch (Exception $e) {
            return "Message could not be sent. Mailer Error: ".$mail->ErrorInfo;
        }

        return true;
    }
}

?>
