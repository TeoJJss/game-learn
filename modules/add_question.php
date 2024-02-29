<?php 
    if ($_SERVER['REQUEST_METHOD']=='POST'){
        require '../modules/config.php';
        if (check_ticket() != 'educator' || !isset($_GET['courseID'])){
            echo "<script>alert('Access denied!'); location.href='../index.php'</script>";
            exit();
        }
        $course_id = $_GET['courseID'];

        $question = $_POST['question'];
        $awardPt = $_POST['awardPt'];

        $img = null;
        $image = $_FILES['questImg']['tmp_name'];
        if (isset($_FILES['questImg']) && file_exists($image)){
            $img = base64_encode(file_get_contents($image));
        }

        $questSql = "INSERT INTO question (courseID, questText, awardPt, questImg) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($questSql);
        $stmt->bind_param("isis", $course_id, $question, $awardPt, $img);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $questionID = $conn->insert_id;

            $options = $_POST['optValue'];
            $correctOpt = $_POST['correctOpt'];

            for ($c = 1; $c <= 4; $c++) {
                $optionValue = $options[$c-1];
                $isAnswer = $c == $correctOpt ? 1 : 0;
                if (!$optionValue){
                    continue;
                }
                $sql = "INSERT INTO `option` (optValue, IsAnswer, questID) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sii", $optionValue, $isAnswer, $questionID);
                $stmt->execute();
                $stmt->close();
            }

            echo '<script>alert("Question is added successfully!"); window.location.href = "../users/quiz.php?courseID='.$course_id.'";</script>';
        }
        $stmt -> close();
    }
?>