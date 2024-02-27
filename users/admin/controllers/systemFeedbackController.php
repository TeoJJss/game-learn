<?php
require_once 'db.php'; 


function fetchAllFeedbacks() {
    global $conn; 


    $sql = "SELECT sfID, sfContent, sfMedia, timestamp, userID FROM system_feedback";

    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $allFeedbacks = array(); 

        while($row = $result->fetch_assoc()) {
            $sfID = $row['sfID'];
            $sfContent = $row['sfContent'];
            $sfMedia = $row['sfMedia'];
            $timestamp = $row['timestamp'];
            $userID = $row['userID'];

            $allFeedbacks[$sfID] = array(
                'sfID' => $sfID,
                'sfContent' => $sfContent,
                'sfMedia' => $sfMedia,
                'timestamp' => $timestamp,
                'userID' => $userID
            );
        }

        return $allFeedbacks;
    } else {
        return array(); 
    }
}

function provideFeedbackReply($sfID, $replyContent, $replyMedia) {
    global $conn;

    // Check if replyContent is empty
    if (empty($replyContent)) {
        $response["message"] = "Reply content cannot be empty.";
        $response["success"] = false;
        return json_encode($response);
    }

    // Check if a reply already exists for the given sfID
    $sql_check = "SELECT COUNT(*) AS num_replies FROM reply WHERE sfID = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("i", $sfID);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $row_check = $result_check->fetch_assoc();
    $num_replies = $row_check['num_replies'];

    $response = array();

    if ($num_replies > 0) {
        $sql_update = "UPDATE reply SET replyContent = ?, replyMedia = ? WHERE sfID = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("ssi", $replyContent, $replyMedia, $sfID);
        $stmt_update->execute();

        $response["message"] = "Reply updated successfully.";
        $response["success"] = true;
    } else {
        $sql_insert = "INSERT INTO reply (sfID, replyContent, replyMedia) VALUES (?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("iss", $sfID, $replyContent, $replyMedia);
        $stmt_insert->execute();

        $response["message"] = "New reply inserted successfully.";
        $response["success"] = true;
    }

    // If there was an error with the SQL statement, set success to false
    if ($conn->error) {
        $response["message"] = $conn->error;
        $response["success"] = false;
    }

    return json_encode($response);
}


function checkReplyStatus($sfID) {
    global $conn;

    // Prepare SQL statement to check if the sfID exists in the reply table
    $sql = "SELECT COUNT(*) AS reply_count FROM reply WHERE sfID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $sfID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $replyCount = $row['reply_count'];
        return ($replyCount > 0); // Return true if reply exists, false otherwise
    } else {
        return false; // Return false if no rows found (sfID not found)
    }
}

function fetchFeedbackWithProfile($sfID) {
    global $conn;

    // Prepare SQL statement to fetch feedback and user profile based on sfID
    $sql = "SELECT sf.sfID, sf.sfContent, sf.sfMedia, sf.timestamp, sf.userID, pr.profilePic, pr.userID AS profileUserID
            FROM system_feedback sf
            INNER JOIN profile pr ON sf.userID = pr.userID
            WHERE sf.sfID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $sfID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        $feedbackWithProfile = array(
            'sfID' => $row['sfID'],
            'sfContent' => $row['sfContent'],
            'sfMedia' => $row['sfMedia'],
            'timestamp' => $row['timestamp'],
            'userID' => $row['userID'],
            'profilePic' => $row['profilePic'],
            'profileUserID' => $row['profileUserID']
        );

        return $feedbackWithProfile;
    } else {
        return null; 
    }
}