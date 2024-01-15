<?php 
    require '../../modules/config.php';

    if (check_ticket() != 'admin'){
        header("Location: ../../index.php");
        exit();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin</title>
</head>
<body>
    <h1>Admin Homepage</h1>
    <button onclick="location.href='../../modules/logout.php'">Logout</button>
    <button onclick="location.href='../index.php'">Profile</button>
</body>
<script src="../../src/logout.js"></script>
</html>