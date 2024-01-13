<?php 
    require './config.php';

    if (!check_ticket()){
        echo "<script>alert('Unauthorized access!')</script>";
        header("Location: ../../index.php");
        exit();
    }

    session_start();
    $request_body = json_encode(array(
        "ticket" => $_SESSION['ticket']
    ));
    $ch = curl_init("$base_url/logout");
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $request_body);
    $response = curl_exec($ch);

    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200){
        session_unset();
        session_destroy();
        echo "<script>alert('Logout success!')</script>";
        header("Location: ../index.php");
        exit();
    }else{
        header("Location: ../index.php");
        exit();
    }
?>