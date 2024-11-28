<?php

    if(isset($_SESSION[$_ENV["PROJECT_TOKEN"]]['user'])){
        header("Location: panel");
        exit();
    }

    $tpl = new TPLEngine('recovery');

    $tpl->print_view();

?>