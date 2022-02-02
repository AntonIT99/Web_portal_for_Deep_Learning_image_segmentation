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

/* creates a compressed zip file */
function create_zip($files = array(), $filenames = array(), $destination = '') {

    //if we have files...
    if(count($files)) {
    
	//create the archive
	$zip = new ZipArchive;
	$res = $zip->open($destination, ZipArchive::CREATE | ZipArchive::OVERWRITE);
	if ($res === TRUE) {
	    for($i = 0; $i < count($files); $i++) {
		$zip->addFile($files[$i], $filenames[$i]);
		//echo $files[$i];
	    }
	    $zip->close();
	} else {
	    echo "Could not open archive: Error Code ".$res;
	}

	//check to make sure the file exists
	return file_exists($destination);
    }
}

$filescan0 = scandir('segmentation'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'results'.DIRECTORY_SEPARATOR);
$files0 = array();

//copy files from working directory to user directory

for($i = 0, $n = 0; $i < count($filescan0); $i++) {
    if ($filescan0[$i] != '.' && $filescan0[$i] != '..') {
	$files0[] = $filescan0[$i];
	$n++;
    }
}

for($i = 0; $i < count($files0); $i++) {
    copy('segmentation'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'results'.DIRECTORY_SEPARATOR.$files0[$i], 'images'.DIRECTORY_SEPARATOR.$user_id.DIRECTORY_SEPARATOR.'results'.DIRECTORY_SEPARATOR.$files0[$i]); 
}

//prepare files to zip

$filescan = scandir('images'.DIRECTORY_SEPARATOR.$user_id.DIRECTORY_SEPARATOR.'results'.DIRECTORY_SEPARATOR);
$files = array();
$files_path = array();

for($i = 0, $n = 0; $i < count($filescan); $i++) {
    if ($filescan[$i] != '.' && $filescan[$i] != '..' && $filescan[$i] != 'results.zip') {
	$files[] = $filescan[$i];
	$n++;
    }
}

for($i = 0; $i < count($files); $i++) {
    $files_path[] = 'images'.DIRECTORY_SEPARATOR.$user_id.DIRECTORY_SEPARATOR.'results'.DIRECTORY_SEPARATOR.$files[$i];
}

$zip_name = 'images'.DIRECTORY_SEPARATOR.$user_id.DIRECTORY_SEPARATOR.'results'.DIRECTORY_SEPARATOR.'results.zip';
$result = create_zip($files_path, $files, $zip_name);


ob_clean();
header('Content-Type: application/zip');
header('Content-disposition: attachment; filename=results.zip');
header('Content-Length: '.filesize($zip_name));
readfile($zip_name);


?>
 
