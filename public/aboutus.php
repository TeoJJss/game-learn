<?php 
    require '../modules/config.php';
    if (check_ticket()){
        header("Location: ../index.php");
        exit();
    }
    include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="stylesheet" href="../styles/aboutus.css">

</head>
<body>
    <div class="pages">

        <div class="intro">
                <h1>About Us</h1>
        </div>

        <h1 class="obj_tittle">Project Objectives</h1>
        <div class="obj">
            <img class="obj_img" src="../images/aboutus_pic/obj" alt="whatsapp">
            <p> Our primary goal is to boost student motivation and overall learning outcomes through the implementation of a <strong>gamified e-learning </strong>system. By creating a competitive and dynamic environment complete with points systems and leaderboards, we inspire students to take charge of their learning journey. Moreover, our platform empowers educators by providing comprehensive insights into student progress, enabling them to tailor their teaching methods accordingly. To ensure the success and accessibility of our platform, we meticulously test and assess its features, gathering feedback from undergraduate students to continually enhance their learning experience.  </p>
        </div>

        <h1 class="obj_tittle">Our Group</h1>
        <div class="contact_card">
            <div class="card">
                <img class="card_img" src="../images/aboutus_pic/junjia.png" alt="whatsapp">
                <hr>
                <h2>Teo Jun Jia</h2>
                <p>TP067775</p>
            </div>
            <div class="card">
                <img class="card_img" src="../images/aboutus_pic/shengyao" alt="whatsapp">
                <hr>
                <h2>Siew Sheng Yao</h2>
                <p>TP068174</p>
            </div>
            <div class="card">
                <img class="card_img" src="../images/aboutus_pic/kahjun" alt="whatsapp">
                <hr>
                <h2>Chong Kah Jun</h2>
                <p>TP067165</p>
            </div>
            <div class="card">
                <img class="card_img" src="../images/aboutus_pic/boon" alt="whatsapp">
                <hr>
                <h2>Sin Boon Leon</h2>
                <p>TP068552</p>
            </div>
            <div class="card">
                <img class="card_img" src="../images/aboutus_pic/leewai" alt="whatsapp">
                <hr>
                <h2>Yong Lee Wai</h2>
                <p>TP068636</p>
            </div>
        </div>

        <h1 class="obj_tittle">Location</h1>

        <div class="map">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3984.1466274575805!2d101.69798647472992!3d3.0554056969203804!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31cc4abb795025d9%3A0x1c37182a714ba968!2z5Lqa5aSq56eR5oqA5aSn5a2m!5e0!3m2!1szh-CN!2smy!4v1708782500387!5m2!1sus-EN!2smy" width="100%" height="400" style="border:0;border-radius:3vh;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
        </div>




</body>
<?php include '../includes/footer.php';?>
</html>