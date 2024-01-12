<?php 
    require '../../modules/config.php';

    if (check_ticket() != 'student'){
        header("Location: ../../index.php");
        exit();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student</title>
</head>
<body>
    <h1>Student Homepage</h1>
    <button onclick="location.href='../../modules/logout.php'">Logout</button>
</body>
</html>