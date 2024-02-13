<?php
require '../modules/config.php';
$role = check_ticket();
// if ($role) {
//     header("Location: ../index.php");
//     exit();
// }
include '../includes/header.php';
$search="";
if (isset($_GET['search'])) {
    $search  = $_GET['search'];
    $sql = "SELECT course.courseID, course.courseThumb, course.courseName, course.userID as eduID, `profile`.`jobTitle`, ROUND(AVG(course_feedback.ratings),1) as rating, COUNT(course_enrolment.courseID) as enrolled, course.category
                FROM `course` 
                LEFT JOIN course_feedback ON course.courseID = course_feedback.courseID 
                LEFT JOIN course_enrolment ON course.courseID = course_enrolment.courseID 
                LEFT JOIN `profile` ON `profile`.`userID` = course.userID 
                WHERE LOWER(course.courseName) LIKE LOWER(?) AND course.status='active'
                GROUP BY course.courseID
                ";
    $stmt = $conn->prepare($sql);
    $param = "%$search%";
    $stmt->bind_param("s", $param);
}else{
    $sql = "SELECT course.courseID, course.courseThumb, course.courseName, course.userID as eduID, `profile`.`jobTitle`, ROUND(AVG(course_feedback.ratings),1) as rating, COUNT(course_enrolment.courseID) as enrolled, course.category
                FROM `course` 
                LEFT JOIN course_feedback ON course.courseID = course_feedback.courseID 
                LEFT JOIN course_enrolment ON course.courseID = course_enrolment.courseID 
                LEFT JOIN `profile` ON `profile`.`userID` = course.userID 
                WHERE course.status='active'
                GROUP BY course.courseID
                ";
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
// $stmt->fetch();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search results</title>
    <link rel="stylesheet" href="../styles/style.css">
    <style>
        .course-row {
            display: flex;
            margin-bottom: 6vh;
            border: 1px solid;
            padding: 2vw;
            width: 70vw;
        }

        .course-thumb {
            max-width: 10vw;
            margin-right: 2vw;
        }

        a.course-title {
            font-weight: bold;
            font-size: 2vw;
        }

        a.course-title:hover {
            text-decoration: underline;
            cursor: pointer;
        }

        .course-details {
            align-content: center;
            margin-left: 5vw;
        }

        .rating-num {
            font-weight: bold;
            font-family: 'Poppins', sans-serif;
            margin-right: 2vw;
        }

        .rating {
            display: flex;
            align-items: center;
            font-size: 2vw;
            margin-left: 0.5vw;
        }

        p.detail {
            font-size: 1.5vw;
        }

        .filters {
            display: flex;
            gap: 2vw;
        }

        .count-results {
            margin-right: 20vw;
            margin-bottom: 5vh;
            float: right;
            font-weight: bold;
            color: #6F6F6F;
        }

        #point {
            width: 4vw;
            max-width: 5vw;
            cursor: default;
            pointer-events: none;
        }

        #your-course{
            background-color: #d6af63;
            pointer-events: none;
            color: black;
        }
    </style>
</head>

