<?php 
    require '../modules/config.php';

    if (check_ticket()){
        header("Location: ../index.php");
        exit();
    }
    include '../includes/header.php';
    if ($_SERVER['REQUEST_METHOD']=='POST'){
        $email = $_POST['email'];
        $password = $_POST['password'];

        $request_body = json_encode(array(
            "email" => $email,
            "password" => $password
        ));
        $ch = curl_init("$base_url/login");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request_body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $response = json_decode(curl_exec($ch), true);
        if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 201) {
            $ticket = $response['ticket'];
            session_start();
            $_SESSION['ticket'] = $ticket;
            header('Location: ../index.php');
            exit();
        }else if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 401){
            $msg = $response['msg'];
            echo "<script>alert(`$msg`);</script>";
        }else{
            echo "<script>alert('Login failed! Email or password is incorrect. ');</script>";
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');

        body {
            background-image: url('../images/login_background.png'); 
            background-size: cover; 
            background-position: center; 
        }

        h1{
            text-align: center;
        }

        form {
            width: 300px;
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

        label {
            display: block;
            text-align: left; 
            margin-bottom: 5px; 
            font-size: 0.8em; 
            color: black;
            font-family: 'Poppins', sans-serif;
            font-weight: 600; 
        }


        #email, #password{
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

        .show-pass-btn{
            cursor: pointer;
            max-width: 1.5vw;
        }
    </style>
</head>
<body>
    
    <form method="post">
        <h1>Welcome Back</h1>
        <label for="email">Email:</label> 
            <input type="email" id="email" class="searchbar" name="email" autocomplete="off" required autofocus placeholder="name@email.com"><br><br>
        <label for="password">Password:</label> 
            <input type="password" id="password" class="searchbar" name="password" required placeholder="********">
            <img src="../images/showPassword.png" title="Show/Hide Password" class="show-pass-btn" id="show-pass-btn" onclick="showPass()">
        <input class="login_signup_button" type="submit" value="Login Now!">
        <div class="login-link">
            Or <a href="./reset_pass.php" class="link">Forgot Password</a>
        </div>
        <hr>
        <div class="register-link">
        No account? Please <a class="link" href="./register.php"> Sign Up</a>    
        </div>
        
    </form>
    <script src="../src/showPass.js"></script>
</body>
<?php include '../includes/footer.php'; ?>
</html>
