<?php 
    require '../../modules/config.php';
    $role = check_ticket();
    if ($role != 'student') {
        header("Location: ../../index.php");
        exit();
    }
    include '../../includes/header.php';

    $ticket = $_SESSION['ticket'];
    $ch = curl_init("$base_url/check-ticket?ticket=$ticket");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));


    // Execute the cURL request
    $response = curl_exec($ch);

    // Check if the request was successful (HTTP code 202)
    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 202) {
        // Parse the JSON response
        $responseData = json_decode($response, true);

        // Check if 'user_id' exists in the response
        if (isset($responseData['data']['user_id']) || isset($responseData['data']['name'])) {
            $userID = $responseData['data']['user_id'];
            $username = $responseData['data']['name'];
        } else {
            // Handle the case when 'user_id' is not present in the response
            echo "User ID not found in the response.";
        }

       

    } else {
        // Handle the case when the request was not successful
        echo " ";
    }

    function getCourseIDByUserID($userID) {
        global $conn;
    
        // SQL query to get courseID based on userID
        $sql = "SELECT courseID FROM course_enrolment WHERE userID = ?";
    
        try {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $userID);
            $stmt->execute();
            $stmt->bind_result($courseID);
    
            if ($stmt->fetch()) {
                // Return the courseID if a match is found
                return $courseID;
            } else {
                // Return null or any default value if no match is found
                return null;
            }
    
            $stmt->close();  // Close the prepared statement
        } catch (Exception $e) {
            // Handle exceptions (e.g., database error)
            echo "Error: " . $e->getMessage();
            return null;
        }
    }

    function getUserIDByCourseID($courseID) {
        global $conn;
        // Prepare and execute the SQL query to get userID from the course table
        $sql = "SELECT userID FROM course WHERE courseID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $courseID); // Assuming courseID is an integer, adjust the type if needed
        $stmt->execute();
        $stmt->bind_result($userID);
    
        // Check if a match is found
        if ($stmt->fetch()) {
            // Match found, return the userID
            $stmt->close();
            return $userID;
        } else {
            // No match found, return false
            $stmt->close();
            return false;
        }
    }
    
    $courseID = getCourseIDByUserID($userID);
    $educatorID = getUserIDByCourseID($courseID);

    $ch = curl_init("$base_url/user-detail?user_id=$educatorID");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    $response = curl_exec($ch);
    $responseData = json_decode($response, true);
    $educatorName = $responseData['msg'];

    function getAllCourseDetails($courseIDs) {
        global $conn; // Assuming $conn is a valid database connection
        global $base_url;
    
        // Initialize the result array
        $allCourseDetails = array();
        
        foreach ($courseIDs as $courseID) {
            // Prepare and execute the SQL query to get all course details
            $courseQuery = "SELECT courseID, courseName, userID FROM course WHERE courseID = ?";
            $stmt = $conn->prepare($courseQuery);
        
            if (!$stmt) {
                // Handle the case where the SQL query preparation fails
                return false;
            }

            $stmt->bind_param("i", $courseID);
            $stmt->execute();
            $stmt->bind_result($courseID, $courseName, $userID);
        
            // Loop through all courses
            while ($stmt->fetch()) {
                $courseDetails = array();
        
                // Use fetch_assoc() to get an associative array
                $courseDetails['courseID'] = $courseID;
                $courseDetails['courseName'] = $courseName;
                $courseDetails['courseName'] = $courseName;
        
                // Use cURL to get educator details
                $ch = curl_init("$base_url/user-detail?user_id=$userID");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                $response = curl_exec($ch);
                curl_close($ch);
        
                $responseData = json_decode($response, true);
        
                // Check if the cURL request was successful
                if ($responseData && isset($responseData['msg'])) {
                    $courseDetails['educatorName'] = $responseData['msg'];
        
                    // Add course details to the result array
                    $allCourseDetails[] = $courseDetails;
                } else {
                    // Handle the case where cURL request fails or response is invalid
                    return false;
                }
            }
        }
    
    
        // Return the array of all course details
        return $allCourseDetails;
    }

    function getUserEnrolledCourses($userID) {
        global $conn; // Assuming $conn is a valid database connection
    
        // Initialize the result array
        $enrolledCourses = array();
    
        // Prepare and execute the SQL query to get distinct courseIDs for the specified user
        $query = "SELECT DISTINCT module.courseID 
                  FROM module_enrolment
                  INNER JOIN module ON module_enrolment.moduleID = module.moduleID
                  WHERE module_enrolment.userID = ?";
    
        $stmt = $conn->prepare($query);
    
        if (!$stmt) {
            // Handle the case where the SQL query preparation fails
            return false;
        }
    
        $stmt->bind_param("i", $userID);
        $stmt->execute();
        $stmt->bind_result($courseID);
    
        // Loop through the result set
        while ($stmt->fetch()) {
            // Add courseID to the result array without repetition
            if (!in_array($courseID, $enrolledCourses)) {
                $enrolledCourses[] = $courseID;
            }
        }
    
        // Close the statement
        $stmt->close();
    
        // Return the array of distinct courseIDs
        return $enrolledCourses;
    }
    
    $enrolledCourseID = getUserEnrolledCourses($userID);
    $allCourseDetails = getAllCourseDetails($enrolledCourseID);

