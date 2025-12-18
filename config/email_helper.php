<?php
if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

$phpmailer_path = __DIR__ . '/../phpmailer';

if (file_exists($phpmailer_path . '/Exception.php')) {
    require_once $phpmailer_path . '/Exception.php';
    require_once $phpmailer_path . '/PHPMailer.php';
    require_once $phpmailer_path . '/SMTP.php';
} else {
    require_once __DIR__ . '/../assets/phpmailer/Exception.php';
    require_once __DIR__ . '/../assets/phpmailer/PHPMailer.php';
    require_once __DIR__ . '/../assets/phpmailer/SMTP.php';
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
use Dotenv\Dotenv;

if (file_exists(__DIR__ . '/../../.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
    $dotenv->safeLoad();
} elseif (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->safeLoad();
}

function kirimEmail($to_email, $to_name, $subject, $body) {
    $mail = new PHPMailer(true);

    try {
        $mail->SMTPDebug = 0; 
        $mail->isSMTP();
        
        $mail->Host       = $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['SMTP_USER']; 
        $mail->Password   = $_ENV['SMTP_PASS'];  
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $_ENV['SMTP_PORT'] ?? 587;

        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        $fromEmail = $_ENV['SMTP_FROM_EMAIL'] ?? 'noreply@eventkampus.com';
        $fromName  = $_ENV['SMTP_FROM_NAME'] ?? 'Admin EventKampus';
        
        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($to_email, $to_name);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        return true;

    } catch (Exception $e) {
        file_put_contents(__DIR__ . '/../email_error_log.txt', date('Y-m-d H:i:s') . " - Error: " . $mail->ErrorInfo . "\n", FILE_APPEND);
        return false;
    }
}
?>