<?php
    require '../../modules/config.php';
    require './controllers/systemFeedbackController.php';

    if (check_ticket() != 'admin'){
        header("Location: ../../index.php");
        exit();
    }

    $ticket = $_SESSION['ticket'];

    if(isset($_GET['sfID'])) { // Retrieve sfID parameter from URL
        $sfID = $_GET['sfID'];

        $currentPage = isset($_GET['page']) ? intval($_GET['page']) : 1;
    } else {
        header("Location: ./system_feedback.php?page=1.php"); 
        exit();
    }

    $feedbackWithProfile = fetchFeedbackWithProfile($sfID);

    if ($feedbackWithProfile) {
        $userID = $feedbackWithProfile['userID']; 
        $ch = curl_init("$base_url/user-detail?user_id=$userID");

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $response = curl_exec($ch);
        $userData = json_decode($response, true);

        if (isset($userData['msg'])) {
            $username = $userData['msg'];
        } else {
            echo "Error retrieving username";
        }
    } else {
        echo "No data found";
    }

    include "../../includes/header.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../styles/style.css">
    <title>Feedback to user</title>
    <style>
        .box-container {
            display: flex;
            padding-left: 3rem;
            padding-right: 3rem;
            padding-bottom: 3rem;
            flex-wrap: wrap; /* Allow flex items to wrap */
        }

        .category {
            font-size: 2rem; 
            padding-left: 1rem;
            padding-top: 2rem; 
            padding-bottom: 1rem;
            width: 100%;
        }

        .main {
            display: flex;
            flex-direction: column;
            padding: 20px; /* Adjust as needed */
            width: 100%;
        }

        .userProfile {
            display: flex;
            align-items: center;
            padding-bottom: 20px; /* Adjust as needed */
        }

      
       
    </style>
</head>
<body>
    <div class="box-container">
        <h1 class="category">Feedback to User</h1>
        <div class="main">
            <div style="display: flex; justify-content: space-between;">
                <div class="userProfile">
                        <?php
                        $feedbackWithProfile = fetchFeedbackWithProfile($sfID);
                        if ($feedbackWithProfile) {
                            $userID = $feedbackWithProfile['userID'];
                            $profilePicBlob = $feedbackWithProfile['profilePic'];
                            $profilePicBase64 = base64_encode($profilePicBlob);
                            $profilePicSrc = 'data:image/jpeg;base64,' . $profilePicBase64;
                        ?>
                            
                            <img src="<?php echo $profilePicSrc; ?>" alt="User Profile Picture">
                            <p style="margin-left: 15px;"><?php echo $username; ?></p>

                        <?php } else { ?>
                            <p>No user data found</p>
                        <?php } ?>        
                </div>

                <a href="system_feedback.php" class="button" style="margin-bottom: 15px; margin-top: 12px; text-decoration: none; padding-top: 1rem;">Back</a>
            </div>

            <form action="./api/provideFeedbackApi.php" method="POST">
                <textarea id="feedback" name="feedback" placeholder="Enter your feedback..." style="width: 100%; height: 300px; resize: none;"></textarea>
                <input type="hidden" name="sfID" value="<?php echo htmlspecialchars($sfID); ?>"> 
                <input type="hidden" name="page" value="<?php echo $currentPage; ?>">
                <div style="display: flex; justify-content:space-between ; margin-top: 15px;">
                    <div>
                        <label for="image" style="cursor: pointer;"> 
                            <img id="imagePreview" src="../../images/admin_pic/picture.png" alt="Preview" class="button" style="width: 50px; height: 50px;"> <!-- Initial empty image -->
                        </label>
                        <input type="file" id="image" name="image" style="display: none;" onchange="previewImage(event)"> 
                    </div>

                    <input type="submit" value="Submit" class="button">
                </div>
                <div id="uploadedImage" style="margin-top: 10px;"></div> 
            </form>

            <script>
                    //Show error or success message from the API
                    const urlParams = new URLSearchParams(window.location.search);
                    const status = urlParams.get('status');
                    const message = urlParams.get('message');

                    // Display message based on status
                    if (status === 'success') {
                        alert('Feedback submitted successfully!');
                    } else if (status === 'error') {
                        const errorMessage = message ? decodeURIComponent(message) : 'An error occurred. Please try again.';
                        alert(errorMessage);
                    }

                function previewImage(event) {
                    const input = event.target;
                    const file = input.files[0];
                    const reader = new FileReader();

                    reader.onload = function () {
                        const uploadedImage = document.getElementById('uploadedImage');

                        if (file.type.startsWith('image/')) {
                            uploadedImage.innerHTML = '<h1>You uploaded this image: </h1> </br> <img src="' + reader.result + '" alt="Uploaded Image" style="max-width: 100%; max-height: 200px;">'; // Display the uploaded image
                        } else {
                            window.alert('Error: Please upload an image file.'); // Display error message using prompt
                            input.value = ''; 
                        }
                    };

                    reader.readAsDataURL(file); 
                }

                feedbackForm.addEventListener('submit', async (event) => {
                    event.preventDefault(); 

                    const currentPage = <?php echo json_encode($currentPage); ?>;
                    const formData = new FormData(feedbackForm);
                    formData.append('page', currentPage);

                
                    const response = await fetch('./api/provideFeedbackApi.php/', {
                        method: 'POST',
                        body: formData
                    });

                    if (!response.ok) {
                        throw new Error(`API call failed with status ${response.status}`);
                    }

                    feedbackForm.reset(); 
                });

            </script>
        </div>
    </div>
</body>

</html>

<?php include '../../includes/footer.php'; ?>