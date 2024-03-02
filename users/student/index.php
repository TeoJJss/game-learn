<?php
    require '../../modules/config.php';

    $role = check_ticket();

    if ($role != 'student') {
        header("Location: ../../index.php");
        exit();
    }
    include '../../includes/header.php';


    if (!isset($_SESSION['ticket'])) {
        header("Location: ../index.php");
        exit();
    }
    $ticket = $_SESSION['ticket'];
    $ch = curl_init("$base_url/check-ticket?ticket=$ticket");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    $response = json_decode(curl_exec($ch), true);

    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 202) {
        $stuID = $response["data"]["user_id"];
    } else {
        header("Location: ../index.php");
        exit();
    }

    $sql = "SELECT course.courseID, course.courseThumb, course.courseName, course.userID 
            FROM `course_enrolment` JOIN `course` ON course_enrolment.courseID=course.courseID 
            WHERE course_enrolment.userID=?
            ORDER BY course_enrolment.timestamp DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $stuID);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Homepage</title>
    <style>
        .page-content {
            display: flex;
            flex-wrap: wrap;
        }

        .course-container {
            margin-right: 10vw;
            border: 3px solid;
            padding: 1vw;
            border-radius: 10%;
            justify-content: center;
            text-align: center;
            min-width: 15vw;
            max-width: 15vw;
        }

        .courseThumb {
            max-width: 12vw;
            min-width: 12vw;
            min-height: 30vh;
            max-height: 30vh;
            cursor: pointer;
        }

        .courseName {
            color: darkblue;
            font-weight: bold;
            font-size: 1vw;
        }

        .eduName{
            font-weight: 500;
        }
    </style>
    <link rel="stylesheet" href="../../styles/style.css">
</head>

<body>
    <div class="page">
        <div class="page-title">
            <img src="../../images/nav_picture/my_learning.png" alt="Header Image" class="header-image">
            <h1>My Learning</h1>
            <input type="search" class="nav_search" id="course-search-inp" placeholder="Filter course by course name" oninput="filterCourse()">
        </div>
        <div class="page-content">
            <?php while ($row = $result->fetch_assoc()) { ?>
                <div class="course-container">
                    <img src="data:image/png;base64,<?php echo $row['courseThumb'] ?>" class="courseThumb" onclick="location.href='../course.php?courseID=<?php echo $row['courseID'] ?>'"><br>
                    <a href="../course.php?courseID=<?php echo $row['courseID'] ?>" class="courseName"><?php echo $row['courseName']; ?></a><br>
                    <?php
                        $progress_sql = "SELECT (SELECT COUNT(moduleID) FROM module WHERE courseID=$row[courseID]) as module_total, 
                                                        (SELECT COUNT(module_enrolment.moduleID) FROM module_enrolment LEFT JOIN module ON module.moduleID=module_enrolment.moduleID 
                                                        WHERE module_enrolment.userID=$stuID AND module.courseID=$row[courseID]) as enrolled_count";
                        $stmt = $conn->prepare($progress_sql);
                        $stmt->execute();
                        $stmt->bind_result($total, $current);
                        $stmt->fetch();
                        $stmt->close();
                        if ($total > 0) {
                            $progress = $current / $total * 100;
                        } else {
                            $progress = 0;
                        }

                        $ch = curl_init("$base_url/user-detail?user_id=$row[userID]");
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                        $response = json_decode(curl_exec($ch), true);

                        if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200) {
                            $eduName = $response['msg'];
                        }
                    ?>
                    <progress class="course-progress" value="<?php echo $progress;?>" max="100"></progress><br>
                    <span class="eduName"><?php echo $eduName; ?></span>
                </div>
            <?php } ?>
        </div>
    </div>
    <script>
        function filterCourse(){
            var inpVal = document.getElementById('course-search-inp').value.toUpperCase();
            var containers = document.getElementsByClassName('course-container');
            for (let i = 0; i< containers.length; i++){
                var courseName = containers[i].getElementsByClassName('courseName')[0].innerHTML;
                if (courseName.toUpperCase().indexOf(inpVal) > -1){
                    containers[i].hidden = false;
                }else{
                    containers[i].hidden = true;
                }
            }
        }
    </script>
</body>
<?php include '../../includes/footer.php'; ?>

</html>