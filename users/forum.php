<?php
require '../modules/config.php';
$role = check_ticket();
if (!$role) {
    header("Location: ../index.php");
    exit();
}
include '../includes/header.php';

if (!isset($_GET['search'])) {
    $sql = "SELECT post.userID, `profile`.`profilePic`, post.postID, post.content, post.timestamp, post.postMedia
            FROM post 
            LEFT JOIN `profile` ON post.userID=`profile`.`userID`
            ORDER BY post.`timestamp` DESC";
    $stmt = $conn->prepare($sql);
} else {
    $key = $_GET['search'];
    $sql = "SELECT post.userID, `profile`.`profilePic`, post.postID, post.content, post.timestamp, post.postMedia
            FROM post 
            LEFT JOIN `profile` ON post.userID=`profile`.`userID`
            WHERE LOWER(post.content) LIKE LOWER(?)
            ORDER BY post.`timestamp` DESC";
    $stmt = $conn->prepare($sql);
    $keyword = "%$key%";
    $stmt->bind_param("s", $keyword);
}

$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ticket = $_SESSION['ticket'];
    $ch = curl_init("$base_url/check-ticket?ticket=$ticket");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    $response = json_decode(curl_exec($ch), true);
    $userID_comment = $response['data']['user_id'];

    $postID = $_POST['postID'];
    $commentText = $_POST['commentText'];
    $img = null;
    $image = $_FILES['commentMedia']['tmp_name'];
    if (isset($_FILES['commentMedia']) && file_exists($image)) {
        $img = base64_encode(file_get_contents($image));
    }

    $commentSql = "INSERT INTO comment (`commentText`, `commentMedia`, `postID`, `userID`) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($commentSql);
    $stmt->bind_param("ssii", $commentText, $img, $postID, $userID_comment);
    $stmt->execute();
    if ($stmt->affected_rows < 1) {
        trigger_error("Update Failed");
    } else {
        echo '<script>alert("Comment posted. "); location.href="./forum.php";</script>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forum</title>
    <link rel="stylesheet" href="../styles/style.css">
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
            border-left: 4px solid #333;
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1);
        }

        .user-info {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            font-size: 20px;
        }

        .user-info img,
        .profilePic {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .post-content {
            margin-top: 10px;
            margin-bottom: 40px;
        }

        .actions {
            display: flex;
            justify-content: flex-start;
            gap: 10px;
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
            right: 10px;
            bottom: 20px;
            text-decoration: none;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0, 0, 0);
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 18% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 35%;
            height: 20%;
        }

        .username,
        .timestamp {
            font-weight: bold;
        }

        .timestamp {
            color: #A0A0A0;
            margin-left: 2vw;
        }

        .postMedia {
            max-width: 10vw;
        }

        input.comment {
            width: 25vw;
            height: 5vh;
        }

        .comment-btn {
            margin-left: 3vw;
        }

        .comment-a,
        .ban-act {
            color: darkblue;
            cursor: pointer;
            text-decoration: underline;
        }

        .ban-act {
            color: red;
            margin-left: 5vw;
        }

        #search-forum {
            margin-left: 5vw;
            height: 5vh;
        }

        .create-post-btn {
            margin-top: 5vh;
        }

        .comment-wrapper {
            margin-left: 50px;
        }

        .comment-a {
            color: black;
            text-decoration: none;
            margin-right: 30px;
            display: inline-block;
            transition: color 0.3s ease-in-out, transform 0.3s ease-in-out;
        }

        .comment-a:hover {
            color: #1E90FF;
            transform: scale(1.2);
        }

        .time {
            margin-left: 13px;
        }
    </style>

</head>

