<?php
require '../../modules/config.php';
$role = check_ticket();
if ($role != 'educator') {
    header("Location: ../../index.php");
    exit();
}
include '../../includes/header.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ticket = $_SESSION['ticket'];
    $ch = curl_init("$base_url/check-ticket?ticket=$ticket");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    $response = json_decode(curl_exec($ch), true);
    $userID = $response['data']['user_id'];

    // Code for course insertion
    $course_image = $_FILES['courseThumb']['tmp_name'];
    $course_img = base64_encode(file_get_contents($course_image));

    $stmt = $conn->prepare("INSERT INTO course (courseName, intro, `description`, userID, courseThumb, label, category) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssisss", $courseName, $intro, $description, $userID, $course_img, $label, $courseCategory);

    $courseName = $_POST["fullName"];
    $intro = $_POST["courseIntroduction"];
    $description = $_POST["courseSummary"];
    $label = $_POST["label"];
    $courseCategory = $_POST["courseCategory"];
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $courseID = $conn->insert_id;

        // Code for module insertion
        $moduleTitle = $_POST['moduleTitle'];
        $moduleDesc = $_POST['moduleDescription'];
        $moduleFilename = $_POST['filename'];
        $moduleFile = $_FILES['moduleFile']['tmp_name'];
        $file_extension = strtolower(pathinfo($_FILES['moduleFile']['name'], PATHINFO_EXTENSION));

        $sql = "INSERT INTO module (moduleTitle, moduleDesc, `filename`, courseID) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $moduleTitle, $moduleDesc, $moduleFilename, $courseID);
        $stmt->execute();

        if ($stmt->affected_rows === 0) {
            echo "<script>alert('Action failed! Something went wrong.')</script>";
            $stmt->close();
            $conn->close();
            exit();
        } else {
            $stmt->close();
            $moduleID = $conn->insert_id;

            // Save file
            $upload_directory = '../../tmp/' . $moduleID . '/';
            $init_filename = $moduleFilename;

            if (!file_exists($upload_directory)) {
                mkdir($upload_directory, 0777, true);
            }
            $destination = $upload_directory . $moduleFilename;

            $count = 1;
            while (file_exists($upload_directory . $moduleFilename . '.pdf') || file_exists($upload_directory . $moduleFilename . '.docx')) {
                $moduleFilename = $count . "_$moduleFilename";
                $destination = $upload_directory . $moduleFilename;
                $count++;
            }

            if (!move_uploaded_file($moduleFile, $destination . ".$file_extension")) {
                trigger_error("Failed to upload file.");
                $conn->close();
                exit(); // Add exit to prevent further execution
            }
        }

        // Code for question insertion
        $questText = $_POST['questText'];
        $awardPt = $_POST['awardPt'];

        // Handle file upload
        $img = null;
        $image = $_FILES['questImg']['tmp_name'];
        if (isset($_FILES['questImg']) && file_exists($image)) {
            $img = base64_encode(file_get_contents($image));
        }

        $sql = "INSERT INTO question (courseID, questText, awardPt, questImg) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isis", $courseID, $questText, $awardPt, $img);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $questionID = $conn->insert_id;

            $options = $_POST['optValue'];
            $correctOpt = $_POST['correctOpt'];

            for ($c = 1; $c <= 4; $c++) {
                $optionValue = $options[$c-1];
                $isAnswer = $c == $correctOpt ? 1 : 0;
                if (!$optionValue){
                    continue;
                }
                $sql = "INSERT INTO `option` (optValue, IsAnswer, questID) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sii", $optionValue, $isAnswer, $questionID);
                $stmt->execute();
                $stmt->close();
            }

            echo '<script>alert("A Course has been created!"); window.location.href = "course_management.php";</script>';
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Course</title>
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


        .nav-link {
            text-decoration: none;
        }

        .form-group {
            display: flex;
            align-items: flex-start;
            margin-bottom: 20px;
            max-width: 50vw;
        }

        .form-group label {
            width: 10vw;
            margin-right: 10px;
        }

        .form-group input,
        .form-group textarea {
            flex-grow: 1;
            min-height: 3vh;
        }

        .form-group textarea {
            height: 100px;
        }


        .button-group {
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .button-group input[type="submit"],
        .button-group input[type="reset"] {
            padding: 10px 20px;
            /* Adjust as needed */
            border: none;
            border-radius: 5px;
            /* Adjust as needed */
            font-size: 16px;
            /* Adjust as needed */
            cursor: pointer;
            transition: background-color 0.3s ease;
            /* Smooth transition */
        }

        .button-group input[type="submit"] {
            background-color: #4CAF50;
            /* Adjust as needed */
            color: white;
            /* Adjust as needed */
        }

        .button-group input[type="submit"]:hover {
            background-color: #45a049;
            /* Darker shade of green */
        }

        .button-group input[type="reset"] {
            background-color: #f44336;
            /* Adjust as needed */
            color: white;
            /* Adjust as needed */
        }

        .button-group input[type="reset"]:hover {
            background-color: #da190b;
            /* Darker shade of red */
        }

        .radio-group {
            display: flex;
            align-items: center;
        }

        .radio-group input[type="radio"] {
            margin-right: 1px;
            /* Adjust as needed */
        }

        .required-label {
            color: red;
            font-size: 1vw;
        }
    </style>
</head>

<body>
    <div class="page">
        <div class="page-title">
            <img src="<?php echo $base; ?>images/educator_pic/course.png" alt="Course Icon">
            Course Management
        </div>
        <div class="nav-container">
            <h5><a href="course.php" class="nav-link">Course Management</a>/<a href="create_new_course.php" class="nav-link">Create New Course</a></h5>
        </div>
        <div class="page-content">
            <form action="create_new_course.php" method="post" enctype="multipart/form-data">
                <!-- Course Details -->
                <div class="form-group">
                    <label for="fullName">Course Full Name:</label>
                    <input type="text" id="fullName" name="fullName" placeholder="DIfferentiation" required>
                </div>

                <div class="form-group">
                    <label for="courseIntroduction">Course Introduction:</label>
                    <textarea id="courseIntroduction" name="courseIntroduction" placeholder="Add your description" required></textarea>
                </div>

                <div class="form-group">
                    <label for="courseSummary">Course Summary:</label>
                    <textarea id="courseSummary" name="courseSummary" placeholder="Add your description" required></textarea>
                </div>

                <div class="form-group">
                    <label for="courseThumb">Upload Course Image (PNG, JPEG):</label>
                    <input type="file" id="courseThumb" name="courseThumb" accept=".png, .jpg, .jpeg" required>
                </div>

                <div class="form-group">
                    <label for="label">Label:</label>
                    <input type="text" id="label" name="label" placeholder="Math">
                </div>

                <div class="form-group">
                    <label for="courseCategory">Course Category:</label>
                    <input type="text" id="courseCategory" name="courseCategory" placeholder="Enter the course category">
                </div>


                <br><br>

                <!-- Module Details -->
                <h2>Create the first module</h2>
                <div class="form-group">
                    <label for="moduleTitle">Module Title:</label>
                    <input type="text" id="moduleTitle" name="moduleTitle" placeholder="Topic 1: Base 64" required>
                </div>

                <div class="form-group">
                    <label for="moduleDescription">Module Description:</label>
                    <textarea id="moduleDescription" name="moduleDescription" placeholder="Add your description" required></textarea>
                </div>

                <div class="form-group">
                    <label for="moduleFile">Module File (PDF , DOCX):</label>
                    <input type="file" id="moduleFile" name="moduleFile" accept=".pdf,.docx " required>
                </div>

                <div class="form-group">
                    <label for="moduleFilename">Displayed Filename</label><br>
                    <input type="text" name="filename" id="moduleFilename" placeholder="Enter the displayed filename" required>
                </div>

                <br><br>

                <!-- Question Details -->
                <h2>Create the first question</h2>
                <div class="form-group">
                    <label for="questText">Question:</label>
                    <textarea id="questText" name="questText" placeholder="Enter your question" maxlength="150" required></textarea>
                </div>

                <div class="form-group">
                    <label for="awardPt">Award Points:</label>
                    <input type="number" id="awardPt" name="awardPt" min="1" max="20" placeholder="Enter the award points" required>
                </div>

                <div class="form-group">
                    <label for="questImg">Upload Question Image (PNG, JPG, JPEG):</label>
                    <input type="file" id="questImg" name="questImg" accept=".png, .jpg, .jpeg" required>
                </div>
                <!-- Option Details -->
                <p class="required-label">You must create 2-4 options for a question, leave option 3 and 4 empty if not required</p>
                <?php for ($i = 1; $i <= 4; $i++) { ?>
                    <div class="form-group">
                        <label for="optValue">Option <?php echo $i; ?>:</label>
                        <input type="text" id="optValue" name="optValue[]" placeholder="Enter option value" maxlength="150" <?php if ($i < 3) {
                                                                                                                                echo 'required';
                                                                                                                            } ?>>
                    </div>
                <?php } ?>

                <div class="form-group">
                    <label for="IsAnswer">Correct Option:</label>
                    <input type="number" name="correctOpt" id="correctOpt" min="1" max="4" placeholder="Enter Correct Option Number" required>
                </div>

                <div class="button-group">
                    <input type="submit" value="Create Course">
                    <input type="reset" value="Reset">
                </div>

            </form>
        </div>
    </div>
</body>
<?php include '../../includes/footer.php'; ?>

</html>