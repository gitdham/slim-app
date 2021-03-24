<?php

namespace mail;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader
require_once "phpmailer/PHPMailer.php";
require_once "phpmailer/Exception.php";
require_once "phpmailer/OAuth.php";
require_once "phpmailer/POP3.php";
require_once "phpmailer/SMTP.php";

class Mail {
  private $smtp_host = 'tls://smtp.gmail.com';
  private $username = 'sibarang999@gmail.com';
  private $password = 'pasmodbatununggal';
  private $port = 587;
  private $from = 'Stock Information System';
  private $mail;

  public function __construct($to, $name, $subject, $body, $alt_body) {
    $this->mail = new PHPMailer(true);
    $this->mail->SMTPDebug = 0;
    $this->mail->isSMTP();
    $this->mail->Host = $this->smtp_host;
    $this->mail->SMTPAuth = true;
    $this->mail->Username = $this->username;
    $this->mail->Password = $this->password;
    $this->mail->SMTPSecure = "tls";
    $this->mail->Port = $this->port;
    $this->mail->From = $this->username;
    $this->mail->FromName = $this->from;
    $this->mail->addAddress($to, $name);
    $this->mail->isHTML(true);
    $this->mail->Subject = $subject;
    $this->mail->Body = $body;
    $this->mail->AltBody = $alt_body;
  }

  public function sentMail() {
    try {
      $this->mail->send();
      return 'mail sent';
    } catch (Exception $e) {
      echo "Mailer Error: {$this->mail->ErrorInfo}";
    }
  }
}
