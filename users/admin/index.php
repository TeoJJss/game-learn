<?php 
require '../../modules/config.php';

if (check_ticket() != 'admin'){
    header("Location: ../../index.php");
    exit();
}

$ticket = $_SESSION['ticket'];

$ch_edu = curl_init("$base_url/edu-list?ticket=$ticket");
curl_setopt($ch_edu, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch_edu, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

$response_edu = json_decode(curl_exec($ch_edu), true);

if (curl_getinfo($ch_edu, CURLINFO_HTTP_CODE) == 200){
    $edu_ls = $response_edu['msg'];
}

$ch_user = curl_init("$base_url/user-list?ticket=$ticket");
curl_setopt($ch_user, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch_user, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

$response_user = json_decode(curl_exec($ch_user), true);

if (curl_getinfo($ch_user, CURLINFO_HTTP_CODE) == 200){
    $user_ls = $response_user['msg'];
}

include '../../includes/header.php';

// Include controller functions
function getTop5Courses() {
    global $conn;

    $sql = "SELECT course.courseID, course.courseName, COUNT(course_enrolment.courseID) AS enrolment_count
            FROM course_enrolment
            INNER JOIN course ON course_enrolment.courseID = course.courseID
            GROUP BY course.courseID, course.courseName
            ORDER BY enrolment_count DESC
            LIMIT 5";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Error in preparing statement: " . $conn->error);
    }
    $stmt->execute();
    $stmt->bind_result($courseID, $courseName, $enrolmentCount);

    // Fetch the results and store them in an associative array
    $results = array();
    while ($stmt->fetch()) {
        $results[$courseID] = array(
            "courseName" => $courseName,
            "enrolmentCount" => $enrolmentCount
        );
    }

    $stmt->close();

    return $results;
}

function getTop5Modules() {
    global $conn;

    $sql = "SELECT module.moduleID, module.moduleTitle, COUNT(module_enrolment.moduleID) AS enrolment_count
            FROM module_enrolment
            INNER JOIN module ON module_enrolment.moduleID = module.moduleID
            GROUP BY module.moduleID, module.moduleTitle
            ORDER BY enrolment_count DESC
            LIMIT 5";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Error in preparing statement: " . $conn->error);
    }
    $stmt->execute();
    $stmt->bind_result($moduleID, $moduleName, $enrolmentCount);

    // Fetch the results and store them in an associative array
    $results = array();
    while ($stmt->fetch()) {
        $results[$moduleID] = array(
            "moduleName" => $moduleName,
            "enrolmentCount" => $enrolmentCount
        );
    }

    $stmt->close();

    return $results;
}



function getTotalPendingCourses() {
    global $conn;

    $sql = "SELECT courseID, courseName, lastUpdate FROM course WHERE status = 'pending' ORDER BY lastUpdate ASC";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Error in preparing statement: " . $conn->error);
    }
    $stmt->execute();
    $stmt->bind_result($courseID, $courseName, $lastUpdate);

    // Fetch the results and store them in an associative array
    $results = array();
    while ($stmt->fetch()) {
        $results[] = array(
            "courseID" => $courseID,
            "courseName" => $courseName,
            "lastUpdate" => $lastUpdate
        );
    }

    $stmt->close();

    return $results;
}

// Call controller functions
$topCourses = getTop5Courses();
$topModules = getTop5Modules();
$totalPendingCourses = getTotalPendingCourses();

$totalStudents = 0;
$totalEducators = 0;
$totalEducatorApplications = 0;

