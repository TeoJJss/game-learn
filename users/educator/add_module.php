<?php 
    require '../../modules/config.php';
    $role = check_ticket();
    if ($role != 'educator') {
        header("Location: ../../index.php");
        exit();
    }
    include '../../includes/header.php';

    if ($_SERVER['REQUEST_METHOD'] == 'POST'){
        $courseID = $_POST['courseID'];
        $moduleTitle = $_POST['title'];
        $moduleDesc = $_POST['desc'];
        $moduleFilename = $_POST['filename'];
        $moduleFile = $_FILES['moduleFile']['tmp_name'];
        $file_extension = strtolower(pathinfo($_FILES['moduleFile']['name'], PATHINFO_EXTENSION));

        // Save DB
        $sql = "INSERT INTO module (moduleTitle, moduleDesc, `filename`, courseID) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $moduleTitle, $moduleDesc, $moduleFilename, $courseID);
        $stmt->execute();

        if($stmt->affected_rows === 0){
            echo "<script>alert('Action failed! Something went wrong.')</script>";
            $stmt -> close();
        }
        $stmt -> close();
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

        if (!move_uploaded_file($moduleFile, $destination.".$file_extension")) {
            trigger_error("Failed to upload file.");
        }

        if ($init_filename != $moduleFilename){
            $sql = "UPDATE module SET `filename`=? WHERE moduleID=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $moduleFilename, $moduleID);
            $stmt->execute();

            if($stmt->affected_rows === 0){
                echo "<script>alert('Action failed! Something went wrong.')</script>";
                $stmt -> close();
            }
        }
        echo "<script>location.href='../course.php?courseID=$courseID'</script>";
    }

    $courseID = $_GET['courseID'];

    $ticket = $_SESSION['ticket'];
    $ch = curl_init("$base_url/check-ticket?ticket=$ticket");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    $response = json_decode(curl_exec($ch), true);

    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 202) {
        $userID = $response['data']['user_id'];
    } else {
        echo "<script>alert('Something went wrong!'); history.back(); </script>";
        exit();
    }

    $sql = "SELECT course.courseID, course.courseThumb, course.courseName, course.intro, course.description, course.label, course.category
                FROM course 
                WHERE course.courseID = ? AND course.userID=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $courseID, $userID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        echo "<script>alert('Course Not Found!'); history.back(); </script>";
        $stmt->close();
        exit();
    }
    $row = $result->fetch_assoc();
    $stmt->close();
    $courseName = $row['courseName'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add new module</title>
    <link rel="stylesheet" href="../../styles/style.css">
    <style>
        .course-name {
            color: #FF3300;
        }

        h2{
            font-weight: bold;
        }
        .page-content{
            margin-left: 25vw;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="page-title">
            <h1><img src="../../images/educator_pic/assignment.png" alt="Course">Add new module in <span class="course-name"><?php echo $courseName; ?></span></h1>
        </div>
        <div class="page-content">
            <form method="post" enctype="multipart/form-data">
                <h2>Enter details of new module</h2>
                <input type="text" name="courseID" value="<?php echo $courseID; ?>" hidden>
                <div class="inp-row">
                    <label for="moduleTitle">Module Title</label><br>
                    <input type="text" name="title" id="moduleTitle" placeholder="Enter the module title" minlength="1" maxlength="20" required autofocus autocomplete="off">
                </div>
                <div class="inp-row">
                    <label for="moduleDesc">Module Description</label><br>
                    <textarea name="desc" id="moduleDesc" placeholder="Enter the module description" cols="30" rows="3" minlength="1" required autocomplete="off"></textarea>
                </div>
                <div class="inp-row">
                    <label for="moduleFile">Module File</label><br>
                    <input type="file" name="moduleFile" id="moduleFile" accept=".pdf, .docx" required autocomplete="off">
                </div>
                <div class="inp-row">
                    <label for="moduleFilename">Displayed Filename</label><br>
                    <input type="text" name="filename" id="moduleFilename" placeholder="Enter the displayed filename" minlength="1" maxlength="20" required autocomplete="off">
                </div>
                <input type="submit" value="Save" class="button" style="margin-left: 5vw;">
                <input type="reset" class="button">
            </form>
        </div>
    </div><br>
</body>
<?php include '../../includes/footer.php';?>
</html>