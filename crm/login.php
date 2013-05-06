<?php
/***********************************************************************************************************/
	
	require_once($_SERVER["DOCUMENT_ROOT"].'/crm/_inc/functions.php');

/***********************************************************************************************************/


	//LOGOUT THE USER INCASE THEY ARE ALREADY LOGGED IN
	bb_do_logout();
	
	//CLEAR KEEP SIGNED IN COOKIE
	setcookie("c1", "", time() - 3600);

	//INITIALSE LOGIN VALUES
	$email 				= '';
	$password 			= '';
	$remember			= '';
	$lostpassword 		= false;
	$resetpassword 		= false;
	$changepassword 	= false;
	$passwordchanged	= false;
	$errormessage 		= '';
		
	//PROCESS LOST PASSWORD LINK
	if(isset($_GET['lostpassword'])) {
		if($_GET['lostpassword']=='true') {
			$lostpassword = true;
		}
	}
	
	//PROCESS LOGIN
	if (isset($_POST['txtEmail']) && isset($_POST['txtPassword'])) {
		
		//GET THE LOGIN VALUES
		$email = bb_sanitise($_POST['txtEmail']);
		$password = bb_sanitise($_POST['txtPassword']);
		
		//LOGIN WITH THE SUPPLIED VALUES
		bb_do_login($email, $password);
		
		//IF THE LOGIN WAS SUCCESSFUL...
		if ($_SESSION["user"]<>'[NONE]') {
		
			//IF THE KEEP SIGNED IN OPTION WAS SUPPLIED...
			if(isset($_POST["chkRemember"])) {
			
				//IF THE KEEP SIGNED IN OPTION WAS TICKED...
				if($_POST["chkRemember"]=="on") {
			
					//BAKE A COOKIE
					$expire=time()+60*60*24*30*12; //1 YEAR
					setcookie("c1", bb_get_login_cookie($email), $expire);

				}
		
				//...OTHERWISE
				else {
		
					//DESTROY THE COOKIE
					setcookie("c1", "", time() - 3600);
				}
			}
		
			//...OTHERWISE
			else {
		
				//DESTROY THE COOKIE
				setcookie("c1", "", time() - 3600);
		
			}
		
			//IF WE WERE SENT TO LOGIN FROM ANOTHER PAGE
			if(isset($_SESSION["redirect_to"])) {
			
				//REDIRECT TO IT
				header('Location: '.$_SESSION["redirect_to"]);		
			
			}
			
			//..OTHERWISE
			else {
			
				//REDIRECT TO THE HOMEPAGE
				header('Location: http://forms.dev/crm/');
			}

		}
		
		//...OTHERWISE
		else {
		
			//SHOW ERROR MESSAGE
			$errormessage = 'The email or password you entered is incorrect.</a>';
		}
	}
	
	//PROCESS LOST PASSWORD
	if(isset($_POST['txtLostPassword'])) {
	
		//GET THE EMAIL ADDRESS
		$email = bb_sanitise($_POST['txtLostPassword']);
		
		//IF THE EMAIL ADDRESS IS A VALID USER
		if(bb_user_exists($email)) {
		
			//SEND A RESET PASSWORD LINK BY EMAIL
			bb_send_reset_password_link($email);
			$resetpassword = true;
		}
		
		//...OTHERWISE
		else {
		
			//SHOW AN ERROR MESSAGE
			$lostpassword = true;
			$errormessage = 'The email you entered is incorrect.';
		}	
	}
	
	//PROCESS RESET PASSWORD LINK
	if(isset($_GET['key']) && isset($_GET['email'])) {

		//GET THE VALUES
		$key = bb_sanitise($_GET['key']);
		$email = bb_sanitise($_GET['email']);
		
		//IF THE KEY AND EMAIL ADDRESS MATCH
		if($key==bb_generate_hash($email)) {
		
			//ALLOW USER TO CHANGE PASSWORD
			$changepassword = true;
		}
	}
	
	//PROCESS CHANGE PASSWORD
	if(isset($_POST['txtNewPasswordEmail']) && isset($_POST['txtNewPassword1']) && isset($_POST['txtNewPassword2'])) {

		//IF THE TWO PASSWORDS ENTERED MATCH...
		if(bb_sanitise($_POST['txtNewPassword1'])==bb_sanitise($_POST['txtNewPassword2'])) {

			//GET THE VALUES
			$email = bb_sanitise($_POST['txtNewPasswordEmail']);
			$password = bb_sanitise($_POST['txtNewPassword1']);

			//IF THE EMAIL ADDRESS IS A VALID USER
			if(bb_user_exists("$email")) {
			
				//SET THE USERS PASSWORD TO THE SUPPLIED VALUE
				bb_set_password_hash($email, $password);
				$passwordchanged=true;
			}
		}
		
		//...OTHERWISE
		else {
		
			//SHOW AN ERROR MESSAGE
			$changepassword = true;
			$errormessage = 'The passwords you entered do not match.';
		}
	}

