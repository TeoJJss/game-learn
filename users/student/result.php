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

    $ticket = $_SESSION['ticket'];
    $ch = curl_init("$base_url/check-ticket?ticket=$ticket");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    $response = json_decode(curl_exec($ch), true);

    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 202){
        $userID = $response['data']['user_id'];
    }

    $sql = "SELECT quiz_enrolment.userID FROM quiz_enrolment 
            JOIN question ON question.questID = quiz_enrolment.questID
            JOIN course ON question.courseID = course.courseID
            WHERE course.courseID=? AND quiz_enrolment.userID=?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $courseID, $userID);
    $stmt->execute();
    $enrol = $stmt->get_result();

    if ($enrol->num_rows == 0){
        header('Location: ../../users/quiz.php?courseID='.$courseID);
        exit();
    }

    $stmt -> close();

    include '../../includes/header.php';

    $sql = "SELECT question.questID, question.questText, question.awardPt, course.courseName
                FROM question LEFT JOIN course ON course.courseID = question.courseID
                WHERE question.courseID=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $courseID);
    $stmt->execute();
    $result = $stmt->get_result();

    $stmt->close();
    if ($result->num_rows == 0) {
        echo "<script>alert('Quiz Not Found!'); history.back(); </script>";
        exit();
    }
    $row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Result</title>
    <link rel="stylesheet" href="../../styles/style.css">
    <link rel="stylesheet" href="../../styles/course_style.css">
    <style>
        .opt, .opt-label {
            pointer-events: none;
        }

        .course-head {
            min-height: 11vh;
            max-height: 11vh;
        }

        .course-title {
            font-size: 1.8vw;
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
            margin-left: 5vw;
        }

        .opt-label {
            margin-right: 2vw;
        }

        .point {
            font-weight: lighter;
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
        }

        .correct-ans {
            background-color: darkgreen;
        }

        .wrong-ans{
            background-color: red;
        }
    </style>
</head>

<body>
    <div class="page" id="course-page">
        <div class="page-content">
            <div class="course-head">
                <span class="course-title">Quiz Result - <?php echo $row['courseName']; ?></span>
            </div><br>
            <div class="course-content">
                <?php
                $result->data_seek(0);
                $count = 1;
                while ($row = $result->fetch_assoc()) { ?>
                    <span class="questCount">Q<?php echo $count; ?></span><br><br>
                    <span class="questContent"><?php echo $row['questText']; ?><span class="point"> [<?php echo $row['awardPt']; ?>m]</span></span><br><br>
                    <?php
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

                    $sql = "SELECT `option`.optID, `option`.optValue, `option`.optImg 
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
                    <?php if ($isAns) { ?>
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
                    <?php } ?>
                    <br>
                    <hr style="width:95%;text-align:left;margin-left:0"><br>
                <?php $count++;
                } ?>
                <button class="button" onclick="location.href='../course.php?courseID=<?php echo $courseID; ?>';">Back</button>
            </div>
        </div>
    </div>
</body><br><br>
<?php include '../../includes/footer.php'; ?>

</html>