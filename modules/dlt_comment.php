<?php 
    require '../modules/config.php';
    $role = check_ticket();

    if ($role != 'admin' || !isset($_GET['postID']) || !isset($_GET['commentID'])){
        echo "<script>alert('Unauthorized access');location.href='../index.php';</script>";
        exit();
    }
    $postID=$_GET['postID'];
    $commentID = $_GET['commentID'];

    $dltCommentSql = "DELETE FROM comment WHERE postID=? AND commentID=?";
    $stmt = $conn->prepare($dltCommentSql);
    $stmt->bind_param("ii", $postID, $commentID);
    $stmt->execute();
    

    if ($stmt->affected_rows < 1) {
        trigger_error("Action Failed");
    } else {
        echo '<script>alert("Deletion Success"); location.href="../users/forum.php";</script>';
    }
    $stmt -> close();
?>