<?php
require '../modules/config.php';
$role = check_ticket();
include '../includes/header.php';

if (!isset($_GET['courseID'])) {
    echo "<script>alert('Invalid Course ID!'); history.back(); </script>";
    exit();
}

$courseID = $_GET['courseID'];
$sql = "SELECT course.courseID, course.courseThumb, course.courseName, course.intro, course.description, course.lastUpdate, course.status, course.userID,
                    ROUND(AVG(course_feedback.ratings),1) as rating, COUNT(course_enrolment.courseID) as enrolled, COUNT(course_feedback.fbID) as num_fb, 
                    `profile`.profilePic, `profile`.about,
                    `profile`.linkedin, `profile`.jobTitle
            FROM course
            LEFT JOIN course_feedback ON course.courseID = course_feedback.courseID 
            LEFT JOIN course_enrolment ON course.courseID = course_enrolment.courseID 
            LEFT JOIN `profile` ON `profile`.`userID` = course.userID 
            WHERE course.courseID=? AND course.`status`='active'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $courseID);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
if ($row['courseID']==null){
    echo "<script>alert('Course Not Found!'); history.back(); </script>";
    exit();
}
$courseRating = $row['rating'] == null ? 0 : $row['rating'];
$enrolled = $row['enrolled'] == null ? 0 : $row['enrolled'];
$eduID = $row['userID'];
$courseThumb = $row['courseThumb'] == null ? "<img src='../images/nav_picture/course.png' alt='Course Thumbnail' class='course-thumb'>" : "<img src='data:image/png;base64," . $row['courseThumb'] . "' alt='Course Thumbnail' class='course-thumb'>";

$ch = curl_init("$base_url/edu-detail?user_id=$eduID");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
$response = json_decode(curl_exec($ch), true);

if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200) {
    $eduName = $response['msg'];
} else {
    trigger_error("Unknown educator!");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mathy Course</title>
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="stylesheet" href="../styles/course_style.css">
    <style>
        .text p {
            word-wrap: break-word;
            min-width: 50vw;
            max-width: 52vw;
            color: white;
            margin-left: 5vw;

        }

        .rating span.rating-num {
            color: #FF9900;
            font-weight: bold;
            font-size: 1.5vw;
            margin-right: 1vw;
        }

        .num-fb {
            color: #FF9900;
        }

        span.fa {
            gap: 1px;
        }

        .rating {
            display: flex;
            align-items: center;
            font-size: 1vw;
            margin-left: 5vw;
            gap: 0.1vw;
        }

        .num-stu {
            color: azure;
            margin-left: 5vw;
        }

        .created-by,
        .last-update {
            font-size: 0.9vw;
        }

        .course-info-card {
            background-color: azure;
            margin-top: 5vh;
            flex-direction: column;
            justify-content: center;
            text-align: center;
            box-shadow: 0px 16px 16px rgba(0, 0, 0, 0.2);
            margin-left: 8vw;
            min-height: 40vh;
            height: 100%;
            width: 20vw;
            padding-bottom: 2vh;
        }

        .preview-content {
            margin-left: 4vw;
            margin-top: 12vh;
            max-width: 90%;
            word-wrap: break-word;
        }

        .preview-content h2 {
            font-weight: bold;
            margin-left: 0.3vw;
        }

        .instructor {
            box-shadow: 0px 6px 6px rgba(0, 0, 0, 0.2);
        }

        #profilePic-edu {
            width: 10vw;
            margin-left: 2vw;
            margin-top: 5vh;
            margin-bottom: 4vh;
            border-radius: 60%;
            max-height: 20vh;
        }

        .instructor-info {
            display: flex;
            flex-direction: row;
        }

        .instructor-info h3 {
            color: #FF9900;
            font-weight: bold;
        }

        .info {
            display: flex;
            flex-direction: column;
        }

        .desc,
        .info {
            margin-left: 3vw;
            margin-bottom: 2vh;
            margin-right: 5vw;
            text-align: justify;
        }

        #dlt-btn {
            background-color: red;
        }

        .rating-field {
            margin-top: 5vh;
        }
    </style>
</head>

