<?php

    $_SERVER["REQUEST_METHOD"] = "PUT";

    include_once("models/User.php");
    $user = new User();

    $token = explode("?", $_SERVER["REQUEST_URI"]);

    if(!isset($token[1])){
        $tpl = new TPLEngine('templates/invalidToken');
        $tpl->print_view();
        exit();
    }

    $token = $token[1];

    if($user->block(["token" => $token])["errno"] === 404){
        $tpl = new TPLEngine('templates/invalidToken');
        $tpl->print_view();
        exit();
    }
    
    $tpl = new TPLEngine('blocked');
    $tpl->print_view();
    exit();

?>