foreach ($user_ls as $row) {
    if ($row['status'] == "active" && $row['role'] == "student") {
        $totalStudents++;
    } else if ($row['status'] == "active" && $row['role'] == "educator") {
        $totalEducators++;
    } else if ($row['status'] == "pending" && $row['role'] == "educator") {
        $totalEducatorApplications++;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../styles/style.css">
    <title>Admin</title>
    <style>
        .box-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr); /* Three equal columns */
            gap: 20px; /* Gap between grid items */
            padding-left: 3rem;
            padding-right: 3rem;
            padding-bottom: 3rem;
        }

        .box-container2 {
            display: grid;
            grid-template-columns: repeat(2, 1fr); /* Three equal columns */
            gap: 20px; /* Gap between grid items */
            padding-left: 8rem;
            padding-right: 8rem;
            padding-bottom: 3rem;
        }

        .box {
            display: flex;
            flex-direction: column;
            padding: 0.5rem;
            border: 1px solid #ccc;
        }

        .info {
            margin-bottom: 10px;
        }

        .category {
            font-size: 2rem; 
            padding-left: 5rem;
            padding-top: 2rem; 
            padding-bottom: 5rem;
            width: 100%;
            display: flex;
            align-items: center;
        }

        .category img {
            margin-right: 10px;
            height: 5rem;
        }
        
        .category h1 {
            padding-left: 1rem;
        }
    </style>
</head>

<body>
    <script> //checking value of ticket
        var ticket = "<?php echo $ticket; ?>";
        console.log("Ticket value:", ticket);
    </script>

    <div class="category">
        <img src="../../images/admin_pic/analytics.png" alt="Educators Applications">
        <h1>Admin Homepage</h1> 
    </div>

    <div class="box-container">
        <div class="topCourses box">
            <h1>Top 5 courses</h1>
            <?php foreach ($topCourses as $courseID => $courseData): ?>
                <div class="info">
                    <h2><?php echo $courseData['courseName']; ?></h2>
                    <p>Enrollment Count: <?php echo $courseData['enrolmentCount']; ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="topModules box">
            <h1>Top 5 modules</h1>
            <?php foreach ($topModules as $moduleID => $moduleData): ?>
                <div class="info">
                    <h2><?php echo $moduleData['moduleName']; ?></h2>
                    <p>Enrollment Count: <?php echo $moduleData['enrolmentCount']; ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="newNumApplication box">
            <h1>New enrollment</h1>
            <h2>Total applications for educator: <br><?php echo $totalEducatorApplications; ?></h2>
            <h2>Total applications for course: <br><?php echo count($totalPendingCourses); ?></h2>
            <h2>Total Students: <br><?php echo $totalStudents; ?></h2>
            <h2>Total Educators: <br><?php echo $totalEducators; ?></h2>
        </div>
    </div>

    <div class="box-container2">
        <div class="checkEducatorApplication box">
            <h1>Check Educator Applications</h1>
            <?php $count = 0; ?>
            <?php foreach ($user_ls as $row): ?>
                <?php if ($row['status'] == "pending" && $row['role'] == "educator" && $count < 5): ?>
                    <div class="info">
                        <h2>Email: <?php echo $row['email']; ?></h2>
                        <p>Fullname: <?php echo $row['name']; ?></p>
                        <p>User_ID: <?php echo $row['user_id']; ?></p>
                    </div>
                    <?php $count++; ?>
                <?php endif; ?>
            <?php endforeach; ?>

            <button onclick="window.location.href='edu_console.php';" class="button">Check more</button>
        </div>

        <div class="checkCourseApplication box">
            <h1>Check Course Applications</h1>
            <?php $count = 0; ?>
            <?php foreach ($totalPendingCourses as $pendingCourse): ?>
                <?php if ($count < 5): ?>
                    <div class="info">
                        <h2>Course Name: <?php echo $pendingCourse['courseName']; ?></h2>
                        <p>Last Update: <?php echo $pendingCourse['lastUpdate']; ?></p>
                        <p>Course_ID: <?php echo $pendingCourse['courseID']; ?></p>
                    </div>
                    <?php $count++; ?>
                <?php else: ?>
                    <?php break; ?>
                <?php endif; ?>
            <?php endforeach; ?>

            <button onclick="window.location.href='./app_console.php';" class="button">Check more</button>
            </div>
        </div>
    
</body>

</html>

<?php include '../../includes/footer.php'; ?>