/***********************************************************************************************************/
?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="title" content="Bamboo Forms" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Bamboo Forms</title>
		<script type="text/javascript" src="_scripts/jquery.js"></script>
		<script type="text/javascript" src="_scripts/jquery.easing.js"></script>
		<!-- BEGIN BOOTSTRAP -->
		<link href="_bootstrap/css/bootstrap.css" rel="stylesheet" type="text/css" />
		<link href="_bootstrap/css/bootstrap-responsive.css" rel="stylesheet">
		<script type="text/javascript" src="_bootstrap/js/bootstrap.min.js"></script>

		<link href="_bootstrap/css/flat-ui.css" rel="stylesheet" type="text/css" />
	    <script src="_bootstrap/js/jquery-1.8.2.min.js"></script>
	    <script src="_bootstrap/js/jquery-ui-1.10.0.custom.min.js"></script>
	    <script src="_bootstrap/js/jquery.dropkick-1.0.0.js"></script>
	    <script src="_bootstrap/js/custom_checkbox_and_radio.js"></script>
	    <script src="_bootstrap/js/custom_radio.js"></script>
	    <script src="_bootstrap/js/jquery.tagsinput.js"></script>
	    <script src="_bootstrap/js/bootstrap-tooltip.js"></script>
	    <script src="_bootstrap/js/jquery.placeholder.js"></script>
	    <script src="http://vjs.zencdn.net/c/video.js"></script>
	    <script src="_bootstrap/js/application.js"></script>
		<!-- END BOOTSTRAP -->
		<link href="_css/screen.css" rel="stylesheet" type="text/css" />
		<script type="text/javascript">
		//<![CDATA[		
			$(document).ready(function(){
<?php if ($lostpassword) { ?>
				$('#txtLostPassword').focus();
<?php } ?>
<?php if (!$lostpassword && !$resetpassword && !$changepassword && !$passwordchanged){ ?>
				$('#txtEmail').focus();
<?php } ?>		
			});
			
			function doLogin(){
				$('#login-form').submit();
			}
			
			function doReset() {
				$('#lostpassword-form').submit();
			}
			
			function doContinue() {
				$('#resetpassword-form').submit();
			}

			function doChange() {
				$('#changepassword-form').submit();
			}

			function doChanged() {
				document.location.href='login.php';
			}
			
			function txtPasswordKeyDown(e) {
				if(e.keyCode==13) doLogin();
			}

			function txtNewPassword1KeyDown(e) {
				if(e.keyCode==13) $('#txtNewPassword2').focus();
			}
			
			function txtNewPassword2KeyDown(e) {
				if(e.keyCode==13) doChange();
			}

			function txtLostPasswordKeyDown(e) {
				if(e.keyCode==13) doReset();
			}
		//]]>
		</script>
	</head>
	<body>
		<div class="container-fluid">