<body>
    <div class="page">
        <div class="page-title">
            <h1><img src="<?php echo $base; ?>images/educator_pic/forum.png" alt="Forum Icon" class="forum-image">Forum</h1>
            <input type="text" class="nav_search" maxlength="50" id="search-forum" placeholder="Search For Forum Posts (Enter key to search)" onclick="addParam()" autocomplete="off">
            <button class="button create-post-btn" onclick="location.href='../users/create_forum.php'">Create Post</button>
        </div>
        <div class="page-content">
            <?php 
            if ($result->num_rows == 0){ // if no search result
                echo "<p>No matched forum post.</p>";
            }
            while ($row = $result->fetch_assoc()) { ?>
                <div class="post">
                    <div class="user-info">
                        <img src='data:image/png;base64,<?php echo $row['profilePic'] ?>' class='profilePic'>
                        <span class="username"><b>
                                <?php
                                $userID = $row['userID'];
                                $ch = curl_init("$base_url/user-detail?user_id=$userID");
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                                $response = json_decode(curl_exec($ch), true);

                                if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200) {
                                    $userName = $response['msg'];
                                } else {
                                    trigger_error("Unknown user!");
                                    exit();
                                }
                                echo $userName;
                                ?></b>
                        </span>
                        <span class="timestamp">
                            <span class="date">
                                <?php
                                $timestamp = new DateTime($row['timestamp']);
                                echo $timestamp->format('d-m-Y');
                                ?>
                            </span>
                            <span class="time">
                                <?php
                                echo $timestamp->format('H:i:s');
                                ?>
                            </span>
                        </span>
                    </div>
                    <p><?php echo $row['content'] ?></p>
                    <?php if ($row['postMedia']) { ?>
                        <img src='data:image/png;base64,<?php echo $row['postMedia']; ?>' class='postMedia'><br>
                    <?php } ?>
                    <a class="comment-a" onclick="showCommentInp(<?php echo $row['postID']; ?>)"><i class="fa fa-comment"></i> Comment</a>
                    <?php if ($role == 'admin') { ?>
                        <a class="ban-act" onclick="dltPost(<?php echo $row['postID']; ?>)">Delete Post (admin only)</a>
                    <?php } ?>
                    <form method="post" id="comment-form-<?php echo $row['postID']; ?>" enctype="multipart/form-data" hidden>
                        <input type="number" name="postID" value="<?php echo $row['postID']; ?>" hidden>
                        <input type="text" name="commentText" placeholder="Enter comment..." maxlength="150" autocomplete="off" class="comment" required>
                        <input type="file" accept=".jpg, .jpeg, .png" name="commentMedia">
                        <button type="submit" class="button comment-btn">Post Comment</button>
                    </form>
                    <?php
                    $getCommentSql = "SELECT comment.`commentText`, comment.`commentMedia`, comment.`timestamp`, comment.`userID`, `profile`.profilePic, comment.commentID 
                                            FROM `comment` JOIN `profile` ON comment.userID = `profile`.userID
                                            WHERE comment.postID=?";
                    $stmt = $conn->prepare($getCommentSql);
                    $stmt->bind_param("i", $row['postID']);
                    $stmt->execute();
                    $comments = $stmt->get_result();
                    $stmt->close();
                    if ($comments->num_rows > 0) {
                    ?>
                        <hr style="width:95%;text-align:left;margin-left:0"><br>
                        <?php while ($row_comment = $comments->fetch_assoc()) {
                            $comment_userID = $row_comment['userID'];
                            $ch = curl_init("$base_url/user-detail?user_id=$comment_userID");
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                            $response = json_decode(curl_exec($ch), true);

                            if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200) {
                                $comment_userName = $response['msg'];
                            } else {
                                trigger_error("Unknown user!");
                                exit();
                            }
                        ?>
                            <div class="comment-wrapper">
                                <div class="user-info">
                                    <img src='data:image/png;base64,<?php echo $row_comment['profilePic'] ?>' class='profilePic'>
                                    <span class="username"><?php echo $comment_userName; ?></span><span class='timestamp'><?php $row_comment['timestamp']; ?></span>
                                    <span class="timestamp">
                                        <?php
                                        $commentTimestamp = new DateTime($row_comment['timestamp']);
                                        ?>
                                        <span class="date">
                                            <?php echo $commentTimestamp->format('d-m-Y'); ?>
                                        </span>
                                        <span class="time">
                                            <?php echo $commentTimestamp->format('H:i:s'); ?>
                                        </span>
                                    </span>
                                </div>
                                <span><?php echo $row_comment['commentText'] ?></span>
                                <?php if ($row_comment['commentMedia'] != null) { ?>
                                    <br><img src='data:image/png;base64,<?php echo $row_comment['commentMedia']; ?>' class='postMedia'>
                                <?php } ?>
                                <?php if ($role == 'admin') { ?>
                                    <a class="ban-act" onclick="dltComment(<?php echo $row['postID']; ?>, <?php echo $row_comment['commentID']; ?>)">Delete Comment (admin only)</a>
                                <?php } ?>
                            </div><br>
                        <?php } ?>
                    <?php  } ?>
                </div>
            <?php } ?>
        </div>
    </div>

    <script>
        function showCommentInp(postID) {
            document.getElementById(`comment-form-${postID}`).hidden = false;
        }

        function dltPost(postID) {
            if (window.confirm("Are you sure to delete this post?") == true) {
                location.href = '../modules/dlt_post.php?postID=' + postID;
            }
        }

        function dltComment(postID, commentID) {
            if (window.confirm("Are you sure to delete this comment?") == true) {
                location.href = `../modules/dlt_comment.php?postID=${postID}&commentID=${commentID}`;
            }
        }

        var searchInput = document.getElementById("search-forum");

        searchInput.addEventListener("keypress", function(event) {
            if (event.key == "Enter") {
                searchInputVal = searchInput.value;
                location.href = './forum.php?search=' + searchInputVal;
                event.preventDefault();
            }
        });
    </script>
</body>
<?php include '../includes/footer.php'; ?>

</html>
