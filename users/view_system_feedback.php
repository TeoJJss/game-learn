<?php
require '../modules/config.php';
$role = check_ticket();
if (!$role) {
    header("Location: ../index.php");
    exit();
}
include '../includes/header.php';

// Check if sfID is set in the URL
if (isset($_GET['sfID'])) {
    $sfID = $_GET['sfID'];

    $sql = "SELECT system_feedback.sfID, system_feedback.sfContent, system_feedback.sfMedia, system_feedback.`timestamp`, reply.`replyContent`, reply.`replyMedia`
            FROM system_feedback LEFT JOIN reply ON system_feedback.sfID=reply.sfID 
            WHERE system_feedback.sfID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $sfID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $feedbackDate = date('Y-m-d', strtotime($row['timestamp']));
        $feedbackTime = date('H:i:s', strtotime($row['timestamp']));
        $sfContent = $row['sfContent'];
        $sfMedia = $row['sfMedia'];

?>
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>View system Feedback</title>
            <link rel="stylesheet" href="../styles/style.css">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
            <style>
                @import url('https://fonts.googleapis.com/css2?family=Roboto&display=swap');
                @import url('https://fonts.googleapis.com/css2?family=Pacifico&display=swap');

                .page {
                    width: 80%;
                    margin: auto;
                }

                .page-title {
                    font-size: 24px;
                    font-weight: bold;
                    margin-bottom: 20px;

                }

                .feedback {
                    background-color: #f9f9f9;
                    padding: 20px;
                    border-radius: 20px;
                    text-align: center;
                    border-left: 4px solid #333;
                    box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1);
                }


                .feedback p {
                    color: #666;
                    line-height: 1.6em;
                    margin-top: 50px;
                    margin-bottom: 20px;
                    padding-left: 80px;
                    font-size: 24px;
                    font-family: 'Poppins', sans-serif;

                }

                .feedback::after {
                    content: "";
                    display: table;
                    clear: both;
                }


                .return-button {
                    float: right;
                    padding: 10px 20px;
                    color: #fff;
                    background-color: #007BFF;
                    border: none;
                    border-radius: 5px;
                    text-decoration: none;
                    transition: background-color 0.3s ease;
                }

                .return-button:hover {
                    background-color: #0056b3;
                }

                .question.image {
                    width: 400px;
                    height: 300px;
                    margin-left: 70px;
                }

                .fbReply {
                    background-color: rgba(128, 128, 128, 0.5);
                    color: darkblue;
                    width: 100%;
                    font-size: 1.4vw;
                    border-radius: 10px;
                    margin-top: 20px;
                    margin-bottom: 50px;
                    border-left: 4px solid #333;
                    box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1);
                }

                .fbReply b {
                    color: #fff;
                    font-size: 1.2em;
                    margin-left: 40px;
                }

                .fbReply .replyContent {
                    display: block;
                    margin-top: 10px;
                    margin-left: 40px;
                    font-weight: bold;
                }

                .fbReply img.replyMedia {
                    max-width: 30%;
                    margin-top: 10px;
                    border-radius: 5px;
                    margin-bottom: 20px;
                    margin-left: 20px;
                }
            </style>

        </head>

        <body>
            <div class="page">
                <div class="page-title">
                    <img src="<?php echo $base; ?>images/sys_feedback.png" alt="system Feedback Icon" class="system_feedback image">
                    System Feedback
                </div>

                <div class="page-content">
                    <div class="feedback">
                        <?php
                        if ($sfMedia) {
                            echo '<img src="data:image/jpeg;base64,' . $sfMedia . '" alt="Feedback Media" class="question image">';
                        }
                        ?>
                        <p><?php echo $sfContent; ?></p>

                        <!-- Return button -->
                        <a href="<?php if ($role == 'admin') {
                                        echo './admin/';
                                    } ?>system_feedback.php" class="return-button"><i class="fa fa-arrow-left"></i> Return</a>
                    </div>
                    <?php if ($row['replyContent']) { ?>
                        <div class="fbReply">
                            <span><b>Admin's Reply:</b></span><br>
                            <span class="replyContent"><?php echo $row['replyContent']; ?></span><br>
                            <?php if ($row['replyMedia']) { ?>
                                <img src='data:image/png;base64,<?php echo $row['replyMedia'] ?>' class='replyMedia'>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
            </div>


        </body>
        <?php
        include '../includes/footer.php';
        ?>

        </html>
<?php
    } else {
        echo "Feedback not found.";
    }
} else {
    echo "Invalid request. Missing sfID.";
}
?>
