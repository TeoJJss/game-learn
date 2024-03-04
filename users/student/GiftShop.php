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

    function fetchGifts() {
        global $conn;
    
        $sql = "SELECT giftID, giftName, giftPoints, giftMedia FROM gift";
    
        $result = $conn->query($sql);
    
        if ($result && $result->num_rows > 0) {
            $gifts = array();
    
            while ($row = $result->fetch_assoc()) {
                $gift = array(
                    'giftID' => $row['giftID'],
                    'giftName' => $row['giftName'],
                    'giftPoints' => $row['giftPoints'],
                    'giftMedia' => $row['giftMedia']
                );
    
                $gifts[] = $gift;
            }
    
            return $gifts;
        } else {
            return array();
        }
    }

    $gifts = fetchGifts();

    function deductPoints($userID, $point) {
        global $conn;
    
        // SQL query to retrieve the current point value for the given user ID
        $selectSql = "SELECT pointValue FROM point WHERE userID = ?";
    
        // SQL query to update the point value after deduction
        $updateSql = "UPDATE point SET pointValue = ? WHERE userID = ?";
    
        try {
            // Retrieve the current point value
            $stmtSelect = $conn->prepare($selectSql);
            $stmtSelect->bind_param("i", $userID);  // Bind the parameter
            $stmtSelect->execute();
            $stmtSelect->bind_result($currentPoints);
            $stmtSelect->fetch();
            $stmtSelect->close();  // Close the prepared statement
    
            if ($currentPoints === false) {
                throw new Exception("User not found or has no points.");
            }
    
            // Check if points are enough for deduction
            if ($currentPoints < $point) {
                throw new Exception("Not enough points to redeem the gift.");
            }
    
            // Deduct points
            $newPoints = max(0, $currentPoints - $point);
    
            // Update the point value
            $stmtUpdate = $conn->prepare($updateSql);
            $stmtUpdate->bind_param("ii", $newPoints, $userID);  // Bind the parameters
            $stmtUpdate->execute();
    
            // Success message or further processing
            return true;
    
        } catch (Exception $e) {
            // Handle exceptions (e.g., user not found, database error)
            $errorMessage = "Error: " . $e->getMessage();
    
            // Display the error message using JavaScript alert
            echo "<script>alert('" . addslashes($errorMessage) . "');</script>";
    
            return false;
        }
    }
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Retrieve user input
        $userID = isset($_POST["user_id"]) ? $_POST["user_id"] : null;
        $point = isset($_POST["gift_points"]) ? $_POST["gift_points"] : null;
        $giftID = isset($_POST["gift_id"]) ? $_POST["gift_id"] : null;
        $quantity = isset($_POST["quantity"]) ? $_POST["quantity"] : null;
    
        // Validate user input
        if ($userID !== null && $point !== null) {
            // Your deduction logic
            if (deductPoints($userID, $point)) {
                // Deduction successful, proceed with other operations
                insertUserGift($userID, $giftID, $quantity);
                header("Location: GiftShop.php?user_id=$userID");
            } else {
                // Deduction failed, display an appropriate message
                echo "";
            }
        } else {
            // Handle invalid input
            echo "Invalid input. Please provide both user ID and points.";
        }
    }
    function insertUserGift($userID, $giftID, $quantity = 1 ) {
        global $conn;
    
        // SQL query to insert user and gift details into the user_gift table
        $insertSql = "INSERT INTO user_gift (userID, giftID) VALUES (?, ?)";
    
        // Insert multiple records based on quantity
        for ($i = 1; $i <= $quantity; $i++) {
            try {
                $stmtInsert = $conn->prepare($insertSql);
                $stmtInsert->bind_param("ii", $userID, $giftID);  // Bind the parameters
                $stmtInsert->execute();
                $stmtInsert->close();  // Close the prepared statement
            } catch (Exception $e) {
                // Handle exceptions (e.g., database error)
                echo "Error: " . $e->getMessage();
            }
        }
    
        // Additional processing or success message
        echo "Gifts purchased successfully!";
    } 
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
        
        #point {
            width: 7vw;
            max-width: 5vw;
            cursor: default;
            pointer-events: none;
            background-color: #FFE9C8;
            color: black;
            font-size: 1.5vw;
        }

        #point * {
            vertical-align: middle;
        }
        .ptImg {
            width: 2vw;
            margin-right: 0.3vw;
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

        }

        .right-content {
            display: flex;
            flex-direction: column;
            padding: 10px;
            padding-left: 5rem;
        }

        .quantity-selector {
            display: flex;
            align-items: center;
            margin-top: 10px;
        }

        section {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around; /* Adjust as needed */
        }
    </style>
