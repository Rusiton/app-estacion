<?php

    if(isset($_SESSION[$_ENV["PROJECT_TOKEN"]]['user'])){
        header("Location: panel");
        exit();
    }

    $_SERVER["REQUEST_METHOD"] = "GET";

    include_once("models/User.php");
    $user = new User();

    $token_action = explode("?", $_SERVER["REQUEST_URI"]);

    if(!isset($token_action[1])){
        $tpl = new TPLEngine('templates/invalidToken');
        $tpl->print_view();
        exit();
    }

    $token_action = $token_action[1];

    $response = $user->getUserByTokenAction(["token_action" => $token_action]);

    if($response["errno"] === 404){
        $tpl = new TPLEngine('templates/invalidToken');
        $tpl->print_view();
        exit();
    }

    header("Token-Action: $token_action");

    $tpl = new TPLEngine('reset');
    $tpl->print_view();

?>