<?php if (!$lostpassword && !$resetpassword && !$changepassword && !$passwordchanged) { ?>
					<div id="login" class="login-form">
						<form method="post" action="login.php" id="login-form">
							<h1>Login</h1>
							<div class="control-group">
								<input class="login-field" placeholder="Enter your email address" id="txtEmail" name="txtEmail" type="email" size="22" value="<?php echo $email ?>" />
								<label class="login-field-icon fui-man-16" for="txtEmail"></label>	
							</div>
							<div class="control-group">
								<input class="login-field" placeholder="Enter your password" id="txtPassword" name="txtPassword" size="22" type="password" value="<?php echo $password ?>" onkeydown="txtPasswordKeyDown(event)"/>
								<label class="login-field-icon fui-lock-16" for="txtEmail"></label>	
							</div>	
							<label class="checkbox" for="chkRemember">
								<span class="icon"></span>
								<span class="icon-to-fade"></span>
								<input type="checkbox" id="chkRemember" name="chkRemember" <?php if($remember=="on") echo 'checked="checked" '; ?>>
								Remember me
		 					</label>
							<p
							 class="text-error"><?php echo $errormessage; ?><br/><br/></p>
							<a href="?lostpassword=true">Lost your password ?</a>
							<a class="btn btn-primary" href="javascript:doLogin()">Login</a>
						</form>
					</div>
<?php } ?>		

<?php if ($lostpassword) { ?>
			<div id="lostpassword" class="login-form">
				<form method="post" action="login.php" id="lostpassword-form">
					<h1>Lost Password</h1>
					<p>Enter your email address below to reset your password. A link enabling you to reset your password will be emailed to you.<br/><br/></p>
					<div class="control-group">
						<input class="login-field" placeholder="Enter your email address" id="txtLostPassword" name="txtLostPassword" type="email" size="22" value="<?php echo $email ?>" />
						<label class="login-field-icon fui-man-16" for="txtEmail"></label>	
					</div>
					<p class="text-error"><?php echo $errormessage; ?><br/><br/></p>
					<a class="btn btn-danger" href="?lostpassword=false">Cancel</a>
					<a class="btn btn-primary" href="javascript:doReset()" >Reset Password</a>
				</form>
			</div>
<?php } ?>

<?php if ($resetpassword) { ?>
			<div id="resetpassword" class="login-form">
				<form method="post" action="login.php" id="resetpassword-form">
					<h1>Reset Password</h1>
					<p>An email has been sent to you with a link to reset your password.<br/><br/></p>
					<a class="btn btn-primary" href="javascript:doContinue()" >Continue</a>
				</form>
			</div>
<?php } ?>

<?php if ($changepassword) { ?>		
			<div id="changepassword" class="login-form">
					<form method="post" action="login.php" id="changepassword-form">
						<h1>Change Password</h1>
						<input id="txtNewPasswordEmail" name="txtNewPasswordEmail" type="hidden" value="<?php echo $email; ?>" />

						<div class="control-group">
							<input class="login-field" placeholder="Enter new password" id="txtNewPassword1" name="txtNewPassword1" size="22" type="password" onkeydown="txtPassword1KeyDown(event)"/>
							<label class="login-field-icon fui-lock-16" for="txtNewPassword1"></label>	
						</div>	

						<div class="control-group">
							<input class="login-field" placeholder="Confirm new password" id="txtNewPassword2" name="txtNewPassword2" size="22" type="password" onkeydown="txtPassword2KeyDown(event)"/>
							<label class="login-field-icon fui-lock-16" for="txtNewPassword2"></label>	
						</div>	

						<p class="text-error"><?php echo $errormessage; ?></p>
						<a class="btn btn-danger" href="login.php">Cancel</a>
						<a class="btn btn-primary" href="javascript:doChange()" >Change Password</a>
					</form>
			</div>
<?php } ?>

<?php if ($passwordchanged) { ?>
			<div id="passwordchanged" class="login-form">
				<form method="post" action="login.php" id="resetpassword-form">
					<h1>Password Changed</h1>
						<p>Your password has been changed. You can now log in with your new password.<br/><br/></p>
						<a class="btn btn-primary" href="javascript:doChanged()" >Login</a>
				</form>
			</div>
<?php } ?>
		</div>
	</body>
</html>