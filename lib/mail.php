<?php
    defined('VALID_PAGE') or die('You are not authorized to view this page.');
    
    function mail_f ($mail_to, $mail_subject, $mail_body)
    {
    require 'class.phpmailer.php';

    $mail             = new PHPMailer(); 

    $body             = preg_replace('/\[\]/','',$mail_body);

    $mail->SetFrom('noreply@domain.com', 'Domain');

    $mail->AddReplyTo("noreply@domain.com","Domain");

    $mail->AddAddress($mail_to);

    $mail->Subject    = $mail_subject;

    $mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test

    $mail->MsgHTML($body);

    if(!$mail->Send()) {
      $succes = 0;
    } 
    else {
      $succes = 1;
    }
    return $succes;

    }