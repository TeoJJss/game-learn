<?php
require '../../modules/config.php';

if (check_ticket() != 'admin'){
    header("Location: ./index.php");
    exit();
}

$ticket = $_SESSION['ticket'];

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

$allFeedbacks = fetchAllFeedbacks();



// Pagination variables
$feedbacksPerPage = 5; // Number of feedbacks per page
$totalFeedbacks = count($allFeedbacks);
$totalPages = ceil($totalFeedbacks / $feedbacksPerPage);

// Get the current page number from the URL query string, default to 1
$currentPage = isset($_GET['page']) ? intval($_GET['page']) : 1;
$startIndex = ($currentPage - 1) * $feedbacksPerPage;
$endIndex = $startIndex + $feedbacksPerPage;

// Slice the array to get feedbacks for the current page
$feedbacksOnPage = array_slice($allFeedbacks, $startIndex, $feedbacksPerPage);

include '../../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../styles/style.css">
    <title>System Feedbacks</title>
    <style>
        .box-container {
            padding-left: 3rem;
            padding-right: 3rem;
            padding-bottom: 3rem;
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

        .feedback-container {
            width: 100%;
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
            background-color: #f2f2f2; /* Light grey background color */
            border: 1px solid #ccc; /* Grey border */
            padding: 10px; /* Padding for inner content */
            margin-bottom: 10px; /* Add margin bottom for spacing between containers */
        }

        .feedback-content {
            flex: 1; 
        }

        .date-container {
            display: flex;
            align-items: center; /* Align items vertically */
        }

        .date-container h2,
        .date-container h3 {
            margin: 0; 
            padding: 0;
        }

        .sFContent {
            max-width: 11rem; 
            max-height: 5rem; 
            min-width: 20rem; 
            overflow: auto; 
            margin-right: 11rem;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 20px; 
            padding-bottom: 25px;
        }

        .pagination .button {
            margin: 0 5px;
        }

        .pagination .button.active {
            background-color: #007bff;
            color: white;
        }

        .pagination .button.inactive {
            background-color: #f2f2f2;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="box-container">
        <div class="category">
            <img src="../../images/admin_pic/feedback.png" alt="Educators Applications">
            <h1>System Feedbacks</h1> 
        </div>

        <?php if (empty($feedbacksOnPage)): ?>
            <p>There are no new system feedbacks from the user.</p>
        <?php else: ?>
            <?php foreach ($feedbacksOnPage as $feedback): ?>
                <div class="feedback-container">
                    <?php 
                        // Convert timestamp to separate day, month (in English), year, and hour
                        $timestamp = strtotime($feedback['timestamp']);
                        $day = date('d', $timestamp);
                        $month = date('F', $timestamp); // Full month name
                        $year = date('Y', $timestamp);
                        $hour = date('h:i A', $timestamp); // Hour and minute in 12-hour format with AM/PM
                    ?>
                    <div class="date-container">
                        <h2><?php echo $day; ?></h2> <!-- Display day -->
                        <h3 style="padding-left: 0.3rem;"><?php echo $month . ' ' . $year; ?></h3> 
                    </div>

                    <p><?php echo $hour; ?></p> 

                    <div>
                        <?php if (checkReplyStatus($feedback["sfID"])) : ?>
                            <p>You replied</p>
                        <?php else: ?>
                            <p>Haven't replied</p>
                        <?php endif; ?>
                    </div>

                    <div style="display: flex; align-items: center;">
                       <div class="sFContent">
                            <p><?php echo $feedback["sfContent"] ?><p>
                        </div>

                        <a href="./provide_feedback.php?sfID=<?php echo $feedback['sfID']; ?>">
                            <img src="../../images/admin_pic/provide_feedback.png" alt="Provide feedback" style="width: 30px; height: 40px;" class="button">
                        </a> 
                    </div>
                </div>
                
            <?php endforeach; ?>

             <!-- Pagination -->
            <div class="pagination">
                <?php if ($totalPages > 1): ?>
                    <?php if ($currentPage > 1): ?>
                        <button class="button" onclick="window.location.href='?page=<?php echo max($currentPage - 1, 1); ?>'">Previous</button>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <button 
                            class="button <?php echo ($i === $currentPage) ? 'active' : 'inactive'; ?>" 
                            style="background-color: <?php echo ($i !== $currentPage) ? 'gray' : ''; ?>;" 
                            onclick="window.location.href='?page=<?php echo $i; ?>'"><?php echo $i; ?>
                        </button>
                    <?php endfor; ?>
                    <?php if ($currentPage < $totalPages): ?>
                        <button class="button" onclick="window.location.href='?page=<?php echo min($currentPage + 1, $totalPages); ?>'">Next</button>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

        <?php endif; ?>
    </div>
</body>
</html>

<?php include '../../includes/footer.php'; ?>