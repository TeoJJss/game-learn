<?php
require '../../modules/config.php';

// Redirect if user is not an admin
if (check_ticket() != 'admin'){
    header("Location: ../../index.php");
    exit();
}

$ticket = $_SESSION['ticket'];

function fetchPendingCourses() {
    global $conn;

    $sql = "SELECT courseID, courseThumb, courseName, intro, description, lastUpdate, status, category, label, userID 
            FROM course
            WHERE status = 'pending'";
    
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $pendingCourses = array();

        while($row = $result->fetch_assoc()) {
            $course = array(
                'courseID' => $row['courseID'],
                'courseThumb' => $row['courseThumb'],
                'courseName' => $row['courseName'],
                'intro' => $row['intro'],
                'description' => $row['description'],
                'lastUpdate' => $row['lastUpdate'],
                'status' => $row['status'],
                'category' => $row['category'],
                'label' => $row['label'],
                'userID' => $row['userID']
            );

            $pendingCourses[] = $course;
        }

        return $pendingCourses;
    } else {
        return array();
    }
}

function updateCourseStatus($courseID, $newStatus) {
    global $conn;

    $stmt = $conn->prepare("UPDATE course SET status = ? WHERE courseID = ?");

    $stmt->bind_param("si", $newStatus, $courseID);

    if ($stmt->execute()) {
        return array('success' => true, 'message' => "Course status updated to '$newStatus'");
    } else {
        return array('success' => false, 'message' => "Failed to update course status");
    }
}

// Fetch pending courses
$pendingCourses = fetchPendingCourses();

$usernames = array(); // Initialize an empty array

// Retrieve usernames for pending courses
foreach ($pendingCourses as $course) {
    $userID = $course['userID']; 
    $ch = curl_init("$base_url/user-detail?user_id=$userID");

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    $username = json_decode(curl_exec($ch), true)['msg'];

    $usernames[$userID] = $username; 
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['approveBtn']) || isset($_POST['rejectBtn'])) {
        // Get the course ID and new status from the form
        $courseId = $_POST['courseId'];
        $newStatus = $_POST['newStatus'];

        // Update the course status using the controller function
        $updateResult = updateCourseStatus($courseId, $newStatus);

        // Check if the update was successful
        if ($updateResult['success']) {
            // Redirect to the same page to refresh the data
            header("Location: {$_SERVER['PHP_SELF']}");
            exit();
        } else {
            // Handle the error if the update failed
            echo "Failed to update course status: " . $updateResult['message'];
        }
    }
}

include '../../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../styles/style.css">
    <title>Courses Applications</title>
    <style>
        .box-container {
            display: flex;
            padding-left: 3rem;
            padding-right: 3rem;
            padding-bottom: 3rem;
            flex-wrap: wrap; /* Allow flex items to wrap */
        }

        .box-container:hover {
            cursor: pointer; /* Change cursor to pointer on hover */
        }

        .course-dropdown {
            position: relative; /* Corrected */
            background-color: #f2f2f2; /* Light grey background color */
            border: 1px solid #ccc; /* Grey border */
            padding: 10px; /* Padding for inner content */
            width: 100%;
        }

        .course-dropdown:hover {
            cursor: pointer; /* Change cursor to pointer on hover */
        }

        .category {
            font-size: 2rem; 
            padding-left: 1rem;
            padding-top: 2rem; 
            padding-bottom: 5rem;
            width: 100%;
            display: flex;
            align-items: center;
        }

        .category img {
            margin-right: 10px;
            height: 5rem;
        }

        .course-header {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            padding-right: 1rem;
        }

        .info-column {
            display: flex;
            flex-direction: column;
        }

        .courseThumb {
            width: 100px; /* Adjust width as needed */
            height: auto; /* Maintain aspect ratio */
            margin-bottom: 10px; /* Add margin bottom */
        }

        .button-row {
            display: flex;
            flex-direction: row;
            margin-top: 10px; /* Adjust as needed */
        }

        .button-row button {
            margin-right: 10px; /* Adjust as needed */
        }
    </style>
</head>
<body>
    <div class="box-container">
        <div class="category">
            <img src="../../images/admin_pic/course_review.png" alt="Educators Applications">
            <h1>Courses Applications</h1> 
        </div>

        <?php if (empty($pendingCourses)): ?>
            <p>There are no new applications for course.</p>
        <?php else: ?>
            <?php foreach ($pendingCourses as $course): ?>
                <div class="course-dropdown">
                    <div class="course-header" onclick="toggleDropdown(this)">
                        <h1 class="course-name"><?php echo $course['courseName']; ?></h1>
                        <p>Show more</p>
                    </div>

                    <div class="course-info" style="display: none;">
                        <!-- Thumbnail image -->
                        <img src='data:image/png;base64,<?php echo $course['courseThumb']; ?>' title='<?php echo $course['courseName']; ?>' class='courseThumb' alt='Course thumbnail'>

                        <!-- Description and other information in a column -->
                        <div class="info-column">
                            <p><strong>Description:</strong> <?php echo $course['description']; ?></p>
                            <p><strong>Category:</strong> <?php echo $course['category']; ?></p>
                            <p><strong>Label:</strong> <?php echo $course['label']; ?></p>
                            <p><strong>Last Update:</strong> <?php echo $course['lastUpdate']; ?></p>
                            <p><strong>Created by:</strong> <?php echo $usernames[$course['userID']]; ?></p>
                        </div>
                        <!-- Approve and reject buttons in a row -->
                        <div class="button-row">
                            <!-- Approve form -->
                            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" onsubmit="return confirmSubmit('approve')">
                                <input type="hidden" name="courseId" value="<?php echo $course['courseID']; ?>">
                                <input type="hidden" name="newStatus" value="active"> 
                                <button type="submit" class="button" name="approveBtn">Approve</button>
                            </form>
                            <!-- Reject form -->
                            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" onsubmit="return confirmSubmit('reject')">
                                <input type="hidden" name="courseId" value="<?php echo $course['courseID']; ?>">
                                <input type="hidden" name="newStatus" value="banned"> 
                                <button type="submit" class="button" name="rejectBtn">Reject</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script>
         function toggleDropdown(element) {
            var courseInfo = element.nextElementSibling;
            courseInfo.style.display = (courseInfo.style.display === 'none') ? 'block' : 'none';
        }

        function confirmSubmit(action) {
            var confirmation = window.confirm("Are you sure you want to " + action + " this?");
            return confirmation;
        }

    </script>
</body>
</html>

<?php include '../../includes/footer.php'; ?>
