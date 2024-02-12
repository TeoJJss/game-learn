<?php
    require '../../modules/config.php';
    $role = check_ticket();
    if ($role != 'educator') {
        header("Location: ../../index.php");
        exit();
    }
    include '../../includes/header.php';

    if ($_SERVER['REQUEST_METHOD']=='POST'){
        $courseID = $_POST['courseID'];
        $courseName = $_POST['courseName'];
        $courseIntro = $_POST['courseIntro'];
        $courseDesc = $_POST['courseDesc'];
        $courseLabel = $_POST['courseLabel'];
        $courseCat = $_POST['courseCat'];

        $image = $_FILES['courseThumb']['tmp_name'];
        $courseThumb = 'course.courseThumb';
        if (isset($_FILES['courseThumb']) && file_exists($image)){
            $courseThumb = "'".base64_encode(file_get_contents($image))."'";
        }

        $sql = "UPDATE course 
                SET course.courseThumb=$courseThumb, course.courseName=?, course.intro=?, course.description=?, course.label=?, course.category=?
                WHERE course.courseID=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $courseName, $courseIntro, $courseDesc, $courseLabel, $courseCat, $courseID);
        $stmt->execute();
        echo "<script>location.href='../../public/course.php?courseID=$courseID'</script>";
        exit();
    }

    if (!isset($_GET['courseID'])) {
        echo "<script>alert('Invalid Course ID!'); history.back(); </script>";
        exit();
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
    $courseThumb = $row['courseThumb'] == null ? "<img src='../images/nav_picture/course.png' alt='Course Thumbnail' class='course-thumb'>" : "<img src='data:image/png;base64," . $row['courseThumb'] . "' alt='Course Thumbnail' class='course-thumb'>";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Course</title>
    <link rel="stylesheet" href="../../styles/style.css">
    <style>
        .courseName {
            color: #FF3300;
        }

        .inp-row label{
            font-weight: bold;
        }

        .inp-row input, .inp-row textarea{
            width: 250vw;
            margin-top: 1vh;
            resize: none;
        }

        .inp-row textarea{
            min-height: 18vh;
            text-align: justify;
        }   

        .page-content{
            margin-left: 20vw;
        }

        .button-row{
            margin-left: 5vw;
            margin-top: 5vh;
        }

        .button-row input{
            margin-right: 5vw;
        }

        .course-thumb{
            width: 7vw;
        }
    </style>
</head>

<body>
    <div class="page">
        <div class="page-title">
            <h1><img src="../../images/educator_pic/course.png" alt="Course">Edit Course - <span class="courseName"><?php echo $courseName; ?></span></h1>
        </div>
        <div class="page-content">
            <form method="post" enctype="multipart/form-data">
                <input type="text" name="courseID" value="<?php echo $row['courseID']; ?>" hidden>
                <div class="inp-row">
                    <label for="courseName">Course Name</label><br>
                    <input type="text" name="courseName" id="courseName" value="<?php echo $courseName; ?>" minlength="1" maxlength="50" oninput="editable()">
                </div>
                <div class="inp-row">
                    <label for="courseIntro">Course Introduction</label><br>
                    <textarea name="courseIntro" id="courseIntro" minlength="1" maxlength="100" oninput="editable()"><?php echo $row['intro']; ?></textarea>
                </div>
                <div class="inp-row">
                    <label for="courseDesc">Course Description</label><br>
                    <textarea name="courseDesc" id="courseDesc" minlength="1" maxlength="100" oninput="editable()"><?php echo $row['description']; ?></textarea>
                </div>
                <div class="inp-row">
                    <label for="courseLabel">Course Label</label><br>
                    <input type="text" name="courseLabel" id="courseLabel" value="<?php echo $row['label']; ?>" minlength="1" maxlength="10" oninput="editable()">
                </div>
                <div class="inp-row">
                    <label for="courseCat">Course Category</label><br>
                    <input type="text" name="courseCat" id="courseCat" value="<?php echo $row['category']; ?>" minlength="1" maxlength="10" oninput="editable()">
                </div>
                <div class="inp-row">
                    <label for="courseThumb">Course Thumbnail</label><br>
                    <?php echo $courseThumb; ?><br>
                    <input type="file" name="courseThumb" id="courseThumb" accept=".jpeg, .jpg, .png" oninput="editable()">
                </div>
                <div class="button-row">
                    <input type="submit" class="button" id="update-btn" value="Update Course" disabled>
                    <input type="reset" name="button" class="button" onclick="noUpdate()">
                </div>
                
            </form>
        </div>
    </div>
    <script>
        function editable(){
            var submitBtn = document.getElementById('update-btn');
            document.getElementById('update-btn').disabled =false;
        }
        function noUpdate(){
            var submitBtn = document.getElementById('update-btn');
            document.getElementById('update-btn').disabled = true;
        }
    </script>
</body>
<?php include '../../includes/footer.php'; ?>
</html>