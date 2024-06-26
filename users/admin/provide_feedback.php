<?php
require '../../modules/config.php';
$role = check_ticket();
if ($role != 'admin') {
    header("Location: ../index.php");
    exit();
}
include '../../includes/header.php';

if (isset($_GET['sfID'])) {
    $sfID = $_GET['sfID'];
} else {
    echo "No sfID parameter found in the URL.";
}

function provideFeedbackReply($sfID, $replyContent, $replyMedia)
{
    global $conn;

    // Check if replyContent is empty
    if (empty($replyContent)) {
        return array(
            "message" => "Reply content cannot be empty.",
            "success" => false
        );
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

    return $response;
}

function getFeedbackData($sfID)
{
    global $conn;

    // Prepare and execute a SQL statement to fetch data from the database
    $sql = "SELECT sfContent, sfMedia, userID FROM system_feedback WHERE sfID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $sfID); // Assuming sfID is an integer, change "i" if it's a different data type
    $stmt->execute();

    // Bind result variables
    $stmt->bind_result($sfContent, $sfMedia, $userID);

    // Fetch the data
    $stmt->fetch();

    // Close statement
    $stmt->close();

    // Return the fetched data as an associative array
    return array(
        'sfContent' => $sfContent,
        'sfMedia' => $sfMedia,
        'userID' => $userID
    );
}

function getProfilePicture($userID)
{
    global $conn;
    // Prepare and execute a SQL statement to fetch data from the database
    $sql = "SELECT profilePic FROM profile WHERE userID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userID); // Assuming userID is an integer, change "i" if it's a different data type
    $stmt->execute();

    // Bind result variables
    $stmt->bind_result($profilePic);

    // Fetch the data
    $stmt->fetch();

    // Close statement
    $stmt->close();

    // Return the profile picture URL
    return $profilePic;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve input field values
    $userFB = $_POST['userFB'];
    $img = null;
    if (isset($_FILES['userFile']) && $_FILES['userFile']['size'] > 0) {
        $image = $_FILES['userFile']['tmp_name'];
        $img = base64_encode(file_get_contents($image));
    }

    // Check if sfID is set
    if (isset($_POST['sfID'])) {
        $sfID = $_POST['sfID'];

        // Check if userFB is empty
        if (empty($userFB)) {
            echo '<script>alert("Reply content cannot be empty.");</script>';
        } else {
            $replyResult = provideFeedbackReply($sfID, $userFB, $img);

            // Check the result of provideFeedbackReply
            if ($replyResult['success']) {
                echo '<script>alert("System feedback is submitted!");</script>';
            } else {
                echo '<script>alert("An error occurred while submitting the system feedback: ' . $replyResult['message'] . '");</script>';
            }
        }
    } else {
        echo '<script>alert("No sfID parameter found in the form submission.");</script>';
    }
    echo '<script>location.href="../admin/system_feedback.php"</script>';
    exit();
}

$feedbackDetails = getFeedbackData($sfID);
$userID = $feedbackDetails['userID'];
$userPic = getProfilePicture($userID);

$defaultProfilePic = '../../images/admin_pic/user.png';

if (!empty($userPic)) {
    $profilePicSrc = 'data:image/png;image/jpg;base64,' . $userPic;
} else {
    $profilePicSrc = $defaultProfilePic;
}


$ch = curl_init("$base_url/user-detail?user_id=$userID");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
$username = json_decode(curl_exec($ch), true)['msg'];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Respond to Feedback</title>
    <link rel="stylesheet" href="../../styles/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .category {
            font-size: 2rem;
            padding-left: 1rem;
            padding-top: 2rem;
            width: 100%;
            display: flex;
            align-items: center;
        }

        .category img {
            margin-right: 10px;
            height: 5rem;
        }

        .page {
            width: 80%;
            margin: auto;
        }

        .page-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .page-content {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .post {
            width: 100%;
            border: 1px solid #ccc;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
            margin-bottom: 40px;
            position: relative;
        }

        .user-info {
            display: flex;
            align-items: center;
            margin-top: 60px;
            margin-bottom: 20px;
        }

        .user-info img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 10px;
        }



        .user-input-style {
            width: 100%;
            height: 200px;
            /* Adjust as needed */
            padding: 10px;
            margin-top: 10px;
        }

        .action-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            margin-bottom: 60px;
        }

        .file-upload-button {
            width: 45%;
            /* Adjust as needed */
        }

        .submit-button {
            width: 10%;
            /* Adjust as needed */
            background-color: #FFA500;
            /* Orange background */
            border: none;
            /* Remove border */
            color: white;
            /* White text */
            padding: 15px 32px;
            /* Some padding */
            text-decoration: none;
            /* Remove underline */
            font-size: 16px;
            cursor: pointer;
            /* Pointer/hand icon */
            transition-duration: 0.4s;
            /* Transition effect */
            border-radius: 12px;
            /* Rounded corners */
        }

        .submit-button:hover {
            background-color: #FF4500;
            /* Darker orange */
        }
    </style>

</head>

<body>
    <div class="page">
        <div class="category">
            <img src="../../images/admin_pic/feedback.png" alt="Educators Applications">
            <h1>Feedback to User</h1>
        </div>
        <br><br>

        <div class="custoomerDetails" style="display: flex; flex-direction: row;  gap: 1rem;">
            <img src="<?php echo $profilePicSrc; ?>" alt="User Profile Picture" style="width: 3rem; height: 3rem;">

            <div style="margin-bottom: 0; margin-right: 0.5rem;">
                <div style="margin-bottom: 0; font-weight:bold;"><?php echo $username ?></div>
                <h2 style="margin-top: 0;"><strong>Says:</strong>
                    <span style="overflow: auto; max-height: 3rem;">
                        <?php echo $feedbackDetails['sfContent']; ?>
                    </span>
                </h2>
            </div>
        </div>


        <form action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="sfID" value="<?php echo htmlspecialchars($sfID); ?>">
            <div class="user-input">
                <textarea id="userQuestion" name="userFB" class="user-input-style" placeholder="Write a feedback" required></textarea>
            </div>
            <div class="action-buttons">
                <input type="file" id="userFile" name="userFile" class="file-upload-button">
                <input type="submit" value="Submit" class="button">
            </div>
        </form>
    </div>
    <?php include '../../includes/footer.php'; ?>
</body>

</html>
