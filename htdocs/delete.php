<?php
// We need to use sessions, so you should always start sessions using the below code.
session_start();
// If the user is not logged in redirect to the login page...
if(!isset($_SESSION['loggedin'])) {
	header('Location: login.html');
	exit;
}

$DATABASE_HOST = 'localhost';
$DATABASE_USER = 'root';
$DATABASE_PASS = 'admin';
$DATABASE_NAME = 'phplogin';
$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
if (mysqli_connect_errno()) {
	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}
$stmt = $con->prepare('SELECT user_id FROM accounts WHERE id = ?');
// In this case we can use the account ID to get the account info.
$stmt->bind_param('i', $_SESSION['id']);
$stmt->execute();
$stmt->bind_result($user_id);
$stmt->fetch();
$stmt->close();

$filescan = scandir('images'.DIRECTORY_SEPARATOR.$user_id.DIRECTORY_SEPARATOR.'results'.DIRECTORY_SEPARATOR);
$files = array();

for($i = 0, $n = 0; $i < count($filescan); $i++) {
    if ($filescan[$i] != '.' && $filescan[$i] != '..') {
	$files[] = $filescan[$i];
	$n++;
    }
}

for($i = 0; $i < count($files); $i++) {
    unlink('images'.DIRECTORY_SEPARATOR.$user_id.DIRECTORY_SEPARATOR.'results'.DIRECTORY_SEPARATOR.$files[$i]); 
}

echo "Results deleted <br />";
header('Refresh: 3; URL=https://torque-server/home.php');

?>