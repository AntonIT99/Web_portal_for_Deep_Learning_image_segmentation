<?php
// We need to use sessions, so you should always start sessions using the below code.
session_start();
// If the user is not logged in redirect to the login page...
if(!isset($_SESSION['loggedin'])) {
	header('Location: login.html');
	exit;
}

$output="";

if (isset($_POST['status'])) 
{
	$output = shell_exec('systemctl status pbs_server.service');

} else if (isset($_POST['config'])) {
      
	$output = shell_exec('qmgr -c "list server"');
	    
} else if (isset($_POST['attributes'])) {
      
	$output = shell_exec('qmgr -c "p s"');
	    
} else if (isset($_POST['restart'])) {
	
	$output = shell_exec("./run.sh");
 
} else if (isset($_POST['queue'])) {
      
	$output = shell_exec('qmgr -c "list queue batch"');
 
} else if (isset($_POST['jobs'])) {
      
	$output = shell_exec('qstat -a');
 
} else if (isset($_POST['jobsdetails'])) {
      
	$output = shell_exec('qstat -f');
 
} else if (isset($_POST['nodes'])) {
      
	$output = shell_exec('qnodes');
 
} else if (isset($_POST['deeplearning']) or isset($_POST['deeplearningtorque'])) {
	
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
	
	//delete files in working directory
	$filescan1 = scandir('segmentation'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR);
	$filescan2 = scandir('segmentation'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'results'.DIRECTORY_SEPARATOR);
	$files1 = array();
	$files2 = array();

	for($i = 0, $n = 0; $i < count($filescan1); $i++) {
	    if ($filescan1[$i] != '.' && $filescan1[$i] != '..') {
		$files1[] = $filescan1[$i];
		$n++;
	    }
	}
	for($i = 0, $n = 0; $i < count($filescan2); $i++) {
	    if ($filescan2[$i] != '.' && $filescan2[$i] != '..') {
		$files2[] = $filescan2[$i];
		$n++;
	    }
	}

	for($i = 0; $i < count($files1); $i++) {
	    unlink('segmentation'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.$files1[$i]); 
	}

	for($i = 0; $i < count($files2); $i++) {
	    unlink('segmentation'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'results'.DIRECTORY_SEPARATOR.$files2[$i]); 
	}
	
	//add files from user directory to working directory
	$filescan0 = scandir('images'.DIRECTORY_SEPARATOR.$user_id.DIRECTORY_SEPARATOR);
	$files0 = array();

	//copy files from working directory to user directory

	for($i = 0, $n = 0; $i < count($filescan0); $i++) {
	    if ($filescan0[$i] != '.' && $filescan0[$i] != '..') {
		$files0[] = $filescan0[$i];
		$n++;
	    }
	}
	
	for($i = 0; $i < count($files0); $i++) {
	    copy('images'.DIRECTORY_SEPARATOR.$user_id.DIRECTORY_SEPARATOR.$files0[$i], 'segmentation'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.$files0[$i]); 
	}
	
	if (isset($_POST['deeplearningtorque']))
	{
	    $output = shell_exec('qsub job.sh');
	}
	else if(isset($_POST['deeplearning']))
	{
	    $output = shell_exec("python3 01_UNET_TF2_test.py");
	}
	
} else if (isset($_POST['test'])) {

	header('Location: test.php');
}

?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Home Page</title>
		<link href="style.css" rel="stylesheet" type="text/css">
		<link rel="stylesheet" href="fontawesome/css/all.css">
		<style> 
		      textarea 
		      {
			width: 100%;
			height: 250px;
			padding: 12px 20px;
			box-sizing: border-box;
			box-shadow: 1px 1px 1px #999;
			border: 2px solid #ccc;
			border-radius: 4px;
			background-color: #f8f8f8;
			font-size: 16px;
			resize: none;
			letter-spacing: 1px;
		      }
		</style>
	</head>
	<body class="loggedin">
		<nav class="navtop">
			<div>
				<h1>Web Interface for Image Processing with Torque</h1>
				<a href="home.php"><i class="fas fa-house"></i>Home</a>
				<a href="profile.php"><i class="fas fa-user-circle"></i>Profile</a>
				<a href="logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a>
			</div>
		</nav>
		<div class="content">
			<h2>Home Page</h2>
			<p>Welcome back, <?=$_SESSION['name']?>!</p>
			<h2>Torque PBS Log</h2>
			<textarea id="log" name="log" rows="50" cols="33"><?=$output?></textarea>
			<form action="" method="post">
			  <input type="hidden" value="t">
			  <input type="submit" value="Server Status" name="status">
			  <input type="submit" value="Server Config" name="config">
			  <input type="submit" value="Server & Queue Attributes" name="attributes">
			  <input type="submit" value="Queues" name="queue">
			  <input type="submit" value="Jobs" name="jobs">
			  <input type="submit" value="Jobs Details" name="jobsdetails">
			  <input type="submit" value="Nodes" name="nodes">
			  <input type="submit" value="Restart Server" name="restart">
			</form>
			<h2>Upload Files</h2>
			<form action="upload.php" method="POST" enctype="multipart/form-data">
			    <p>Select files to upload:
				<input type="file" name="files[]" multiple>
				<br>
			    </p>
				<input type="submit" name="upload" value="Upload">
				<input type="submit" name="delete" value="Delete uploaded files">
			</form>
			
			<h2>Process Images with Segmentation Algorithm</h2>
			<form action="" method="post">
			    <input type="hidden" value="t">
			    <input type="submit" name="deeplearningtorque" value="Submit Job Deep Learning">
			    <input type="submit" name="deeplearning" value="Execute Deep Learning">
			    <input type="submit" name="test" value="Debug Test">
			</form>
			<h2>Results</h2>
			<a href="download.php">
			    <input type="submit" value="Download results">
			</a>
			<a href="delete.php">
			    <input type="submit" value="Delete results">
			</a>
		</div>
	</body>
</html>
