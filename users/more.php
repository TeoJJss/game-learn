<?php
    require '../modules/config.php';
    $role = check_ticket();
    if (!$role) {
        header("Location: ../index.php");
        exit();
    }
    include '../includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>More</title>
    <link rel="stylesheet" href="../styles/style.css">
    <style>
        .page-content {
            display: flex;
            gap: 5vw;
            flex-wrap: wrap;
            margin-right: 5vw;
            align-items: center;
        }
    </style>
</head>

<body>
    <div class="page">
        <div class="page-title">
            <h1><img src="../images/nav_picture/more.png" alt="Settings">More</h1>
        </div>
        <div class="page-content">
            <?php if ($role == "student") { ?>
                <button class="more_button" onclick="location.href='../public/search_result.php'">
                    <img src="../images/nav_picture/course.png" alt="nav_button">
                    <a>Course</a>
                </button>
                <button class="more_button">
                    <img src="../images/nav_picture/gift_shop.png" alt="nav_button">
                    <a>Gift Shop</a>
                </button>
                <button class="more_button">
                    <img src="../images/forum.png" alt="nav_button">
                    <a>Forum</a>
                </button>
                <button class="more_button">
                    <img src="../images/nav_picture/my_learning.png" alt="nav_button">
                    <a>My Learning</a>
                </button>
                <button class="more_button">
                    <img src="../images/nav_picture/report.png" alt="nav_button">
                    <a>Report</a>
                </button>
                <button class="more_button">
                    <img src="../images/nav_picture/feedback.png" alt="nav_button">
                    <a>Feedback</a>
                </button>
                <button class="more_button" onclick="location.href='./index.php';">
                    <img src="../images/setting.png" alt="nav_button">
                    <a>Settings</a>
                </button>

            <?php }else if($role == 'educator'){ ?>
                <button class="more_button" onclick="location.href = '<?php echo $base; ?>users/educator/course_management.php'">
                    <img src="../images/educator_pic/course.png" alt="nav_button">
                    <a>My Courses</a>
                </button>
                <button class="more_button" onclick="location.href = '<?php echo $base; ?>users/educator/forum.php'">
                    <img src="../images/forum.png" alt="nav_button">
                    <a>Forum</a>
                </button>
                <button class="more_button" onclick="location.href = '<?php echo $base; ?>users/educator/report.php'">
                    <img src="../images/nav_picture/report.png" alt="nav_button">
                    <a>Report</a>
                </button>
                <button class="more_button" onclick="location.href='./index.php';">
                    <img src="../images/setting.png" alt="nav_button">
                    <a>Settings</a>
                </button>
                <button class="more_button" onclick="location.href = '<?php echo $base; ?>users/educator/system_feedback.php'">
                    <img src="../images/sys_feedback.png" alt="nav_button">
                    <a>System Feedback</a>
                </button>
                <button class="more_button" onclick="location.href = '<?php echo $base; ?>users/educator/student_feedback.php'">
                    <img src="../images/educator_pic/stu_feedback.png" alt="nav_button">
                    <a>Student's Feedback</a>
                </button>
                <button class="more_button">
                    <img src="../images/leaderboard.png" alt="nav_button">
                    <a>Leaderboard</a>
                </button>
            <?php }else if ($role=='admin'){ ?>
                <button class="more_button" onclick="location.href='./admin/edu_console.php';">
                    <img src="../images/admin_pic/edu_approval.png" alt="nav_button">
                    <a>Educators Applications</a>
                </button>
                <button class="more_button">
                    <img src="../images/admin_pic/course_review.png" alt="nav_button">
                    <a>Courses Application</a>
                </button>
                <button class="more_button" onclick="location.href='./admin/user_console.php';">
                    <img src="../images/admin_pic/manage_user.png" alt="nav_button">
                    <a>User Management</a>
                </button>
                <button class="more_button">
                    <img src="../images/admin_pic/feedback.png" alt="nav_button">
                    <a>System Feedbacks</a>
                </button>
                <button class="more_button">
                    <img src="../images/forum.png" alt="nav_button">
                    <a>Forum</a>
                </button>
                <button class="more_button">
                    <img src="../images/report.png" alt="nav_button">
                    <a>Report</a>
                </button>
                <button class="more_button" onclick="location.href='./index.php';">
                    <img src="../images/setting.png" alt="nav_button">
                    <a>Settings</a>
                </button>
            <?php } ?>
            <button class="more_button" onclick="location.href='../modules/logout.php';">
                <img src="../images/logout.png" alt="nav_button">
                <a>Logout</a>
            </button>
        </div>
    </div>
</body>
<?php include '../includes/footer.php'; ?>

</html>
