<?php
require_once ABSPATH . WPINC . '/class-phpmailer.php';
require_once 'IMailer.php';

class CustomMailer implements IMailer {

  private $mail;

  function __construct(
    $options = array(
      'host' => 'localhost',
      'username' => '',
      'password' => '',
      'smtpAuth' => false,
      'smptAutoTLS' => false,
      'encryption' => '',
      'port' => 25
    )
  ) {
    $this->initializeMailer($options);
  }

  public function initializeMailer(
    $options = array(
      'host' => 'localhost',
      'username' => '',
      'password' => '',
      'smtpAuth' => false,
      'smptAutoTLS' => false,
      'encryption' => '',
      'port' => 25
    )
  ) {
    $data = (object) $options;
    $this->mail = new PHPMailer();

    // Set mailer to use SMTP
    $this->mail->isSMTP();
    // Specify SMTP host
    $this->mail->Host = $data->host;
    // Enable SMTP authentication. Options: true or false
    $this->mail->SMTPAuth = $data->smtpAuth;
    // SMTP username
    $this->mail->Username = $data->username;
    // SMTP password
    $this->mail->Password = $data->password;
    // SMTP Auto TLS. Options: true or false
    $this->mail->SMTPAutoTLS = $data->smptAutoTLS;
    // Setup encryption. Options: '', tls' or 'ssl'    
    $this->mail->SMTPSecure = $data->encryption;
    // Setup TCP port to connect to
    $this->mail->Port = $data->port;
  }

  public function sendEmail(
    $emailContent = array(
      'recipientEmail' => '',
      'recipientName' => '',
      'subject' => '',
      'body' => '',
      'icals' => '',
      'successMessage' => '',
      'errorMessage' => '',
    )
  ) {

    $message = null;
    $data = (object) $emailContent;

    // Add recipient/s - will let you add multiple emails but will only have one name.
    $this->setEmailRecipient($this->mail, $data->recipientName, $data->recipientEmail);

    // Set email format to HTML
    $this->mail->isHTML(true);
    $this->mail->Subject = $data->subject;
    $this->mail->Body    = $data->body;

    if (!empty($data->icals))
      $this->attachIcalFiles($data->icals);

    if($this->mail->send())
      $message = $data->successMessage;
    else
      $message = str_replace('{error_info}', $this->mail->ErrorInfo, $data->errorMessage);

    echo $message;
  }

  public function setEmailSender( $senderEmail, $senderName ) {
    $this->mail->setFrom($senderEmail, $senderName);
  }

  protected function setEmailRecipient( $mail, $name, $emails ) {
    $mail->ClearAllRecipients();
    foreach (explode(',', $emails) as $email) {
      $mail->addAddress($email, $name);
    }
  }

  protected function attachIcalFiles( $icals ) {
    $this->mail->ClearAttachments();
    foreach ($icals as $ical) {
      $this->mail->addStringAttachment($ical->content, $ical->filename . '.ics');
    }
  }
}