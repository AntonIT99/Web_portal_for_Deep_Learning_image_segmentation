<?php
// Change this to your connection info.
$DATABASE_HOST = 'localhost';
$DATABASE_USER = 'root';
$DATABASE_PASS = 'admin';
$DATABASE_NAME = 'phplogin';
// Try and connect using the info above.
$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);

function createRandomString($length) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

if (mysqli_connect_errno()) {
	// If there is an error with the connection, stop the script and display the error.
	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}
// Now we check if the data was submitted, isset() function will check if the data exists.
if (!isset($_POST['username'], $_POST['password'], $_POST['email'])) {
	// Could not get the data that should have been sent.
	exit('Please complete the registration form!');
	header('Refresh: 3; URL=https://torque-server/register.html');
}
// Make sure the submitted registration values are not empty.
if (empty($_POST['username']) || empty($_POST['password']) || empty($_POST['email'])) {
	// One or more values are empty.
	exit('Please complete the registration form');
	header('Refresh: 3; URL=https://torque-server/register.html');
}
//Email Validation
if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
	exit('Email is not valid!');
	header('Refresh: 3; URL=https://torque-server/register.html');
}
//Invalid Characters Validation
if (preg_match('/^[a-zA-Z0-9]+$/', $_POST['username']) == 0) {
    exit('Username is not valid!');
    header('Refresh: 3; URL=https://torque-server/register.html');
}
//Character Length Check
if (strlen($_POST['password']) > 20 || strlen($_POST['password']) < 4) {
	exit('Password must be between 4 and 20 characters long!');
	header('Refresh: 3; URL=https://torque-server/register.html');
}
// We need to check if the account with that username exists.
if ($stmt = $con->prepare('SELECT id, password FROM accounts WHERE username = ?')) {
	// Bind parameters (s = string, i = int, b = blob, etc), hash the password using the PHP password_hash function.
	$stmt->bind_param('s', $_POST['username']);
	$stmt->execute();
	$stmt->store_result();
	// Store the result so we can check if the account exists in the database.
	if ($stmt->num_rows > 0) {
		// Username already exists
		echo 'Username exists, please choose another!';
		header('Refresh: 3; URL=https://torque-server/register.html');
	} else {
		  // Username doesnt exists, insert new account
		  if ($stmt = $con->prepare('INSERT INTO accounts (username, user_id, password, email) VALUES (?, ?, ?, ?)')) {
			  // We do not want to expose passwords in our database, so hash the password and use password_verify when a user logs in.
			  $user_id = createRandomString(5);
			  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
			  $stmt->bind_param('ssss', $_POST['username'], $user_id, $password, $_POST['email']);
			  $stmt->execute();
			  echo 'You have successfully registered, you can now login!';
			  header('Refresh: 3; URL=https://torque-server/login.html');
		  } else {
			  // Something is wrong with the sql statement, check to make sure accounts table exists with all 3 fields.
			  echo 'Could not prepare statement!';
			  header('Refresh: 3; URL=https://torque-server/register.html');
		  }
		}
	$stmt->close();
} else {
	// Something is wrong with the sql statement, check to make sure accounts table exists with all 3 fields.
	echo 'Could not prepare statement!';
	header('Refresh: 3; URL=https://torque-server/register.html');
}
$con->close();
?>