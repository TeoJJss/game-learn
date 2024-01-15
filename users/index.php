<!-- Profile page -->
<?php 
    include '../modules/config.php';

    if (!check_ticket()){
        header("Location: ../index.php");
        exit();
    }

    $ticket = $_SESSION['ticket'];
    $ch = curl_init("$base_url/check-ticket?ticket=$ticket");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    $response = json_decode(curl_exec($ch), true);

    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 202){
        $email = $response["data"]["email"];
        $name = $response["data"]["name"];
        $role = $response["data"]["role"];
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
</head>
<body>
    <h1>My Profile</h1>
    <p>Email: <?php echo $email; ?></p>
    <p>Name: <?php echo $name; ?></p>
    <p>Role: <?php echo $role; ?></p>
</body>
</html>