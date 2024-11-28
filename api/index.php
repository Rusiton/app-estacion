<?php 

	/**
	* @file index.php
	* @brief API Restful para el proyecto LosApuntes
	* @author Matias Leonardo Baez
	* @date 2024
	* @contact elmattprofe@gmail.com
	*/

	// se inicia o se continua con la sesion
	session_start();

	/*< La respuesta será un JSON */
	header("Content-Type: application/json");

	/*< Se incluyen las variables de entorno*/
	include_once '../env.php';

	/*< Se incluyen las librerias para el manejo de correo electrónico*/
	include '../lib/PHPMailer/Mailer/src/PHPMailer.php';
	include '../lib/PHPMailer/Mailer/src/SMTP.php';
	include '../lib/PHPMailer/Mailer/src/Exception.php';

	/*< Captura el método por el cual se llamo a la API */
	$request_method = $_SERVER["REQUEST_METHOD"];

	/*< Quitamos /api/ de la url */
	$url_result = str_replace("/alumno/11994/app-estacion/api/", "", $_SERVER["REQUEST_URI"]);

	/*< Separa lo que hay en la url (modelo/metodo/)*/
	$url_detonate = explode("/", $url_result);

	// si no se especifico el modelo
	if(!isset($url_detonate[0])){
		echo json_encode(["errno" => 404, "error" => "Falta la variable modelo"]);
		exit();
	}

	// Revisa si la variable modelo esta vacia
	if($url_detonate[0]==""){
		echo json_encode(["errno" => 404, "error" => "Falta especificar el nombre de un modelo"]);
		exit();
	}

	/*< pasa el valor del vector (modelo) a una variable para que sea más siemple*/
	$url_modelo = $url_detonate[0];

	// Normaliza el valor de url_modelo (todo a minuscula y primer letra en mayuscala)
	$modelo = ucfirst(strtolower($url_modelo));

	// Valida la existencia del modelo
	if(!file_exists("../models/$modelo.php")){
		echo json_encode(["errno" => 404, "error" => "Modelo no encontrado"]);
		exit();
	}

	// carga el modelo
	include_once "../models/$modelo.php";

	/*< instancia la clase en un objeto */
	$object = new $modelo();

	/*< si no existe el método */
	if(!isset($url_detonate[1])){
		echo json_encode(["errno" => 404, "error" => "Falta la variable metodo"]);
		exit();
	}

	/*< si el método no tiene valor*/
	if($url_detonate[1]==""){
		echo json_encode(["errno" => 404, "error" => "Falta el valor metodo"]);
		exit();
	}

	/*< se pasa el valor de método a una variable para que sea más fácil*/
	$method =  $url_detonate[1];

	/*< si no existe el método dentro del objeto*/
	if(!method_exists($object, $method)){
		echo json_encode(["errno" => 404, "error" => "Metodo no encontrado dentro de la clase"]);
		exit();
	}

	// captura los datos desde el vector de method correspondiente
	switch ($request_method) {
		case 'DELETE':
		case 'GET':
				$params = $_GET;
			break;
		
		case 'POST':
				$params = $_POST;
			break;
		case 'PUT':
				/*< las variables que se envian por método PUT viajan en el body */
				/*< se captura la petición y se pasan las variables al vector $_PUT */
				parse_str(file_get_contents("php://input"),$_PUT);
				$params = $_PUT;
			break;
	}

	/*< ejecuta el método con los parámetros correspondientes*/
	$result = $object->$method($params);

	/*< imprime el JSON en la página*/
	echo json_encode($result);