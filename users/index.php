<!-- Profile page -->
<?php 
    include '../modules/config.php';

    if (!check_ticket()){
        header("Location: ../index.php");
        exit();
    }

    $ticket = $_SESSION['ticket'];
    $ch = curl_init("$base_url/check-ticket?ticket=$ticket");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    $response = json_decode(curl_exec($ch), true);

    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 202){
        $email = $response["data"]["email"];
        $name = $response["data"]["name"];
        $role = $response["data"]["role"];

        $sql = "SELECT profilePic FROM `profile` WHERE userID=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $response['data']['user_id']);
        $stmt->execute();
        $stmt->bind_result($profilePic);
        $stmt->fetch();

        $no_img = '<img src="../images/user.png" class="profilePic">';
        if ($profilePic !== null) {
            $imageInfo = getimagesizefromstring($profilePic);

            if ($imageInfo !== false) {
                $mime = $imageInfo['mime'];
                $base64Image = base64_encode($profilePic);
                $img_html =  '<img src="data:' . $mime . ';base64,' . $base64Image . '" alt="Profile Picture">';
            } else {
                $img_html = $no_img;
            }
        } else {
            $img_html = $no_img;
        }
        $stmt->close();
    }else{
        header("Location: ../index.php");
        exit();
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <style>
        .profilePic{
            width: 10vh;
        }
    </style>
</head>
<body>
    <h1>My Profile</h1>
    <p><?php echo $img_html;?></p>
    <p>Email: <?php echo $email; ?></p>
    <p>Name: <?php echo $name; ?></p>
    <p>Role: <?php echo $role; ?></p>
</body>
</html>