<!-- HomePage for Guest -->
<?php 
    require '../modules/config.php';
    if (check_ticket()){
        header("Location: ../index.php");
        exit();
    }
    include '../includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guest Homepage</title>
    <link rel="stylesheet" href="../styles/style.css">
</head>
<body>
    <h1>Guest Homepage</h1>
</body>
<?php include '../includes/footer.php';?>
</html>