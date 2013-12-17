<?php
//----------------------------------------------------------------------------------------------------------------

	// Requirements
	require_once($_SERVER["DOCUMENT_ROOT"].'/crm/_inc/functions.php');

//----------------------------------------------------------------------------------------------------------------

	// Establish the current path
	$path = '';
	if(isset($_SESSION['path'])) $path = $_SESSION['path'];
	if(isset($_GET['path'])) $path = $_GET['path'];
	if(isset($_POST['form_path'])) $path = $_POST['form_path'];

	// Remove any leading or trailing slashes from the current path
	if(substr($path,0,1)=='/') $path = substr($path,-1*(strlen($path)-1));
	if(substr($path,-1,1)=='/') $path = substr($path,0,(strlen($path)-1));

	// If the current path hasnt been specified initialise it
	if($path=='[NONE]') $path = '';

	// If the current path doesnt exist clear it
	if(!file_exists($_SERVER["DOCUMENT_ROOT"] . SITE_URL . FORM_PATH . '/' . $path)) $path = '';

	// If the current path hasnt been set and the user is not admin set the current path to the users folder
	if($path=='' && $_SESSION['user']!='[ADMIN]') $path = $_SESSION['user'];

	// Store the current path
	$_SESSION['path'] = $path;

	// If the user is not admin
	if($_SESSION['user']!='[ADMIN]') {

		// Get the owner of the current path
		$parts = explode('/', $_SESSION['path']);
		$owner=$parts[0];

		// If the current user is not the owner of the current path
		if($_SESSION['user']!=$owner) {

			// Switch the path to their own folder
			$_SESSION['path'] = $_SESSION['user'];
		}
	}

	// If a form has been submitted
	if(isset($_POST["form_submitted"]))  {

		// Load the original form
		$form = new bb_form();
		$load_path = $_SERVER["DOCUMENT_ROOT"] . SITE_URL . FORM_PATH . '/' . $_SESSION['path'];
		$xml = simplexml_load_file($load_path);
		$form = new bb_form();
		$form->load_from_xml($xml);

		// Update the form with the submitted values
		$form->load_from_form();

		// Set the form modified date to now
		$form->modified = date("d/m/Y H:i:s");

		// Set the form modified by to the current user
		$form->modifiedby = $_SESSION['user'];

		// Update the signature
		$signed = false;
		if(isset($_POST["form_signature"])) {
			if($_POST["form_signature"]!="") {
				$form->signature = $_POST["form_signature"];
				$form->signed = date("d/m/Y H:i:s");
				$signed = true;
			}
		}

		// Save the form
		$xml = $form->get_xml();
		$file = fopen($load_path, 'w');
		fwrite($file, $xml);
		fclose($file);

		// Return status and exit
		if($signed) {
			echo 'SIGNED';
		} else {
			echo 'OK';
		}
		exit();
	}

	// Establish the current and previous folders for the heading and back links
	$current_folder = '';
	$previous_folder = '[ROOT]';
	$return_path = '';
	$current_path = $_SESSION['path'];
	if($current_path=='') {
		$current_path='Home';
		$previous_folder='[NONE]';
	}
	if($_SESSION['path']!='') {
		$folders = explode('/', $_SESSION['path']);
		$current_folder = array_pop($folders);
		if(sizeof($folders)>0) $previous_folder = $folders[sizeof($folders)-1];
		if($previous_folder=='') $previous_folder='Home';
		$return_path = implode('/', $folders);
	}

	// Establish the current mode (folder or form) based on the path
	$mode = 'folder';
	if(substr($_SESSION['path'],-4)=='.xml') $mode='form';

	// If form mode...
	if($mode=='form') {

		// ...Load the form
		$load_path = $_SERVER["DOCUMENT_ROOT"] . SITE_URL . FORM_PATH . '/' . $_SESSION['path'];
		$xml = simplexml_load_file($load_path);
		$form = new bb_form();
		$form->load_from_xml($xml);

		// If the form is signed and the user is not admin...
		if($form->signature!="" && $_SESSION['user']!='[ADMIN]') {

			// ...Set the form mode to view only
			$form->mode="view";
		}

		// ...Otherwise
		else
		{

			// ...Set the form mode to edit
			$form->mode="edit";
		}

		// If the current user is admin...
		if($_SESSION['user']=='[ADMIN]') {

			// ...Set the form admin mode on
			$form->admin=true;
		}

		// ...Otherwise
		else {

			// ...Set the form admin mode off
			$form->admin=false;
		}

	}

