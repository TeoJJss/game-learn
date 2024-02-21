<?php 
    require '../modules/config.php';
    if ($_SERVER['REQUEST_METHOD'] == 'POST'){
        $fbID = $_POST['fbID'];
        $reply = $_POST['eduReply'];
        $upSQL = "UPDATE course_feedback SET eduReply=? WHERE fbID=?";
        $stmt = $conn->prepare($upSQL);
        $stmt->bind_param("si", $reply, $fbID);
        $stmt->execute();
        if ($stmt->affected_rows < 1) {
            trigger_error("Update Failed");
        }else{
            echo "<script>alert('Reply success!'); history.back();</script>";
        }
        exit();
    }
?> 