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
		
		// Update the signature
		if(isset($_POST["form_signature"])) {
			if($_POST["form_signature"]!="") {
				$form->signature = $_POST["form_signature"];
			}
		}

		// Save the form 
		$xml = $form->get_xml();
		$file = fopen($load_path, 'w');
		fwrite($file, $xml);
		fclose($file);

		// Return ok and exit
		echo 'OK';
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

		// If the form is signed...
		if($form->signature!='') {
		
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
		$crumb_count = sizeof($crumbs);
		$crumb_index = 1;

		echo '<ul class="breadcrumb">';

		foreach ($crumbs as $crumb) {
			if ($crumb_index<$crumb_count) {
				$crumb_path = SITE_URL . FORM_PATH;
				for ($i=0; $i <$crumb_index ; $i++) { 
					$crumb_path.="/".$crumbs[$i];
				}
				echo '<li><a href="' . $crumb_path . '/">' . $crumb . '</a>';
				echo '<span class="divider">/</span></li>';
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


		echo '<table class="table table-striped well">' . "\r\n";
		echo '<thead>'. "\r\n";
		echo '<tr>'. "\r\n";
		echo '	<th>&nbsp;</th>' . "\r\n";
		echo '	<th>Name</th>' . "\r\n";
		echo '	<th>Signed</th>' . "\r\n";
		echo '	<th>Last Modified</th>' . "\r\n";
		echo '</tr>'. "\r\n";
		echo '</thead>'. "\r\n";
		echo '<tbody>'. "\r\n";

		
		write_document_list($_SESSION['path']);
		
		echo '</tbody>'. "\r\n";
		echo  "</table>". "\r\n";
		
	}
	
//----------------------------------------------------------------------------------------------------------------

	function write_document_list($path='[NONE]') {
			
		$scan_path = $_SERVER["DOCUMENT_ROOT"] . SITE_URL . FORM_PATH . '/' . $path;		

		$documents = scandir($scan_path);	
		foreach($documents as $document) {
	        if ($document!=".DS_Store" && $document != "." && $document != ".." && $document != "password.txt") {
				if(is_dir($scan_path.'/'.$document)) {
					write_folder($path, $document);
				}
				else {
					write_document($path, $document);
			    }
	        }
		}

	}
	
//----------------------------------------------------------------------------------------------------------------

	function write_folder($path='[NONE]', $document='[NONE]') {
	
		$link =  SITE_URL . FORM_PATH . '/' . $path . '/' . $document;

		echo '<tr>' . "\r\n";
		echo '<td><a href="' . $link . '"><i class="icon-folder-close"></i></a></td>' . "\r\n";
		echo '<td><a href="' . $link . '">' . $document . '</a></td>' . "\r\n";
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
		echo '<td><a href="' . $link . '"><i class="icon-file"></i></a></td>' . "\r\n";
		echo '<td><a href="' . $link . '">' . $form->legend . '</a></td>' . "\r\n";
		echo '<td>' . "\r\n";
		if($form->signature!='') echo '<i class="icon-ok"></i>' . "\r\n";
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
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Bamboo Forms</title>
		<!--- JQUERY -->
		<script type="text/javascript" src="/crm/_scripts/jquery.js"></script>
		<script type="text/javascript" src="/crm/_scripts/jquery.easing.js"></script>
		<script type="text/javascript" src="/crm/_scripts/jquery.iframe-post-form.js"></script>
		<!-- BOOTSTRAP -->
		<link href="/crm/_bootstrap/css/bootstrap.css" rel="stylesheet" type="text/css" />
		<link href="/crm/_bootstrap/css/bootstrap-responsive.css" rel="stylesheet">
		<script type="text/javascript" src="/crm/_bootstrap/js/bootstrap.js"></script>
		<!-- OTHER -->
		<link href="/crm/_css/screen.css" rel="stylesheet" type="text/css" />
		<script type="text/javascript" src="/crm/_scripts/index.js"></script>
	</head>

	<body>
	
		<div class="container-fluid">
	
			<label class="label label-success" id="ajax_status">Saving</label>

			<div class="navbar">
  				<div class="navbar-inner">
    				<a class="brand" href="#"><img alt="" src="_images/logo.jpg" /></a>
    				<a class="btn btn-link pull-right" href="/crm/login.php">Log out</a>
    				<p class="navbar-text pull-right"><?php echo $_SESSION["user"];?>&nbsp;&nbsp;</p>
    			</div>
    		</div>

			<?php render_breadcrumbs(); ?>

			<?php 
				if($mode=='folder') { 
					echo '<h2>' . end(explode("/", $_SESSION['path'])) . '</h2>' . "\r\n";
					render_folder();
				} 
				else { 
					render_form();
				} 
			?>	

		<?php if($mode=='form' && $form->mode=="edit"): ?>
			<div class="navbar">
  				<div class="navbar-inner">
  					<span class="navbar-text pull-right"><a href="javascript:doSignature()">Click here to sign this document</a> - Please note once you have signed this document you will no longer be able to change it.</span>
  				</div>
  			</div>
		<?php endif; ?>

		</div>
		
		<div class="modal hide fade" id="confirmsignature">
			<div class="modal-header">
				<h4>Sign Document</h4>
			</div>
			<div class="modal-body">
				<p align="center"><strong>PLEASE NOTE</strong><br/>Once you have signed this document you will no longer be able to change it.<br/><br/></p>
				<p align="center">To sign this document enter your name in the box below and click &lsquo;Sign it&rsquo;:</p>
				<p align="center"><input type="text" value="" name="txtSigature" id="txtSignature" placeholder="Your Name"></p>
			</div>
			<div class="modal-footer">
				<a class="btn btn-danger" href="javascript:cancelSignature()">Not now</a>
				<a class="btn btn-success disabled" href="javascript:confirmSignature()" >Sign it</a>
			</div>
		</div>

	</body>
	
</html>