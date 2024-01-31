<?php 
    if(file_exists('../modules/config.php')){
        include_once '../modules/config.php'; 
    }else{
        include_once '../../modules/config.php'; 
    }
    $role = check_ticket();
?>
<header>
    <?php if (file_exists("../styles/header.css")){ ?>
        <link rel="stylesheet" href="../styles/header.css">
    <?php }else{?>
        <link rel="stylesheet" href="../../styles/header.css">
    <?php }?>
    <div class="navbar">
        <img src="../images/nav_picture/logo01.png" alt="Mathy logo" class="logo">
        <div class="nav">
        <?php if (!$role){ //Guest header ?>
            <button class="nav_button">
                <img src="../images/nav_picture/Teach On Mathy.png" alt="Teach On Mathy">
                <a>Teach On Mathy</a>
            </button>
            <button class="nav_button">
                <img src="../images/nav_picture/My Learning.png" alt="My Learning">
                <a>My Learning</a>
            </button>
            <input type="text" class="nav_search" placeholder="Search For Anything">
            <button class="guest-btn" id="login">Log In</button>
            <button class="guest-btn" id="signup">Sign Up</button>

        <?php }else if ($role == "student"){ //student header ?>
            
            <input type="text" class="nav_search" placeholder="Search For Anything">
            <button class="nav_button">
                <img src="../images/nav_picture/course.png" alt="Course">
                <a>Course</a>
            </button>
            <button class="nav_button">
                <img src="../images/nav_picture/My Learning.png" alt="My Learning">
                <a>My Learning</a>
            </button>
            
        <?php } ?>
            <button class="nav_button">
                <?php if ($role){ 
                    $ticket = $_SESSION['ticket'];
                    $ch = curl_init("$base_url/check-ticket?ticket=$ticket");
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                    $response = json_decode(curl_exec($ch), true);
                
                    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 202){
                        $email = $response["data"]["email"];
                        $name = $response["data"]["name"];
                        $role = $response["data"]["role"];
                
                        $sql = "SELECT profilePic FROM `profile` WHERE userID=?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $response['data']['user_id']);
                        $stmt->execute();
                        $stmt->bind_result($profilePic);
                        $stmt->fetch();
                
                        $no_img = '<img src="../images/user.png">';
                        if ($profilePic !== null) {
                            $imageInfo = getimagesizefromstring($profilePic);
                
                            if ($imageInfo !== false) {
                                $mime = $imageInfo['mime'];
                                $base64Image = base64_encode($profilePic);
                                $img_html =  '<img src="data:' . $mime . ';base64,' . $base64Image . '" alt="Profile Picture">';
                            } else {
                                $img_html = $no_img;
                            }
                        } else {
                            $img_html = $no_img;
                        }
                        $stmt->close();
                    }
                    echo $img_html;
                } 
                ?>
            </button>
        </div>
    </div>
</header>