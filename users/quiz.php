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

// Get the user ID of the user
$ticket = $_SESSION['ticket'];
$ch = curl_init("$base_url/check-ticket?ticket=$ticket");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
$response = json_decode(curl_exec($ch), true);

if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 202) {
    $userID = $response['data']['user_id'];
}

// If the user is student and has enrolled in the quiz before, show results directly
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

// Query the quiz content
$sql = "SELECT question.questID, question.questText, question.awardPt, course.courseName, question.questImg
            FROM question LEFT JOIN course ON course.courseID = question.courseID
            WHERE question.courseID=?";
if ($role != 'educator') {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $courseID);
} else {
    $sql .= " AND course.userID=?"; // Educator can only view his/her own quiz
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
                    WHERE `option`.`IsAnswer`=1 AND question.courseID=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $courseID);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        $redemption = $_POST['useGift'];
        $redemptionID = $redemption == 'null' ? null : $redemption;

        $minus = 0;

        if ($redemptionID != null) {
            $chkGift = "SELECT giftID FROM user_gift WHERE redemptionID=?";
            $stmt = $conn->prepare($chkGift);
            $stmt->bind_param("i", $redemptionID);
            $stmt->execute();
            $stmt->bind_result($giftID);
            $stmt->fetch();
            $stmt->close();

            if ($giftID === 2) { // Minus One Wrong
                $minus = 1;
            } else if ($giftID === 3) { // Minus Two Wrong
                $minus = 2;
            }
        }

        $addTotal = 0;
        while ($row = $result->fetch_assoc()) {
            $questID = $row['questID'];
            $selectedOpt = isset($_POST["$questID"]) ? $_POST["$questID"] : null;
            if ($redemptionID != null) {
                $post_sql = "INSERT INTO quiz_enrolment(userID, questID, optID, redemptionID)
                                VALUES(?, ?, ?, ?)";
                $stmt = $conn->prepare($post_sql);
                $stmt->bind_param("iiii", $userID, $questID, $selectedOpt, $redemptionID);
            } else {
                $post_sql = "INSERT INTO quiz_enrolment(userID, questID, optID)
                                VALUES(?, ?, ?)";
                $stmt = $conn->prepare($post_sql);
                $stmt->bind_param("iii", $userID, $questID, $selectedOpt);
            }
            $stmt->execute();
            $stmt->close();
            $addPt = $_POST["ptValue-$questID"];
            if ($selectedOpt == $row['optID']) {
                $addTotal += $addPt;
            } else if (($selectedOpt != $row['optID']) && $minus) {
                $addTotal += $addPt / 2;
                $minus--;
            } else {
                $addTotal += 0;
            }
        }

        $point_sql = "UPDATE `point` SET pointValue=pointValue+$addTotal WHERE userID=?";
        $stmt = $conn->prepare($point_sql);
        $stmt->bind_param("i", $userID);
        $stmt->execute();
        $stmt->close();

        if ($redemptionID != null) {
            $usedSQL = "UPDATE user_gift SET isUsed=1 WHERE redemptionID=?";
            $stmt = $conn->prepare($usedSQL);
            $stmt->bind_param("i", $redemptionID);
            $stmt->execute();
            $stmt->close();
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

        .gift-bag {
            top: 23vh;
            position: fixed;
            background-color: #0557B8;
            right: 10vw;
            color: white;
            min-width: 15vw;
            min-height: 10vh;
            text-align: center;
        }

        .gift-bag-title {
            font-weight: bold;
            font-size: 2vw;

        }

        .giftBagTitle {
            text-align: center;
            margin-bottom: 2vh;
        }

        .gift-bag-icon {
            width: 2vw;
            vertical-align: middle;
        }

        .giftPic {
            width: 3vw;
            margin-right: 1vw;
        }

        .gift-row {
            text-align: left;
            margin-left: 2vw;
            vertical-align: middle;
            margin-bottom: 2vh;
        }

        .gift-count {
            border-radius: 90%;
            background-color: red;
            padding-left: 0.5vw;
            padding-right: 0.5vw;
        }

        .add-question-form {
            border: 0.1vw solid;
            padding: 1vw;
            margin-top: 2vh;
            max-width: 60vw;
        }

        .add-question-form input[type="text"] {
            width: 50vw;
            height: 6vh;
            font-size: 1.5vw;
            margin-bottom: 2vh;
        }

        .add-question-form label {
            font-size: 1.2vw;
            margin-bottom: 2vh;
        }

        .add-question-form input[type="number"] {
            width: 3vw;
            height: 4vh;
            font-size: 1vw;
            margin-bottom: 2vh;
        }

        .required-label {
            color: red;
            font-size: 1vw;
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
                <?php if ($role == 'student') { ?>
                    <p style="color: red;">Please do not refresh or the quiz will be terminated immediately. </p>
                    <div class="gift-bag">
                        <div class="giftBagTitle">
                            <span class="gift-bag-title"><img src="../images/nav_picture/giftbag.png" class="gift-bag-icon">Gift Bag</span><br>
                        </div>
                        <?php
                        $giftSql = "SELECT gift.giftID, gift.giftName, gift.giftMedia, user_gift.redemptionID, COUNT(user_gift.redemptionID) AS gift_count
                                        FROM user_gift JOIN gift ON user_gift.giftID=gift.giftID 
                                        WHERE user_gift.userID=? AND user_gift.isUsed=0
                                        GROUP BY gift.giftID";
                        $stmt = $conn->prepare($giftSql);
                        $stmt->bind_param("i", $userID);
                        $stmt->execute();
                        $gifts = $stmt->get_result();
                        $stmt->close();

                        while ($gift = $gifts->fetch_assoc()) {
                        ?>
                            <div class="gift-row">
                                <img src='data:image/png;base64,<?php echo $gift['giftMedia'] ?>' title='<?php echo $gift['giftName']; ?>' class='giftPic'>
                                <button class="button use-gift-btn" id="gift-<?php echo $gift['giftID']; ?>" onclick="useGift(<?php echo $gift['redemptionID']; ?>, <?php echo $gift['giftID']; ?>)">Use
                                    <span class="gift-count" id="gift-count-<?php echo $gift['giftID']; ?>"><?php echo $gift['gift_count']; ?></span></button>
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>
                <form method="post" id="quiz-form">
                    <input type="text" value="<?php echo $courseID; ?>" name="courseID" hidden>
                    <input type="number" name="useGift" id="useGift" value="null" hidden>
                    <?php $result->data_seek(0);
                    $count = 1;
                    while ($row = $result->fetch_assoc()) {
                        $questID = $row['questID'];
                    ?>
                        <span class="questCount">Q<?php echo $count; ?></span><br><br>
                        <span class="questContent"><?php echo $row['questText']; ?><span class="point"> [<span class="ptVal"><?php echo $row['awardPt']; ?></span>m]</span></span><br><br>
                        <input type="number" name="ptValue-<?php echo $questID; ?>" id="ptValue-<?php echo $questID; ?>" value="<?php echo $row['awardPt']; ?>" required hidden>
                        <?php if ($row['questImg'] != null) {
                            echo "<img src='data:image/png;base64," . $row['questImg'] . "' class='questImg'><br><br>";
                        } ?>
                        <?php
                        $sql = "SELECT `option`.optID, `option`.optValue, `option`.IsAnswer
                                    FROM `option`
                                    WHERE questID=?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $questID);
                        $stmt->execute();
                        $options = $stmt->get_result();
                        $stmt->close();
                        while ($q_row = $options->fetch_assoc()) {
                            $optID = $q_row['optID'];
                            $optAns = '';
                            $checked = '';
                            if ($q_row['IsAnswer']) {
                                $optAns = "opt-ans";
                            }
                            if ($q_row['IsAnswer'] && $role != 'student') {
                                $checked = 'checked';
                            }
                            $disabled = '';
                            if ($role != 'student') {
                                $disabled = 'style="pointer-events: none;"';
                            }
                            echo "<input type='radio' class='opt $optAns' id='opt-$optID' name='$questID' value='$optID' required $checked $disabled>
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
                    <button class="button" onclick="location.href='../users/educator/edit_quiz.php?courseID=<?php echo $courseID; ?>';">Edit Quiz</button>
                    <button class="button" id="add-question-btn" onclick="showAddQuestForm()">Add new question</button><br>
                    <form method="post" class="add-question-form" id="add-question-form" action="../modules/add_question.php?courseID=<?php echo $courseID; ?>" enctype="multipart/form-data" hidden>
                        <h2>Add new question</h2>
                        <label for="question">Question: </label>
                        <input type="text" name="question" id="question" placeholder="Enter question" maxlength="150" required>
                        <label for="awardPt">Award Point:</label>
                        <input type="number" id="awardPt" name="awardPt" min="1" max="20" required><br>
                        <label for="questImg">Question Image:</label>
                        <input type="file" name="questImg" id="questImg" accept=".jpg, .png">
                        <p class="required-label">You must create 2-4 options for a question, leave option 3 and 4 empty if not required</p>
                        <?php for ($i = 1; $i <= 4; $i++) { ?>
                            <div class="form-group">
                                <label for="optValue">Option <?php echo $i; ?>:</label>
                                <input type="text" id="optValue" name="optValue[]" placeholder="Enter option value" maxlength="150" <?php if ($i < 3) {
                                                                                                                                        echo 'required';
                                                                                                                                    } ?>>
                            </div>
                        <?php } ?>

                        <div class="form-group">
                            <label for="IsAnswer">Correct Option:</label>
                            <input type="number" name="correctOpt" id="correctOpt" min="1" max="4" required>
                        </div>
                        <input class="button" type="submit" name="submit" id="submit" value="Add Question">
                    </form>
                    <br><br>
                <?php } ?>
            </div>
        </div>
    </div>
    <?php if ($role == 'student') { ?>
        <script>
            if (window.performance && window.performance.navigation.type === window.performance.navigation.TYPE_RELOAD) {
                document.getElementById("quiz-form").submit();
            }
        </script>
    <?php } ?>
    <script>
        var questions = document.getElementsByClassName('questCount');
        if (questions.length < 2) {
            document.getElementById('gift-3').disabled = true; // Minus Two Wrong
            document.getElementById('gift-5').disabled = true; // Skip Two Questions
            document.getElementById('gift-6').disabled = true; // Skip Three Questions
        } else if (questions.length < 3) {
            document.getElementById('gift-5').disabled = true;
            document.getElementById('gift-6').disabled = true;
        }

        function useGift(redemptionID, giftID) {
            if (giftID === 1) { // Double Points
                var questPt = document.getElementsByClassName('ptVal');
                for (let i = 0; i < questPt.length; i++) {
                    var newPt = (parseInt(questPt[i].innerHTML) * 2).toString();
                    questPt[i].innerHTML = `<s><i>${questPt[i].innerHTML}</i></s> <b>${newPt}</b>`
                    document.getElementById(`ptValue-${i+1}`).value = newPt;
                }
            } else if (giftID >= 4 && giftID <= 6) { // Skip question gift
                var correctopt = document.getElementsByClassName('opt-ans');
                switch (giftID) {
                    case 4: // Skip One Questions
                        skip = 1;
                        break;
                    case 5: // Skip Two Questions
                        skip = 2;
                        break;
                    case 6: // Skip Three Questions
                        skip = 3;
                        break;
                    default:
                        skip = 0;
                }
                console.log(skip);
                for (let i = 0; i < skip; i++) {
                    correctopt[i].checked = true;
                }
            }

            var useGiftBtn = document.getElementsByClassName('use-gift-btn');
            for (let i = 0; i < useGiftBtn.length; i++) {
                useGiftBtn[i].disabled = true;
            }
            document.getElementById('useGift').value = redemptionID;

            var currentCount = document.getElementById(`gift-count-${giftID}`).innerHTML;
            document.getElementById(`gift-count-${giftID}`).innerHTML = currentCount - 1;
        }

        function showAddQuestForm() {
            document.getElementById('add-question-form').hidden = false;
            document.getElementById('add-question-btn').hidden = true;
        }
    </script>
</body>
<?php include '../includes/footer.php'; ?>

</html>