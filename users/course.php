<?php
    require '../modules/config.php';
    $role = check_ticket();
    include '../includes/header.php';
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
        $userID = $response["data"]["user_id"];
    } else {
        header("Location: ../index.php");
        exit();
    }

    if (!isset($_GET['courseID'])) {
        echo "<script>alert('Invalid Course ID!'); history.back(); </script>";
        exit();
    }
    $courseID = $_GET['courseID'];
    if (!$role) {
        header("Location: ../public/course.php?courseID=$courseID");
        exit();
    }

    if ($role == 'student') {
        $course_enrolled_sql = "INSERT INTO course_enrolment(courseID, userID) VALUES(?, ?)";
        $stmt = $conn->prepare($course_enrolled_sql);
        $stmt->bind_param("ii", $courseID, $userID);
        $stmt->execute();
    }

    $sql = "SELECT course.courseID, course.courseThumb, course.courseName, course.description, module.moduleID, module.moduleTitle, module.moduleDesc, module.filename
            FROM course LEFT JOIN module ON course.courseID = module.courseID
            WHERE course.courseID=? AND course.status = 'active'";
    if ($role!='educator'){
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $courseID);
    }else{
        $sql .= " AND course.userID=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $courseID, $userID);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        echo "<script>alert('Course Not Found!'); history.back(); </script>";
        exit();
    }
    $row = $result->fetch_assoc();

    $courseThumb = $row['courseThumb'] == null ? "<img src='../images/nav_picture/course.png' alt='Course Thumbnail' class='course-thumb'>" : "<img src='data:image/png;base64," . $row['courseThumb'] . "' alt='Course Thumbnail' class='course-thumb'>";
    $stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course</title>
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="stylesheet" href="../styles/course_style.css">
    <style>
        .course-head {
            min-height: 11vh;
            max-height: 11vh;
        }

        .course-title {
            font-size: 1.8vw;
        }

        .course-thumb-layer {
            display: flex;
            margin: 5vw 5vw 3vw 0vw;
        }

        .course-thumb {
            max-width: 8vw;
        }

        .thumb-buttons {
            margin-left: 40vw;
        }

        .thumb-button {
            background-color: #FFCA79;
            color: black;
            height: 8vh;
            margin-left: 2vw;
        }

        .thumb-button:hover {
            color: black;
        }

        #pointer-btn {
            cursor: context-menu;
        }

        .thumb-button img {
            width: 1.4vw;
        }

        .course-content {
            margin-left: 5vw;
        }

        p.desc {
            margin-right: 5vw;
            text-align: justify;
        }

        .filename {
            margin-left: 1vw;
            color: #0553AE;
            font-weight: bold;
            font-size: 1.5vw;
            text-decoration: none;
        }

        .filename:hover, a.quiz:hover {
            text-decoration: underline;
            cursor: pointer;
        }

        .module-check {
            width: 2vw;
        }

        h2 {
            font-weight: bold;
        }

        .module-check {
            cursor: pointer;
        }

        .edit-btn {
            margin-left: 10vw;
        }

        a.quiz{
            color: #45AE05;
            font-weight: bold;
            text-decoration: none;
            font-size: 1.5vw;
        }

        #progress-btn{
            cursor: default;
            pointer-events: none;
        }

        .att-icon{
            width: 5vw;
            margin-left: 12vw;
            margin-top: 5vh;
        }
    </style>
</head>

