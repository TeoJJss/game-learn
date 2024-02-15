<?php
if (file_exists('../modules/error_handler.php')) {
    $base = "../";
    include '../modules/error_handler.php';
} else if (file_exists('../../modules/error_handler.php')) {
    $base = "../../";
    include '../../modules/error_handler.php';
} else {
    $base = "./";
    include './modules/error_handler.php';
}
echo "<link rel='shortcut icon' type='image/png' href='$base\images/favicon.png'>";
$host = "localhost";
$user = "root";
$password = "";
$database = "math_gamelearn";
$conn = mysqli_connect($host, $user, $password, $database);
if (mysqli_connect_errno()) {
    die;
}

$base_url = "http://127.0.0.1:5000/login-api";

$ch = curl_init("$base_url/");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_exec($ch);

if (curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200) {
    trigger_error("No active authentication backend system!");
    die;
}

function check_ticket()
{
    global $base_url;
    session_start();

    if (isset($_SESSION['ticket'])) {
        if ($_SESSION['ticket'] != '') {
            $ticket = $_SESSION['ticket'];

            $ch = curl_init("$base_url/check-ticket?ticket=$ticket");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            $response = json_decode(curl_exec($ch), true);

            if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 202) {
                $role = $response['data']['role'];
                session_write_close();
                return $role;
            }
        }
    }
    session_write_close();
    return False;
}
?>