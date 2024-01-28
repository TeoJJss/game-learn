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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Educators Approval Console</title>
</head>
<body>
    <table>
        <tr>
            <th>Email</th>
            <th>Name</th>
            <th>Role</th>
            <th>Status</th>
            <th>Remark</th>
            <th>Action</th>
        </tr>
        <?php foreach ($edu_ls as $row){ ?>
            <tr>
                <td><?php echo $row['email']; ?></td>
                <td><?php echo $row['name']; ?></td>
                <td><?php echo $row['role']; ?></td>
                <td id="status"><?php echo $row['status']; ?></td>
                <td><?php echo $row['remark']; ?></td>
                <td><button id="banBtn" onclick="uptFunc('banned', '<?php echo $row['user_id']; ?>')" <?php if ($row['status'] == 'banned') {echo 'disabled style="cursor:not-allowed"';}?>>Ban</button>
                    <br><button id="unbanBtn" onclick="uptFunc('active', '<?php echo $row['user_id']; ?>')" <?php if ($row['status'] == 'active') {echo 'disabled style="cursor:not-allowed"';}?>>Unban</button>
                </td>
            </tr>
        <?php } ?>
    </table>
    <script>
        function uptFunc(newStatus, userId){
            var re = window.prompt("Add a remark for this action");
            if (!re){
                re = null;
            }

            location.href = `../../modules/update_status.php?new_status=${newStatus}&uid=${userId}&remark=${re}`;
        }
    </script>
</body>
</html>