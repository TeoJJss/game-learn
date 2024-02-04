<!-- Profile page -->
<?php
    include '../modules/config.php';

    if (!check_ticket()) {
        header("Location: ../index.php");
        exit();
    }
    include '../includes/header.php';

    $ticket = $_SESSION['ticket'];
    $ch = curl_init("$base_url/check-ticket?ticket=$ticket");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    $response = json_decode(curl_exec($ch), true);

    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 202) {
        $email = $response["data"]["email"];
        $name = $response["data"]["name"];
        $role = $response["data"]["role"];
        $user_id = $response['data']['user_id'];

        $sql = "SELECT profile.profilePic, profile.linkedin, profile.about, profile.jobTitle FROM `profile` WHERE userID=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($profilePic, $link, $abt, $job);
        $stmt->fetch();

        $no_img = '<img src="../images/user.png" id="profPic-settings">';
        if ($profilePic !== null) {
            $base64Image = $profilePic;
            $img_html =  '<img src="data:image/png;image/jpg;base64,' . $base64Image . '" alt="Profile Picture" id="profPic-settings">';
        } else {
            $img_html = $no_img;
        }
        $stmt->close();
    } else {
        header("Location: ../index.php");
        exit();
    }
?>
<?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $new_name = $_POST['username'];
        $new_email = $_POST['email'];

        $uptBody = json_encode(array(
            'ticket' => $ticket,
            'email' => $new_email,
            'name' => $new_name
        ));
        $ch = curl_init("$base_url/update-prof");

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $uptBody);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
        ));
        $response = curl_exec($ch);
        curl_close($ch);
        
        if ($role == "educator"){
            $sql = "UPDATE `profile` SET about=?, linkedin=?, jobTitle=? WHERE userID=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssi", $_POST['about'], $_POST['linkedin'], $_POST['jobTitle'], $user_id);
            $stmt->execute();
        }
        if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200) {
            echo "<script>alert('Action success!')</script>";
        } else if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 400) {
            echo "<script>alert('Action failed! Email has been registered.')</script>";
        } else {
            echo "<script>alert('Action failed! Something went wrong!')</script>";
        }
        echo "<script>location.href='./index.php';</script>";
        exit();
    }
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link rel="stylesheet" href="../styles/style.css">
    <style>
        #profPic-settings {
            border-radius: 10%;
            margin-left: 1vw;
            max-width: 15vw;
        }

        #edit-button {
            background-color: #D9D9D9;
            color: black;
            margin-left: 3vw;
        }

        .profilePic-container {
            align-items: center;
        }

        .page-content {
            display: flex;
            margin-left: 7vw;
        }

        .details-container {
            margin-left: 10vw;
        }

        .details-container label {
            color: #727272;
        }

        .prof-row {
            padding-bottom: 3vh;
            font-size: 1.5vw;
            gap: 2vw;
        }

        .prof-row input, .prof-row textarea {
            /* width: 40vw; */
            /* height: 5vh; */
            font-size: 1.5vw;
            font-family: 'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif;
            min-height: fit-content;
            min-width: 32vw;
            max-width: 40vw;
        }
        .prof-row textarea{
            min-height: 30vh;
        }
        .prof-action-buttons {
            margin-left: 17vw;
        }

        button#logout {
            background-color: #9747FF;
        }

        button#logout:hover {
            box-shadow: 5px 0 10px rgba(151, 71, 255, 0.5);
        }

        .button {
            margin-right: 3vh;
        }

        #imgUpload {
            display: none;
        }

        #about-field{
            display: flex;
            align-items: center;
        }
        .badge{
            max-width: 10vw;
            margin-left: 2vw;
        }
    </style>
</head>

