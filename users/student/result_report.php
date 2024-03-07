<?php
require '../../modules/config.php';
$role = check_ticket();
if ($role != 'student') {
    header("Location: ../../index.php");
    exit();
}
if (!isset($_GET['courseID'])) {
    echo "<script>alert('Invalid Course ID!'); history.back(); </script>";
    exit();
}
$courseID = $_GET['courseID'];
include '../../includes/header.php';
$ticket = $_SESSION['ticket'];
$ch = curl_init("$base_url/check-ticket?ticket=$ticket");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
$response = json_decode(curl_exec($ch), true);

if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 202) {
    $userID = $response["data"]["user_id"];
    $username = $response["data"]["name"];
} else {
    header("Location: ../../index.php");
    exit();
}
$quizSQL = "SELECT course.courseName, quiz_enrolment.timestamp, course.courseThumb, question.questText, question.questID, question.questImg, question.awardPt, `option`.`IsAnswer` 
                FROM `quiz_enrolment` 
                JOIN question ON quiz_enrolment.questID = question.questID 
                JOIN course ON question.courseID=course.courseID 
                JOIN `option` ON `option`.`optID`=quiz_enrolment.optID 
                WHERE quiz_enrolment.userID=? AND course.courseID=?";
$stmt = $conn->prepare($quizSQL);
$stmt->bind_param("ii", $userID, $courseID);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    echo "<script>alert('Quiz Result Not Found!'); history.back(); </script>";
    exit();
}
$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Result Report</title>
    <link rel="stylesheet" href="../../styles/style.css">
    <link rel="stylesheet" href="../../styles/course_style.css">
    <style>
        .report-title {
            text-align: center;
        }

        .report-title h2 {
            font-size: 2vw;
        }

        .report-body table {
            margin-left: 15vw;
        }

        .report-body td {
            border: 1px solid;
            width: 25vw;
            padding: 0.3vw 1vw;
        }

        .report-body .title-col {
            font-weight: bold;
        }

        .questCount,
        .questContent {
            font-weight: bold;
            font-size: 2vw;
        }

        .questContent {
            font-size: 1vw;
        }

        .course-content {
            margin-left: 25vw;
        }

        .questImg {
            margin-bottom: 2vh;
            width: 25vw;
        }

        .opt {
            pointer-events: none;
        }

        .correct-ans,
        .wrong-ans {
            width: max-content;
            max-width: 50vw;
            text-align: center;
            font-size: 1.5vw;
            padding: 2vh 2vw 1vh;
            min-height: 5vh;
            font-family: Arial, Helvetica, sans-serif;
            color: white;
            margin-bottom: 2vh;
        }

        .correct-ans {
            background-color: darkgreen;
        }

        .wrong-ans {
            background-color: red;
        }

        .summary {
            margin-left: 22vw;
            margin-top: 5vh;
            font-size: 1.5vw;
            font-weight: 800;
            color: darkblue;
            margin-bottom: 5vh;
        }

        .courseThumb {
            width: 1.6vw;
        }

        .ending {
            margin-left: 22vw;
            margin-bottom: 5vh;
        }

        .ending .sign {
            font-family: Verdana, Geneva, Tahoma, sans-serif;

        }

        #print-btn {
            margin-left: 15vw;
        }

        #logo-foot img {
            width: 10vw;
            margin-top: 5vh;
            margin-left: 8vw;
        }

        @media print {

            .report-body table,
            .summary,
            .ending {
                margin-left: 5vw;
            }

            .report-body td {
                width: 80vw;
            }

            #logo-foot img {
                margin-left: 30vw;
            }

            .questCount,
            .questContent {
                font-weight: bold;
                font-size: 5vw;
            }

            .questContent {
                font-size: 4vw;
            }

            .course-content {
                margin-left: 5vw;
            }

            .opt-label {
                font-size: 2vw;
            }

            .correct-ans {
                color: darkgreen;
                font-weight: bold;
            }

            .wrong-ans {
                color: red;
                font-weight: bold;
            }
        }
    </style>
</head>

