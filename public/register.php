<?php
require '../modules/config.php';

if (check_ticket()) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $name = $_POST['name'];
    $password = $_POST['password'];
    $role = $_POST['user_type'];
    $img = null;
    if (isset($_FILES['profPic'])){
        $image = $_FILES['profPic']['tmp_name'];
        $img = base64_encode(file_get_contents($image));
    }

    if ($role=='educator'){
        $abt = isset($_POST['about']) ? $_POST['about'] : null;
        $link = isset($_POST['link']) ? $_POST['link'] : null;
        $jobTitle = isset($_POST['jobTitle']) ? $_POST['jobTitle'] : null;
    }

    $request_body = json_encode(array(
        "email" => $email,
        "name" => $name,
        "password" => $password,
        "role" => $role
    ));

    $ch = curl_init("$base_url/register");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $request_body);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    $response = json_decode(curl_exec($ch), true);

    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200) {
        $sql = "INSERT INTO `profile`(userID, profilePic) VALUES(?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $response['user_id'], $img);
        $stmt->execute();
        if ($role == "student") {
            $sql = "INSERT INTO `point`(userID) VALUES(?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $response['user_id']);
            $stmt->execute();
        }else if ($role == "educator"){
            $sql = "UPDATE `profile` SET about=?, linkedin=?, jobTitle=? WHERE userID=?";
            $stmt = $conn->prepare($sql);
            
            $stmt->bind_param("sssi", $abt, $link, $jobTitle, $response['user_id']);
            $stmt->execute();
        }
        $stmt->close();

        echo "<script>alert('Registration success!');</script>";
        echo "<script>location.href = '../index.php';</script>";
        exit();
    } else if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 403) {
        $msg = $response['msg'];
        echo "<script>alert('$msg');</script>";
    } else {
        echo "<script>alert('Register failed!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="../styles/style.css">
</head>

<body>
    <div class="page">
        <div class="page-title">
            <h1>Register</h1>
        </div>
        <div class="page-content">
            <form method="post" enctype="multipart/form-data">
                <div class="inp-row">
                    <label for="email">Email:</label> <input type="text" id="email" name="email" autocomplete="off" required autofocus>
                </div><br><br>
                <div class="inp-row">
                    <label for="name">Name:</label> <input type="text" id="name" name="name" autocomplete="off" required><br><br>
                </div>
                <div class="inp-row">
                    <label for="password">Password:</label> <input type="password" id="password" name="password" minlength="8" autocomplete="off" required>
                </div><br><br>
                <div class="inp-row">
                    <select id="user_type" class="dropbutton" name="user_type" onchange="educator_selected()" required>
                        <option value="" disabled selected>Select a role</option>
                        <option value="student">Student</option>
                        <option value="educator">Educator</option>
                    </select>
                </div><br>
                <div id="educator-only" style="display: none;">
                    <div class="inp-row" id="textarea-row">
                        <label for="about">About: </label><textarea name="about" id="about" cols="10" rows="2" autocomplete="off"></textarea>
                    </div><br>
                    <div class="inp-row">
                        <label for="link">Linkedin: </label><input type="url" name="link" id="link" autocomplete="off">
                    </div><br>
                    <div class="inp-row">
                        <label for="jobTitle">Job Title: </label><input type="text" name="jobTitle" id="jobTitle" autocomplete="off">
                    </div><br>
                </div>
                <div class="inp-row">
                    <input type="file" name="profPic" id="profPic-inp" accept=".jpeg, .jpg, .png">
                </div>
                <input type="submit" class="login_signup_button" value="Register"> Already have an account? <a href="./login.php">Login</a> Now!
            </form>
        </div>
    </div>
    <script>
        function educator_selected() {
            var userTypeSelect = document.getElementById('user_type');
            if (userTypeSelect.value == 'educator') {
                document.getElementById('educator-only').style.display = 'block';
            } else {
                document.getElementById('educator-only').style.display = 'none';
            }
        }
    </script>
</body>

</html>