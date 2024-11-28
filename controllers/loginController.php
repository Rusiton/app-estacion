<?php

    session_unset();
    session_destroy();

    if(isset($_SESSION[$_ENV["PROJECT_TOKEN"]]['user'])){
        header("Location: panel");
        exit();
    }

    $tpl = new TPLEngine('login');

    $tpl->print_view();

?>