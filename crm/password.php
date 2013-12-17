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
	if(isset($_POST['user'])) $user = $_POST['user'];
	if(isset($_GET['user'])) $user = $_GET['user'];

	// If no user is specified
	if($user=="") {

		// Redirect to the login page
		header('Location: ' . SITE_URL . '/login.php');

	}

	//Process the form
	$errormessage = '';
	if (isset($_POST['txtPassword']) && isset($_POST['txtConfirmPassword'])) {
		$password = bb_sanitise($_POST['txtPassword']);
		$confirmation = bb_sanitise($_POST['txtConfirmPassword']);
		if($password=='' || $confirmation=='') {
			$errormessage = 'You must fill in both boxes';
		} else {
			if($password!=$confirmation) {
				$errormessage = 'The passwords you have entered do not match';
			} else {
				// Update the users password
				bb_set_password_hash($user, $password);
				header('Location: ' . SITE_URL);
			}
		}
	}


//----------------------------------------------------------------------------------------------------------------

	function render_breadcrumbs() {

		global $user;
		$mode='folder';

		$crumbs = array($user);

		if($_SESSION['user']=='[ADMIN]') array_unshift($crumbs, "/");

		$crumb_count = sizeof($crumbs);
		$crumb_index = 1;

		echo '<ul class="breadcrumbs">';

		foreach ($crumbs as $crumb) {
			if ($crumb_index<$crumb_count) {
				$crumb_path = SITE_URL . FORM_PATH;
				for ($i=0; $i <$crumb_index ; $i++) {
					$crumb_path.="/".$crumbs[$i];
				}
				echo '<li><a class="rounded button" href="' . $crumb_path . '/">' . $crumb . '</a></li>';
			}
			else {
				echo '<li class="active">' . $crumb . '</li>';
			}
			$crumb_index++;
		}

		echo '</ul>';

	}

//----------------------------------------------------------------------------------------------------------------

?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="title" content="Bamboo Forms" />
		<title><?php echo SITE_TITLE?></title>
		<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Open+Sans:400italic,700italic,400,700)">
		<link href="<?php echo SITE_URL; ?>_css/screen.css" rel="stylesheet" type="text/css" />
		<script type="text/javascript" src="<?php echo SITE_URL; ?>_scripts/jquery.js"></script>
		<script type="text/javascript" src="<?php echo SITE_URL; ?>_scripts/jquery.easing.js"></script>
		<script type="text/javascript">
		//<![CDATA[

			function doChange(){
				$('#password-form').submit();
			}

			function txtConfirmPasswordKeyDown(e) {
				if(e.keyCode==13) doLogin();
			}

			function txtPasswordKeyDown(e) {
				if(e.keyCode==13) $('#txtConfirmPassword').focus();
			}

		//]]>
		</script>
	</head>
	<body>

		<div class="control-bar">
			<a class="brand" href="#"><img alt="" src="<?php echo SITE_URL; ?>_images/logo.png" /></a>
			<a class="user-logout rounded button right" href="<?php echo SITE_URL; ?>login.php">Log out</a>
			<span class="user-name active right"><?php echo $_SESSION["user"];?></span>
			<?php render_breadcrumbs(); ?>
		</div>

		<div class="document">
			<h2>Change Password</h2>
			<form method="post" action="password.php" id="password-form">
				<input type="hidden" name="user" value="<?php echo $user; ?>" />
				<div class="question">
					<div class="prompts">
						<label>New Password</label>
					</div>
					<div class="inputs">
						<input class="rounded" placeholder="Enter New Password" id="txtPassword" name="txtPassword" type="password" size="22" value="" onkeydown="txtPasswordKeyDown(event)"/>
					</div>
				</div>
				<div class="question">
					<div class="prompts">
						<label>Confirm Password</label>
					</div>
					<div class="inputs">
						<input class="rounded" placeholder="Confirm New Password" id="txtConfirmPassword" name="txtConfirmPassword" type="password" size="22" value="" onkeydown="txtConfirmPasswordKeyDown(event)"/>
					</div>
				</div>
 				<div class="question">
					<p class="error"><?php echo $errormessage; ?></p>
					<div class="prompts">&nbsp;</div>
					<div class="inputs">
						<a class="rounded button primary right" href="javascript:doChange()">Change Password</a>
						<a class="rounded button right" href="<?php echo SITE_URL; ?>">Cancel</a>
					</div>
				</div>
			</form>
			<div class="clearfix"></div>
		</div>

	</body>
</html>