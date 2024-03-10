<?php
require '../modules/config.php';
$role = check_ticket();
if ($role == 'admin') {
    header("Location: ../index.php");
    exit();
}
include '../includes/header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userFB = $_POST['userFB'];
    $img = null;
    if (isset($_FILES['userFile']) && $_FILES['userFile']['size'] > 0) {
        $image = $_FILES['userFile']['tmp_name'];
        $img = base64_encode(file_get_contents($image));
    } 

    $ticket = $_SESSION['ticket'];
    $ch = curl_init("$base_url/check-ticket?ticket=$ticket");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    $response = json_decode(curl_exec($ch), true);

    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 202){
        $userID = $response["data"]["user_id"];
    }else{
        trigger_error("Invalid User");
    }

    $stmt =  $conn->prepare("INSERT INTO system_feedback (`sfContent`, `sfMedia`, `userID`) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $userFB, $img, $userID);
    $stmt->execute();
    $stmt->close();
    echo '<script>alert("System feedback is submitted!"); location.href="../users/system_feedback.php";</script>';
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>add_feedback</title>
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .page {
            width: 80%;
            margin: auto;
        }

        .page-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .page-content {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .post {
            width: 100%;
            border: 1px solid #ccc;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
            margin-bottom: 40px;
            position: relative;
        }

        .user-info {
            display: flex;
            align-items: center;
            margin-top: 60px;
            margin-bottom: 20px;
        }

        .user-info img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .user-input-style {
            width: 100%;
            height: 200px;
            padding: 10px;
            margin-top: 10px;
        }

        .action-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            margin-bottom: 60px;
        }

        .file-upload-button {
            width: 45%;
        }

        .submit-button {
            width: 10%;
            background-color: #FFA500;
            border: none;
            color: white;
            padding: 15px 32px;
            text-decoration: none;
            font-size: 16px;
            cursor: pointer;
            transition-duration: 0.4s;
            border-radius: 12px;
        }

        .submit-button:hover {
            background-color: #FF4500;
            transform: scale(1.1);
        }
    </style>

</head>

<body>
    <div class="page">
        <div class="page-title">
            <img src="<?php echo $base; ?>images/sys_feedback.png" alt="system feedback Icon" class="system_feedback image">
            System Feedback
        </div>
        <br><br><br>
        <form action="" method="post" enctype="multipart/form-data">
            <div class="user-input">
                <textarea id="userQuestion" name="userFB" class="user-input-style" placeholder="Write a feedback" required></textarea>
            </div>
            <div class="action-buttons">
                <input type="file" id="userFile" name="userFile" class="file-upload-button">
                <input type="submit" value="Submit" class="submit-button">
            </div>
        </form>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>

</html>
