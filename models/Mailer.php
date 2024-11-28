<?php 
	
	/**
	* @file Mailer.php
	* @brief Declaraciones de la clase Mailer para el envio de correo electrónico.
	* @author Matias Leonardo Baez
	* @date 2024
	* @contact elmattprofe@gmail.com
	*/

	
	/*< declaración de la clase Mailer*/
	class Mailer{

		/**
		 * 
		 * Constructor de la clase
		 * @brief constructor de la clase
		 * 
		 * */
		function __construct(){

		}


		/**
		 * 
		 * Envia un correo electrónico a un correo especificado
		 * @brief Envia un correo electrónico
		 * @param array $params [destinatario, motivo, contenido]
		 * @return array [errno,error]
		 * 
		 * */
		function send($params){


			$destinatario = $params["destinatario"];
			$motivo = $params["motivo"];
			$contenido = $params["contenido"];

			$mail = new PHPMailer\PHPMailer\PHPMailer();

			$mail->isSMTP();
			$mail->SMTPDebug = 0;
			$mail->Host = $_ENV['MAILER_HOST'];
			$mail->Port = $_ENV['MAILER_PORT'];
			$mail->SMTPAuth = $_ENV['MAILER_SMTP_AUTH'];
			$mail->SMTPSecure = $_ENV['MAILER_SMTP_SECURE'];
			$mail->Username = $_ENV['MAILER_REMITENTE'];
			$mail->Password = $_ENV['MAILER_PASSWORD'];

			$mail->setFrom($_ENV['MAILER_REMITENTE'], $_ENV['MAILER_NOMBRE']);
			$mail->addAddress($destinatario);

			$mail->isHTML(true);

			$mail->Subject = utf8_decode($motivo);
			$mail->Body = utf8_decode($contenido);

			if(!$mail->send()){
				error_log("Mailer no se pudo enviar el correo!" );
				$body = array("errno" => 1, "error" => "No se pudo enviar: ". $mail->ErrorInfo);
			}else{
				$body = array("errno" => 0, "error" => "Enviado con exito.");
			}

			return $body;

		}

	}



?>