<body>
    <div class="page" id="course-page">
        <div class="page-content">
            <div class="course-head">
                <div class="text">
                    <p class="course-title"><?php echo $row['courseName']; ?></p>
                    <p class="course-intro"><?php echo $row['intro']; ?></p>
                    <div class="rating">
                        <span class="rating-num"><?php echo $courseRating; ?> </span>
                        <?php
                        $i = 0;
                        do {
                            if ($courseRating == 0) {
                                echo '<span class="fa fa-star"></span>';
                            } else {
                                echo '<span class="fa fa-star checked"></span>';
                            }

                            $i++;
                        } while ($i < $courseRating)
                        ?>
                        <span class="num-fb">(<?php echo $row['num_fb'] ?> ratings)</span>
                        <span class="num-stu"><?php echo $enrolled; ?> enrolments</span>
                    </div>
                    <p class="created-by">Created by <span><?php echo $eduName . ", " . $row['jobTitle']; ?></span></p>
                    <p class="last-update">🕛 Last Updated <?php echo $row['lastUpdate']; ?></p>
                </div>
                <div class="course-info-card">
                    <?php echo $courseThumb; ?><br>
                    <?php if ($role == 'student') {
                        if ($row['status'] == 'active') {
                            echo '<br><button class="button" onclick="location.href='."'../users/course.php?courseID=$row[courseID]';".'">Learn Now</button><br><br>';
                        } else {
                            echo '<p style="color: red;">The course is unavailable at the moment!</p>';
                        }
                    } else if ($role == 'educator') {
                        $ticket = $_SESSION['ticket'];
                        $ch = curl_init("$base_url/check-ticket?ticket=$ticket");
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                        $response = json_decode(curl_exec($ch), true);

                        if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 202) {
                            if ($response['data']['user_id'] == $eduID) {
                                if ($row['status'] == 'active') {
                                    echo '<br><button class="button">Enter</button><br><br>
                                    <button class="button" id="edit-btn">Edit</button><br><br>
                                    <button class="button" id="dlt-btn" onclick="location.href='."'../modules/dlt_course.php?cid=$row[courseID]';".'">Delete course</button><br>';
                                } else if ($row['status'] == 'banned') {
                                    echo '<p style="color: red;">Your course is banned!</p>';
                                } else if ($row['status'] == 'pending') {
                                    echo '<p style="color: red;">Your course is waiting for approval!</p>';
                                } else {
                                    trigger_error('Course invalid!');
                                    exit();
                                }
                            }
                        }
                    } else if ($role == 'admin') {
                        if ($row['status'] == 'active') {
                            echo '<br><button class="button" onclick="location.href='."'../modules/update_course_status.php?cid=$row[courseID]&new_status=banned';".'">Ban</button><br>';
                        } else if ($row['status'] == 'banned') {
                            echo '<br><button class="button" onclick="location.href='."'../modules/update_course_status.php?cid=$row[courseID]&new_status=active';".'">Unban</button><br>';
                        } else if ($row['status'] == 'pending') {
                            echo '<br><button class="button" onclick="location.href='."'../modules/update_course_status.php?cid=$row[courseID]&new_status=active';".'">Approve</button><br>';
                            echo '<br><button class="button" onclick="location.href='."'../modules/update_course_status.php?cid=$row[courseID]&new_status=banned';".'">Reject</button><br>';
                        } else {
                            trigger_error('Course invalid!');
                            exit();
                        }
                    } else {
                        echo '<p style="color: red;">Please login first!</p>';
                    } ?>
                </div>
            </div>
            <div class="preview-content">
                <div class="desc">
                    <h2>Description</h2>
                    <p><?php echo $row['description']; ?></p>
                </div><br>
                <div class="instructor">
                    <h2>Instructor</h2>
                    <div class="instructor-info">
                        <br>
                        <?php
                        $no_img = "<img src='../images/user.png' id='profilePic-edu'>";
                        if ($row['profilePic'] !== null) {
                            $img_html =  '<img src="data:image/png;image/jpg;base64,' . $row['profilePic'] . '" alt="Profile Picture" id="profilePic-edu">';
                        } else {
                            $img_html = $no_img;
                        }
                        echo $img_html;

                        ?>
                        <div class="info">
                            <h3><?php echo $eduName . " , " . $row['jobTitle']; ?></h3>
                            <span><?php echo $row['about'] ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body><br><br>
<?php include '../includes/footer.php'; ?>

</html>