//----------------------------------------------------------------------------------------------------------------

	function render_breadcrumbs() {

		global $mode;
		$crumbs = explode("/", $_SESSION["path"]);

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
				if($mode=='folder') {
					echo '<li class="active">' . $crumb . '</li>';
				}
				else {
					$load_path = $_SERVER["DOCUMENT_ROOT"] . SITE_URL . FORM_PATH . '/' . $_SESSION["path"];
					$xml = simplexml_load_file($load_path);
					$form = new bb_form();
					$form->load_from_xml($xml);
					echo '<li class="active">' . $form->legend . '</li>';
				}
			}
			$crumb_index++;
		}

		echo '</ul>';

	}

//----------------------------------------------------------------------------------------------------------------

	function render_form() {

		global $form;
		echo $form->get_html();

	}

//----------------------------------------------------------------------------------------------------------------

	function render_folder() {


		echo '<table class="table">' . "\r\n";
		echo '<tr>'. "\r\n";
		if($_SESSION['user']=='[ADMIN]') {
			echo '	<th width="45%">Name</th>' . "\r\n";
			echo '	<th width="20%">&nbsp;</th>' . "\r\n";
		} else {
			echo '	<th width="65%">&nbsp;</th>' . "\r\n";
		}
		echo '	<th width="10%" align="center">Signed</th>' . "\r\n";
		echo '	<th width="25%">Last Modified</th>' . "\r\n";
		echo '</tr>'. "\r\n";

		write_document_list($_SESSION['path']);

		echo  "</table>". "\r\n";

	}

//----------------------------------------------------------------------------------------------------------------

	function write_document_list($path='[NONE]') {

		$scan_path = $_SERVER["DOCUMENT_ROOT"] . SITE_URL . FORM_PATH . '/' . $path;

		$folders = array();
		$files = array();

		$documents = scandir($scan_path);
		foreach($documents as $document) {
	        if ($document!=".DS_Store" && $document != "." && $document != ".." && $document != "password.txt") {
	        	$item = null;
				$item->path = $path;
				$item->document = $document;
				if(is_dir($scan_path.'/'.$document)) {
					array_push($folders, $item);
				}
				else {
					array_push($files, $item);
			    }
	        }
		}

		foreach($folders as $folder) {
			write_folder($folder->path, $folder->document);
		}
		foreach($files as $file) {
			write_document($file->path, $file->document);
		}
	}

//----------------------------------------------------------------------------------------------------------------

	function write_folder($path='[NONE]', $document='[NONE]') {

		$link =  SITE_URL . FORM_PATH . '/' . $path . '/' . $document;

		echo '<tr>' . "\r\n";
		echo '<td><a class="rounded button" href="' . $link . '"><i class="fa fa-folder-o"></i>' . $document . '</a></td>' . "\r\n";
		if($_SESSION['user']=='[ADMIN]') echo '<td>&nbsp;</td>' . "\r\n";
		echo '<td>&nbsp;</td>' . "\r\n";
		echo '<td>&nbsp;</td>' . "\r\n";
		echo '</tr>' . "\r\n";

	}

//----------------------------------------------------------------------------------------------------------------

	function write_document($path='[NONE]', $document='[NONE]') {

		$load_path = $_SERVER["DOCUMENT_ROOT"] . SITE_URL . FORM_PATH . '/' . $path. '/' .$document;
		$xml = simplexml_load_file($load_path);
		$form = new bb_form();
		$form->load_from_xml($xml);

		$link = SITE_URL . FORM_PATH . '/' . $path . '/' . $document;

		echo '<tr>' . "\r\n";
		echo '<td><a class="rounded button" href="' . $link . '"><i class="fa fa-file-o"></i>' . $form->legend . '</a></td>' . "\r\n";
		if($_SESSION['user']=='[ADMIN]') echo '<td>&nbsp;</td>' . "\r\n";
		echo '<td align="center">' . "\r\n";
		if($form->signature!="") {
			echo '<i class="fa fa-check-square-o"></i>';
		} else {
			echo '<i class="fa fa-square-o"></i>';
		}
		echo '</td>' . "\r\n";
		echo '<td>' . $form->modified . '</td>' . "\r\n";
		echo '</tr>' . "\r\n";

	}

