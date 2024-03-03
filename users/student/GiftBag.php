<?php 
    require '../../modules/config.php';
    $role = check_ticket();
    if ($role != 'student') {
        header("Location: ../../index.php");
        exit();
    }
    include '../../includes/header.php';

    $ticket = $_SESSION['ticket'];
    $ch = curl_init("$base_url/check-ticket?ticket=$ticket");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

    // Execute the cURL request
    $response = curl_exec($ch);

    // Check if the request was successful (HTTP code 202)
    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 202) {
        // Parse the JSON response
        $responseData = json_decode($response, true);

        // Check if 'user_id' exists in the response
        if (isset($responseData['data']['user_id'])) {
            $user_id = $responseData['data']['user_id'];
        } else {
            // Handle the case when 'user_id' is not present in the response
            echo "User ID not found in the response.";
        }
    } else {
        // Handle the case when the request was not successful
        echo " ";
    }

    // Close cURL resource
    curl_close($ch);

    
    function fetchGiftBagDetails($userID) {
        global $conn;
    
        // SQL query to join user_gift and gift tables, count total quantity, and select giftMedia
        $sql = "SELECT user_gift.giftID, gift.giftMedia, COUNT(user_gift.giftID) AS totalQuantity
                FROM user_gift
                INNER JOIN gift ON user_gift.giftID = gift.giftID
                WHERE user_gift.userID = ? AND user_gift.isUsed = 0
                GROUP BY user_gift.giftID";

    
        try {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $userID);
            $stmt->execute();
            $result = $stmt->get_result();
    
            // Fetch and save data into an associative array
            $giftBagDetails = array();
            while ($row = $result->fetch_assoc()) {
                $giftDetails = array(
                    'giftID' => $row['giftID'],
                    'giftMedia' => $row['giftMedia'],
                    'totalQuantity' => $row['totalQuantity']
                );
                $giftBagDetails[] = $giftDetails;
            }
    
            // Return the associative array
            return $giftBagDetails;
    
            $stmt->close();  // Close the prepared statement
        } catch (Exception $e) {
            // Handle exceptions (e.g., database error)
            echo "Error: " . $e->getMessage();
        }
    }

$giftBagDetails = fetchGiftBagDetails($user_id);
// Loop through the gift bag details and echo giftID and totalQuantity
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Template</title>
    <link rel="stylesheet" href="../../styles/style.css">
    <style>
         .category {
            font-size: 2rem; 
            padding-left: 5rem;
            padding-top: 2rem; 
            padding-bottom: 5rem;
            width: 100%;
            display: flex;
            align-items: center;
        }

        .category img {
            margin-right: 10px;
            height: 5rem;
        }
        
        .category h1 {
            padding-left: 1rem;
        }

        .top {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .game-cell {
            display: flex;
            border: 1px solid #ddd;
            border-radius: 8px;
            width: 30rem; /* Adjust the width as needed */
            margin: 5px;
        }

        .game-cell img {
            width: 10rem; /* Adjust the width as needed */
            height: 10rem;
            object-fit: cover;
            padding-top: 2rem;
            padding-left: 2rem;
            align-items: center;
        }

        .right-content {
            display: flex;
            flex-direction: column;
            padding: 10px;
            padding-left: 5rem;
            font-size: 5rem;
        }

        section {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around; /* Adjust as needed */
            padding-bottom: 5rem;
        }
    </style>
</head>
<body>
    <header class="top">
        <div class="category">
            <img src="../../images/nav_picture/giftbag.png" alt="Educators Applications">
            <h1>Gift Bag</h1> 
        </div>
    </header>
    <section >
        <?php foreach ($giftBagDetails as $giftDetails) : ?>
            <div class="game-cell">
                <img src='data:image/png;image/jpg;base64,<?php echo$giftDetails['giftMedia']; ?>' alt='giftMedia'>
                <p class="right-content">X<?php echo$giftDetails['totalQuantity']; ?></p>
            </div>
        <?php endforeach; ?>
    </section>
</body>
<?php include '../../includes/footer.php';?>
</html>