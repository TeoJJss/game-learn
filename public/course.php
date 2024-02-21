<?php
require '../modules/config.php';
$role = check_ticket();
include '../includes/header.php';

if (!isset($_GET['courseID'])) {
    echo "<script>alert('Invalid Course ID!'); history.back(); </script>";
    exit();
}

$owner = false;
$courseID = $_GET['courseID'];
$sql = "SELECT course.courseID, course.courseThumb, course.courseName, course.intro, course.description, course.lastUpdate, course.status, course.userID,
                    COUNT(course_enrolment.courseID) as enrolled, 
                    `profile`.profilePic, `profile`.about,
                    `profile`.linkedin, `profile`.jobTitle
            FROM course
            LEFT JOIN course_enrolment ON course.courseID = course_enrolment.courseID 
            LEFT JOIN `profile` ON `profile`.`userID` = course.userID 
            WHERE course.courseID=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $courseID);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
if ($row['courseID'] == null) {
    echo "<script>alert('Course Not Found!'); history.back(); </script>";
    exit();
}

$enrolled = $row['enrolled'] == null ? 0 : $row['enrolled'];
$eduID = $row['userID'];
$courseThumb = $row['courseThumb'] == null ? "<img src='../images/nav_picture/course.png' alt='Course Thumbnail' class='course-thumb'>" : "<img src='data:image/png;base64," . $row['courseThumb'] . "' alt='Course Thumbnail' class='course-thumb'>";

