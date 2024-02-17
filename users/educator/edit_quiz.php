<?php
    require '../../modules/config.php';
    $role = check_ticket();
    if ($role != 'educator') {
        header("Location: ../../index.php");
        exit();
    }
    if (!isset($_GET['courseID'])) {
        echo "<script>alert('Invalid Course ID!'); history.back(); </script>";
        exit();
    }

    $ticket = $_SESSION['ticket'];
    $ch = curl_init("$base_url/check-ticket?ticket=$ticket");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    $response = json_decode(curl_exec($ch), true);

    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 202) {
        $eduID = $response["data"]["user_id"];
    } else {
        header("Location: ../../index.php");
        exit();
    }

    if ($_SERVER['REQUEST_METHOD']=='POST'){
        $courseID = $_POST['courseID'];
        $questID = $_POST['questID'];
        $questText = $_POST['questText'];
        $questPt = $_POST['questPt'];
        $optID = $_POST["$questID"];

        $img = 'questImg';
        $image = $_FILES['questImg']['tmp_name'];
        if (isset($_FILES['questImg']) && file_exists($image)){
            $img = "'".base64_encode(file_get_contents($image))."'";
        }

        $update_sql = "UPDATE question 
                        SET questText=?, awardPt=?, questImg=$img
                        WHERE questID=?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("sii", $questText, $questPt, $questID);
        $stmt->execute();

        $ptSql = "UPDATE `option` 
                    SET IsAnswer = CASE WHEN optID=? THEN 1 ELSE 0 END 
                    WHERE questID=?";
        $stmt = $conn->prepare($ptSql);
        $stmt->bind_param("ii", $optID, $questID);
        $stmt->execute();
        $stmt -> close();

        echo "<script>alert('Changes are saved!'); location.href='../../users/quiz.php?courseID=$courseID'</script>";
        exit();
    }

    $courseID = $_GET['courseID'];
    include '../../includes/header.php';

    $sql = "SELECT question.questID, question.questText, question.awardPt, question.questImg, course.courseName
                FROM question LEFT JOIN course ON course.courseID = question.courseID
                WHERE question.courseID=? AND course.userID=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $courseID, $eduID);
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
    <title>Edit Quiz</title>
    <link rel="stylesheet" href="../../styles/style.css">
    <link rel="stylesheet" href="../../styles/course_style.css">
    <style>
        .course-head {
            min-height: 11vh;
            max-height: 11vh;
        }

        .course-title {
            font-size: 1.8vw;
        }

        .questCount,
        .questInp {
            font-weight: bold;
            font-size: 2vw;
        }

        .questInp {
            font-size: 1vw;
            width: 30vw;
        }

        .course-content {
            margin-left: 5vw;
        }

        .opt-label {
            margin-right: 2vw;
        }

        .point {
            font-weight: bold;
        }

        #questPt {
            width: 3vw;
        }
        .questImg{
            width: 5%;
        }
    </style>
</head>

<body>
    <div class="page" id="course-page">
        <div class="page-content">
            <div class="course-head">
                <span class="course-title">Edit Quiz - <?php echo $row['courseName']; ?></span>
            </div><br>
            <div class="course-content">
                <?php $result->data_seek(0);
                $count = 1;
                while ($row = $result->fetch_assoc()) { ?>
                    <form method="post" enctype="multipart/form-data">
                        <input type="text" value="<?php echo $courseID; ?>" name="courseID" hidden>
                        <input type="text" value="<?php echo $row['questID']; ?>" name="questID" hidden>
                        <span class="questCount">Q<?php echo $count; ?></span><br><br>
                        <?php if ($row['questImg'] != null){ 
                            echo "<img src='data:image/png;base64," . $row['questImg'] . "' class='questImg'><br><br>";
                        }?>
                        <input type="file" name="questImg" accept=".jpg, .jpeg, .png" id="questImg"><br><br>
                        <input type="text" class="questInp" name="questText" id="qustText" value="<?php echo $row['questText']; ?>" required>
                        <span class="point">Point: [<input type="number" class="questInp" name="questPt" id="questPt" value="<?php echo $row['awardPt'] ?>" min="1" max="100">m]</span><br><br>
                        <span>(Select the correct option)</span><br>
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
                            echo "<input type='radio' class='opt' id='opt-$optID' name='$questID' value='$optID' required $checked>
                                <label for='opt-$optID' class='opt-label'>" . $q_row['optValue'] . "</label><br><br>";
                        }
                        ?>
                        <input type="submit" class="button" name="submit" id="submit" value="Save changes">
                    </form>
                    <br>
                    <hr style="width:95%;text-align:left;margin-left:0"><br>
                <?php $count++;
                } ?>
            </div>
        </div>
    </div>
</body>
<?php include '../../includes/footer.php'; ?>

</html>