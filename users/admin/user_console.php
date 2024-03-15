<?php
    require '../../modules/config.php';

    if (check_ticket() != 'admin'){
        header("Location: ../../index.php");
        exit();
    }
    $ticket = $_SESSION['ticket'];

    $ch = curl_init("$base_url/user-list?ticket=$ticket");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    $response = json_decode(curl_exec($ch), true);

    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200){
        $edu_ls = $response['msg'];
    }

    include '../../includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../styles/style.css">
    <title>Educators Approval Console</title>
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
            padding-bottom: 5rem;
            width: 100%;
            display: flex;
            align-items: center;
        }

        .category img {
            margin-right: 10px;
            height: 5rem;
        }

        table {
            border-collapse: collapse; /* Collapse borders between cells */
            width: 100%; /* Ensure the table spans the whole width of the container */
        }


        th, td {
            border: 1px solid black; /* Add border to table cells */
            padding: 8px; /* Add padding to cells */
        }

        th {
            background-color: #f2f2f2; 
        }
    </style>
</head>
<body>
    <div class="box-container">
        <table>
            <div class="category">
                <img src="../../images/admin_pic/manage_user.png" alt="Educators Applications">
                <h1>User Management</h1> 
                <input type="search" class="nav_search" id="user-search-inp" placeholder="Filter user by username" oninput="filterUser()">
            </div>
            <tr>
                <th>Email</th>
                <th>Name</th>
                <th>Role</th>
                <th>Status</th>
                <th>Remark</th>
                <th>Action</th>
            </tr>
            <?php foreach ($edu_ls as $row){ ?>
                <tr class="user-row">
                    <td><?php echo $row['email']; ?></td>
                    <td class="user-name"><?php echo $row['name']; ?></td>
                    <td><?php echo $row['role']; ?></td>
                    <td id="status"><?php echo $row['status']; ?></td>
                    <td><?php echo $row['remark']; ?></td>
                    <td>

                        <button 
                            id="banBtn" 
                            class="button"
                            onclick="uptFunc('banned', '<?php echo $row['user_id']; ?>')" 
                            <?php 
                                if ($row['status'] == 'banned') {
                                    echo 'disabled style="cursor:not-allowed; background-color: grey; padding: 5px; margin-bottom: 4px;"';
                                } else {
                                    echo 'style="background-color: red; padding: 5px; margin-bottom: 4px;"';
                                }
                            ?>
                        >
                            Ban
                        </button>
                        <br>
                        <button 
                            id="unbanBtn" 
                            class="button"
                            onclick="uptFunc('active', '<?php echo $row['user_id']; ?>')" 
                            <?php 
                                if ($row['status'] == 'active') {
                                    echo 'disabled style="cursor:not-allowed; background-color: grey; padding: 5px; margin-bottom: 4px;"';
                                } else {
                                    echo 'style="background-color: green; padding: 5px; margin-bottom: 4px;"';
                                }
                            ?>
                        >
                            Unban
                        </button>
                    </td>
                </tr>
            <?php } ?>
            <tr id="no-matched" hidden><td colspan="6" style="text-align: center;">No matched user</td></tr>
        </table>
    </div>

    <script>
        function uptFunc(newStatus, userId){
            var re = window.prompt("Add a remark for this action");
            if (re === null){ //close when press cancel
                return;
            }

            if (!re){
                re = null;
            }

            location.href = `../../modules/update_usr_status.php?new_status=${newStatus}&uid=${userId}&remark=${re}`;
        }

        function filterUser(){
            var inpVal = document.getElementById('user-search-inp').value.toUpperCase();
            var userRows = document.getElementsByClassName('user-row');
            var count = 0; 

            for (let i=0; i<userRows.length; i++){
                userName = userRows[i].getElementsByClassName('user-name')[0].innerHTML;
                if (userName.toUpperCase().indexOf(inpVal) > -1){
                    userRows[i].hidden = false;
                    count+=1;
                }else{
                    userRows[i].hidden = true;
                }
            }

            if (!count){ // if no result
                document.getElementById('no-matched').hidden = false;
            }else{
                document.getElementById('no-matched').hidden = true;
            }
        }
    </script>
</body>
</html>

<?php include '../../includes/footer.php'; ?>