<?php
	/*if (!empty($_SERVER['HTTPS']) && ('on' == $_SERVER['HTTPS'])) {
		$uri = 'https://';
	} else {
		$uri = 'http://';
	}*/
	
	//force https
	$uri = 'https://'.$_SERVER['HTTP_HOST'];
	
	session_start();
	
	// If the user is not logged in redirect to the login page otherwise access to the home page
	if(!isset($_SESSION['loggedin'])) {
		 header('Location: '.$uri.'/login.html');
		 exit;
	} else {
		header('Location: '.$uri.'/home.php');
	}
	exit;
?>