<body>
    <div class="page" id="course-page">
        <div class="page-content">
            <div class="course-head">
                <span class="course-title"><?php echo $row['courseName']; ?></span>
            </div>
            <div class="course-content">
                <div class="course-thumb-layer">
                    <?php echo $courseThumb; ?>
                    <div class="thumb-buttons">
                        <button class="button thumb-button" onclick="location.href='../users/leaderboard.php?courseID=<?php echo $row['courseID'] ?>';"><img src="../images/leaderboard.png" alt="Leaderboard">Leaderboard</button>
                        <?php if ($role == 'student') {
                            $progress_sql = "SELECT (SELECT COUNT(moduleID) FROM module WHERE courseID=$row[courseID]) as module_total, 
                                                    (SELECT COUNT(module_enrolment.moduleID) FROM module_enrolment LEFT JOIN module ON module.moduleID=module_enrolment.moduleID 
                                                    WHERE module_enrolment.userID=$userID AND module.courseID=$row[courseID]) as enrolled_count";
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
                        ?>
                            <button class="button thumb-button" id="progress-btn">Progress <?php echo $progress; ?>%</button>
                        <?php } else if ($role == 'educator') { ?>
                            <button class="button thumb-button" onclick="location.href='./educator/add_module.php?courseID=<?php echo $courseID; ?>';">Add new module</button>
                        <?php } ?>
                    </div>

                </div>
                <p class="desc"><?php echo $row['description'] ?></p>
                <hr style="width:95%;text-align:left;margin-left:0">
                <?php $result->data_seek(0);
                while ($row = $result->fetch_assoc()) { ?>
                    <h2><?php echo $row['moduleTitle']; ?></h2>
                    <p><?php echo $row['moduleDesc']; ?></p>
                    <?php if ($role == 'student') {
                        $checked_sql = "SELECT moduleID FROM module_enrolment WHERE userID=? AND moduleID=?";
                        $checked = false;
                        $stmt = $conn->prepare($checked_sql);
                        $stmt->bind_param("ii", $userID, $row['moduleID']);
                        $stmt->execute();
                        $result2 = $stmt->get_result();
                        if ($result2->num_rows > 0) {
                            $checked = true;
                        }
                        $stmt->close();
                    ?>
                        <input type="checkbox" name="tick" class="module-check" id="module-check-<?php echo $row['moduleID']; ?>" onclick="check_module('<?php echo $row['moduleID']; ?>')" <?php if ($checked) {
                                                                                                                                                                                                echo 'checked';
                                                                                                                                                                                            } ?>>
                    <?php } ?>
                    <b>Attachment: </b>
                    <a class="filename" <?php if (file_exists("../tmp/$row[moduleID]/$row[filename].pdf")) { $file_type="pdf"; ?>onclick="showPdfPreview('<?php echo $row['moduleID'] ?>', '<?php echo $row['filename'] ?>')" <?php } else { $file_type="docx"; ?>href='../tmp/<?php echo $row['moduleID'] ?>/<?php echo $row['filename'] ?>.docx' download<?php } ?>> <?php echo $row['filename']; ?>
                    <br><img src="../images/<?php echo $file_type; ?>.png" alt="Attachment" class="att-icon"></a>
                    <div id="pdfPreview<?php echo $row['moduleID']; ?>"></div><br>
                    <?php if ($role == 'educator') { ?>
                        <iframe src="../frames/edit_module.php?moduleID=<?php echo $row['moduleID']; ?>" id="edit-frame-<?php echo $row['moduleID']; ?>" frameborder="0" width="1000" height="300" style="display: none;"></iframe><br>
                        <button class="button edit-btn" id="edit-btn-<?php echo $row['moduleID']; ?>" style="display: ''" onclick="showEdit('<?php echo $row['moduleID']; ?>');">Edit Module</button>
                    <?php } ?>
                    <hr style="width:95%;text-align:left;margin-left:0">
                <?php } ?>
                <a href="../users/quiz.php?courseID=<?php echo $courseID; ?>" class="quiz">Take the quiz now!</a>
                <br><br>
            </div>
        </div>
    </div>
    <script>
        function showPdfPreview(moduleID, filename) {
            document.getElementById(`pdfPreview${moduleID}`).innerHTML = `<iframe src="../tmp/${moduleID}/${filename}" frameborder="0" width="70%" height="60%" title="${filename}" allowfullscreen></iframe>`;
        }

        function check_module(moduleID) {
            var target_module = document.getElementById(`module-check-${moduleID}`);

            if (target_module.checked == true) {
                location.href = `../modules/check_module.php?mid=${moduleID}`;
            }
        }

        function refresh() {
            location.reload();
        }

        function showEdit(moduleID) {
            var iframe = document.getElementById(`edit-frame-${moduleID}`);
            var editBtn = document.getElementById(`edit-btn-${moduleID}`);
            iframe.style.display = '';
            editBtn.style.display = 'none';
        }
    </script>
</body>
<?php include '../includes/footer.php'; ?>

</html>