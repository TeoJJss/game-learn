<?php
    require_once '../modules/config.php';
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $moduleID = $_POST['moduleID'];
        $moduleTitle = $_POST['moduleTitle'];
        $moduleDesc = $_POST['moduleDesc'];
        $filename = $_POST['moduleFilename'];
        $moduleFilename = $filename;
        if (isset($_FILES['moduleFile']) && $_FILES['moduleFile']['error'] !== UPLOAD_ERR_NO_FILE) {
            $upload_directory = '../tmp/' . $moduleID . '/';

            if (!file_exists($upload_directory)) {
                mkdir($upload_directory, 0777, true);
            }

            $moduleFile = $_FILES['moduleFile']['tmp_name'];
            $file_extension = strtolower(pathinfo($_FILES['moduleFile']['name'], PATHINFO_EXTENSION));
            $moduleFilename = $filename;
            $filename = $filename . ".$file_extension";

            $destination = $upload_directory . $filename;

            $count = 1;
            while (file_exists($upload_directory . $moduleFilename . '.pdf') || file_exists($upload_directory . $moduleFilename . '.docx')) {
                $moduleFilename = $count . "_$moduleFilename";
                $filename = $count . "_$filename";
                $destination = $upload_directory . $filename;
                $count++;
            }
            if (!move_uploaded_file($moduleFile, $destination)) {
                trigger_error("Failed to upload file.");
            }
        }
        $sql = "UPDATE module SET moduleTitle=?, moduleDesc=?, `filename`=? WHERE moduleID=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $moduleTitle, $moduleDesc, $moduleFilename, $moduleID);
        $stmt->execute();
        if ($stmt->affected_rows < 1) {
            trigger_error("Update Failed");
        }
    }
    $moduleID = $_GET['moduleID'];
    $sql = "SELECT moduleTitle, moduleDesc, `filename` FROM module WHERE moduleID=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $moduleID);
    $stmt->execute();
    $stmt->bind_result($moduleTitle, $moduleDesc, $moduleFilename);
    $stmt->fetch();
    $stmt->close();
?>
<link rel="stylesheet" href="../styles/style.css">
<style>
    .inp-row label {
        font-weight: bold;
    }

    .inp-row textarea {
        width: 250vw;
        margin-top: 1vh;
        resize: none;
    }
    .button{
        height: 10vh;
    }
</style>
<form method="post" enctype="multipart/form-data">
    <h2>Enter new details for this module</h2>
    <div class="inp-row">
        <label for="moduleTitle">Module Title</label>
        <input type="text" name="moduleTitle" id="moduleTitle" value="<?php echo $moduleTitle; ?>" minlength="1" maxlength="20">
    </div>
    <div class="inp-row">
        <label for="moduleDesc">Module Description</label>
        <textarea name="moduleDesc" id="moduleDesc" cols="30" minlength="1"><?php echo $moduleDesc; ?></textarea>
    </div>
    <div class="inp-row">
        <label for="moduleFile">Module File</label>
        <input type="file" name="moduleFile" id="moduleFile" accept=".pdf, .docx" onchange="document.getElementById('display-file').hidden=false;">
    </div>
    <div class="inp-row" id="display-file" hidden>
        <label for="filename">Module Displayed Filename</label>
        <input type="text" name="moduleFilename" id="filename" value="<?php echo $moduleFilename; ?>" minlength="1" maxlength="20">
    </div>
    <input type="submit" value="Save Changes" class="button" style="margin-left: 5vw;" onclick="parent.refresh();">
    <input type="text" name="moduleID" id="moduleID" value="<?php echo $moduleID; ?>" hidden>
</form>