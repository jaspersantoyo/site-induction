<?php

interface IMailer
{
  public function initializeMailer(
    $options = array(
      'host' => 'localhost',
      'username' => '',
      'password' => '',
      'smtpAuth' => false,
      'smptAutoTLS' => false,
      'encryption' => '',
      'port' => 25,   
      'senderEmail' => '',
      'senderName' => '',
    )
  );
  public function sendEmail(
    $emailContent = array(
      'recipientEmail' => '',
      'recipientName' => '',
      'subject' => '',
      'body' => '',
      'ical' => '',
    )
  );
}