$ch = curl_init("$base_url/user-detail?user_id=$eduID");
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

        .instructor h2 {
            padding-left: 3vw;
            padding-top: 5vh;
        }

        .rating-text {
            margin-left: 0.5vw;
            margin-right: 5vw;
        }

        .dot {
            height: 4vh;
            width: 1.7vw;
            background-color: #bbb;
            border-radius: 50%;
            display: inline-block;
            margin-right: 0.5vw;
        }

        .ratings {
            margin-left: 5vw;
        }

        .ratings-h2 {
            vertical-align: center;
            font-weight: 1500;
        }

        .profilePic {
            width: 4vw;
            border-radius: 50%;
            margin-right: 2vw;
        }

        .usr-info {
            display: flex;
        }

        .username,
        .timestamp {
            font-weight: bold;
        }

        .timestamp {
            color: #A0A0A0;
        }

        .fbText {
            margin-left: 5vw;
            font-family: Verdana, Geneva, Tahoma, sans-serif;
            margin-bottom: 5vh;
        }

        .fbRow {
            max-width: 80vw;
        }

        .fbImg {
            max-width: 10vw;
            margin-left: 5vw;
        }

        .eduReply {
            width: 25vw;
            height: 5vh;
            margin-right: 2vw;
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
                        <?php
                        $rating_sql = "SELECT ROUND(AVG(course_feedback.ratings),1) as rating, COUNT(course_feedback.fbID) AS num_fb 
                                        FROM course_feedback 
                                        WHERE course_feedback.courseID = ?";
                        $stmt = $conn->prepare($rating_sql);
                        $stmt->bind_param("i", $courseID);
                        $stmt->execute();
                        $stmt->bind_result($courseRating, $num_fb);
                        $stmt->fetch();
                        $stmt->close();
                        ?>
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
                        <span class="num-fb">(<?php echo $num_fb; ?> ratings)</span>
                        <span class="num-stu"><?php echo $enrolled; ?> enrolments</span>
                    </div>
                    <p class="created-by">Created by <span><?php echo $eduName . ", " . $row['jobTitle']; ?></span></p>
                    <p class="last-update">🕛 Last Updated <?php echo $row['lastUpdate']; ?></p>
                </div>
                <div class="course-info-card">
                    <?php echo $courseThumb; ?><br>
                    <?php if ($role == 'student') {
                        if ($row['status'] == 'active') {
                            echo '<br><button class="button" onclick="location.href=' . "'../users/course.php?courseID=$row[courseID]';" . '">Learn Now</button><br><br>';
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
                                    $owner = true;
                                    echo '<br><button class="button" onclick="location.href=' . "'../users/course.php?courseID=$row[courseID]';" . '">Enter</button><br><br>
                                    <button class="button" id="edit-btn" onclick="location.href=' . "'../users/educator/edit_course.php?courseID=$row[courseID]';" . '">Edit</button><br><br>
                                    <button class="button" id="dlt-btn" onclick="if(confirm(\'Are you sure to delete this course?\')){location.href=' . "'../modules/dlt_course.php?cid=$row[courseID]';}" . '">Delete course</button><br>';
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
                            echo '<br><button class="button" onclick="location.href=' . "'../modules/update_course_status.php?cid=$row[courseID]&new_status=banned';" . '">Ban</button><br>';
                        } else if ($row['status'] == 'banned') {
                            echo '<br><button class="button" onclick="location.href=' . "'../modules/update_course_status.php?cid=$row[courseID]&new_status=active';" . '">Unban</button><br>';
                        } else if ($row['status'] == 'pending') {
                            echo '<br><button class="button" onclick="location.href=' . "'../modules/update_course_status.php?cid=$row[courseID]&new_status=active';" . '">Approve</button><br>';
                            echo '<br><button class="button" onclick="location.href=' . "'../modules/update_course_status.php?cid=$row[courseID]&new_status=banned';" . '">Reject</button><br>';
                        } else {
                            trigger_error('Course invalid!');
                            exit();
                        }
                        echo '<br><button class="button" onclick="location.href=' . "'../users/course.php?courseID=$row[courseID]';" . '">View Course Content</button><br><br>';
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
                <div class="ratings"><br>
                    <h2 class="ratings-h2">
                        <span class="fa fa-star checked" style="font-size: 2vw; margin-left:0.1vw; "></span><span class="rating-text"><?php echo $courseRating; ?> course rating</span>
                        <span class="dot"></span><span class="num-ratings"><?php echo $num_fb; ?> ratings</span>
                    </h2>
                    <table>
                        <?php
                        $fbSql = "SELECT course_feedback.userID, `profile`.`profilePic`, course_feedback.timestamp, course_feedback.fbText, course_feedback.ratings, course_feedback.fbImg, course_feedback.eduReply, course_feedback.fbID
                                        FROM course_feedback
                                        LEFT JOIN `profile` ON course_feedback.userID=`profile`.`userID`
                                        WHERE course_feedback.courseID=?";
                        $stmt = $conn->prepare($fbSql);
                        $stmt->bind_param("i", $courseID);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        while ($row = $result->fetch_assoc()) {
                            $ch = curl_init("$base_url/user-detail?user_id=$row[userID]");
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                            $response = json_decode(curl_exec($ch), true);

                            if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200) {
                                $fbName = $response['msg'];
                            }
                        ?>
                            <tr class="fbRow">
                                <div class="usr-info">
                                    <?php if ($row['profilePic'] != null) { ?>
                                        <img src='data:image/png;base64,<?php echo $row['profilePic'] ?>' alt='profilePic' class='profilePic'>
                                    <?php } else { ?>
                                        <img src="../images/user.png" alt='profilePic' class='profilePic'>
                                    <?php } ?>
                                    <div>
                                        <span class="username"><?php echo $fbName; ?></span><br>
                                        <?php for ($i = 1; $i <= $row['ratings']; $i++) {
                                            echo '<span class="fa fa-star checked" style="font-size: 1.5vw;  "></span>';
                                        }
                                        echo '<span class="timestamp">' . $row['timestamp'] . '</span>'; ?>
                                    </div>
                                </div>
                                <span class="fbText"><?php echo $row['fbText'] ?></span><br>
                                <?php if ($row['fbImg'] != null) {
                                    echo "<img src='data:image/png;base64," . $row['fbImg'] . "' class='fbImg'>";
                                } ?>
                                <br>
                                <?php if ($role == 'educator' && $owner && $row['eduReply'] == null) {  ?>
                                    <form method="post" action="../modules/reply_feedback.php">
                                        <input type="number" name="fbID" value="<?php echo $row['fbID']; ?>" hidden>
                                        <input type="text" name="eduReply" class="eduReply" placeholder="Your reply" oninput="enableReplyBtn('<?php echo $row['fbID']; ?>')" required autocomplete="off" maxlength="100">
                                        <input type="submit" name="submit" id="submitReply-<?php echo $row['fbID']; ?>" class="button" value="Reply" disabled>
                                    </form>
                                    <?php } else {
                                    if ($row['eduReply'] != null) { ?>
                                        <hr style="width:55%;text-align:left;margin-left:0">
                                        <b>Educator's Reply: </b><?php echo $row['eduReply'];
                                                                } ?>
                                <?php } ?>
                                <br><br>
                            </tr><br>
                        <?php } ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script>
        function enableReplyBtn(fbID) {
            var replyBtn = document.getElementById(`submitReply-${fbID}`);
            replyBtn.disabled = false;
        }
    </script>
</body><br><br>
<?php include '../includes/footer.php'; ?>

</html>