<?php
require '../../modules/config.php';
$role = check_ticket();
if ($role != 'educator') {
    header("Location: ../../index.php");
    exit();
}
include '../../includes/header.php';


$sql = "SELECT courseID, courseName, courseThumb, status FROM course";
$result = mysqli_query($conn, $sql);
?>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../../styles/style.css">
    <style>
        .page {
            width: 85%;
            margin: auto;
        }

        .page-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
            display: flex;
            /* Add this line to enable flex layout */
            align-items: center;
            /* Add this line to vertically center items in flex layout */
        }

        .page-content {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 100px;
        }

        .course-card {
            width: 22%;
            border: 1px solid #ccc;
            border-radius: 10px;
            padding: 8px;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
            margin-bottom: 20px;
            margin-right: 25px;
        }

        .course-card a {
            text-decoration: none;
        }


        .course-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
            text-align: center;
            margin-top: 20px;
        }

        .course-thumb {
            width: 100%;
            /* Adjust this value */
            height: auto;
            border-radius: 10px;
            /* Optional, for rounded corners */
            max-height: 230px;
            /* Add this line */
        }

        .course-card .course-status {
            font-size: 17px;
            font-weight: bold;
            text-align: center;
        }

        .course-card .course-status.active {
            color: darkgreen;
        }

        .course-card .course-status.pending {
            color: orange;
        }

        .course-card .course-status.banned {
            color: red;
        }
    </style>
</head>

<body>
    <div class="page">
        <div class="page-title">
            <img src="<?php echo $base; ?>images/educator_pic/dashboard.png" alt="Dashboard Icon" class="dashboard-image">
            Dashboard
        </div>
        <div class="page-content">
            <!-- Course Card 1 -->
            <?php
            // Check if there are any courses in the database
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {

                    $courseThumb = $row['courseThumb'] == null ? "<img src='../../images/nav_picture/course.png' alt='Course Thumbnail' class='course-thumb'>" : "<img src='data:image/png;base64," . $row['courseThumb'] . "' alt='Course Thumbnail' class='course-thumb'>";
            ?>
                    <!-- Course Card -->
                    <div class="course-card">
                        <a href="../../public/course.php?courseID=<?php echo $row['courseID']; ?>">
                            <?php echo $courseThumb; ?>
                            <div class="course-title">
                                <?php echo $row['courseName']; ?>
                            </div>
                            <?php
                            // Check if 'status' is set in the database result
                            if (isset($row['status'])) {
                                $courseStatus = $row['status'];
                                echo '<p class="course-status ' . ($courseStatus == 'active' ? 'active' : 'pending') . '">Status: ' . $courseStatus . '</p>';
                            } else {
                                // If 'status' is not set, provide a default value or handle accordingly
                                echo '<p class="course-status default">Status: Not specified</p>';
                            }
                            ?>
                        </a>
                    </div>

            <?php
                }
            } else {
                echo "No courses found in the database.";
            }
            ?>
        </div>
    </div>

</body>
<?php include '../../includes/footer.php'; ?>

</html>