//----------------------------------------------------------------------------------------------------------------
?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="title" content="Bamboo Forms" />
		<title>Bamboo Forms</title>
		<link href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css" rel="stylesheet">
		<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Open+Sans:400italic,700italic,400,700)">
		<link href="/crm/_css/screen.css" rel="stylesheet" type="text/css" />
		<script type="text/javascript" src="/crm/_scripts/jquery.js"></script>
		<script type="text/javascript" src="/crm/_scripts/jquery.easing.js"></script>
		<script type="text/javascript" src="/crm/_scripts/jquery.iframe-post-form.js"></script>
		<script type="text/javascript" src="/crm/_scripts/index.js"></script>
	</head>

	<body>

		<label class="ajax-status rounded button">Saving</label>

		<div class="control-bar">
			<a class="brand" href="#"><img alt="" src="/crm/_images/logo.png" /></a>
			<a class="user-logout rounded button right" href="/crm/login.php">Log out</a>
			<span class="user-name active right"><?php echo $_SESSION["user"];?></span>
			<?php render_breadcrumbs(); ?>
		</div>

		<div class="document">

			<?php
				if($mode=='folder') {
					echo '<h2>' . end(explode("/", $_SESSION['path'])) . '</h2>' . "\r\n";
					render_folder();
				}
				else {
					render_form();
				}
			?>

		<?php if($mode=='form' && $form->mode=="edit" && $form->signature==""): ?>
			<fieldset>
				<div class="question">
					<div class="prompts">
						<label class="active">Once you have completed this form, please sign it. This will notify us that you are ready for us to read it.</label>
						<label class="hint">PLEASE NOTE: Once you have signed this form you will not be able to make any further changes.</label>
					</div>
					<div class="inputs">
						<a class="rounded button primary right" href="javascript:doSignature()">Click here to sign this document</a>
					</div>
				</div>
				<div class="question" id="sign-document">
					<div class="prompts">
						<label>To sign this form enter your name<br/>and click &lsquo;Sign it&rsquo;</label>
					</div>
					<div class="inputs">
						<input class="rounded" type="text" value="" name="txtSigature" id="txtSignature" placeholder="Your Name">
						<a class="rounded button" href="javascript:cancelSignature()">Not now</a>
						<a class="rounded button primary disabled" id="sign-confirmation" href="javascript:confirmSignature()" >Sign it</a>
					</div>
				</div>
			</fieldset>
		<?php endif; ?>

		<?php if($mode=='form' && $form->signature!="" && ($form->mode="view" || $_SESSION['user']=='[ADMIN]')): ?>
			<fieldset>
				<div class="question">
					<div class="prompts">
							<label>Signature</label>
					</div>
					<div class="inputs">
						<span class="uneditable-input"><?php echo $form->signature; ?></span>
					</div>
				</div>
				<div class="question">
					<div class="prompts">
							<label>Signed</label>
					</div>
					<div class="inputs">
						<span class="uneditable-input"><?php echo $form->signed; ?></span>
					</div>
				</div>
			</fieldset>
		<?php endif; ?>



		<?php if($mode=='form' && $form->admin): ?>
			<fieldset>
				<div class="question">
					<div class="prompts">
						<label>Created</label>
					</div>
					<div class="inputs">
						<span class="uneditable-input"><?php echo $form->created; ?></span>
					</div>
				</div>
				<div class="question">
					<div class="prompts">
						<label>Modified</label>
					</div>
					<div class="inputs">
						<span class="uneditable-input"><?php echo $form->modified; ?></span>
					</div>
				</div>
				<div class="question">
					<div class="prompts">
						<label>By</label>
					</div>
					<div class="inputs">
						<span class="uneditable-input"><?php echo $form->modifiedby; ?></span>
					</div>
				</div>
			</fieldset>
		<?php endif;?>

			<div class="clearfix"></div>

		</div>

	</body>

</html>