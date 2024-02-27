<?php
include 'db.php';

function getTop5Courses() {
    global $conn;

    $sql = "SELECT course.courseID, course.courseName, COUNT(course_enrolment.courseID) AS enrolment_count
            FROM course_enrolment
            INNER JOIN course ON course_enrolment.courseID = course.courseID
            GROUP BY course.courseID, course.courseName
            ORDER BY enrolment_count DESC
            LIMIT 5";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Error in preparing statement: " . $conn->error);
    }
    $stmt->execute();
    $stmt->bind_result($courseID, $courseName, $enrolmentCount);

    // Fetch the results and store them in an associative array
    $results = array();
    while ($stmt->fetch()) {
        $results[$courseID] = array(
            "courseName" => $courseName,
            "enrolmentCount" => $enrolmentCount
        );
    }

    $stmt->close();

    return $results;
}

function getTop5Modules() {
    global $conn;

    $sql = "SELECT module.moduleID, module.moduleTitle, COUNT(module_enrolment.moduleID) AS enrolment_count
            FROM module_enrolment
            INNER JOIN module ON module_enrolment.moduleID = module.moduleID
            GROUP BY module.moduleID, module.moduleTitle
            ORDER BY enrolment_count DESC
            LIMIT 5";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Error in preparing statement: " . $conn->error);
    }
    $stmt->execute();
    $stmt->bind_result($moduleID, $moduleName, $enrolmentCount);

    // Fetch the results and store them in an associative array
    $results = array();
    while ($stmt->fetch()) {
        $results[$moduleID] = array(
            "moduleName" => $moduleName,
            "enrolmentCount" => $enrolmentCount
        );
    }

    $stmt->close();

    return $results;
}

function getTotalPendingCourses() {
    global $conn;

    $sql = "SELECT courseID, courseName, lastUpdate FROM course WHERE status = 'pending' ORDER BY lastUpdate ASC";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Error in preparing statement: " . $conn->error);
    }
    $stmt->execute();
    $stmt->bind_result($courseID, $courseName, $lastUpdate);

    // Fetch the results and store them in an associative array
    $results = array();
    while ($stmt->fetch()) {
        $results[] = array(
            "courseID" => $courseID,
            "courseName" => $courseName,
            "lastUpdate" => $lastUpdate
        );
    }

    $stmt->close();

    return $results;
}
