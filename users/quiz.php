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

$ticket = $_SESSION['ticket'];
$ch = curl_init("$base_url/check-ticket?ticket=$ticket");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
$response = json_decode(curl_exec($ch), true);

if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 202) {
    $userID = $response['data']['user_id'];
}

if ($role == 'student') {
    $sql = "SELECT quiz_enrolment.userID FROM quiz_enrolment 
                JOIN question ON question.questID = quiz_enrolment.questID
                JOIN course ON question.courseID = course.courseID
                WHERE course.courseID=? AND quiz_enrolment.userID=?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $courseID, $userID);
    $stmt->execute();
    $enrol = $stmt->get_result();

    if ($enrol->num_rows > 0) {
        header('Location: ../users/student/result.php?courseID=' . $courseID);
        exit();
    }

    $stmt->close();
}

include '../includes/header.php';

$sql = "SELECT question.questID, question.questText, question.awardPt, course.courseName, question.questImg
            FROM question LEFT JOIN course ON course.courseID = question.courseID
            WHERE question.courseID=?";
if ($role != 'educator') {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $courseID);
} else {
    $sql .= " AND course.userID=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $courseID, $userID);
}
$stmt->execute();
$result = $stmt->get_result();

$stmt->close();

if ($result->num_rows == 0) {
    echo "<script>alert('Quiz Not Found!'); history.back(); </script>";
    exit();
}
$row = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($role == 'student') {
        $ticket = $_SESSION['ticket'];
        $ch = curl_init("$base_url/check-ticket?ticket=$ticket");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $response = json_decode(curl_exec($ch), true);

        if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 202) {
            $userID = $response["data"]["user_id"];
        } else {
            header("Location: ../index.php");
            exit();
        }

        $sql = "SELECT question.questID, `option`.optID, question.awardPt 
                    FROM question JOIN `option` ON question.questID=`option`.questID 
                    WHERE `option`.`IsAnswer`=1";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        while ($row = $result->fetch_assoc()) {
            $questID = $row['questID'];
            $selectedOpt = $_POST["$questID"];
            $post_sql = "INSERT INTO quiz_enrolment(userID, questID, optID)
                            VALUES(?, ?, ?)";
            $stmt = $conn->prepare($post_sql);
            $stmt->bind_param("iii", $userID, $questID, $selectedOpt);
            $stmt->execute();
            $stmt->close();

            if ($selectedOpt == $row['optID']) {
                $addPt = $row['awardPt'];
                $point_sql = "UPDATE `point` SET pointValue=pointValue+$addPt WHERE userID=?";
                $stmt = $conn->prepare($point_sql);
                $stmt->bind_param("i", $userID);
                $stmt->execute();
                $stmt->close();
            }
        }
        echo "<script>alert('Quiz submitted successfully!'); location.href='../users/student/result.php?courseID=$courseID'</script>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz</title>
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="stylesheet" href="../styles/course_style.css">
    <style>
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

        .questImg {
            width: 25vw;
        }
    </style>
</head>

<body>
    <div class="page" id="course-page">
        <div class="page-content">
            <div class="course-head">
                <span class="course-title">Quiz - <?php echo $row['courseName']; ?></span>
            </div><br>
            <div class="course-content">
                <form method="post">
                    <input type="text" value="<?php echo $courseID; ?>" name="courseID" hidden>
                    <?php $result->data_seek(0);
                    $count = 1;
                    while ($row = $result->fetch_assoc()) { ?>
                        <span class="questCount">Q<?php echo $count; ?></span><br><br>
                        <span class="questContent"><?php echo $row['questText']; ?><span class="point"> [<?php echo $row['awardPt']; ?>m]</span></span><br><br>
                        <?php if ($row['questImg'] != null) {
                            echo "<img src='data:image/png;base64," . $row['questImg'] . "' class='questImg'><br><br>";
                        } ?>
                        <?php
                        $questID = $row['questID'];
                        $sql = "SELECT `option`.optID, `option`.optValue, `option`.IsAnswer, `option`.optImg 
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
                            if ($q_row['IsAnswer'] && $role != 'student') {
                                $checked = 'checked';
                            }
                            $disabled = '';
                            if ($role != 'student') {
                                $disabled = 'style="pointer-events: none;"';
                            }
                            echo "<input type='radio' class='opt' id='opt-$optID' name='$questID' value='$optID' required $checked $disabled>
                                <label for='opt-$optID' class='opt-label' $disabled>" . $q_row['optValue'] . "</label><br><br>";
                        }
                        ?>
                        <br>
                        <hr style="width:95%;text-align:left;margin-left:0"><br>
                    <?php $count++;
                    }
                    if ($role == 'student') { ?>
                        <input type="submit" value="Submit Quiz" class="button">
                    <?php } ?>
                </form>
                <?php if ($role == 'educator') {  ?>
                    <button class="button" onclick="location.href='../users/educator/edit_quiz.php?courseID=<?php echo $courseID; ?>';">Edit Quiz</button><br><br>
                <?php } ?>
            </div>
        </div>
    </div>
</body>
<?php include '../includes/footer.php'; ?>

</html>