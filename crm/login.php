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
		<title>Bamboo Forms</title>
		<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Open+Sans:400italic,700italic,400,700)">
		<link href="/crm/_css/screen.css" rel="stylesheet" type="text/css" />
		<script type="text/javascript" src="/crm/_scripts/jquery.js"></script>
		<script type="text/javascript" src="/crm/_scripts/jquery.easing.js"></script>
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
		<div class="container">
			<div class="control-bar">
				<a class="brand" href="#"><img alt="" src="/crm/_images/logo.png" /></a>
			</div>
			<div class="document">
				<h2>Login</h2>
<?php if (!$lostpassword && !$resetpassword && !$changepassword && !$passwordchanged) { ?>
					<form method="post" action="login.php" id="login-form">
						<div class="question">
							<div class="prompts">
								<label>Email Address</label>
							</div>
							<div class="inputs">
								<input class="rounded" placeholder="Enter your email address" id="txtEmail" name="txtEmail" type="email" size="22" value="<?php echo $email ?>" />
							</div>
						</div>
						<div class="question">
							<div class="prompts">
								<label>Password</label>
							</div>
							<div class="inputs">
								<input class="rounded" placeholder="Enter your password" id="txtPassword" name="txtPassword" size="22" type="password" value="<?php echo $password ?>" onkeydown="txtPasswordKeyDown(event)"/>
							</div>
						</div>
						<div class="question">
							<div class="prompts">
								&nbsp;
							</div>
							<div class="inputs">
								<label class="checkbox" for="chkRemember">
									<input type="checkbox" id="chkRemember" name="chkRemember" <?php if($remember=="on") echo 'checked="checked" '; ?>>
									Remember me
								</label>
							</div>
		 				</div>
		 				<div class="question">
							<p class="error"><?php echo $errormessage; ?></p>
							<div class="prompts">&nbsp;</div>
							<div class="inputs">
								<a class="rounded button primary right" href="javascript:doLogin()">Login</a>
								<a class="rounded button right" href="?lostpassword=true">Lost your password ?</a>
							</div>
						</div>
					</form>
<?php } ?>

<?php if ($lostpassword) { ?>
				<div id="lostpassword" class="document">
					<form method="post" action="login.php" id="lostpassword-form">
						<question>
							Enter your email address below to reset your password. A link enabling you to reset your password will be emailed to you.<br/><br/>
						</question>
						<div class="question">
							<div class="prompts">Email Address</div>
							<div class="inputs">
								<input placeholder="Enter your email address" id="txtLostPassword" name="txtLostPassword" type="email" size="22" value="<?php echo $email ?>" />
							</div>
						<p class="error"><?php echo $errormessage; ?><br/><br/></p>
						</div>
						<div class="question">
							<div class="prompts">&nbsp;</div>
							<div class="questions">
								<a class="rounded button primary right" href="javascript:doReset()" >Reset Password</a>
								<a class="rounded button right" href="?lostpassword=false">Cancel</a>
							</div>
						</div>
					</form>
				</div>
<?php } ?>

<?php if ($resetpassword) { ?>
				<div id="resetpassword" class="document">
					<form method="post" action="login.php" id="resetpassword-form">
						<div class="question">
							An email has been sent to you with a link to reset your password.<br/><br/>
						<div class="question">
							<div class="prompts">&nbsp;</div>
							<div class="questions">
								<a class="rounded button primary right" href="javascript:doContinue()" >Continue</a>
							</div>
					</form>
				</div>
<?php } ?>

<?php if ($changepassword) { ?>
				<div id="changepassword" class="document">
					<form method="post" action="login.php" id="changepassword-form">
						<div class="question">
							<input id="txtNewPasswordEmail" name="txtNewPasswordEmail" type="hidden" value="<?php echo $email; ?>" />
							<div class="prompts">
								<label>New Password</label>
							</div>
							<div class="inputs">
								<input placeholder="Enter new password" id="txtNewPassword1" name="txtNewPassword1" size="22" type="password" onkeydown="txtPassword1KeyDown(event)"/>								<label class="login-field-icon fui-lock-16" for="txtNewPassword1"></label>
							</div>
						</div>
							<div class="prompts">
								<label>Confirm Password</label>
							</div>
							<div class="inputs">
								<input placeholder="Confirm new password" id="txtNewPassword2" name="txtNewPassword2" size="22" type="password" onkeydown="txtPassword2KeyDown(event)"/>								<label class="login-field-icon fui-lock-16" for="txtNewPassword1"></label>
							</div>
						</div>
						<div class="question">
							<p class="error"><?php echo $errormessage; ?></p>
						</div>
						<div class="question">
							<div class="prompts">&nbsp;</div>
							<div class="inputs">
								<a class="rounded button primary right" href="javascript:doChange()" >Change Password</a>
								<a class="rounded button right" href="login.php">Cancel</a>
							</div>
						</div>
					</form>
				</div>
<?php } ?>

<?php if ($passwordchanged) { ?>
				<div id="passwordchanged" class="document">
					<form method="post" action="login.php" id="resetpassword-form">
						<div class="question">
							<p>Your password has been changed. You can now log in with your new password.<br/><br/></p>
							<a class="rounded button primary right" href="javascript:doChanged()" >Login</a>
						</div>
					</form>
			</div>
<?php } ?>
			<div class="clearfix"></div>
			</div>
		</div>
	</body>
</html>