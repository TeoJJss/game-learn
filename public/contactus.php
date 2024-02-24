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
    <link rel="stylesheet" href="../styles/contactus.css">

</head>
<body>
    <div class="pages">
            <div class="intro">
                <h1>We are here for you</h1>
            </div>
            
            <h1 class="contact_title">Contact Us</h1>
            <h1 class="contact_title01">We can help. Our team of experts is on hand to answer your questions.</h1>

            <div class="contact_card">
                <div class="card" onclick="window.open('https://wa.link/1q4nen', '_blank')">
                    <img class="card_img" src="../images/whatsapp" alt="whatsapp">
                    <h2>WhatsApp</h2>
                    <hr>
                    <p><strong>+60 10-872 1394</strong></p>
                    <br>
                    <p><strong>M - F:</strong> 7am-7pm CST</p>
                    <p><strong>Sat:</strong> 7am-3pm CST</p>
                </div>

                <div class="card" onclick="window.open('mailto:TP068174@mail.apu.edu.my', '_blank')">
                    <img class="card_img" src="../images/email" alt="whatsapp">
                    <h2>Email</h2>
                    <hr>
                    <p><strong>TP068174@mail.apu.edu.my</strong></p>
                    <br>
                    <p><strong>M - F:</strong> 7am-7pm CST</p>
                    <p><strong>Sat:</strong> 7am-3pm CST</p>
                </div>
            </div>



    </div>

</body>
<?php include '../includes/footer.php';?>
</html>