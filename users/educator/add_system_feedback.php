<?php
require '../../modules/config.php';
$role = check_ticket();
if ($role != 'educator') {
    header("Location: ../../index.php");
    exit();
}
include '../../includes/header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userQuestion = $_POST['userQuestion'];
    if (isset($_FILES['userFile']) && $_FILES['userFile']['size'] > 0) {
        $image = $_FILES['userFile']['tmp_name'];
        $img = base64_encode(file_get_contents($image));
    } else {
        echo "File upload is mandatory.";
        exit();
    }
    $stmt =  $conn->prepare("INSERT INTO reply (replyContent, replyMedia) VALUES (?, ?)");
    $stmt->bind_param("ss", $userQuestion, $img);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>add_feedback</title>
    <link rel="stylesheet" href="../../styles/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
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
        <div class="page-title">
            <img src="<?php echo $base; ?>images/sys_feedback.png" alt="system feedback Icon" class="system_feedback image">
            System Feedback
        </div>
        <br><br><br>
        <form action="" method="post" enctype="multipart/form-data">
            <div class="user-input">
                <textarea id="userQuestion" name="userQuestion" class="user-input-style" placeholder="Write a feedback"></textarea>
            </div>
            <div class="action-buttons">
                <input type="file" id="userFile" name="userFile" class="file-upload-button">
                <input type="submit" value="Submit" class="submit-button">
            </div>
        </form>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var shareBtns = document.querySelectorAll(".shareBtn");

            // Add a click event listener to each share button
            shareBtns.forEach(function(shareBtn) {
                shareBtn.addEventListener("click", function(event) {
                    // Prevent the default behavior of the anchor tag
                    event.preventDefault();

                    // Get the corresponding modal for the clicked share button
                    var modalId = shareBtn.getAttribute("data-modal");
                    var modal = document.getElementById(modalId);

                    // Display the modal
                    modal.style.display = "block";
                });
            });

            // Get all close buttons
            var closeBtns = document.querySelectorAll(".closeBtn");

            // Add a click event listener to each close button
            closeBtns.forEach(function(closeBtn) {
                closeBtn.addEventListener("click", function() {
                    // Get the corresponding modal for the clicked close button
                    var modalId = closeBtn.getAttribute("data-modal");
                    var modal = document.getElementById(modalId);

                    // Close the modal
                    modal.style.display = "none";
                });
            });

            // Get all modals
            var modals = document.querySelectorAll(".modal");

            // Close the modal when the user clicks anywhere outside of it
            window.onclick = function(event) {
                modals.forEach(function(modal) {
                    if (event.target == modal) {
                        modal.style.display = "none";
                    }
                });
            };
        });
    </script>



    <?php include '../../includes/footer.php'; ?>
</body>

</html>