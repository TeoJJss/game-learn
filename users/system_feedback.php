<?php
require '../modules/config.php';
$role = check_ticket();
if ($role == 'admin') {
    header("Location: ../index.php");
    exit();
}
include '../includes/header.php';

$ticket = $_SESSION['ticket'];
$ch = curl_init("$base_url/check-ticket?ticket=$ticket");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
$response = json_decode(curl_exec($ch), true);
$userID = $response['data']['user_id'];

$sql = "SELECT system_feedback.sfID, system_feedback.sfContent, system_feedback.`timestamp`, reply.replyID 
        FROM system_feedback LEFT JOIN reply ON system_feedback.sfID=reply.sfID
        WHERE system_feedback.userID=?
        ORDER BY `timestamp` DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Feedback</title>
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .page {
            width: 80%;
            margin: auto;
        }

        .page-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            25%,
            75% {
                transform: translateX(-10px);
            }

            50% {
                transform: translateX(10px);
            }
        }

        .compose-button {
            margin-top: 20px;
            margin-bottom: 20px;
            background-color: black;
            border: none;
            color: white;
            padding: 15px 32px;
            text-decoration: none;
            font-size: 16px;
            transition-duration: 0.4s;
            cursor: pointer;
            float: right;
            border-radius: 20px;
            transition-duration: 0.3s;
        }

        .compose-button:hover {
            background-color: #ff6600;
            transform: scale(1.1) rotate(0.1deg);
        }

        .compose-button i {
            transition: transform 0.3s ease;
        }

        .compose-button:hover i {
            transform: scale(1.2);
            animation: shake 0.5s infinite;
        }

        .feedback-container {
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 10px;
            clear: both;
            background-color: white;
            border-left: 4px solid #333;
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1);
        }

        .feedback-item {
            border-top: 1px solid #ccc;
            padding: 30px;
        }

        .feedback-item:first-child {
            border-top: none;
        }

        .feedback-date,
        .feedback-time,
        .feedback-message {
            display: inline-block;
        }

        .feedback-date {
            width: 30%;
        }

        .feedback-time {
            width: 30%;
        }

        .feedback-item {
            white-space: nowrap;
            height: 100px;
            overflow: hidden;
        }

        .next-page-button {
            display: block;
            text-align: right;
            padding-top: 40px;
            margin-right: 475px;
        }

        .next-page-button i {
            font-size: 24px;
            transition: transform 0.3s ease, color 0.3s ease;
        }

        .next-page-button:hover i {
            transform: scale(1.2);
            color: red;
        }

        .fbStatus {
            font-weight: bold;
            color: darkgreen;
        }
    </style>
</head>

<body>
    <div class="page">
        <div class="page-title">
            <img src="<?php echo $base; ?>images/sys_feedback.png" alt="System Feedback Icon" class="system-image">
            System Feedback
        </div>

        <!-- Compose button -->
        <a href="add_system_feedback.php" class="compose-button">
            <i class="fas fa-pen"></i>
            Compose
        </a>

        <div class="feedback-container">
            <?php
            while ($row = $result->fetch_assoc()) {
                $timestamp = new DateTime($row['timestamp']);

                $feedbackDate = $timestamp->format('Y-m-d');
                $feedbackTime = $timestamp->format('H:i:s');
            ?>
                <div class="feedback-item">
                    <div class="feedback-date">Date: <?php echo $feedbackDate; ?></div>
                    <div class="feedback-time">Time: <?php echo $feedbackTime; ?></div>

                    <div class="feedback-message">Message: <?php echo $row['sfContent']; ?></div>
                    <a href="view_system_feedback.php?sfID=<?php echo $row['sfID']; ?>" class="next-page-button">
                        <i class="fas fa-arrow-right"></i>
                    </a>
                    <?php if ($row['replyID']) { ?>
                        <span class="fbStatus">Replied</span>
                    <?php } ?>
                </div><br>
            <?php
            }
            ?>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>

</html>
