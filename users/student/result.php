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

    if ($_SERVER['REQUEST_METHOD'] == "POST"){
        $feedback = $_POST['feedback'];
        $rating = $_POST['rating'];
        $img = null;
        $image = $_FILES['fbFile']['tmp_name'];
        if (isset($_FILES['fbFile']) && file_exists($image)){
            $img = base64_encode(file_get_contents($image));
        }

        $new_FB_sql = "INSERT INTO course_feedback (`fbText`, `ratings`, `userID`, `courseID`, `fbImg`)
                        VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($new_FB_sql);
        $stmt->bind_param("siiis", $feedback, $rating, $userID, $courseID, $img);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($stmt->affected_rows == 0) {
            echo "<script>alert('Feedback is not submitted!'); </script>";
        }else{
            echo "<script>alert('Feedback submitted!'); history.back(); </script>";
        }
        $stmt->close();
        exit();
    }

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

        h2.fb {
            font-weight: bold;
        }

        h2.fb img{
            width: 2vw;
            margin-right: 1vw;
        }

        #feedback{
            width: 60vw;
            height: 5vh;
            vertical-align: top;
            font-size: 1vw;
        }

        .fb-sect {
            border: 1px solid;
            border-radius: 1%;
            max-width: 60vw;
            padding: 1vw;
            background-color: beige;
        }
        #submitFB{
            margin-top: 2vh;
            margin-left: 5vw;
        }

        .fa-star{
            cursor: pointer;
            font-size: 1.6vw;
        }
        .fa-star:hover{
            font-size: 1.8vw;
            color: orange;
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
                <?php
                    // Check if the student already submit feedback, before displaying feedback form
                    $chk_FB_Sql = "SELECT COUNT(*) FROM `course_feedback` WHERE courseID=? AND userID=?";
                    $stmt = $conn->prepare($chk_FB_Sql);
                    $stmt->bind_param("ii", $courseID, $userID);
                    $stmt->execute();
                    $stmt->bind_result($count);
                    $stmt->fetch();
                    $stmt->close();
                    if ($count == 0){
                ?>
                    <div class="fb-sect">
                        <h2 class="fb"><img src="../../images/nav_picture/feedback.png">Feedback</h2>
                        <form method="post" enctype="multipart/form-data">
                            <div class="rating">
                                <span class="fa fa-star" id="fa1" onclick="ratings(1)"></span>
                                <span class="fa fa-star" id="fa2" onclick="ratings(2)"></span>
                                <span class="fa fa-star" id="fa3" onclick="ratings(3)"></span>
                                <span class="fa fa-star" id="fa4" onclick="ratings(4)"></span>
                                <span class="fa fa-star" id="fa5" onclick="ratings(5)"></span>
                            </div>
                            <input type="number" name="rating" id="rating" value="0" min="0" max="5" hidden>
                            <input type="text" name="feedback" id="feedback" maxlength="150" oninput="enableFBtn()" placeholder="Tell us what you think about this course..." autocomplete="off" required>
                            <input type="file" name="fbFile" id="fbFile" accept=".jpg, .jpeg, .png">
                            <input type="submit" name="submitFB" id="submitFB" class="button" disabled>
                        </form>
                    </div><br>
                <?php } ?>
                <button class="button" onclick="location.href='../course.php?courseID=<?php echo $courseID; ?>';">Back to course</button>
            </div>
        </div>
    </div>
    <script>
        function enableFBtn(){
            var FBtn = document.getElementById('submitFB');
            FBtn.disabled = false;
        }

        function ratings(rating){
            var count=0;
            for(var i =1; i<=5; i++){
                var star = document.getElementById(`fa${i}`);
                if (i<=rating){
                    star.classList.add("checked");
                    count=i;
                }else{
                    star.classList.remove('checked');
                }
            }
            document.getElementById('rating').value=count;
        }
    </script>
</body><br><br>
<?php include '../../includes/footer.php'; ?>

</html>