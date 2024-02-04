<?php
    //$imageName = "https://cdn-icons-png.freepik.com/512/9596/9596184.png";
    //$imagePath = "images/" . $imageName;
    function err_handler($err_code, $err_msg, $file_dir, $line_no,){
        $filename= basename($file_dir); //Get the filename from file directory
        echo "<title>Error!</title>";
        echo "<center style='font-family: Arial;' 'margin-top: 6vh;'>";
        echo "<h1 style='color:orange;''margin-top: 6vh;'>Something went wrong!</h1><br>"; 
        echo '<img src="https://cdn-icons-png.freepik.com/512/9596/9596184.png" alt="Something went wrong" width="20%"><br>';
        echo "$err_code $err_msg<br><p>at $filename, line $line_no</p><br>";
        echo "Please refresh the website or try went back to Home Page login again.<br>";
        echo "If you think this is a mistake, copy the message above and report to the email 
                <a href='mailto:assist@mathystudy.com' style='color: orange;'>assist@mathystudy.com</a>.";
        echo "</center>";
    }
    set_error_handler("err_handler");
?>