<body>
    <script>
        var originalName = document.getElementById("username").value;
        var originalEmail = document.getElementById("email").value;

        function showWarning() {
            var nameField = document.getElementById("username").value;
            var emailField = document.getElementById("email").value;

            var warningPopup = document.getElementById("warningPopup");
            if (nameField !== originalName || emailField !== originalEmail) {
                warningPopup.style.display = "block";
                document.getElementById('update-prof').disabled =false;
            } else {
                warningPopup.style.display = "none";
            }
        }
    </script>
    <?php include '../includes/warning.php'; ?>
    <div class="page">
        <br><br>
        
        <div class="page-title">
            <h1><img src="../images/nav_picture/setting.png" alt="Settings">Settings</h1>
        </div>
        <div class="page-content">
            <div class="profilePic-container">
                <?php echo $img_html; ?><br><br>
                <button class="button" id="edit-button">Edit Picture</button>
                <form action="../modules/update_prof_pic.php" method="post" enctype="multipart/form-data">
                    <input type="text" name="user_id" value="<?php echo $user_id; ?>" hidden>
                    <input type="file" name="profImg" accept=".jpeg, .jpg, .png" id="imgUpload" onchange="handleFileSelection()">
                    <input type="submit" id="chg-img" hidden>
                </form>
                <?php if ($role=='student'){
                    $sql = "SELECT pointValue FROM `point` WHERE userID=?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $response['data']['user_id']);
                    $stmt->execute();
                    $stmt->bind_result($points);
                    $stmt->fetch();

                    include '../modules/badge.php';

                    $lvl = check_badge($points);
                    echo "<img src='../images/nav_picture/badge0$lvl.png' class='badge' title='Level $lvl'>";
                }
                ?>
            </div>
            <div class="details-container">
                <form method="post">
                    <div class="prof-row">
                        <label for="username">Username :</label><br>
                        <input type="text" name="username" id="username" value="<?php echo $name; ?>" oninput="showWarning()" required autocomplete="off"><br>
                    </div>
                    <div class="prof-row">
                        <label for="email">Email : </label><br>
                        <input type="email" name="email" id="email" value="<?php echo $email; ?>" oninput="showWarning()" required autocomplete="off"><br><br>
                    </div>
                    <div class="prof-row">
                        <label for="role">Role : </label><input type="text" name="" id="role" value="<?php echo $role; ?>" disabled><br>
                    </div>
                    <?php if ($role == 'student') { ?>
                        <div class="prof-row">
                            <label for="status">Status : </label><input type="text" name="" id="status" disabled><br>
                        </div>
                        <div class="prof-row"><label for="point">Point : </label><input type="number" name="" id="point" value="<?php echo $points; ?>" disabled></div>
                    <?php } else if ($role == 'educator') { ?>
                        <div class="prof-row">
                            <label for="linkedin">Linkedin : </label>
                            <input type="url" name="linkedin" id="linkedin" value="<?php echo $link; ?>" oninput="showWarning()" autocomplete="off"><br><br>
                        </div>
                        <div class="prof-row">
                            <label for="jobTitle">Job Title : </label>
                            <input type="text" name="jobTitle" id="jobTitle" value="<?php echo $job; ?>" oninput="showWarning()" autocomplete="off"><br><br>
                        </div>
                        <div class="prof-row" id="about-field">
                            <label for="about">About : </label>
                            <textarea name="about" id="about" oninput="showWarning()" autocomplete="off"><?php echo $abt; ?></textarea><br><br>
                        </div>
                    <?php } ?>
                    <br>
                    <div class="prof-action-buttons">
                        <button class="button" type="button" id="logout" onclick="location.href='../modules/logout.php'">Logout <img src="../images/nav_picture/logout.png" alt="Logout" width="18"></button>
                        <button class="button" type="submit" id="update-prof" disabled>Update Profile</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('edit-button').addEventListener('click', function() {
                document.getElementById('imgUpload').click();
            });
        });

        function handleFileSelection() {
            const fileInput = document.getElementById('imgUpload');
            const selectedFile = fileInput.files[0];

            if (selectedFile) {
                document.getElementById('chg-img').click();
            }
        }
    </script>
</body>
<?php include '../includes/footer.php'; ?>

</html>