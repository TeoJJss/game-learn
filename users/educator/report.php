<?php
require '../../modules/config.php';
$role = check_ticket();
if ($role != 'educator') {
    header("Location: ../../index.php");
    exit();
}
include '../../includes/header.php';

// Fetch course names from the database
$courses = [];
$sql = "SELECT `courseName` FROM `course`";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $courses[] = $row['courseName'];
    }

    $stmt->close();
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report</title>
    <link rel="stylesheet" href="../../styles/style.css">
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
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            background-color: white;
            /* Light grey background for contrast */
            border-left: 4px solid #333;
            /* Adds a solid line to the left for style */
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1);
            /* Adds a subtle shadow for depth */
        }

        select {
            padding: 5px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 18px;
            /* Adjust this value to your preference */
        }

        .btn {
            background-color: lightblue;
            /* Green */
            border: none;
            color: white;
            padding: 15px 32px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            border-radius: 10px;
        }

        .btn:hover {
            background-color: darkblue;
        }
    </style>
</head>

<body>
    <div class="page">
        <div class="page-title">
            <img src="<?php echo $base; ?>images/educator_pic/report.png" alt="report Icon" class="report image">
            Report
        </div>
        <div class="page-content">
            <!-- You can add your content here -->
            <div style="display: flex; align-items: center; gap: 10px;">
                <h2 style="margin: 0;">Select Course Name:</h2>
                <select name="course_name">
                    <!-- Loop through courses to generate options -->
                    <?php foreach ($courses as $course) : ?>
                        <option value="<?php echo $course; ?>"><?php echo $course; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="text-align: right;">
                <button type="submit" class="btn">Generate report</button>
            </div>
        </div>
        <!-- Moved the #report div outside the .page-content div -->
        <div id="report"></div>
    </div>
</body>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
    $(document).ready(function() {
        $("button").click(function() {
            // Get the selected course name
            var selectedCourse = $("select[name='course_name']").val();

            // Create a new iframe and set its source to 'generated_report.php' with the selected course name
            var iframe = $("<iframe />");
            iframe.attr("src", "generated_report.php?course=" + encodeURIComponent(selectedCourse));

            // Set the width and height of the iframe
            iframe.css({
                "width": "100%", // Adjust as needed
                "height": "500px" // Adjust as needed
            });

            // Insert the iframe into the #report div
            $("#report").empty().append(iframe);
        });
    });
</script>




<?php include '../../includes/footer.php'; ?>

</html>