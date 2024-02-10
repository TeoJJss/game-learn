<?php  
    require '../modules/config.php';
    if (check_ticket() != 'admin'){
        header("Location: ../../index.php");
        exit();
    }

    $course_id = $_GET['cid'];
    $status = $_GET['new_status'];

    $update_sql = "UPDATE course SET `status`=? WHERE courseID=?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("si", $status, $course_id);
    $stmt->execute();
    if ($stmt->affected_rows > 0) {
        echo "<script>alert('Action success!')</script>";
    }else{
        echo "<script>alert('Action failed!')</script>";
    }
    echo "<script>history.back()</script>";
    exit();
?>