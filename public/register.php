<?php 
    require '../modules/config.php';
    if ($_SERVER['REQUEST_METHOD'] == 'POST'){
        $email = $_POST['email'];
        $name = $_POST['name'];
        $password = $_POST['password'];
        $role = $_POST['user_type'];

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
            echo "<script>alert('Registration success!');</script>";
            echo "<script>location.href = '../index.php';</script>";
            exit();
        }else if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 403){
            $msg = $response['msg'];
            echo "<script>alert('$msg');</script>";
        }else{
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
</head>
<body>
    <h1>Register</h1>
    <form method="post">
        <label for="email">Email:</label> <input type="text" id="email" name="email" required autofocus><br><br>
        <label for="name">Name:</label> <input type="text" id="name" name="name" required><br><br>
        <label for="password">Password:</label> <input type="password" id="password" name="password" required><br><br>
        <select id="user_type" name="user_type"  required>
            <option value="" disabled selected>Select a role</option>
            <option value="student">Student</option>
            <option value="educator">Educator</option>
        </select>
        <input type="submit"> <a href="./index.php">Login</a>
    </form>
</body>
</html>