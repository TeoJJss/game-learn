<?php
require '../../modules/config.php';
$role = check_ticket();
if ($role != 'educator') {
    header("Location: ../../index.php");
    exit();
}
include '../../includes/header.php';


// Query to fetch courses
$sql = "SELECT * FROM course";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Template</title>
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
            /* Add this line */
            margin: auto;
            /* Add this line to center the h1 element */
        }

        .course {
            border: 2px solid black;
            padding: 10px;
            margin-bottom: 1px;
            position: relative;
            /* Add this line */
        }

        .course p {
            display: inline-block;
        }

        .course .course-status {
            color: #555;
            margin-bottom: 10px;
            position: absolute;
            bottom: 50px;
            /* Adjusted to "top" */
            left: 920px;
            /* Adjusted to "left" */
            font-size: 17px;
            font-weight: bold;
        }

        .course .course-status.active {
            color: darkgreen;
            /* Change color for active status */
        }

        .course .course-status.pending {
            color: red;
            /* Change color for pending status */
        }

        .course a {
            position: absolute;
            /* Add this line */
            bottom: 10px;
            /* Add this line */
            right: 10px;
            /* Add this line */
            text-decoration: none;
        }


        .course-categories button {
            display: block;
            margin: auto;
            padding: 15px 30px;
            /* Increase button size */
            font-size: 20px;
            /* Increase font size */
            color: #000000;
            /* Text color */
            background-color: transparent;
            /* Button color */
            border: 2px solid #000000;
            /* Border color */
            border-radius: 5px;
            /* Rounded corners */
            cursor: pointer;
            /* Hand cursor on hover */
            transition: background-color 0.3s ease;
            /* Smooth transition */
            margin-bottom: 30px;
        }

        .course-categories button:hover {
            background-color: #FFA07A;
            /* Change color on hover to light orange */
        }

        .course a::before {
            content: "\f06e";
            /* Font Awesome eye icon */
            font-family: "Font Awesome 5 Free";
            /* Font Awesome font-family */
            padding-right: 10px;
            /* Space between the icon and the text */
        }
    </style>

</head>

<body>
    <div class="page">
        <div class="page-title">
            <img src="<?php echo $base; ?>images/educator_pic/course.png" alt="Course Icon">
            Course Management
        </div>
        <div class="page-content">
            <h1>Course Categories</h1>
            <div class="course-categories">
                <button onclick="window.location.href='create_new_course.php'">Create New Course</button>
                <?php
                // Check if there are any courses
                if ($result->num_rows > 0) {
                    // Output data of each row
                    while ($row = $result->fetch_assoc()) {
                        echo '<div class="course">';
                        echo '<p>' . $row['courseName'] . '</p>';

                        $courseStatus = $row['status'];
                        echo '<p class="course-status ' . ($courseStatus == 'active' ? 'active' : 'pending') . '">Status: ' . $courseStatus . '</p>';

                        // Check the status and generate the link accordingly
                        if ($courseStatus == 'pending') {
                            echo '<a href="pending.php">View course</a>';
                        } else {
                            echo '<a href="../../public/course.php?courseID=' . $row['courseID'] . '?">View course</a>';
                        }


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