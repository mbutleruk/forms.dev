<?php
//----------------------------------------------------------------------------------------------------------------

	// Site URL
	define('SITE_URL', '/crm/');

//----------------------------------------------------------------------------------------------------------------

	// Requirements
	require_once($_SERVER["DOCUMENT_ROOT"]. SITE_URL . '_inc/bb_forms.php');
	require_once($_SERVER["DOCUMENT_ROOT"]. SITE_URL . '_inc/bb_form_fieldsets.php');
	require_once($_SERVER["DOCUMENT_ROOT"]. SITE_URL . '_inc/bb_form_fields.php');

//----------------------------------------------------------------------------------------------------------------

	// Definitions
	define('SITE_TITLE', 			'Bamboo Forms');
	define('SITE_DOMAIN', 			'forms.dev');
	define('FORM_PATH', 			'forms');
	define('TEMPLATE_PATH', 		'templates');
	define('SITE_EMAIL_ADDRESS', 	'website@forms.dev');
	define('HASH_KEY', 				'wT3C[pzVbx2ay*m8fNnSUI|hJWZ`(>sL76901Ql5FH4/%o._DO');
	define('HASH_SALT', 			'=Uk5lC?9v6iuX8OG$1hD)xyQP2JS7A!0+NRV[w3ct4{LMz;I>T');
	define('PASSWORD_RESET_EMAIL',	'You have requested that the password be reset for {email}. To reset this password please click the link below. <br/><br/>{link}');
	define('ADMIN_USERNAME', 		'admin');
	define('ADMIN_PASSWORD', 		'admin');

//----------------------------------------------------------------------------------------------------------------

	// Turn on error reporting
	ini_set('display_errors',1);
	error_reporting(E_ALL);

	// Initialise the session
	session_start();

	// Initialise the user
	if(!isset($_SESSION["user"])) $_SESSION["user"] = "[NONE]";
	if($_SESSION["user"] == "") $_SESSION["user"] = "[NONE]";

	// If the user is not logged in...
	if($_SESSION["user"] == "[NONE]") {

		// If a login cookie is set...
		if(isset($_COOKIE["c1"])) {

			// Attempt to log in with the cookie
			bb_do_login_from_cookie($_COOKIE["c1"]);
		}

		// If the user is still not logged in and we are not already on the login page...
		if($_SESSION["user"] == "[NONE]" && bb_get_current_page()!='login.php') {

			// Redirect to the login page
			$_SESSION["redirect_to"] = $_SERVER['REQUEST_URI'];
			header('Location: /crm/login.php');

		}

	}

//----------------------------------------------------------------------------------------------------------------

	function bb_sanitise($value='') {

		// Strip invalid characters and trim the value
		$result = str_replace('"', '', trim($value));
		$result = str_replace("'", '', $result);
		$result = str_replace(';', '', $result);
		$result = str_replace('<', '', $result);
		$result = str_replace('>', '', $result);

		// Return the result
		return $result;

	}

//----------------------------------------------------------------------------------------------------------------

	function bb_get_login_cookie($email='[NONE]') {

		// Generate the cookie which consists of the email address and the hashed email address seperated by a pipe
		if($email=='') $email = '[NONE]';
		$result = $email."|";
		$result.= bb_get_password_hash($email);

		// Return the result
		return $result;

	}

//----------------------------------------------------------------------------------------------------------------

	function bb_do_login($email='[NONE]', $password='[NONE]') {

		if($email=='') $email = '[NONE]';

		// If the login credentials match the built in admin login...
		if($email==ADMIN_USERNAME && $password==ADMIN_PASSWORD) {

			// Set the user to admin and return
			$_SESSION["user"] = '[ADMIN]';
			return true;

		}

		// Otherwise...
		else {

			// If the user exists...
	  		if(bb_user_exists($email)) {

				// Generate the hash of the supplied password
	  			$supplied_hash = bb_generate_hash($password);

	  			// Get the hash from the stored password
	  			$actual_hash = bb_get_password_hash($email);

	  			// If the hashes match...
	  			if($supplied_hash==$actual_hash) {

	  				// Set the user to the current user and return
	  				$_SESSION["user"] = $email;
					return true;
	  			}

	  			// Otherwise...
	  			else {

	  				// The login failed
	  				return false;
	  			}
	  		}

	  		// Otherwise...
	  		else {

	  			// The login failed
	  			return false;
	  		}

	  	}

	}

