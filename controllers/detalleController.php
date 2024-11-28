<?php

    if(!isset($_SESSION[$_ENV["PROJECT_TOKEN"]]['user'])){
        header("Location: login");
        exit();
    }

    $tpl = new TPLEngine('detalle');

    $tpl->print_view();

?>