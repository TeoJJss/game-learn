<?php 
    require '../modules/config.php';

    if (check_ticket()){
        header("Location: ../index.php");
        exit();
    }

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
</head>
<body>
    <h1>Login</h1>
    <form method="post">
        <label for="email">Email:</label> <input type="text" id="email" name="email" required autofocus><br><br>
        <label for="password">Password:</label> <input type="password" id="password" name="password" required><br><br>
        <input type="submit" value="Login Now!"> <a href="./register.php">Register</a>
    </form>
</body>
</html>