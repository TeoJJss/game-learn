<?php
require '../modules/config.php';
include '../includes/header.php';

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
    $image = $_FILES['profPic']['tmp_name'];
    if (isset($_FILES['profPic']) && file_exists($image)){
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

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');

        body {
            background-image: url('../images/register_background.png'); 
            background-size: cover; 
            background-position: center; 
        }

        h1{
            text-align: center;
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 3vw;
        }

        .page-content{
            display: flex;
            justify-content: center;
            align-items: center;
        }

        form {
            width: auto;
            margin: 0 auto;
            padding: 20px;
            text-align: center;
            margin-top: 2%;
            margin-bottom: 5%;
            border-color: black;
            background-color: rgba(255, 255, 255, 0.5); 
            backdrop-filter: blur(10px); 
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1); 
            border-radius: 10px; 
            transition: border-radius 0.3s;
        }

        form:hover {
            border-radius: 20px; 
        }

        .searchbar{
            height: 2.5dvw;
            width: auto;
            font-family: 'Poppins', sans-serif;
            font-weight: 200;
            color: #666;
            transition: 0ms;   
        }

        .searchbar:hover,
        .searchbar:focus {
            font-family: 'Poppins', sans-serif;
            font-weight: 400;
            color: black;
        }

        label {
            display: block;
            text-align: left; 
            margin-bottom: 5px; 
            font-size: 0.8em; 
            color: #666; 
        }

        .dropbutton {
            color: black;
            padding: 5px 10px;
            text-decoration: none;
            display: block;
            font-family: 'Poppins', sans-serif;
            font-weight: 300;
            transition: 0.4s;
            border: 1px solid black; 
            background: white; 
            border-radius: 0.7vw;
        }


        .dropbutton option {
            color: black;
            padding: 10px;
            font-family: 'Poppins', sans-serif;
            font-weight: 300;
            transition: 0.4s;
            text-align: center;
        }

        .link {
            display: inline-block;
            vertical-align: middle;
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
        }


        .login-link{
            padding-top: 8px;
        }

    </style>
</head>

<body>

        <div class="page-title">
            
        </div>
        <div class="page-content">
            <form method="post" enctype="multipart/form-data">
            <h1>Register</h1>
            <br>
                <div class="inp-row">
                    <label for="email">Email:</label> 
                        <input type="text" id="email" name="email" autocomplete="off" required autofocus class="searchbar" placeholder="name@email.com">
                </div>
                <div class="inp-row">
                    <label for="name">Name:</label> 
                        <input type="text" id="name" name="name" autocomplete="off" required class="searchbar" placeholder="Enter Your Full Name"><br>
                </div>
                <div class="inp-row">
                    <label for="password">Password:</label> 
                        <input type="password" id="password" name="password" minlength="8" autocomplete="off" required class="searchbar" placeholder="Create Password">
                </div><br>
                <div class="inp-row">
                    <select id="user_type" class="dropbutton" name="user_type" onchange="educator_selected()" required>
                        <option value="" disabled selected>Select a role</option>
                        <option value="student">Student</option>
                        <option value="educator">Educator</option>
                    </select>
                </div><br>
                <div id="educator-only" style="display: none;">
                    <div class="inp-row" id="textarea-row">
                        <label for="about">About: </label>
                            <textarea name="about" id="about" cols="10" rows="2" autocomplete="off" class="searchbar"></textarea>
                    </div><br>
                    <div class="inp-row">
                        <label for="link">Linkedin: </label>
                            <input type="url" name="link" id="link" autocomplete="off" class="searchbar" placeholder="https://www.linkedin.com/in/example-a037278/">
                    </div><br>
                    <div class="inp-row">
                        <label for="jobTitle">Job Title: </label>
                            <input type="text" name="jobTitle" id="jobTitle" autocomplete="off" class="searchbar" placeholder="Teacher">
                    </div><br>
                </div>
                <div class="inp-row">
                    <input type="file" name="profPic" id="profPic-inp" accept=".jpeg, .jpg, .png">
                </div><br>
                <input type="submit" class="login_signup_button" value="Register"> 

                <div class="login-link">    
                    Already have an account? 
                    <a class="link" href="./login.php">Login</a> Now!
                </div>
            </form>
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
<?php include '../includes/footer.php'; ?>
</html>
