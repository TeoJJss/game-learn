<?php 
    require '../../modules/config.php';

    $role = check_ticket();

    if ($role != 'student'){
        echo "<script>location.href='../../index.php'</script>";
        exit();
    }
    include '../../includes/header.php';

    $search = "";
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
    } else {
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
    $result = $stmt->get_result();
    function truncateDescription($description, $wordLimit = 20) {
        // Explode the description into an array of words
        $words = explode(' ', $description);
    
        // Limit the array to the specified number of words
        $limitedWords = array_slice($words, 0, $wordLimit);
    
        // Implode the limited words back into a string
        $limitedDescription = implode(' ', $limitedWords);
    
        return $limitedDescription;
    }
    
    $queryAllCourses = "SELECT courseID, courseThumb, courseName, intro, description, label, category FROM course";
    
    $resultAllCourses = $conn->query($queryAllCourses);
    
    // Check if the query executed successfully
    if (!$resultAllCourses) {
        die("Query failed: " . $conn->error . "<br>Query: " . $queryAllCourses);
    }
    
    // Rest of your code to fetch and display data goes here...
    
    // Add the following code to filter courses
    $categoryFilter = isset($_GET['category']) ? $_GET['category'] : '';
    $progressFilter = isset($_GET['progress']) ? $_GET['progress'] : '';
    
    $queryFilteredCourses = "SELECT courseID, courseThumb, courseName, intro, description, label, category FROM course WHERE 1";
    
    if (!empty($categoryFilter)) {
        $queryFilteredCourses .= " AND category = '$categoryFilter'";
    }
    
    if (!empty($progressFilter)) {
        if ($progressFilter == 'InProgress') {
            $queryFilteredCourses .= " AND progress < 100";
        } elseif ($progressFilter == 'Completed') {
            $queryFilteredCourses .= " AND progress = 100";
        }
    }
    
    $resultFilteredCourses = $conn->query($queryFilteredCourses);
    
    $searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
    
    if (!empty($searchTerm)) {
        $querySearch = "SELECT courseID, courseThumb, courseName, intro, description, label, category, instructor FROM course WHERE 
            courseName LIKE '%$searchTerm%' OR 
            intro LIKE '%$searchTerm%' OR 
            description LIKE '%$searchTerm%' OR 
            label LIKE '%$searchTerm%' OR 
            category LIKE '%$searchTerm%' OR 
            instructor LIKE '%$searchTerm%'";
    } else {
        $querySearch = "SELECT courseID, courseThumb, courseName, intro, description, label, category, instructor FROM course";
    }
    
    $resultSearch = $conn->query($querySearch);

    $perPage = 5; // Number of courses per page
    $totalCourses = $result->num_rows; // Total number of courses

    $totalPages = ceil($totalCourses / $perPage);

    // Get the current page from the query parameter
    $currentPage = isset($_GET['page']) ? $_GET['page'] : 1;

    // Calculate the offset for fetching courses
    $offset = ($currentPage - 1) * $perPage;

    // Fetch courses for the current page
    $sql .= " LIMIT $offset, $perPage";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // ... (Rest of your HTML and JavaScript code remains unchanged)
    // Get existing query parameters
    $queryParams = $_SERVER['QUERY_STRING'];

// If there are existing query parameters, append an ampersand to separate them
    $queryString = empty($queryParams) ? '' : '&' . $queryParams;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Homepage</title>
    <style>
        .header-content {
                display: flex;
                align-items: center;
            }

            .header-image {
                width: 50px;
                height: auto;
                margin-right: 10px;
            }

            .header-text {
                display: flex;
                flex-direction: column;
            }

            .filter-form {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            }

            .filter-container {
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .filter-column {
                margin-right: 13px;
                border-radius: 5px;
            }

            select {
                padding: 8px;
                font-size: 16px;
            }

            section {
                padding: 10px;
            }

            #game-field {
                display: grid;
                grid-template-columns: repeat(5, 1fr);
                gap: 10px;
            }

            .game-cell {
                background-color: #ddd;
                padding: 20px;
                text-align: center;
                font-size: 18px;
                font-weight: bold;
                border-radius: 5px;
                width: 350px;
                vertical-align: middle
            }

            .progress-bar-container {
                background-color: #ddd;
                height: 20px;
                margin-bottom: 10px;
            }

            .progress-bar {
                height: 20px;
                background: #1da1f2;
                box-shadow: 2px 14px 15px -7px rgba(30, 166, 250, 0.36);
                border-radius: 50px;
                transition: all 0.5s;
                }


            .course-thumb {
                max-width: 10vw;
                margin-right: 2vw;
            }

            a.course-title:hover {
                text-decoration: underline;
                cursor: pointer;
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
                width: 7vw;
                max-width: 5vw;
                cursor: default;
                pointer-events: none;
                background-color: #FFE9C8;
                color: black;
                font-size: 1.5vw;
            }

            #point * {
                vertical-align: middle;
            }

            #your-course {
                background-color: #d6af63;
                pointer-events: none;
                color: black;
            }

            .ptImg {
                width: 2vw;
                margin-right: 0.3vw;
            }
            body {
            display: flex;
            flex-direction: column;
            min-height: 100vh; /* This ensures the body takes at least the height of the viewport */
            margin: 0;
            }

            main {
                flex: 1; /* This makes the main content area grow to fill the available space */
            }
            .pagination-container {
            margin-top: 50px;
            margin-right: auto; /* Set margin-right and margin-left to auto to center horizontally */
            margin-left: auto;
            text-align: center; /* Set text-align to center for the list items */
            }

            .pagination {
                display: inline-block; /* Change display to inline-block for centering */
                list-style: none;
                padding: 0;
            }

            .pagination li {
                display: inline; /* Set list items to display inline */
                margin-right: 15px;
            }

            .pagination a {
                text-decoration: none;
                padding: 5px 10px;
                border: 1px solid #ddd;
                color: #333;
            }

            .pagination a:hover,
            .pagination .active a {
                background-color: #ddd;
            }

    </style>
    <link rel="stylesheet" href="../../styles/style.css">
