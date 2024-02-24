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
    
<style>
        @-webkit-keyframes tracking-in-expand{0%{letter-spacing:-.5em;opacity:0}40%{opacity:.6}100%{opacity:1}}@keyframes tracking-in-expand{0%{letter-spacing:-.5em;opacity:0}40%{opacity:.6}100%{opacity:1}}

@-webkit-keyframes kenburns-bottom-left{0%{-webkit-transform:scale(1) translate(0,0);transform:scale(1) translate(0,0);-webkit-transform-origin:16% 84%;transform-origin:16% 84%}100%{-webkit-transform:scale(1.25) translate(-20px,15px);transform:scale(1.25) translate(-20px,15px);-webkit-transform-origin:left bottom;transform-origin:left bottom}}@keyframes kenburns-bottom-left{0%{-webkit-transform:scale(1) translate(0,0);transform:scale(1) translate(0,0);-webkit-transform-origin:16% 84%;transform-origin:16% 84%}100%{-webkit-transform:scale(1.25) translate(-20px,15px);transform:scale(1.25) translate(-20px,15px);-webkit-transform-origin:left bottom;transform-origin:left bottom}}

.pages{
    margin-left: 0vw;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    gap: 0px;
}


.intro {
    position: relative;
    display: flex;
    flex-direction: row;
    width: 100%;
    height: 50vh;
    font-family: 'Poppins', sans-serif;
    justify-content: center;
    align-items: center;
    margin-bottom: 0px;
    text-align: center;
    
}

.intro::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: url('../images/term'); 
    background-size: cover; 
    background-position: center; 
    filter: blur(1px); 
    z-index: -1; 
    -webkit-animation:kenburns-bottom-left 5s ease-out both;animation:kenburns-bottom-left 5s ease-out both
}

.intro h1 {
    padding-left: 80px;
    padding-top: 20px;
    font-family: 'Poppins', sans-serif;
    font-size: 12vh;
    font-weight: 700;
    padding-bottom: 0%;
    color: white;
    position: relative;
    -webkit-animation:tracking-in-expand .7s cubic-bezier(.215,.61,.355,1.000) both;animation:tracking-in-expand .7s cubic-bezier(.215,.61,.355,1.000) both
}

.content{
    font-family: 'Poppins', sans-serif;
    font-size: 3vh;
    font-weight: 300;
    color: black;
    margin: 0%;
    /* width: 150vh; */
    margin:30vh;
    margin-top: 10vh;
    margin-bottom: 15vh;

}

    
</style>

</head>
<body>
<div class="pages">
    <div class="intro">
        <h1>Terms and Conditions</h1>
    </div>
    
    <p class="content">
    Welcome to Mathy! These terms and conditions govern your use of the Mathy website (the "Site") and any related services provided by Mathy (collectively, the "Services"). By accessing or using the Site or Services, you agree to be bound by these terms and conditions ("Terms"). Please read them carefully before using Mathy.<br><br>

<strong> 1. Acceptance of Terms</strong><br>
By accessing the Site or using the Services, you agree to be bound by these Terms, whether or not you are a registered user of our Services. If you do not agree to these Terms, you may not access or use the Site or Services.
<br><br>
<strong> 2. Use of Services</strong><br>
a. You must be 18 years or older to use Mathy. Individuals under the age of 18 may only use Mathy with the involvement and supervision of a parent or legal guardian.
<br>
b. You agree to use Mathy solely for lawful purposes and in accordance with these Terms and all applicable laws and regulations.
<br>
c. You are responsible for maintaining the confidentiality of your account and password and for restricting access to your account. You agree to accept responsibility for all activities that occur under your account.
<br><br>
<strong> 3. Content</strong><br>
a. You understand that all content posted on the Site, including but not limited to courses, lessons, videos, and materials (collectively, "Content"), is the sole responsibility of the person who created such Content. Mathy does not endorse, support, represent, or guarantee the completeness, truthfulness, accuracy, or reliability of any Content.
<br>
b. You acknowledge that by using the Site or Services, you may be exposed to Content that is offensive, indecent, or objectionable. Under no circumstances will Mathy be liable in any way for any Content, including but not limited to any errors or omissions in any Content or any loss or damage of any kind incurred as a result of the use of any Content.
<br><br>
<strong> 4. Intellectual Property</strong><br>
a. Mathy and its licensors own all rights, title, and interest in and to the Site and Services, including all intellectual property rights. You agree not to reproduce, distribute, modify, or create derivative works of any part of the Site or Services without our prior written consent.
<br><br>
<strong> 5. Termination</strong><br>
a. Mathy reserves the right to suspend or terminate your access to the Site and Services at any time, without prior notice or liability, for any reason whatsoever, including without limitation if you breach these Terms.
<br><br>
<strong> 6. Disclaimer of Warranties</strong><br>
a. Your use of the Site and Services is at your sole risk. The Site and Services are provided on an "as is" and "as available" basis. Mathy disclaims all warranties of any kind, whether express, implied, or statutory, including but not limited to the implied warranties of merchantability, fitness for a particular purpose, and non-infringement.
<br><br>
<strong> 7. Limitation of Liability</strong><br>
a. In no event shall Mathy, nor its directors, employees, partners, agents, suppliers, or affiliates, be liable for any indirect, incidental, special, consequential, or punitive damages, including without limitation, loss of profits, data, use, goodwill, or other intangible losses, resulting from <br>(i) your access to or use of or inability to access or use the Site or Services; <br>(ii) any conduct or content of any third party on the Site or Services; <br>(iii) any content obtained from the Site or Services; and <br>(iv) unauthorized access, use, or alteration of your transmissions or content, whether based on warranty, contract, tort (including negligence), or any other legal theory, whether or not we have been informed of the possibility of such damage, and even if a remedy set forth herein is found to have failed of its essential purpose.
<br><br>
<strong> 8. Governing Law</strong><br>
a. These Terms shall be governed by and construed in accordance with the laws of <a href="https://www.acm.org/code-of-ethics" target="_blank">[ACM Code of Ethics and Professional Conduct]</a>, without regard to its conflict of law provisions.
<br><br>
<strong> 9. Changes to Terms</strong><br>
a. Mathy reserves the right, at its sole discretion, to modify or replace these Terms at any time. If a revision is material, we will provide notice on the Site or by email. What constitutes a material change will be determined at our sole discretion.
<br><br>
<strong> 10. Contact Us</strong><br>
If you have any questions about these Terms, please contact us at <a href="../public/contactus.php">[Contact Us]</a>.
<br><br>
By using Mathy, you agree to these Terms and conditions. Thank you for choosing Mathy!
    </p>



</div>
</body>
<?php include '../includes/footer.php';?>
</html>