//----------------------- Progress ----------------------------------------------------------------------------------------------------------

    function totalCurrentProgress() {
        global $conn; // Assuming $conn is a valid database connection
    
        // Initialize the result array
        $courseProgressCounts = array();
    
        // Prepare and execute the SQL query to count occurrences of each courseID
        $query = "SELECT courseID, COUNT(*) AS progressCount FROM module GROUP BY courseID";
        
        $stmt = $conn->prepare($query);
    
        if (!$stmt) {
            // Handle the case where the SQL query preparation fails
            return false;
        }
    
        $stmt->execute();
        $stmt->bind_result($courseID, $progressCount);
    
        // Loop through the result set
        while ($stmt->fetch()) {
            // Store the progress count in the result array
            $courseProgressCounts[$courseID] = $progressCount;
        }
    
        // Close the statement
        $stmt->close();
    
        // Return the associative array of courseID counts
        return $courseProgressCounts;
    }
    
    // Example usage:
    $total = totalCurrentProgress();
    

    function countEnrolledModules($enrollCourseIDs, $userID) {
        global $conn; // Assuming $conn is a valid database connection
    
        // Initialize the result array
        $enrolledModuleCounts = array();
    
        // Prepare and execute the SQL query to count enrolled modules for each course
        $query = "SELECT module.courseID, COUNT(module_enrolment.moduleID) AS enrolledModuleCount
                  FROM module
                  LEFT JOIN module_enrolment ON module.moduleID = module_enrolment.moduleID
                  WHERE module_enrolment.userID = ? AND module.courseID = ?";
    
        $stmt = $conn->prepare($query);
    
        if (!$stmt) {
            // Handle the case where the SQL query preparation fails
            return false;
        }
    
        // Bind parameters
        $stmt->bind_param("ii", $userID, $courseID);
    
        // Loop through each enrollCourseID
        foreach ($enrollCourseIDs as $courseID) {
            // Set the courseID parameter
            $courseID = (int)$courseID;
    
            // Execute the query for the current courseID
            $stmt->execute();
    
            // Bind the result
            $stmt->bind_result($courseID, $enrolledModuleCount);
    
            // Fetch the result
            $stmt->fetch();
    
            // Store the enrolled module count in the result array
            $enrolledModuleCounts[$courseID] = $enrolledModuleCount;
        }
    
        // Close the statement
        $stmt->close();
    
        // Return the associative array of enrolled module counts
        return $enrolledModuleCounts;
    }
    
    $current = countEnrolledModules($enrolledCourseID, $userID);
    


    function quizTotal() {
        global $conn; // Assuming $conn is a valid database connection
    
        // Initialize the result array
        $quizTotal = array();
    
        // Prepare and execute the SQL query to count occurrences of each courseID in the quiz table
        $query = "SELECT courseID, COUNT(*) AS quizCount FROM question GROUP BY courseID";
    
        $stmt = $conn->prepare($query);
    
        if (!$stmt) {
            // Handle the case where the SQL query preparation fails
            return false;
        }
    
        $stmt->execute();
        $stmt->bind_result($courseID, $quizCount);
    
        // Loop through the result set
        while ($stmt->fetch()) {
            // Store the quiz count in the result array
            $quizTotal[$courseID] = $quizCount;
        }
    
        // Close the statement
        $stmt->close();
    
        // Return the associative array of courseID counts for quizzes
        return $quizTotal;
    }
    
    // Example usage:
    $quizTotal = quizTotal();


    function countEnrolledQuiz($enrollCourseIDs, $userID) {
        global $conn; // Assuming $conn is a valid database connection
    
        // Initialize the result array
        $enrolledQuizCounts = array();
    
        // Prepare and execute the SQL query to count enrolled quizzes for each course
        $query = "SELECT question.courseID, COUNT(quiz_enrolment.questID) AS enrolledquizCount
                  FROM question
                  LEFT JOIN quiz_enrolment ON question.questID = quiz_enrolment.questID
                  WHERE quiz_enrolment.userID = ? AND question.courseID = ?";
    
        $stmt = $conn->prepare($query);
    
        if (!$stmt) {
            // Handle the case where the SQL query preparation fails
            return false;
        }
    
        // Bind parameters (corrected order)
        $stmt->bind_param("ii", $userID, $courseID);
    
        // Loop through each enrollCourseID
        foreach ($enrollCourseIDs as $courseID) {
            // Set the courseID parameter
            $courseID = (int)$courseID;
    
            // Execute the query for the current courseID
            $stmt->execute();
    
            // Bind the result
            $stmt->bind_result($courseID, $enrolledQuizCount);
    
            // Fetch the result
            $stmt->fetch();
    
            // Store the enrolled quiz count in the result array
            $enrolledQuizCounts[$courseID] = $enrolledQuizCount;
        }
    
        // Close the statement
        $stmt->close();
    
        // Return the associative array of enrolled quiz counts
        return $enrolledQuizCounts;
    }
    
    $quizCurrent = countEnrolledQuiz($enrolledCourseID, $userID);
    
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Template</title>
    <link rel="stylesheet" href="../../styles/style.css">
    <style>
        .category {
            font-size: 2rem; 
            padding-left: 5rem;
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
        
        .category h1 {
            padding-left: 1rem;
        }

        table {
        width: 50%;
        border-collapse: collapse;
        margin: 5rem;
        margin-left: 25rem;
        background-color: #f0f0f0; /* Set a background color for the table */
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Add a subtle box shadow */
        }

        th, td {
            border: 1px solid #ddd; /* Set border color */
            padding: 15px; /* Increase padding for better readability */
        }

        th {
            background-color: #333; /* Set background color for table header */
            color: white; /* Set text color for table header */
        }

        tr:nth-child(even) {
            background-color: #f9f9f9; /* Set background color for even rows */
        }

        /* Add some creative styling */
        h1 {
            text-align: center;
            color: #333;
        }

        /* Optional: Add a hover effect on table rows */
        tr:hover {
            background-color: #e0e0e0;
        }
    </style>
</head>
<body>
    <div class="category">
        <img src="../../images/nav_picture/report.png" alt="Educators Applications">
        <h1>Report</h1>
    </div>

    <div class="name">
        <h1>Name: <?php echo $username; ?></h1>
    </div>
    
    <table>
    <tr>
        <th>Course Name</th>
        <th>Educator</th>
        <th>Progress</th>
        <th>Quiz</th>   
        <!-- Add more table headers as needed -->
    </tr>
    <?php foreach ($allCourseDetails as $courseDetails): ?>
        <tr>
            <td><?php echo $courseDetails["courseName"]; ?></td>
            <td><?php echo $courseDetails["educatorName"]; ?></td>
            <td>
                <?php
                foreach ($total as $courseID => $progressCount) {
                    // Check if the corresponding current count exists for the current course ID
                    if (isset($current[$courseID])) {
                        $enrolledModuleCount = $current[$courseID];

                        if ($progressCount > 0) {
                            $progress = $enrolledModuleCount / $progressCount * 100;
                        } else {
                            $progress = 0;
                        }

                        // Output or use the calculated progress for the current course
                        if ($courseDetails['courseID'] == $courseID) {
                            echo number_format($progress, 2) . "%<br>";
                        }
                    }
                }
                ?>
            </td>
            <td>
            <?php
                foreach ($quizTotal as $courseID => $quizCount) {
                    // Check if the corresponding current count exists for the current course ID
                    if (isset($quizCurrent[$courseID])) {
                        $enrolledQuizCount = $quizCurrent[$courseID];

                        if ($quizCount > 0) {
                            $quizProgress = $enrolledQuizCount / $quizCount * 100;
                        } else {
                            $quizProgress = 0;
                        }

                        // Output or use the calculated progress for the current course
                        if ($courseDetails['courseID'] == $courseID) {
                            if ($quizProgress == 100) {
                                echo "Done";
                            } 
                            if ($quizProgress == 0){
                                echo "Undone";
                            }
                        }
                    }
                }
                ?>
            </td>
            <!-- Add more cells for additional details -->
        </tr>
    <?php endforeach; ?>
</table>

    <?php include '../../includes/footer.php'; ?>
</body>
</html>