</head>
<body>
<header>
        <span class="count-results">
                <?php if ($role == 'student') {
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
                    echo "<p class='button' id='point'><img src='../../images/nav_picture/point.png' class='ptImg'><span>$pointVal<span></p>";
                } ?>
        </span>
        <div class="header-content">
            <img src="../../images/nav_picture/my_learning.png" alt="Header Image" class="header-image">
            <h1>My Learning</h1>
        </div>
        <form method="get" action="">
        <div class="filter-container">
        <div class="filter-column">
        <select name="category" onchange="this.form.submit()">
                <option value="">Categories</option>
                <option value="maths">Math</option>
                <option value="AddMath">Additional Mathematics</option>
                <!-- Add more categories as needed -->
            </select>
        </div>
        <div class="filter-column">
            <select name="progress" onchange="this.form.submit()">
                <option value="">Progress</option>
                <option value="All">All</option>
                <option value="In Progress">In Progress</option>
                <option value="Completed">Completed</option>
            </select>
        </div> 
        <input type="text" class="nav_search" id="search-inp" placeholder="Search My Course">
        <button class="nav_button" onclick="location.href = '<?php echo $base; ?>public/search_result.php'">
        </div>
        </form>
        </div>
        </div>
    </header>
    

    <section>
    <div id="game-field">
    <div class="game-cell">
        <?php $result->data_seek(0);
            while ($row = $result->fetch_assoc()) {
                $edu_name = "";
                $courseThumb = $row['courseThumb'] == null ? "<img src='../images/nav_picture/course.png' alt='Course Thumbnail' class='course-thumb'>" : "<img src='data:image/png;base64," . $row['courseThumb'] . "' alt='Course Thumbnail' class='course-thumb'>";
                $courseName = $row['courseName'];
                $courseEdu = $row['eduID'];
                $courseJob = $row['jobTitle'];
                $courseRating = $row['rating'] == null ? 0 : $row['rating'];
                $courseEnrolled = $row['enrolled'];

                $ch = curl_init("$base_url/user-detail?user_id=$courseEdu");
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
                        <a class="course-title" onclick="location.href='../../public/course.php?courseID=<?php echo $row['courseID']; ?>'"><?php echo $courseName; ?></a>
                        <p class="detail">
                            <?php echo $eduName; ?>/<?php echo $courseJob; ?>
                            <?php if ($role == 'educator') {
                                $ticket = $_SESSION['ticket'];
                                $ch = curl_init("$base_url/check-ticket?ticket=$ticket");
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                                $response = json_decode(curl_exec($ch), true);
                                $user_id = $response['data']['user_id'];
                                if ($user_id == $courseEdu) {
                                    echo "<span class='button' id='your-course'>Your course</span>";
                                }
                            }
                            ?>
                            <?php
                                // Assuming $row['courseID'] and $userID are defined before this code block

                                $progress_sql = "SELECT 
                                    (SELECT COUNT(moduleID) FROM module WHERE courseID = ?) as module_total,
                                    (SELECT COUNT(module_enrolment.moduleID) FROM module_enrolment 
                                    LEFT JOIN module ON module.moduleID = module_enrolment.moduleID 
                                    WHERE module_enrolment.userID = ? AND module.courseID = ?) as enrolled_count";

                                $stmt = $conn->prepare($progress_sql);

                                // Check if the preparation of the statement is successful
                                if ($stmt) {
                                    // Bind parameters and execute the statement
                                    $stmt->bind_param("iii", $row['courseID'], $userID, $row['courseID']);
                                    $stmt->execute();

                                    // Bind results
                                    $stmt->bind_result($total, $current);
                                    
                                    // Fetch the values
                                    $stmt->fetch();
                                    
                                    // Close the statement
                                    $stmt->close();
                                    
                                    // Calculate progress
                                    if ($total > 0) {
                                        $progress = $current / $total * 100;
                                    } else {
                                        $progress = 0;
                                    }

                                    // Now you can use $progress as needed
                                } else {
                                    // Handle the case where statement preparation fails
                                    echo "Error preparing statement.";
                                }
                            ?>
                            <div class="progress-bar-container">
                            <div class="progress-bar" style=<?php echo $progress; ?>%;></div>
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
    <p> </p>
    </section>
    <div class="pagination-container">
    <div class="pagination">
    <?php
        // Previous button
        if ($currentPage > 1) {
            echo '<a href="' . $base_url . 'page=' . ($currentPage - 1) . $queryString . '">Previous</a>';
        }

        // Page numbers
        for ($i = 1; $i <= $totalPages; $i++) {
            echo '<a href="' . $base_url . 'page=' . $i . $queryString . '">' . $i . '</a>';
        }

        // Next button
        if ($currentPage < $totalPages) {
            echo '<a href="' . $base_url . 'page=' . ($currentPage + 1) . $queryString . '">Next</a>';
        }
        ?>
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

    <footer><?php include '../../includes/footer.php';?></footer>

</body>
</html>