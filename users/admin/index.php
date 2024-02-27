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
    include './controllers/mainPageController.php';
    $topCourses = getTop5Courses();
    $topModules = getTop5Modules();
    $totalPendingCourses = getTotalPendingCourses();

    $totalStudents = 0;
    $totalEducators = 0;

    foreach ($user_ls as $row) {
        if ($row['status'] == "active" && $row['role'] == "student") {
            $totalStudents++;
        } else if ($row['status'] == "active" && $row['role'] == "educator") {
            $totalEducators++;
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
            padding-left: 1rem;
            padding-top: 2rem; 
            padding-bottom: 1rem;
        }
    </style>
</head>

<body>
    <script> //checking value of ticket
        var ticket = "<?php echo $ticket; ?>";
        console.log("Ticket value:", ticket);
    </script>

    <h1 class="category">Admin Homepage</h1>

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
            <h2>Total applications for educator: <br><?php echo count($edu_ls); ?></h2>
            <h2>Total applications for course: <br><?php echo count($edu_ls); ?></h2>
            <h2>Total Students: <br><?php echo $totalStudents; ?></h2>
            <h2>Total Educators: <br><?php echo $totalEducators; ?></h2>
        </div>
    </div>

    <div class="box-container2">
        <div class="checkEducatorApplication box">
            <h1>Check Educator Applications</h1>
            <?php $count = 0; ?>
            <?php foreach ($edu_ls as $row): ?>
                <?php if ($count < 5): ?>
                    <div class="info">
                        <h2>Email: <?php echo $row['email']; ?></h2>
                        <p>Fullname: <?php echo $row['name']; ?></p>
                        <p>User_ID: <?php echo $row['user_id']; ?></p>
                    </div>
                    <?php $count++; ?>
                <?php else: ?>
                    <?php break; ?>
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

            <button onclick="window.location.href='user_console.php';" class="button">Check more</button>
            </div>
        </div>
    
</body>

</html>

<?php include '../../includes/footer.php'; ?>