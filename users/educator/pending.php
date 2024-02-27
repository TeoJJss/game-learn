<?php
require '../../modules/config.php';
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
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap');

        .page {
            width: 90%;
            margin: auto;
        }

        .page-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .page-content {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            text-align: center;
        }

        h2 {
            font-weight: bold;
        }

        a {
            text-decoration: none;
        }

        .review-status {
            display: flex;
            flex-direction: column;
            justify-content: center;
            /* This will center the image */
            text-align: center;
        }

        .review-status img {
            margin-left: 60px;
            /* Adjust this value to move the image to the right */
        }

        .review-message {
            margin-bottom: 40px;
        }

        .review-message p {
            font-family: 'Montserrat', sans-serif;
            font-size: 20px;
            color: #333;
            line-height: 1.6;
        }
    </style>
</head>

<body>
    <div class="page">
        <div class="page-title">
            <img src="<?php echo $base; ?>images/educator_pic/course.png" alt="Course Icon">
            Course Management
        </div>
        <div class="nav-container">
            <h5><a href="course_management.php" class="nav-link">Course Management</a>/<a href="pending.php" class="nav-link">Course verification</a></h5>
        </div>
        <div class="page-content">
            <div class="review-status">
                <img src="<?php echo $base; ?>images/educator_pic/course_review.png" alt="Review Icon" width="350" height="350">
                <h2>Your course is under review</h2>
            </div>
            <div class="review-message">
                <p>Thank you for creating a course with us. We are currently reviewing your course to ensure it meets our quality standards.<br>
                    This process may take some time, and we appreciate your patience. Please check back for updates on the status of your course.</p>
            </div>
        </div>
    </div>
</body>
<?php include '../../includes/footer.php'; ?>

</html>