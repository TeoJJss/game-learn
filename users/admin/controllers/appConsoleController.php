<?php
    include 'db.php';

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
