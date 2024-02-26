<?php
require '../../modules/config.php';
$role = check_ticket();
if ($role != 'educator') {
    header("Location: ../../index.php");
    exit();
}
include '../../includes/header.php';

// Check if sfID is set in the URL
if (isset($_GET['sfID'])) {
    $sfID = $_GET['sfID'];

    // Fetch system feedback for the specific sfID
    $sql = "SELECT sfID, sfContent, sfMedia, timestamp FROM system_feedback WHERE sfID = $sfID";
    $result = $conn->query($sql);

    // Check if feedback exists
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $feedbackDate = date('Y-m-d', strtotime($row['timestamp'])); // Format: Year-Month-Day
        $feedbackTime = date('H:i:s', strtotime($row['timestamp'])); // Format: Hour:Minute:Second
        $sfContent = $row['sfContent'];
        $sfMedia = base64_encode($row['sfMedia']); // Assuming sfMedia is a blob

        // Rest of your HTML code
?>
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>View system Feedback</title>
            <link rel="stylesheet" href="../../styles/style.css">
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

                .page-content {
                    background-color: #f9f9f9;
                    padding: 20px;
                    border-radius: 20px;
                    text-align: center;
                }


                .page-content p {
                    color: #666;
                    line-height: 1.6em;
                    margin-top: 50px;
                    margin-bottom: 20px;
                    padding-left: 80px;
                    /* Added space to the left */
                    font-size: 24px;
                    font-family: 'Pacifico', cursive;

                }

                .page-content::after {
                    content: "";
                    display: table;
                    clear: both;
                }


                .return-button {
                    float: right;
                    /* Position the return button on the right */
                    padding: 10px 20px;
                    /* Add some padding */
                    color: #fff;
                    /* White text color */
                    background-color: #007BFF;
                    /* Blue background */
                    border: none;
                    /* Remove border */
                    border-radius: 5px;
                    /* Slightly rounded corners */
                    text-decoration: none;
                    /* Remove underline */
                    transition: background-color 0.3s ease;
                    /* Smooth transition */
                }

                .return-button:hover {
                    background-color: #0056b3;
                    /* Darken the background on hover */
                }

                .question.image {
                    width: 400px;
                    height: 300px;
                    margin-left: 70px;
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
                    <!-- Display feedback content and media -->

                    <?php
                    if ($sfMedia) {
                        echo '<img src="data:image/jpeg;base64,' . $sfMedia . '" alt="Feedback Media" class="question image">';
                    }
                    ?>
                    <p><?php echo $sfContent; ?></p>

                    <!-- Return button -->
                    <a href="system_feedback.php" class="return-button"><i class="fa fa-arrow-left"></i> Return</a>
                </div>
            </div>


        </body>
        <?php
        include '../../includes/footer.php';
        ?>

        </html>
<?php
    } else {
        // If sfID not found, you may want to handle this case
        echo "Feedback not found.";
    }
} else {
    // If sfID is not set in the URL, handle this case
    echo "Invalid request. Missing sfID.";
}
?>