</head>
<body>
    <header class="top">
        <div class="category">
            <img src="../../images/nav_picture/gift_shop.png" alt="Educators Applications">
            <h1>Gift Shop</h1> 
        </div>
        
        <span class="count-results">
            <?php if ($role == 'student') {
                $ticket = $_SESSION['ticket'];
                $ch = curl_init("$base_url/check-ticket?ticket=$ticket");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                $response = json_decode(curl_exec($ch), true);
                $user_id = $response['data']['user_id'];
                $sql = "SELECT pointValue FROM point WHERE userID=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $stmt->bind_result($pointVal);
                $stmt->fetch();
                echo "<p class='button' id='point'><img src='../../images/nav_picture/point.png' class='ptImg'><span>$pointVal<span></p>";
            } ?>
        </span>
        <button class="button" onclick="location.href = '<?php echo $base; ?>users/student/GiftBag.php'">Gift Bag</button>
    </header>

    <section>
        <?php foreach ($gifts as $gift) : ?>
            <div class="game-cell">
            <img src='data:image/png;image/jpg;base64,<?php echo $gift['giftMedia']; ?>' alt='giftMedia'>

                <div class="right-content">
                    <h2 style="background-color:pink;"><?php echo $gift['giftName']; ?></h2>

                    <h3 class="display: flex; align-items: center;">
                        <img style=" height: 2rem; width: 2rem; padding-top:0; padding-left: 0;" src='../../images/nav_picture/point.png'>
                        <?php echo $gift['giftPoints']; ?>
                    </h3>

                    <div class="quantity-selector" style="padding-bottom: 1rem;">
                        <button class="button" onclick="decrementQuantity(<?php echo $gift['giftID']; ?>)">-</button>
                        <span id="quantityDisplay-<?php echo $gift['giftID']; ?>" style="padding-left: 2rem; padding-right: 2rem;">1</span>
                        <button class="button" onclick="incrementQuantity(<?php echo $gift['giftID']; ?>)">+</button>
                    </div>

                    <button class="button" onclick="buyNow(<?php echo $gift['giftID']; ?>, <?php echo $gift['giftPoints']; ?>)">Buy now</button>
                </div>
            </div>
        <?php endforeach; ?>
    </section>

    <form id="buyForm" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
        <input type="hidden" name="gift_id" value="">
        <input type="hidden" name="quantity" value="">
        <input type="hidden" name="gift_points" value="">
        <!-- <input type="hidden" name="point"value="100"> -->
    </form>

</body>
<?php include '../../includes/footer.php';?>

<script>
    let quantities = {};

    function incrementQuantity(giftId) {
        quantities[giftId] = (quantities[giftId] || 1) + 1;
        updateQuantityDisplay(giftId);
    }

    function decrementQuantity(giftId) {
        if (quantities[giftId] > 1) {
            quantities[giftId]--;
            updateQuantityDisplay(giftId);
        }
    }

    function updateQuantityDisplay(giftId) {
        document.getElementById('quantityDisplay-' + giftId).innerText = quantities[giftId];
    }

    function buyNow(giftId, giftPoints) {
    const quantity = quantities[giftId] || 1;
    const totalPoints = giftPoints * quantity;

    console.log('user_id:', <?php echo $user_id; ?>);
    console.log('gift_id:', giftId);
    console.log('quantity:', quantity);
    console.log('gift_points:', totalPoints);

    // Update the form fields
    const buyForm = document.getElementById('buyForm');
    buyForm.querySelector('[name="gift_id"]').value = giftId;
    buyForm.querySelector('[name="quantity"]').value = quantity;
    buyForm.querySelector('[name="gift_points"]').value = totalPoints;

    // Submit the form
    buyForm.submit();
    }

    function showError(errorMessage) {
        // Display a prompt with the error message
        window.prompt("Error", errorMessage);
    }
</script>  
</html>