<?php 
    require '../modules/config.php';
    $role = check_ticket();

    if ($role != 'admin' or !isset($_GET['postID'])){
        echo "<script>alert('Unauthorized access');location.href='../index.php';</script>";
        exit();
    }
    $postID=$_GET['postID'];

    $dltCommentSql = "DELETE FROM comment WHERE postID=?";
    $stmt = $conn->prepare($dltCommentSql);
    $stmt->bind_param("i", $postID);
    $stmt->execute();
    $stmt -> close();

    $dltPostSql = "DELETE FROM post WHERE postID=?";
    $stmt = $conn->prepare($dltPostSql);
    $stmt->bind_param("i", $postID);
    $stmt->execute();

    if ($stmt->affected_rows < 1) {
        trigger_error("Action Failed");
    } else {
        echo '<script>alert("Deletion Success"); location.href="../users/forum.php";</script>';
    }
    $stmt -> close();
?>