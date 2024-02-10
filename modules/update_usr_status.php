<?php  
    require '../modules/config.php';
    if (check_ticket() != 'admin'){
        header("Location: ../../index.php");
        exit();
    }
    $ticket = $_SESSION['ticket'];
    $user_id = $_GET['uid'];
    $status = $_GET['new_status'];
    $remark = $_GET['remark'];

    $uptBody = json_encode(array(
        'ticket' => $ticket,
        'user_id' => $user_id,
        'new_status' => $status,
        'remark' => $remark,
    ));
    $ch = curl_init("$base_url/update-status");

    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $uptBody);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
    ));
    $response = curl_exec($ch);
    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200){
        echo "<script>alert('Action success!')</script>";
    }else{
        echo "<script>alert('Action failed!')</script>";
    }
    curl_close($ch);
    echo "<script>window.history.back();</script>";
    exit();
?>