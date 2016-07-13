<?php

Trait Emailer {

  public static function getEmailer($key, $data=null) {

    if(Arr::iterable($data)) {
      extract($data);
    }
    $emailer = self::getEmailers($key);
    
    ob_start();
    include $emailer['template_path'];
    $emailer['rendered_template'] = ob_get_contents();
    ob_get_clean();
    
    return $emailer;
  }


  public static function sendEmailer($to, $emailer, $debug=false) {
    if(!$to) return false;
    if(!Arr::iterable($emailer)) return false;
   
    $mail = new PHPMailer;
    $mail->Port = 1025;
    $mail->setFrom($emailer['from']);
    $mail->addReplyTo($emailer['from']);
    $mail->addAddress($to);
    $mail->isHTML(true);
    $mail->Subject = $emailer['subject'];
    $mail->Body = $emailer['rendered_template'];
      
    $did_it_send = $mail->send();

    if($debug) {
      if($did_it_send) {
        echo 'Message could not be sent.';
        echo 'Mailer Error: ' . $mail->ErrorInfo;
      } else {
        echo 'Message has been sent';
      }
      exit;
    }
  }
}