<?php
require '../../modules/config.php';
$role = check_ticket();
if ($role != 'educator') {
    header("Location: ../../index.php");
    exit();
}
include '../../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forum-comment</title>
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
            background-color: white;
            /* Light grey background for contrast */
            border-left: 4px solid #333;
            /* Adds a solid line to the left for style */
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1);
            /* Adds a subtle shadow for depth */
        }

        .user-info {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            font-size: 20px;
        }

        .user-info img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .question-image {
            width: 50%;
            height: auto;
            max-height: 230px;
            /* Adjust this value to change the maximum height of the image */
        }

        .post-content {
            margin-top: 10px;
            margin-bottom: 40px;
        }

        .actions {
            display: flex;
            justify-content: flex-start;
            /* Aligns the items to the start of the container */
            gap: 10px;
            /* Adjust this value to change the space between the items */
        }

        .actions a {
            color: black;
            text-decoration: none;
            margin-right: 30px;
        }

        .actions i {
            margin-right: 5px;
        }

        .view-comment {
            position: absolute;
            /* Positions the link relative to the .post div */
            right: 10px;
            /* Aligns the link to the right of the .post div */
            bottom: 20px;
            /* Aligns the link to the bottom of the .post div */
        }

        /* The modal (background) */
        .modal {
            display: none;
            /* Hidden by default */
            position: fixed;
            /* Stay in place */
            z-index: 1;
            /* Sit on top */
            left: 0;
            top: 0;
            width: 100%;
            /* Full width */
            height: 100%;
            /* Full height */
            overflow: auto;
            /* Enable scroll if needed */
            background-color: rgb(0, 0, 0);
            /* Fallback color */
            background-color: rgba(0, 0, 0, 0.4);
            /* Black w/ opacity */
        }

        /* Modal content */
        .modal-content {
            background-color: #fefefe;
            margin: 18% auto;
            /* 15% from the top and centered */
            padding: 20px;
            border: 1px solid #888;
            width: 35%;
            height: 20%;
            /* Adjust this value */
        }

        .share-options {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 50px;
        }

        .actions .like-btn.liked {
            color: blue;
        }

        .closeBtn {
            font-size: 25px;
        }

        .closeBtn:hover {
            cursor: pointer;
            color: red;
        }

        #addCommentButton {
            background-color: #FFA500;
            /* Orange background */
            border: none;
            /* Remove border */
            color: white;
            /* White text */
            padding: 15px 32px;
            /* Some padding */
            text-align: center;
            /* Centered text */
            text-decoration: none;
            /* Remove underline */
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
            /* Pointer/hand icon */
            transition-duration: 0.4s;
            /* Transition effect */
            border-radius: 12px;
            /* Rounded corners */
        }

        #addCommentButton:hover {
            background-color: #FF4500;
            /* Darker orange */
        }

        .comments-section {
            margin-bottom: 60px;
        }

        .comment {
            display: flex;
            align-items: center;
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 1320px;
        }

        .user-picture {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .username {
            font-weight: bold;
        }

        .user-comment {
            margin-left: 40px;
        }

        .feedback-date {
            margin-left: 10px;
            font-size: 12px;
        }

        .feedback-time {
            margin-left: 10px;
            font-size: 12px;
        }


        .comment-content {
            display: flex;
            align-items: center;

        }

        .username {
            font-weight: bold;
            margin-right: 10px;
            /* Add some space between username and date */
        }
    </style>

</head>

<body>
    <div class="page">
        <div class="page-title">
            <img src="<?php echo $base; ?>images/educator_pic/forum.png" alt="Forum Icon" class="forum-image">
            Forum
        </div>
        <div class="page-content">
            <!-- Post 1 -->
            <?php
            $sql = "SELECT postID, content, timestamp, userID FROM post ORDER BY timestamp DESC";
            $result = $conn->query($sql);

            // Check if there are posts to display
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $postID = $row['postID'];
                    $content = $row['content'];

                    // Parse timestamp using DateTime
                    $timestamp = new DateTime($row['timestamp']);

                    // Format the date and time separately
                    $feedbackDate = $timestamp->format('Y-m-d'); // Format: Year-Month-Day
                    $feedbackTime = $timestamp->format('H:i:s'); // Format: Hour:Minute:Second

                    $userID = $row['userID'];

                    // Additional code for fetching user information (e.g., name, image) based on $userID

                    // Display the post
                    echo '<div class="post">';
                    echo '<div class="user-info">';
                    // Additional code for displaying user information
                    echo '</div>';
                    echo '<div class="feedback-date">' . $feedbackDate . '</div>';
                    echo '<div class="feedback-time">' . $feedbackTime . '</div>';
                    echo '<img src="' . $base . 'images/educator_pic/differentiation.jpg" alt="Question Image" class="question-image">';
                    echo '<div class="post-content">';
                    echo $content;
                    echo '</div>';
                    echo '<div class="actions">';
                    echo '<a href="add_forum_comment.php"><i class="fas fa-comment"></i> Comment</a>';
                    echo '<div class="share">';
                    echo '<a href="#" class="shareBtn" data-modal="shareModal' . $postID . '"><i class="fas fa-share"></i> Share</a>';
                    echo '</div>';
                    // The Share modal (hidden by default)
                    echo '<div id="shareModal' . $postID . '" class="modal">';
                    echo '<div class="modal-content">';
                    echo '<span class="closeBtn" data-modal="shareModal' . $postID . '" style="font-weight: bold;">&times;</span>';
                    echo '<p>Share on:</p>';
                    echo '<div class="share-options">';
                    echo '<a href="https://www.whatsapp.com" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>';
                    echo '<a href="https://www.facebook.com" target="_blank"><i class="fab fa-facebook-f"></i> Facebook</a>';
                    echo '<a href="https://www.instagram.com" target="_blank"><i class="fab fa-instagram"></i> Instagram</a>';
                    echo '<a href="https://www.twitter.com" target="_blank"><i class="fab fa-twitter"></i> Twitter</a>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                    echo '<a href="view_forum_comment.php" class="view-comment">View Comment</a>';
                    echo '</div>';
                }
            } else {
                echo '<p>No posts found.</p>';
            }
            ?>
            <!-- Add more posts as needed -->
        </div>

        <div class="add-comment-section">
            <button id="addCommentButton" onclick="window.location.href='add_forum_comment.php'">
                <i class="fas fa-plus"></i> Add a comment
            </button>
            <!-- User comments -->
            <div class="comments-section">
                <?php
                // Assuming $postId is the post ID for which you want to fetch comments
                $postId = 1; // You need to replace it with the actual post ID

                // Fetch comments from the database
                $sqlComments = "SELECT commentID, userID, commentText, timestamp FROM comment WHERE postID = $postId ORDER BY timestamp DESC";
                $resultComments = $conn->query($sqlComments);

                // Check if there are comments to display
                if ($resultComments->num_rows > 0) {
                    while ($rowComment = $resultComments->fetch_assoc()) {
                        $commentId = $rowComment['commentID'];
                        $userId = $rowComment['userID'];
                        $contentComment = $rowComment['commentText'];

                        // Parse timestamp using DateTime
                        $timestampComment = new DateTime($rowComment['timestamp']);

                        // Format the date and time separately
                        $commentDate = $timestampComment->format('Y-m-d'); // Format: Year-Month-Day
                        $commentTime = $timestampComment->format('H:i:s'); // Format: Hour:Minute:Second

                        // Additional code for fetching user information based on $userId

                        // Display the comment
                        echo '<div class="comment">';
                        // Additional code for displaying user information
                        echo '<div class="comment-content">';
                        echo '<span class="username">' . $userId . '</span>';
                        echo '<div class="feedback-date">' . $commentDate . '</div>';
                        echo '<div class="feedback-time">' . $commentTime . '</div>';
                        echo '<p class="user-comment">' . $contentComment . '</p>';
                        echo '</div>';
                        echo '</div>';
                    }
                } else {
                    echo '<p>No comments found.</p>';
                }
                ?>
            </div>
        </div>
    </div>


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