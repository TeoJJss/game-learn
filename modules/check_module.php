<?php
    require '../modules/config.php';
    $role = check_ticket();

    $ticket = $_SESSION['ticket'];
    $ch = curl_init("$base_url/check-ticket?ticket=$ticket");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    $response = json_decode(curl_exec($ch), true);

    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 202){
        $moduleID = $_GET['mid'];
        $userID = $response["data"]["user_id"];

        $insert_sql = "INSERT INTO module_enrolment(userID, moduleID) VALUES(?, ?)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("ii", $userID, $moduleID);
        $stmt->execute();

        if($stmt->affected_rows === 0){
            echo "<script>alert('Action failed! Something went wrong.')</script>";
        }
        $stmt -> close();
    }
    echo "<script>history.back()</script>";
    exit();
?>