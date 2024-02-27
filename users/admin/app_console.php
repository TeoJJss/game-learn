<?php
    require '../../modules/config.php';
    require './controllers/appConsoleController.php';
    $pendingCourses = fetchPendingCourses();

    if (check_ticket() != 'admin'){
        header("Location: ../../index.php");
        exit();
    }

    $ticket = $_SESSION['ticket'];

    $usernames = array(); // Initialize an empty array

    foreach ($pendingCourses as $course) {
        $userID = $course['userID']; 
        $ch = curl_init("$base_url/user-detail?user_id=$userID");

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $username = json_decode(curl_exec($ch), true)['msg'];

        $usernames[$userID] = $username; 
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
            padding-bottom: 1rem;
            width: 100%;
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

        <h1 class="category">Courses Applications</h1>

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
                        <?php 
                            $imageData = base64_encode($course['courseThumb']);
                            echo '<img src="data:image/png;image/jpg;base64,' . $imageData . '" alt="Course Thumbnail" class="courseThumb">';                        ?>
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
                            <button 
                                id="approveBtn" 
                                class="button"
                                onclick="uptFunc('active', '<?php echo $course['courseID']; ?>')" 
                                <?php 
                                    if ($course['status'] != 'pending'){ 
                                        echo 'disabled style="cursor:not-allowed; background-color: grey; padding: 5px; margin-bottom: 4px;"';
                                    } else {
                                        echo 'style="background-color: green; padding: 5px; margin-bottom: 4px;"';
                                    }
                                ?>
                            >
                                Approve
                            </button>
                            <button 
                                id="rejectBtn" 
                                class="button"
                                onclick="uptFunc('banned', '<?php echo $course['courseID']; ?>')" 
                                <?php 
                                    if ($course['status'] != 'pending'){ 
                                        echo 'disabled style="cursor:not-allowed; background-color: grey; padding: 5px; margin-bottom: 4px; "';
                                    } else {
                                        echo 'style="background-color: red; padding: 5px; margin-bottom: 4px;"';
                                    }
                                ?>
                            >
                                Reject
                            </button>
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

        function uptFunc(newStatus, courseId){
            var confirmed = window.confirm("Are you sure you want to perform this action?");

            if (!confirmed) {
                return; 
            }

            fetch('./api/appConsoleApi.php', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    courseId: courseId,
                    newStatus: newStatus
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log(data)
                location.reload();
            });
        }

    </script>
</body>
</html>

<?php include '../../includes/footer.php'; ?>