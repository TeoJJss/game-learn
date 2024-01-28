<?php 
    require '../modules/config.php';

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
</head>
<body>  
    <h1>Reset Your Password</h1>
    <form method="post">
        <label for="email">Email: </label>
        <input type="text" name="email" id="email" placeholder="Enter your email" autofocus><br>
        <label for="name">Username: </label>
        <input type="text" name="name" id="name" placeholder="Enter your username"><br>
        <label for="password">New Password: </label>
        <input type="password" name="password" id="password" placeholder="New Password"><br>
        <input type="submit" value="Reset Password">
    </form>
</body>
</html>