<body>
    <div class="page">
        <div class="report-title">
            <br>
            <h2>Quiz Result Report for <strong><u><?php echo $username; ?></u></strong></h2>
        </div>
        <div class="report-body">
            <table>
                <tr>
                    <td class="title-col">Student Name:</td>
                    <td><?php echo $username; ?></td>
                </tr>
                <tr>
                    <td class="title-col">Course Name:</td>
                    <td>
                        <img src='data:image/png;base64,<?php echo $row['courseThumb'] ?>' class='courseThumb'>
                        <a href="<?php echo "../course.php?courseID=$courseID"; ?>"><?php echo $row['courseName']; ?></a>
                    </td>
                </tr>
                <tr>
                    <td class="title-col">Participation Date & Time:</td>
                    <td><?php echo $row['timestamp']; ?></td>
                </tr>
                <tr>
                    <td class="title-col">Report Generation Date & Time:</td>
                    <td><?php echo date("Y-m-d"); ?></td>
                </tr>
            </table>
            <div class="course-content">
                <?php $result->data_seek(0);
                $count = 1;
                $correct = 0;
                $score = 0;
                while ($row = $result->fetch_assoc()) { ?><br>
                    <span class="questCount">Q<?php echo $count; ?></span><br><br>
                    <span class="questContent"><?php echo $row['questText']; ?><span class="point"> [<?php echo $row['awardPt']; ?>m]</span></span><br><br>
                    <?php if ($row['questImg'] != null) { ?>
                        <img src='data:image/png;base64,<?php echo $row['questImg'] ?>' class='questImg'><br>
                    <?php }
                    $questID = $row['questID'];
                    $check_sql = "SELECT `option`.optID, `option`.IsAnswer
                                        FROM quiz_enrolment JOIN `option` ON `option`.`optID`=quiz_enrolment.optID 
                                        WHERE quiz_enrolment.questID = ? AND quiz_enrolment.userID=?";
                    $stmt = $conn->prepare($check_sql);
                    $stmt->bind_param("ii", $questID, $userID);
                    $stmt->execute();
                    $stmt->bind_result($selectedOpt, $isAns);
                    $stmt->fetch();
                    $stmt->close();

                    $sql = "SELECT `option`.optID, `option`.optValue
                                FROM `option`
                                WHERE questID=?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $questID);
                    $stmt->execute();
                    $options = $stmt->get_result();
                    $stmt->close();
                    while ($q_row = $options->fetch_assoc()) {
                        $optID = $q_row['optID'];
                        $checked = '';
                        if ($optID == $selectedOpt) {
                            $checked = 'checked';
                        }
                        echo "<input type='radio' class='opt' id='opt-$optID' name='$questID' value='$optID' required $checked><label for='opt-$optID' class='opt-label'>" . $q_row['optValue'] . "</label><br><br>";
                    }
                    ?>
                    <?php if ($isAns) {
                        $correct++;
                        $score += $row['awardPt'];
                    ?>
                        <div class="correct-ans">
                            <span>Your answer is correct! </span>
                        </div>
                    <?php } else {
                        $correct_sql = "SELECT `option`.optValue FROM question JOIN `option` ON question.questID=`option`.questID WHERE `option`.`IsAnswer`=1 AND question.questID=?";
                        $stmt = $conn->prepare($correct_sql);
                        $stmt->bind_param("i", $questID);
                        $stmt->execute();
                        $stmt->bind_result($answer);
                        $stmt->fetch();
                        $stmt->close();
                    ?>
                        <div class="wrong-ans">
                            <span>Your answer is wrong! The correct answer is <b><?php echo $answer; ?></b></span>
                        </div>
                    <?php  } ?>
                <?php
                    $count++;
                }
                $count--; //adjust number of questions
                ?>
            </div>
            <div class="summary">
                <span class="correct"><b>Result:</b> <?php echo "$correct/$count"; ?></span><br>
                <span>Score: <?php echo $score ?> <i>(without gift)</i></span>
            </div>
            <div class="ending">
                <span class="sign">THIS IS A COMPUTER GENERATED REPORT. NO SIGNATURE IS REQUIRED.</span><br><br>
                <div id="end-buttons">
                    <button class="button" onclick="location.href='../course.php?courseID=<?php echo $courseID; ?>';">Back to course</button>
                    <button class="button" id="print-btn" onclick="printReport()">Print</button>
                </div>
                <div id="logo-foot" hidden>
                    <img src="../../images/nav_picture/logo01.png" alt="Mathy Logo">
                </div>
            </div>
        </div>
    </div>
    <script>
        function printReport() {
            document.getElementById('end-buttons').style.display = 'none';
            document.getElementById('logo-foot').hidden = false;
            document.querySelector("header").style.display = "none";
            document.querySelector("footer").style.display = "none";
            window.print();
            document.getElementById('end-buttons').style.display = '';
            document.getElementById('logo-foot').hidden = true;
            document.querySelector("header").style.display = "";
            document.querySelector("footer").style.display = "";
        }
    </script>
</body>
<?php include '../../includes/footer.php'; ?>

</html>