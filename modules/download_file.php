<?php 
require '../modules/config.php';
if(isset($_GET['mid'])) {
    $moduleID = $_GET['mid'];
    $file = $_GET['filename'];
    $filePath = '../tmp/'.$moduleID.'/'.$file;
    header('Content-Description: File Transfer');
    // header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
    header('Content-Length: ' . filesize($filePath));
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Expires: 0');

    // Read the file and output its contents
    readfile($filePath);
    exit;
}
?>