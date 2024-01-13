<?php
    require './modules/config.php';
    
    $role = check_ticket();

    if ($role){
        header("Location: ./users/$role");
        exit();
    }else{
        header("Location: ./public");
        exit();
    }
?>