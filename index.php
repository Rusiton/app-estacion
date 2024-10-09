<?php

    session_start();

    include_once 'env.php';

    include_once 'lib/TPLEngine/TPLEngine.php';

    // por defecto se presenta landing
	$seccion = "landing";

	// Si slug tiene valor
	if(strlen($_GET['slug'])>0){
		$seccion = $_GET['slug'];	
	}

	// Se comprueba que exista el controlador
	if(!file_exists('controllers/'.$seccion.'Controller.php')){
		// No existe el controlador entonces lo llevamos al controlador de error
		$seccion = "error404";
	}
	
	// Se carga el controlador especificado en seccion
	include_once 'controllers/'.$seccion.'Controller.php';

?>