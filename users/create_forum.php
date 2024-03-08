<?php
    require '../modules/config.php';
    $role = check_ticket();
    if (!$role) {
        header("Location: ../index.php");
        exit();
    }
    include '../includes/header.php';

    if ($_SERVER['REQUEST_METHOD'] == 'POST'){
        $ticket = $_SESSION['ticket'];
        $ch = curl_init("$base_url/check-ticket?ticket=$ticket");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $response = json_decode(curl_exec($ch), true);
        $userID_post = $response['data']['user_id'];

        $postContent = $_POST['content'];
        $img = null;
        $image = $_FILES['postMedia']['tmp_name'];
        if (isset($_FILES['postMedia']) && file_exists($image)){
            $img = base64_encode(file_get_contents($image));
        }

        $newPostSql = "INSERT INTO post(content, postMedia, userID) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($newPostSql);
        $stmt->bind_param("ssi", $postContent, $img, $userID_post);
        $stmt->execute();
        if ($stmt->affected_rows < 1) {
            trigger_error("Post Failed");
        } else {
            echo '<script>alert("Post Success"); location.href="./forum.php";</script>';
        }
        $stmt -> close();
        exit();
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create new post in Forum</title>
    <link rel="stylesheet" href="../styles/style.css">
    <style>
        .page-content {
            margin-left: 23vw;
        }
    </style>
</head>

<body>
    <div class="page">
        <div class="page-title">
            <h1><img src="<?php echo $base; ?>images/educator_pic/forum.png" alt="Forum Icon" class="forum-image">Create Forum Post</h1>
        </div>
        <div class="page-content">
            <form method="post" enctype="multipart/form-data">
                <div class="inp-row">
                    <label for="content">Post Content</label><br>
                    <textarea name="content" id="content" placeholder="Enter the content of new post" cols="30" rows="3" maxlength="1500" required autocomplete="off" autofocus></textarea>
                </div>
                <div class="inp-row">
                    <label for="postMedia">Image of Forum Post</label><br>
                    <input type="file" name="postMedia" id="postMedia" accept=".png, .jpg, .jpeg">
                </div>
                <input type="submit" value="Post" class="button" style="margin-left: 5vw;">
                <input type="reset" class="button" style="margin-left: 5vw;">
            </form>
        </div>
    </div>
</body>
<?php include '../includes/footer.php'; ?>

</html>