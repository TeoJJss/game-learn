<?php
require '../../modules/config.php';
$role = check_ticket();
if ($role != 'educator') {
    header("Location: ../../index.php");
    exit();
}
include '../../includes/header.php';

$ticket = $_SESSION['ticket'];
$ch = curl_init("$base_url/check-ticket?ticket=$ticket");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
$response = json_decode(curl_exec($ch), true);
$eduID = $response['data']['user_id'];

$fbSql = "SELECT course_feedback.userID, `profile`.`profilePic`, course_feedback.timestamp, course_feedback.fbText, course_feedback.ratings, course_feedback.fbImg, course_feedback.eduReply, course_feedback.fbID, course_feedback.courseID
            FROM course_feedback
            LEFT JOIN `profile` ON course_feedback.userID=`profile`.`userID`
            LEFT JOIN course ON course.courseID = course_feedback.courseID 
            WHERE course.userID=?";
if (isset($_GET['search'])) {
    $fbSql .= " AND LOWER(course_feedback.fbText) LIKE LOWER(?)";
    $stmt = $conn->prepare($fbSql);
    $keyword = "%$_GET[search]%";
    $stmt->bind_param('is', $eduID, $keyword);
} else {
    $stmt = $conn->prepare($fbSql);
    $stmt->bind_param('i', $eduID);
}

$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Feedback</title>
    <link rel="stylesheet" href="../../styles/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .page {
            width: 80%;
            margin: auto;
        }

        .page-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .page-content {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .post {
            width: 100%;
            border: 1px solid #ccc;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
            margin-bottom: 40px;
            position: relative;
            background-color: white;
            border-left: 4px solid #333;
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1);
        }

        .post-content {
            margin-top: -20px;
        }

        .user-info {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            font-size: 20px;
        }

        .user-info img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .question-image {
            width: 50%;
            height: auto;
            max-height: 230px;
        }

        .post-content {
            margin-bottom: 40px;
        }

        .actions {
            display: flex;
            justify-content: flex-start;
            gap: 10px;
        }

        .actions a {
            color: black;
            text-decoration: none;
            margin-right: 30px;
        }

        .actions a:hover {
            color: #00BFFF;
            transform: scale(1.2);
            transition: color 0.3s, transform 0.3s;
        }

        .actions i {
            margin-right: 5px;
        }

        .view-comment {
            position: absolute;
            right: 10px;
            bottom: 20px;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0, 0, 0);
            background-color: rgba(0, 0, 0, 0.4);
        }

        .user-details {
            margin-left: 30px;
            font-size: 12px;
        }

        .fbImg {
            max-width: 10vw;
            margin-bottom: 3vh;
        }

        .timestamp {
            font-size: 14px;
        }

        .time {
            margin-left: 13px;
            /* Adjust the margin as needed */
        }
    </style>

</head>

<body>
    <div class="page">
        <div class="page-title">
            <h1><img src="<?php echo $base; ?>images/educator_pic/stu_feedback.png" alt="Forum Icon" class="forum-image">Student Feedback</h1>
            <input type="text" class="nav_search" maxlength="50" id="search-student_feedback" placeholder="Search For Student Feedbacks" onclick="addParam()" autocomplete="off">
        </div>
        <div class="page-content">

            <?php
            if ($result->num_rows == 0){ // if no search result
                echo "<p>There are no matched course feedback from students.</p>";
            }
            while ($row = $result->fetch_assoc()) {
                $ch = curl_init("$base_url/user-detail?user_id=$row[userID]");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                $response = json_decode(curl_exec($ch), true);

                if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200) {
                    $fbName = $response['msg'];
                }

                echo "<div class='post'>";
                echo "<div class='user-info'>";
                if ($row['profilePic'] != null) {
                    echo "<img src='data:image/png;base64," . $row['profilePic'] . "' alt='profilePic' class='profilePic'>";
                } else {
                    echo "<img src='../../images/user.png' alt='profilePic' class='profilePic'>";
                }
                echo "<span class='username'>$fbName</span>";
                echo "<div class='user-details'>";
                $timestamp = strtotime($row['timestamp']);
                echo "<span class='timestamp'>";
                echo "<span class='date'>" . date('d - m - Y', $timestamp) . "</span>";
                echo "<span class='time'>" . date('H:i:s', $timestamp) . "</span>";
                echo "</span>";

                echo "</div>";
                echo "</div>";

                if ($row['fbImg'] != null) {
                    echo "<img src='data:image/png;base64," . $row['fbImg'] . "' class='fbImg' style='width: 40%;'>";
                }
                echo "<div class='post-content'>";
                echo "<span class='fbText'>" . $row['fbText'] . "</span><br>";
                echo "</div>";

                echo "<div class='actions'>";
                echo '<a href="../../public/course.php?courseID=' . $row['courseID'] . '"><i class="fas fa-comment"></i> View course</a>';
                echo "</div>";
                echo "</div>";
            }
            ?>

        </div>
    </div>
    <script>
        var searchInput = document.getElementById("search-student_feedback");

        searchInput.addEventListener("keypress", function(event) {
            if (event.key == "Enter") {
                console.log("entered");
                searchInputVal = searchInput.value;
                location.href = './student_feedback.php?search=' + searchInputVal;
                event.preventDefault();
            }
        });
    </script>
</body>
<?php include '../../includes/footer.php'; ?>

</html>
