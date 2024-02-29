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

function getTotalEnrollmentRestModules() {
    global $conn;

    $sql = "SELECT module.moduleID, COUNT(module_enrolment.moduleID) AS enrolment_count
            FROM module_enrolment
            INNER JOIN module ON module_enrolment.moduleID = module.moduleID
            GROUP BY module.moduleID
            ORDER BY enrolment_count DESC";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Error in preparing statement: " . $conn->error);
    }
    $stmt->execute();
    $stmt->bind_result($moduleID, $enrolmentCount);

    // Initialize total enrollment count for rest of the modules
    $totalEnrollmentRestModules = 0;
    $counter = 0;
    while ($stmt->fetch()) {
        // Skip the top 5 modules
        if ($counter >= 5) {
            $totalEnrollmentRestModules += $enrolmentCount;
        }
        $counter++;
    }

    $stmt->close();

    return $totalEnrollmentRestModules;
}

function getTotalEnrollmentRestCourses() {
    global $conn;

    $sql = "SELECT course.courseID, COUNT(course_enrolment.courseID) AS enrolment_count
            FROM course_enrolment
            INNER JOIN course ON course_enrolment.courseID = course.courseID
            GROUP BY course.courseID
            ORDER BY enrolment_count DESC";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Error in preparing statement: " . $conn->error);
    }
    $stmt->execute();
    $stmt->bind_result($courseID, $enrolmentCount);

    // Initialize total enrollment count for rest of the courses
    $totalEnrollmentRestCourses = 0;
    $counter = 0;
    while ($stmt->fetch()) {
        // Skip the top 5 courses
        if ($counter >= 5) {
            $totalEnrollmentRestCourses += $enrolmentCount;
        }
        $counter++;
    }

    $stmt->close();

    return $totalEnrollmentRestCourses;
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
$restCoursesEnrollment = getTotalEnrollmentRestCourses();
$restModulesEnrollment = getTotalEnrollmentRestModules();
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

$topCoursesData = array();

foreach ($topCourses as $courseID => $courseData) {
    $topCoursesData[] = array("label"=> $courseData['courseName'], "y"=> $courseData['enrolmentCount']);
}
$topCoursesData[] = array("label"=> "Others", "y"=> $restCoursesEnrollment);

$topModuleData = array();

foreach ($topModules as $moduleID => $moduleData) {
    $topModuleData[] = array("label"=> $moduleData['moduleName'], "y"=> $moduleData['enrolmentCount']);
}
$topModuleData[] = array("label"=> "Others", "y"=> $restModulesEnrollment);

$newEnrollData = array();

$newEnrollData[] = array("label"=> 'Applicaition for Educator', "y"=> $totalEducatorApplications);
$newEnrollData[] = array("label"=> 'Applicaition for Course', "y"=> count($totalPendingCourses));
$newEnrollData[] = array("label"=> 'Total Students', "y"=> $totalStudents);
$newEnrollData[] = array("label"=> 'Total Educators', "y"=> $totalEducators);

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
<script>
    window.onload = function () {
    
        var topCourseChart = new CanvasJS.Chart("topCourseChart", {
            animationEnabled: true,
            exportEnabled: true,
            title:{
                text: ""
            },
            subtitles: [{
                text: ""
            }],
            data: [{
                type: "pie",
                showInLegend: "true",
                legendText: "{label}",
                indexLabelFontSize: 16,
                indexLabel: "{label} - #percent%",
                yValueFormatString: "฿#,##0",
                dataPoints: <?php echo json_encode($topCoursesData, JSON_NUMERIC_CHECK); ?>
            }]
        });
        topCourseChart.render();

    var topModuleChart = new CanvasJS.Chart("topModuleChart", {
            animationEnabled: true,
            exportEnabled: true,
            title:{
                text: ""
            },
            subtitles: [{
                text: ""
            }],
            data: [{
                type: "pie",
                showInLegend: "true",
                legendText: "{label}",
                indexLabelFontSize: 16,
                indexLabel: "{label} - #percent%",
                yValueFormatString: "฿#,##0",
                dataPoints: <?php echo json_encode($topModuleData, JSON_NUMERIC_CHECK); ?>
            }]
        });
    topModuleChart.render();

    var totalEnrollmentChart = new CanvasJS.Chart("totalEnrollmentChart", {
        animationEnabled: true,
        theme: "light2", // "light1", "light2", "dark1", "dark2"
        title: {
            text: ""
        },
        axisY: {
            title: "Total Amounts"
        },
        data: [{
            type: "bar",
            dataPoints: <?php echo json_encode($newEnrollData, JSON_NUMERIC_CHECK); ?>
        }]
    });
    totalEnrollmentChart.render();
    
    }
 </script>

    <div class="category">
        <img src="../../images/admin_pic/analytics.png" alt="Educators Applications">
        <h1>Admin Homepage</h1> 
    </div>

    <div class="box-container">
        <div class="topCourses box">
            <h1>Top 5 courses</h1>
            <div id="topCourseChart" style="height: 370px; width: 100%;"></div>
            <script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
        </div>

        <div class="topModules box">
            <h1>Top 5 modules</h1>
            <div id="topModuleChart" style="height: 370px; width: 100%;"></div>
            <script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>        
        </div>

        <div class="newNumApplication box">
            <h1>Total Enrollments and Applications</h1>
            <div id="totalEnrollmentChart" style="height: 370px; width: 100%;"></div>
            <script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
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