<body>
    <div class="page">
        <div class="page-title">
            <h1><img src="../images/nav_picture/course.png" alt="Course">Course Search Results</h1>
            
        </div><span>Search key: "<?php echo $search; ?>"</span>
        <div class="page-content">
            <div class="filters">
                <select id="category-filter" class="dropbutton" name="category" onchange="cat_selected()" required>
                    <option value="" disabled selected>Category</option>
                    <?php
                    $count = 0;
                    $cat_arr = [];
                    while ($row = $result->fetch_assoc()) {
                        $count += 1;
                        if (!in_array($row['category'], $cat_arr)) {
                            echo "<option value='$row[category]'>$row[category]</option>";
                            array_push($cat_arr, $row['category']);
                        }
                    }
                    ?>
                </select>
                <select id="rating-filter" class="dropbutton" name="rating" onchange="rat_selected()" required>
                    <option value="" disabled selected>Rating</option>
                    <?php
                    $rat_arr = [];
                    $result->data_seek(0);
                    while ($row = $result->fetch_assoc()) {
                        $rat_val = $row['rating'] == null ? 0 : $row['rating'];
                        if (!in_array($row['rating'], $rat_arr)) {
                            echo "<option value='$rat_val'>$rat_val</option>";
                            array_push($rat_arr, $rat_val);
                        }
                    }
                    ?>
                </select>
                <button class="button" onclick="location.reload()">Clear Filter</button>
            </div>
            <span class="count-results">
                <?php if ($role == 'student'){
                    $ticket = $_SESSION['ticket'];
                    $ch = curl_init("$base_url/check-ticket?ticket=$ticket");
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                    $response = json_decode(curl_exec($ch), true);
                    $user_id = $response['data']['user_id'];
                    $sql = "SELECT `pointValue` FROM `point` WHERE userID=?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $stmt->bind_result($pointVal);
                    $stmt->fetch();
                    echo "<p class='button' id='point'>$pointVal</p>";
                } ?>
                <span id="count-num"><?php echo $count ?></span> results
            </span>
            <br><br>
            <?php
            $result->data_seek(0);
            while ($row = $result->fetch_assoc()) {
                $edu_name = "";
                $courseThumb = $row['courseThumb'] == null ? "<img src='../images/nav_picture/course.png' alt='Course Thumbnail' class='course-thumb'>" : "<img src='data:image/png;base64," . $row['courseThumb'] . "' alt='Course Thumbnail' class='course-thumb'>";
                $courseName = $row['courseName'];
                $courseEdu = $row['eduID'];
                $courseJob = $row['jobTitle'];
                $courseRating = $row['rating'] == null ? 0 : $row['rating'];
                $courseEnrolled = $row['enrolled'];

                $ch = curl_init("$base_url/edu-detail?user_id=$courseEdu");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                $response = json_decode(curl_exec($ch), true);

                if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200) {
                    $eduName = $response['msg'];
                }
            ?>
                <div class="course-row" style="display: '';">
                    <?php echo $courseThumb; ?>
                    <div class="course-details">
                        <a class="course-title" onclick="location.href='../public/course.php?courseID=<?php echo $row['courseID'];?>'"><?php echo $courseName; ?></a>
                        <p class="detail">
                            <?php echo $eduName; ?>/<?php echo $courseJob; ?>
                            <?php if ($role=='educator'){
                                $ticket = $_SESSION['ticket'];
                                $ch = curl_init("$base_url/check-ticket?ticket=$ticket");
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                                $response = json_decode(curl_exec($ch), true);
                                $user_id = $response['data']['user_id'];
                                if ($user_id == $courseEdu){
                                    echo "<span class='button' id='your-course'>Your course</span>";
                                }
                            } 
                            ?>
                        </p>

                        <div class="rating">
                            <span class="rating-num"><?php echo $courseRating; ?> </span>
                            <span class="fa fa-star <?php if ($courseRating >= 1) {
                                                        echo 'checked';
                                                    } ?>"></span>
                            <span class="fa fa-star <?php if ($courseRating >= 2) {
                                                        echo 'checked';
                                                    } ?>"></span>
                            <span class="fa fa-star <?php if ($courseRating >= 3) {
                                                        echo 'checked';
                                                    } ?>"></span>
                            <span class="fa fa-star <?php if ($courseRating >= 4) {
                                                        echo 'checked';
                                                    } ?>"></span>
                            <span class="fa fa-star <?php if ($courseRating >= 5) {
                                                        echo 'checked';
                                                    } ?>"></span>
                        </div>
                    </div>
                    <p class="category-val" hidden><?php echo $row['category']; ?></p>
                    <p class="rating-val" hidden><?php echo $row['rating']; ?></p>
                </div><br>
            <?php } ?>
        </div>
    </div>
    <script>
        function cat_selected() {
            var selected_cat = document.getElementById('category-filter').value;
            var rows = document.getElementsByClassName('course-row');
            var count = 0;

            for (let i = 0; i < rows.length; i++) {
                var cat = rows[i].getElementsByClassName('category-val')[0].innerHTML;
                if (cat == selected_cat) {
                    rows[i].style.display = "";
                    count += 1;
                } else {
                    rows[i].style.display = "none";
                }
            }

            document.getElementById('count-num').innerHTML = count;
        }

        function rat_selected() {
            var selected_cat = document.getElementById('rating-filter').value;
            var rows = document.getElementsByClassName('course-row');
            var count = 0;

            for (let i = 0; i < rows.length; i++) {
                var cat = rows[i].getElementsByClassName('rating-val')[0].innerHTML;
                if (cat == selected_cat) {
                    rows[i].style.display = "";
                    count += 1;
                } else {
                    rows[i].style.display = "none";
                }
            }

            document.getElementById('count-num').innerHTML = count;
        }
    </script>
</body>
<?php include '../includes/footer.php'; ?>

</html>