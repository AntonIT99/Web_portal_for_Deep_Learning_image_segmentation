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

// Check if form was submitted
if(isset($_POST['upload'])) {
 
    // Configure upload directory and allowed file types
    $upload_dir = 'images'.DIRECTORY_SEPARATOR.$user_id.DIRECTORY_SEPARATOR;
    $allowed_types = array('jpg', 'png', 'jpeg', 'gif');
     
    // Define maxsize for files: 10MB
    $maxsize = 10 * 1024 * 1024;
 
    // Checks if user sent an empty form
    if(!empty(array_filter($_FILES['files']['name']))) {
 
        // Loop through each file in files[] array
        foreach ($_FILES['files']['tmp_name'] as $key => $value) {
             
            $file_tmpname = $_FILES['files']['tmp_name'][$key];
            $file_name = $_FILES['files']['name'][$key];
            $file_size = $_FILES['files']['size'][$key];
            $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
 
            // Set upload file path
            $filepath = $upload_dir.$file_name;
 
            // Check file type is allowed or not
            if(in_array(strtolower($file_ext), $allowed_types)) {
 
                // Verify file size
                if ($file_size > $maxsize)        
                    echo "Error: File size is larger than the allowed limit.";
                    header('Refresh: 3; URL=https://torque-server/home.php');
 
                // If file with name already exist then append time in
                // front of name of the file to avoid overwriting of file
                if(file_exists($filepath)) {
                    $filepath = $upload_dir.time().$file_name;
                     
                    if( move_uploaded_file($file_tmpname, $filepath)) {
                        echo "{$file_name} successfully uploaded <br />";
                        header('Refresh: 3; URL=https://torque-server/home.php');
                    }
                    else {                    
                        echo "Error uploading {$file_name} <br />";
                        header('Refresh: 3; URL=https://torque-server/home.php');
                    }
                }
                else {
		    
		    // Create user directory if it does not exist
		    if(!is_dir($upload_dir)) {
			mkdir($upload_dir);
		    }
		    
		    // Create results directory if it does not exist
		    if(!is_dir($upload_dir.DIRECTORY_SEPARATOR.'results'.DIRECTORY_SEPARATOR)) {
			mkdir($upload_dir.DIRECTORY_SEPARATOR.'results'.DIRECTORY_SEPARATOR);
		    }
                 
                    if( move_uploaded_file($file_tmpname, $filepath)) {
                        echo "{$file_name} successfully uploaded <br />";
                        header('Refresh: 3; URL=https://torque-server/home.php');
                    }
                    else {                    
                        echo "Error uploading {$file_name} <br />";
                        header('Refresh: 3; URL=https://torque-server/home.php');
                    }
                }
            }
            else {
                 
                // If file extension not valid
                echo "Error uploading {$file_name} ";
                echo "({$file_ext} file type is not allowed)<br / >";
                header('Refresh: 3; URL=https://torque-server/home.php');
            }
        }
    }
    else {
         
        // If no files selected
        echo "No files selected.";
    }
}


if(isset($_POST['delete'])) {

$filescan = scandir('images'.DIRECTORY_SEPARATOR.$user_id.DIRECTORY_SEPARATOR);
$files = array();

for($i = 0, $n = 0; $i < count($filescan); $i++) {
    if ($filescan[$i] != '.' && $filescan[$i] != '..') {
	$files[] = $filescan[$i];
	$n++;
    }
}

for($i = 0; $i < count($files); $i++) {
    unlink('images'.DIRECTORY_SEPARATOR.$user_id.DIRECTORY_SEPARATOR.$files[$i]); 
}

echo "Uploaded files deleted <br />";
header('Refresh: 3; URL=https://torque-server/home.php');

}
?>