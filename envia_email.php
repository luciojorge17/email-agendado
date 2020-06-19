<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor\autoload.php';
function enviaEmail($idEmpresa, $email_destinatario, $nome_destinatario, $assunto, $mensagem)
{
   include_once 'config_email.php';
   $dadosEmail = get_config($idEmpresa);
   $mail = new PHPMailer(TRUE);
   $mail->isSMTP();
   $mail->Host = $dadosEmail['smtp'];
   $mail->setLanguage('pt_br');
   $mail->CharSet = 'utf-8';
   $mail->SMTPAuth = $dadosEmail['auth'];
   $mail->SMTPSecure = $dadosEmail['secure'];
   $mail->Username = $dadosEmail['email'];
   $mail->Password = $dadosEmail['senha'];
   $mail->Port = $dadosEmail['porta'];
   $mail->isHTML(true);
   $mail->SMTPOptions = array(
      'ssl' => array(
         'verify_peer' => false,
         'verify_peer_name' => false,
         'allow_self_signed' => true
      )
   );
   try {
      $assunto = str_replace('[nome_empresa]', $dadosEmail['empresa'], $assunto);
      $mensagem = str_replace('[nome_empresa]', $dadosEmail['empresa'], $mensagem);
      $mail->setFrom($dadosEmail['email'], $dadosEmail['empresa']);
      $mail->addAddress($email_destinatario, $nome_destinatario);
      $mail->Subject = $assunto;
      $mail->Body = $mensagem;
      if ($mail->send()) {
         return true;
      } else {
         return false;
      }
   } catch (Exception $e) {
      //echo $e->errorMessage();
      return false;
   } catch (\Exception $e) {
      //echo $e->getMessage();
      return false;
   }
}
