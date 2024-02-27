<?php
include '../controllers/systemFeedbackController.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $sfID = $_POST['sfID'];
    $currentPage = $_POST['page'];
    $feedback = $_POST['feedback'];
  
    $replyMedia = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $replyMedia = file_get_contents($_FILES['image']['tmp_name']);
    }
  
    $result = json_decode(provideFeedbackReply($sfID, $feedback, $replyMedia), true);
  
    if ($result["success"]) {
        $redirectUrl = "http://localhost:8080/game-learn/users/admin/provide_feedback.php?sfID=$sfID&page=$currentPage&status=success
        ";
        header("Location: $redirectUrl");
        exit();
    } else {
        $redirectUrl = "http://localhost:8080/game-learn/users/admin/provide_feedback.php?sfID=$sfID&page=$currentPage&status=error&message=" . urlencode($result["message"]);
        header("Location: $redirectUrl");
        exit();
    }
}