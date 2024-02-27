<?php
include '../controllers/appConsoleController.php';

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $putParams = json_decode(file_get_contents("php://input"), true);

    if(isset($putParams['courseId']) && isset($putParams['newStatus'])) {
        $courseId = $putParams['courseId'];
        $newStatus = $putParams['newStatus'];

        $result = updateCourseStatus($courseId, $newStatus);

        echo json_encode($result);
    } else {
        $response = array('success' => false, 'message' => 'CourseId or newStatus parameter is missing.');
        echo json_encode($response);
    }
} else {
    $response = array('success' => false, 'message' => 'Invalid request method.');
    echo json_encode($response);
}

