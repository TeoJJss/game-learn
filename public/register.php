<?php 
    require '../modules/config.php';
    include '../includes/header.php';

    if (check_ticket()){
        header("Location: ../index.php");
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST'){
        $email = $_POST['email'];
        $name = $_POST['name'];
        $password = $_POST['password'];

        $request_body = json_encode(array(
            "email" => $email,
            "name" => $name,
            "password" => $password
        ));

        $ch = curl_init("$base_url/update-pass");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request_body);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
        ));
        $response = json_decode(curl_exec($ch), true);
        if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200){
            echo "<script>alert('Update Password success!')</script>";
        }else{
            echo "<script>alert('Update Password failed!')</script>";
        }
        curl_close($ch);
        echo "<script>location.href = '../index.php';</script>";
        exit();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Passwordt</title>
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

    <style>
        body {
            background-image: url('../images/login_background.png'); 
            background-size: cover; 
            background-position: center; 
        }

        h1{
            text-align: center;
            font-size: 2em;
        }

        form {
            width: 300px;
            margin: 0 auto;
            padding: 30px;
            text-align: center;
            margin-top: 3%;
            margin-bottom: 4%;
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

        label {
            display: block;
            text-align: left; 
            margin-bottom: 5px; 
            font-size: 0.8em; 
            color: #666; 
        }

        #email{
            width: 23em;
            height: 3em;
        }

        .link {
            display: inline-block;
            vertical-align: middle;
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
        }
        
        .login-link{
            padding: 10px;
        }

        .register-link{
            padding-top: 10px;
            padding-bottom: 0%;
        }

    </style>
</head>
<body>  
    
    <form method="post">
    <h1>Reset Password</h1>
        <label for="email">Email: </label>
        <input id="email" class="searchbar" type="text" name="email" id="email" placeholder="Enter your email" autofocus>
        <br><br>
        <label for="name">Username: </label>
        <input id="email" class="searchbar" type="text" name="name" id="name" placeholder="Enter your username">
        <br><br>
        <label for="password">New Password: </label>
        <input id="email" class="searchbar" type="password" name="password" id="password" placeholder="New Password">
        <br><br>

        <input class="login_signup_button" type="submit" value="Reset Password">
        <div class="login-link">
            Or <a href="./login.php" class="link">Login</a>
        </div>
        <hr>
        <div class="register-link">
        No account? Please <a class="link" href="./register.php"> Sign Up</a>    
        </div>
    </form>
</body>
<?php include '../includes/footer.php'; ?>
</html>
