<?php
//----------------------------------------------------------------------------------------------------------------

	// Requirements
	require_once('_inc/functions.php');

//----------------------------------------------------------------------------------------------------------------

	// If the current user is not admin
	if($_SESSION['user']!='[ADMIN]') {

		// Redirect to the login page
		header('Location: ' . SITE_URL . '/login.php');

	}

	// Establish the user
	$user = '';
	if(isset($_GET['user'])) $user = $_GET['user'];

	// If no user is specified
	if($user=="") {

		// Redirect to the login page
		header('Location: ' . SITE_URL . '/login.php');

	}

//----------------------------------------------------------------------------------------------------------------
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="title" content="Bamboo Forms" />
		<title>Bamboo Forms</title>
		<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Open+Sans:400italic,700italic,400,700)">
		<link href="<?php echo SITE_URL; ?>_css/screen.css" rel="stylesheet" type="text/css" />
		<script type="text/javascript" src="<?php echo SITE_URL; ?>_scripts/jquery.js"></script>
		<script type="text/javascript" src="<?php echo SITE_URL; ?>_scripts/jquery.easing.js"></script>
		<script type="text/javascript">
		//<![CDATA[
			$(document).ready(function(){
			});
		//]]>
		</script>
	</head>
	<body>

		<div class="control-bar">
			<a class="brand" href="#"><img alt="" src="<?php echo SITE_URL; ?>_images/logo.png" /></a>
			<a class="user-logout rounded button right" href="<?php echo SITE_URL; ?>login.php">Log out</a>
			<span class="user-name active right"><?php echo $_SESSION["user"];?></span>
		</div>

