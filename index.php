<?php
    require './modules/config.php';
    session_start();
    echo $_SESSION['ticket'];
    $role = check_ticket();

    if ($role){
        header("Location: ./users/$role");
        exit();
    }else{
        header("Location: ./public");
        exit();
    }
?>