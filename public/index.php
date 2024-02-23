<!-- HomePage for Guest -->
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
    <title>Guest Homepage</title>
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="stylesheet" href="../styles/guest.css">
</head>
<body>
    <div class="pages">
        <div class="page-contents">
   
            <div class="intro">
                <div class="intro_left">
                    <h1>Unlock the Fun Side of Math!</h1>
                    <h4>Interactive platforms can adapt to individual learning styles and paces, providing personalized challenges and feedback to keep students motivated.</h4>
                </div>
                <img class="guest01" src="../images/guest_pic/guest01.png" alt="guest01">
            </div>
            
             
            <div class="intro02">
                <img class="guest02" src="../images/guest_pic/guest02.png" alt="guest02">
                <div class="intro02_text">
                    <h1>Gamified Learning with Mathy</h1>
                    <p><strong>67%</strong> of students said that a gamified course is more motivating than their traditional coursework. 
                        <br><small style="margin-left:5vw;">-- Johnny Selawsky (2019)</small></p>
                    <button class="button" href="./register.php">Join For Free</button>
                </div>
            </div>

            <h1 class="intro03_title">Key Features:</h1>
            <div class="intro03">
                <div class="card">
                    <h2>Gamified Learning</h2>
                    <hr>
                    <p>Earn points, badges, and level up</p>
                </div>
                <div id="card02" class="card">
                    <h2>Personalized Learning</h2>
                    <hr>
                    <p>Adapts to your pace and needs</p>
                </div>
                <div id="card03" class="card">
                    <h2>Interactive Courses</h2>
                    <hr>
                    <p>Engaging activities and quizzes</p>
                </div>
                <div id="card04" class="card">
                    <h2>Rewards Shop</h2>
                    <hr>
                    <p>Redeem points for exciting rewards</p>
                </div>
                <div id="card05" class="card">
                    <h2>Leaderboards</h2>
                    <hr>
                    <p>Compete with friends and classmates</p>
                </div>
                <div id="card06" class="card">
                    <h2>Feedback System</h2>
                    <hr>
                    <p>Provide input to improve the platform</p>
                </div>
                <div id="card07" class="card">
                    <h2>Online Discussions</h2>
                    <hr>
                    <p>Connect with peers and educators</p>
                </div>
            </div>

            <div class="instructor">
                <img class="instructor_img" src="../images/guest_pic/instructor.png" alt="instructor">
                <div class="instructor_info">
                    <h1>Become an instructor</h1>
                    <p>Instructors from around the world teach millions of learners on Mathy. We provide the tools and skills to teach what you love.</p>
                    <button class="button" href="./register.php">Start Teaching Today</button>
                </div>
            </div>
            

        </div>
        <div class="intro02_background"></div>  
    </div>
</body>
<?php include '../includes/footer.php';?>
</html>
