<?php

    if(isset($_SESSION[$_ENV["PROJECT_TOKEN"]]['user'])){
        header("Location: panel");
        exit();
    }

    $_SERVER["REQUEST_METHOD"] = "PUT";

    include_once("models/User.php");
    $user = new User();

    $token = explode("?", $_SERVER["REQUEST_URI"])[1];

    if($user->validate(["token_action" => $token])["errno"] === 200){
        header("Location: login");
        exit();
    }
    
    echo file_get_contents('https://mattprofe.com.ar/alumno/11994/app-estacion/views/templates/invalidToken.html');

?>