<?php  
    require '../modules/config.php';
    if (check_ticket() != 'educator'){
        header("Location: ../index.php");
        exit();
    }

    $course_id = $_GET['cid'];

    $dlt_sql = "DELETE FROM course WHERE courseID=?";
    $stmt = $conn->prepare($dlt_sql);
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    if ($stmt->affected_rows > 0) {
        echo "<script>alert('Action success!')</script>";
    }else{
        echo "<script>alert('Action failed!')</script>";
    }
    echo "<script>location.href='../users/educator/index.php'</script>";
    exit();
?>