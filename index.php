<?php

    /* index.php funciona como un router, redirecciona al controlador especificado en slug */

	// se inicia o se continua con la sesion
	session_start();

	/*< se incluyen las variables de entorno*/
	include_once 'env.php';

	/*< Se incluyen las librerias para el manejo de correo electrónico*/
	include 'lib/PHPMailer/Mailer/src/PHPMailer.php';
	include 'lib/PHPMailer/Mailer/src/SMTP.php';
	include 'lib/PHPMailer/Mailer/src/Exception.php';

	include_once 'lib/TPLEngine/TPLEngine.php';

    // por defecto se presenta landing
	$section = "landing";

	// Si slug tiene valor
	if(strlen($_GET['slug'])>0){
		$section = $_GET['slug'];	
	}

	// Se comprueba que exista el controlador
	if(!file_exists('controllers/'.$section.'Controller.php')){
		// No existe el controlador entonces lo llevamos al controlador de error
		$section = "error404";
	}
	
	// Se carga el controlador especificado en section
	include_once 'controllers/'.$section.'Controller.php';

?>