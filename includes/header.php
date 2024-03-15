<?php
if (file_exists('../modules/config.php')) {
    include_once '../modules/config.php';
    $base = "../";
} else {
    include_once '../../modules/config.php';
    $base = "../../";
}
$role = check_ticket();
?>
<header>
    <link rel="stylesheet" href="<?php echo $base; ?>styles/header.css">
    <div class="navbar">
        <?php
        echo "<img src='$base\images/nav_picture/logo01.png' alt='Mathy logo' class='logo' onclick='location.href=\"$base\public/index.php\"'>";
        ?>
        <div class="nav">
            <?php if (!$role) { //Guest header 
            ?>
                <input type="search" class="nav_search" id="search-inp" placeholder="Search For Course (Enter key to search)">
                <button class="nav_button" id="teach-btn-guest" onclick="location.href = '<?php echo $base; ?>public/register.php'">
                    <img src="<?php echo $base; ?>images/nav_picture/teach_on_mathy.png" alt="Teach On Mathy">
                    <a>Teach On Mathy</a>
                </button>
                <button class="guest-btn" id="login" onclick="window.location.href='<?php echo $base; ?>public/login.php'">Log In</button>
                <button class="guest-btn" id="signup" onclick="window.location.href='<?php echo $base; ?>public/register.php'">Sign Up</button>

            <?php } else if ($role == "student") { //student header 
            ?>
                <input type="text" class="nav_search" id="search-inp" placeholder="Search For Course (Enter key to search)">
                <button class="nav_button" onclick="location.href = '<?php echo $base; ?>public/search_result.php'">
                    <img src="<?php echo $base; ?>images/nav_picture/course.png" alt="Course">
                    <a>Course</a>
                </button>
                <button class="nav_button" onclick="location.href = '<?php echo $base; ?>users/forum.php'">
                    <img src="<?php echo $base; ?>images/forum.png" alt="Forum">
                    <a>Forum</a>
                </button>
                <button class="nav_button">
                    <img src="<?php echo $base; ?>images/nav_picture/gift_shop.png" alt="Gift Shop" onclick="location.href = '<?php echo $base; ?>users/student/GiftShop.php'">
                    <a>Gift Shop</a>
                </button>
                <button class="nav_button">
                    <img src="<?php echo $base; ?>images/nav_picture/my_learning.png" alt="My Learning" onclick="location.href = '<?php echo $base; ?>users/student/index.php'">
                    <a>My Learning</a>
                </button>
            <?php } else if ($role == "educator") { //educator header 
            ?>
                <input type="text" class="nav_search" id="search-inp" placeholder="Search For Course (Enter key to search)">
                <button class="nav_button" onclick="location.href = '<?php echo $base; ?>users/educator/index.php'">
                    <img src="<?php echo $base; ?>images/educator_pic/dashboard.png" alt="Dashboard">
                    <a>Dashboard</a>
                </button>
                <button class="nav_button" onclick="location.href = '<?php echo $base; ?>users/educator/course_management.php'">
                    <img src="<?php echo $base; ?>images/educator_pic/course.png" alt="Course">
                    <a>Course</a>
                </button>
                <button class="nav_button" onclick="location.href = '<?php echo $base; ?>users/forum.php'">
                    <img src="<?php echo $base; ?>images/forum.png" alt="Forum">
                    <a>Forum</a>
                </button>
                <button class="nav_button" onclick="location.href = '<?php echo $base; ?>users/educator/report.php'">
                    <img src="<?php echo $base; ?>images/nav_picture/report.png" alt="Report">
                    <a>Report</a>
                </button>
            <?php } else if ($role == "admin") { // admin header 
            ?>
                <input type="text" class="nav_search" id="search-inp" placeholder="Search For Course (Enter key to search)">
                <button class="nav_button" onclick="location.href='<?php echo $base; ?>users/admin/user_console.php'">
                    <img src="<?php echo $base; ?>images/admin_pic/manage_user.png" alt="Manage User">
                    <a>Manage User</a>
                </button>
                <button class="nav_button" onclick="location.href = '<?php echo $base; ?>users/forum.php'">
                    <img src="<?php echo $base; ?>images/forum.png" alt="Forum">
                    <a>Forum</a>
                </button>
            <?php } ?>


            <?php if ($role) { ?>
                <button class="nav_button" onclick="location.href='<?php echo $base; ?>users/more.php'">
                    <img src="<?php echo $base; ?>images/nav_picture/more.png" alt="More">
                    <a>More</a>
                </button>
                <button class="nav_button" onclick="location.href='<?php echo $base; ?>users/index.php'">
                <?php
                $ticket = $_SESSION['ticket'];
                $ch = curl_init("$base_url/check-ticket?ticket=$ticket");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                $response = json_decode(curl_exec($ch), true);

                if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 202) {
                    $email = $response["data"]["email"];
                    $name = $response["data"]["name"];
                    $role = $response["data"]["role"];

                    $sql = "SELECT profilePic FROM `profile` WHERE userID=?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $response['data']['user_id']);
                    $stmt->execute();
                    $stmt->bind_result($profilePic);
                    $stmt->fetch();

                    $no_img = "<img src='$base/images/user.png' id='profilePic-header'>";
                    if ($profilePic !== null) {
                        $base64Image = $profilePic;
                        $img_html =  '<img src="data:image/png;image/jpg;base64,' . $base64Image . '" alt="Profile Picture" id="profilePic-header">';
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
    <script>
        var searchInput = document.getElementById("search-inp");

        searchInput.addEventListener("keypress", function(event) {
            if (event.key == "Enter") {
                searchInputVal = searchInput.value;
                location.href = '<?php echo $base; ?>public/search_result.php?search=' + searchInputVal;
                event.preventDefault();
            }
        });
    </script>
</header>