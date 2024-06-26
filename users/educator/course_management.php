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

$sql = "SELECT course.courseName, course.status, course.courseID FROM course WHERE course.userID=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $eduID);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Educator Course Management</title>
    <link rel="stylesheet" href="../../styles/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Cinzel+Decorative&display=swap">


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
            justify-content: center;
        }

        .course-categories {
            width: 80%;
            border: 2px solid black;
            padding: 20px;
        }

        h1 {
            text-align: center;
            border: 2px solid black;
            padding: 20px;
            width: 80%;
            margin: auto;
        }

        .course {
            border: 2px solid black;
            padding: 10px;
            margin-bottom: 1px;
            position: relative;
        }

        .course p {
            display: inline-block;
        }

        .course .course-status {
            color: #555;
            margin-bottom: 50px;
            position: relative;
            font-size: 17px;
            font-weight: bold;
        }

        .course .course-status.active,
        .course .course-status.pending,
        .course .course-status.banned {
            position: absolute;
            bottom: 10px;
            right: 2vw;
        }

        .course .course-status.active {
            color: darkgreen;
        }

        .course .course-status.pending {
            color: orange;
        }

        .course .course-status.banned {
            color: red;
        }

        .course a {
            position: absolute;
            bottom: 10px;
            right: 2vw;
            text-decoration: none;
        }

        .course-categories button {
            display: block;
            margin: auto;
            padding: 15px 30px;
            font-size: 20px;
            color: #000000;
            background-color: transparent;
            border: 2px solid #000000;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-bottom: 30px;
        }

        .course-categories button:hover {
            background-color: #FFA07A;
            transform: scale(1.1);
            transition: background-color 0.3s ease, transform 0.3s ease;
            animation: shake 0.3s ease;
        }

        .course a::before {
            content: "\f06e";
            font-family: "Font Awesome 5 Free";
            padding-right: 10px;
        }

        .course a:hover {
            color: #1E90FF;
            font-size: 22px;
            transition: color 0.3s, font-size 0.3s;
        }

        .special-font {
            font-family: 'Cinzel Decorative', cursive;
            font-size: 22px;
            color: #00008B;
            font-weight: bold;
        }
    </style>

</head>

<body>
    <div class="page">
        <div class="page-title">
            <img src="<?php echo $base; ?>images/educator_pic/course.png" alt="Course Icon">Course Management
        </div>
        <div class="page-content">
            <h1>Course Categories</h1>
            <div class="course-categories">
                <button onclick="window.location.href='create_new_course.php'">Create New Course</button>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo '<div class="course">';
                        echo '<p class="special-font">' . $row['courseName'] . '</p>';

                        $courseStatus = $row['status'];
                        echo '<p class="course-status ' . $courseStatus . '">Status: ' . $courseStatus . '</p>';
                        echo '<a href="../../public/course.php?courseID=' . $row['courseID'] . '?">View course</a>';
                        echo '</div>';
                    }
                } else {
                    echo '<p>No courses available.</p>';
                }
                ?>
            </div>
        </div>
    </div>
</body>
<?php include '../../includes/footer.php'; ?>

</html>
