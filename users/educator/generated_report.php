<?php
require '../../modules/config.php';
$role = check_ticket();
if ($role != 'educator') {
    header("Location: ../../index.php");
    exit();
}

// Fetch course information based on the selected course name
$selectedCourse = $_GET['courseID'] ?? '';
$ticket = $_SESSION['ticket'];
$ch = curl_init("$base_url/check-ticket?ticket=$ticket");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
$response = json_decode(curl_exec($ch), true);
$userID = $response['data']['user_id'];

// Modify the SQL query to include a WHERE clause to filter by the selected course name
$sql = "SELECT * FROM `course` WHERE `courseID` = ? AND userID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $selectedCourse, $userID);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $courseID = $row['courseID'];

    // Fetch instructor's information using cURL
    $ch = curl_init("$base_url/user-detail?user_id=" . $row['userID']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    $response = json_decode(curl_exec($ch), true);

    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200) {
        $userName = $response['msg'];
    } else {
        trigger_error("Unknown user!");
        exit();
    }

    // Fetch the number of enrolled students for the selected course
    $enrolledStudents = 0; // Initialize to zero

    // Modify the SQL query to count the number of enrolled students
    $enrollmentSql = "SELECT COUNT(*) as enrolledCount FROM `course_enrolment` WHERE `courseID` = ?";
    $enrollmentStmt = $conn->prepare($enrollmentSql);
    $enrollmentStmt->bind_param("i", $courseID);
    $enrollmentStmt->execute();
    $enrollmentResult = $enrollmentStmt->get_result();

    if ($enrollmentResult && $enrollmentResult->num_rows > 0) {
        $enrollmentRow = $enrollmentResult->fetch_assoc();
        $enrolledStudents = $enrollmentRow['enrolledCount'];
    }

    // Fetch the number of active and inactive participants for the selected course
    $activeParticipants = 0;
    $inactiveParticipants = 0;

    // Modify the SQL query to count the number of active participants based on quiz enrolment
    $activeParticipantsSql = "SELECT COUNT(DISTINCT `quiz_enrolment`.`userID`) as activeCount 
                                FROM `quiz_enrolment` JOIN question ON question.questID=quiz_enrolment.questID JOIN course ON course.courseID=question.courseID 
                                WHERE question.courseID = ? AND `optID` IS NOT NULL";
    $activeParticipantsStmt = $conn->prepare($activeParticipantsSql);
    $activeParticipantsStmt->bind_param("i", $courseID);
    $activeParticipantsStmt->execute();
    $activeParticipantsResult = $activeParticipantsStmt->get_result();

    if ($activeParticipantsResult && $activeParticipantsResult->num_rows > 0) {
        $activeParticipantsRow = $activeParticipantsResult->fetch_assoc();
        $activeParticipants = $activeParticipantsRow['activeCount'];
    }

    // Calculate the number of inactive participants
    $inactiveParticipants = $enrolledStudents - $activeParticipants;

    // Fetch the number of students who pass and fail based on point values
    $studentsPass = 0;
    $studentsFail = 0;

    // Modify the SQL query to count the number of students who pass or fail
    $pointSql = "SELECT COUNT(*) as pointCount
            FROM (
                SELECT quiz_enrolment.userID, SUM(IF(quiz_enrolment.optID IS NOT NULL AND `option`.`IsAnswer` = 1, question.awardPt, 0)) AS score
                FROM quiz_enrolment
                LEFT JOIN question ON question.questID = quiz_enrolment.questID
                LEFT JOIN `option` ON `option`.`optID` = quiz_enrolment.optID
                LEFT JOIN course ON course.courseID = question.courseID
                WHERE course.courseID = ?
                GROUP BY quiz_enrolment.userID
            ) result
            WHERE score > (SELECT SUM(question.awardPt) FROM question WHERE question.courseID = ?) * 0.4";
    $pointStmt = $conn->prepare($pointSql);
    $pointStmt->bind_param("ii", $courseID, $courseID);
    $pointStmt->execute();
    $pointResult = $pointStmt->get_result();

    if ($pointResult && $pointResult->num_rows > 0) {
        $pointRow = $pointResult->fetch_assoc();
        $studentsPass = $pointRow['pointCount'];
    }

    // Calculate the number of students who fail
    $studentsFail = $enrolledStudents - $studentsPass - $inactiveParticipants;
} else {
    $userName = "N/A";
    $enrolledStudents = 0;
    $activeParticipants = 0;
    $inactiveParticipants = 0;
    $studentsPass = 0;
    $studentsFail = 0;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generated Report</title>
    <link rel="stylesheet" href="../../styles/style.css">
    <style>
        .page {
            width: 80%;
            margin: auto;

        }

        .page-content {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .course-info {
            width: 100%;
            padding: 15px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            background-color: #FFFFE0;
        }

        .course-info p {
            padding-left: 30px;
            /* Adjust as needed */
        }

        .course-info h1 {
            color: #333;
            font-size: 40px;
            text-decoration: underline;
            text-align: center;
        }

        .course-info h2 {
            color: #333;
            margin-top: 20px;
        }

        button {
            float: right;
            background-color: #333;
            color: #fff;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
        }

        button:hover {
            background-color: #555;
        }
    </style>
</head>

<body>
    <div class="page">
        <div class="page-content">
            <div class="course-info">
                <h1>Report</h1>
                <!-- Course Overview -->
                <h2>Course Overview</h2>
                <p>
                    Course Name: <?php echo $row['courseName']; ?><br />
                    Instructor: <?php echo $userName; ?>
                </p>

                <!-- Enrolled Students -->
                <h2>Enrolled Students</h2>
                <p>
                    Total Number of Enrolled Students: <?php echo $enrolledStudents; ?><br />
                    Completed quiz: <?php echo $activeParticipants; ?><br />
                    Not completed quiz: <?php echo $inactiveParticipants; ?>
                </p>

                <!-- Student Performance -->
                <h2>Student Performance</h2>
                <p>
                    <i>[<b>Grade Distribution:</b> A: >80% B: >60% C: >40% D: Fail]</i> <br />
                    Number of students pass: <?php echo $studentsPass; ?><br />
                    Number of students failed: <?php echo $studentsFail; ?>
                </p>

                <button class="button" id="print-btn" onclick="printReport()">Print</button>

            </div>

        </div>

    </div>
    <script>
        function printReport() {
            // Hide the print button before printing
            document.getElementById('print-btn').style.display = 'none';

            // Use window.print() to open the print dialog
            window.print();

            // Show the print button again after printing
            document.getElementById('print-btn').style.display = 'block';
        }
    </script>
</body>

</html>
