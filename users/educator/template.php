<?php 
    include '../../modules/config.php';
    $role = check_ticket();
    if ($role != 'educator') {
        header("Location: ../../index.php");
        exit();
    }
    include '../../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Template</title>
    <link rel="stylesheet" href="../../styles/style.css">
</head>
<body>
    <div class="page">
        <div class="page-title">

        </div>
        <div class="page-content">

        </div>
    </div>
</body>
<?php include '../../includes/footer.php';?>
</html>