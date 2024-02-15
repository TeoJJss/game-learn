<?php
require '../modules/config.php';
$role = check_ticket();
if (!$role) {
    header("Location: ../index.php");
    exit();
}
if (!isset($_GET['courseID'])) {
    echo "<script>alert('Invalid Course ID!'); history.back(); </script>";
    exit();
}
$courseID = $_GET['courseID'];

include '../includes/header.php';

$sql = "SELECT quiz_enrolment.userID, SUM(question.awardPt) as score 
            FROM quiz_enrolment JOIN question ON quiz_enrolment.questID=question.questID JOIN `option` ON `option`.optID=quiz_enrolment.optID JOIN course ON course.courseID=question.courseID 
            WHERE `option`.`IsAnswer`=1 AND course.courseID=?
            GROUP BY quiz_enrolment.userID 
            ORDER BY score DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $courseID);
$stmt->execute();
$rank = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard</title>
    <link rel="stylesheet" href="../styles/style.css">
    <style>
        .tb-head {
            background-color: black;
            color: whitesmoke;
            align-items: center;
            width: fit-content;
            padding: 1vh;
            margin-bottom: 0;
            max-width: 80vw;
        }

        .tb-body {
            margin-top: 0;
            max-width: 74.5vw;
            padding-top: 2vh;
            padding-bottom: 2vh;
            font-weight: 700;
            display: flex;
            justify-content: space-between;
        }

        .tb-head span {
            font-size: 2vw;
            margin-right: 10vw;
            margin-left: 5vw;
            font-weight: bold;
        }

        .tb-body span {
            font-size: 1.5vw;
            margin-left: 10vw;
            margin-right: 10vw;
            color: black;
        }

        .row {
            background-color: #BEBEBE;
        }

        #row-1 {
            background-color: #FFB64A;
        }

        #row-2 {
            background-color: #82BBFF;
        }

        #row-3 {
            background-color: #FFCA8C;
        }
    </style>
</head>

<body>
    <div class="page">
        <div class="page-title">
            <h1><img src="../images/leaderboard.png" alt="Leaderboard">Leaderboard</h1>
        </div>
        <div class="page-content">
            <div class="tb-head">
                <span>Rank #</span>
                <span>Student Name</span>
                <span>Score</span>
            </div>
            <?php $count = 1;
            while ($row = $rank->fetch_assoc()) {
                $userID = $row['userID'];
                $ch = curl_init("$base_url/user-detail?user_id=$userID");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                $response = json_decode(curl_exec($ch), true);

                if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200) {
                    $stuName = $response['msg'];
                } else {
                    $stuName = "unknown";
                } ?>
                <div class="tb-body row" id="row-<?php echo $count; ?>">
                    <span><?php echo $count; ?></span>
                    <span><?php echo $stuName; ?></span>
                    <span class="score"><?php echo $row['score']; ?></span>
                </div>
            <?php $count++;
            } ?>
            <br><br><button class="button" onclick="location.href='../users/course.php?courseID=<?php echo $courseID; ?>';">Back</button>
        </div>
    </div>
</body>
<?php include '../includes/footer.php'; ?>

</html>