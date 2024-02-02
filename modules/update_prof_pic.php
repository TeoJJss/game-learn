<?php 
    include '../modules/config.php';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $image = $_FILES['profImg']['tmp_name'];
        $img = base64_encode(file_get_contents($image));
        $user_id = $_POST['user_id'];
    
        $stmt = $conn->prepare("UPDATE `profile` SET profilePic=? WHERE userID=?");
        $stmt->bind_param("si", $img, $user_id);
        $stmt->execute();
        header('Location: ../users/index.php');
        exit();
    }
?>