//----------------------------------------------------------------------------------------------------------------

	function bb_do_login_from_cookie($cookie="|") {

		// Extract the email and password from the supplied cookie
		$cookie   = explode("|", $cookie);
		$email 	  = $cookie[0];
		$password = $cookie[1];
		if($email=='') $email = '[NONE]';

		// If the supplied email address is a valid user...
  		if(bb_user_exists($email)) {

  			// Get the stored password hash
  			$actual_hash = bb_get_password_hash($email);

  			// If the supplied password hash matched the stored password hash
  			if($password==$actual_hash) {

  				// Set the user to the current user and return
  				$_SESSION["user"] = $email;
				return true;
  			}

  			// Otherwise...
  			else {

  				// The login failed
  				return false;
  			}
  		}
  		// Otherwise...
  		else {

  			// The login failed
  			return false;
  		}

	}

//----------------------------------------------------------------------------------------------------------------

	function bb_do_logout() {

		// Reset the user and path
		$_SESSION["user"] = '[NONE]';
		$_SESSION["path"] = '[NONE]';

	}

//----------------------------------------------------------------------------------------------------------------

	function bb_user_exists($email='[NONE]') {

		// If there is a form folder for the supplied email address the user exists
		if($email=='') $email = '[NONE]';
		$directory = $_SERVER["DOCUMENT_ROOT"] .  SITE_URL . FORM_PATH . '/' . $email;
		return file_exists($directory);

	}

//----------------------------------------------------------------------------------------------------------------

	function bb_get_password_hash($email='[NONE]') {

		// Get the stored password hash for the supplied email address
		if($email=='') $email = '[NONE]';
		$file = $_SERVER["DOCUMENT_ROOT"] . SITE_URL . FORM_PATH . '/' . $email . '/password.txt';
		$file_handle = fopen($file, "r");
   		$hash = fgets($file_handle);
   		if(substr($hash, -1)=="\n") $hash = substr($hash, 0, strlen($hash)-1);
		fclose($file_handle);
		return $hash;

	}

//----------------------------------------------------------------------------------------------------------------

	function bb_set_password_hash($email='[NONE]', $password='[NONE]') {

		// Set the stored password hash for the supplied email address
		if($email=='') $email = '[NONE]';
		$hash = bb_generate_hash($password);
		$file = $_SERVER["DOCUMENT_ROOT"] . '/' . SITE_URL . FORM_PATH . '/' . $email . '/password.txt';
		$file_handle = fopen($file, 'w');
		fwrite($file_handle, $hash);
		fclose($file_handle);

	}

//----------------------------------------------------------------------------------------------------------------

	function bb_generate_hash($password='[NONE]') {

		// Return the SHA512 hash of the supplied password using the defined salt and key
		return hash_hmac('sha512', $password . HASH_SALT, HASH_KEY);

	}

//----------------------------------------------------------------------------------------------------------------

	function bb_send_reset_password_link($email='[NONE]') {

		if($email=='') $email = '[NONE]';

		// Construct the reset password link for the supplied email address
		$url = 'http://' . SITE_DOMAIN . SITE_URL . '/login.php?key=' . bb_generate_hash($email) . '&email=' . $email;
		$link = '<a href="' . $url . '">' . $url .'</a>';

		// Construct the email containing the link
		$message = PASSWORD_RESET_EMAIL;
		$message = str_replace('{email}', $email, $message);
		$message = str_replace('{link}', $link, $message);

		// Set the email to the supplied email address
		bb_send_email($email, 'Password Reset for '. SITE_TITLE, $message);

	}

//----------------------------------------------------------------------------------------------------------------

	function bb_send_email($to='', $subject='', $message='') {

		// Construct the email body
		$body	 = "<html><body>" . $message . "</body></html>";

		// Construct the email headers
		$headers = "From: " . SITE_EMAIL_ADDRESS . "\r\n" . "Reply-To: ". SITE_EMAIL_ADDRESS . "\r\n" . "X-Mailer: PHP/" . phpversion();
		$headers .= "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type:text/html;charset=iso-8859-1" . "\r\n";

		// Send the email
		mail($to, $subject, $body, $headers);

	}

//----------------------------------------------------------------------------------------------------------------

	function bb_get_current_page() {

		// Return the name of the current page
		return substr($_SERVER["SCRIPT_NAME"],strrpos($_SERVER["SCRIPT_NAME"],"/")+1);

	}

//----------------------------------------------------------------------------------------------------------------
?>