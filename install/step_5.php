<?php
	if (file_exists(dirname(__DIR__).DIRECTORY_SEPARATOR.'configuration.ini')) {
		die("Installation complete. Go <a href='../index.php'>here</a> to access it.");
	}
	
	require('lib/constants.inc.php');
	require('lib/actions.inc.php');
	
	$fail = array();
	
	# Read the default settings either from the previously-entered options, or from the default file
	if (file_exists('step_1.ini')) {
		$defaults = parse_ini_file('step_1.ini');
	} else {
		$defaults = parse_ini_file('defaults.ini');
		update_auto_defaults($defaults);
	}

	# handle Adminer upload / installation
	$adminer_path = $defaults['SLAM_CONF_PATH'].DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'adminer.php';
	$adminer_message = "";
	$adminer_error = false;

	# save the previous page settings
	if ($_REQUEST['STEP'] == 4) {
		if( write_SLAM_options( './step_4.ini' ) === false ) {
			$fail[] = "Could not save your progress. Please contact your system administrator: $ret";
		}
	
	} elseif (($_REQUEST['STEP'] == 5) && ($_REQUEST['TYPE'] == "auto")) {
		$adminer_error = true;
		# attempt to automatically download latest adminer version from the specified URL
		
		try {
			$adminer_in = fopen( $adminer_latest_url, 'rb' );
		} catch ( Exception $e ){
			$adminer_message = $e;
		}
		if ($adminer_in){
			
			try {
				$adminer_out = fopen( $adminer_path, 'wb' );
			} catch ( Exception $e ){
				$adminer_message = $e;
			}

			if ($adminer_out){			
				while ($chunk = fread($adminer_in, 2048)) {
					fwrite($adminer_out, $chunk, 2048);
				}

				fclose($adminer_in);
				fclose($adminer_out);
				$adminer_error = false;
			} else {
				$adminer_message = "Could not save downloaded file to application directory {$adminer_path}.";
			}
		} else {
			$adminer_message = "Could not retrieve latest Adminer version from {$adminer_latest_url}.";
		}

	} elseif (($_REQUEST['STEP'] == 5) && ($_REQUEST['TYPE'] == "manual")) {
		$adminer_error = true;
		# user is uploading adminer, do some basic checks
		
		if ($_FILES["ADMINER_UPLOAD"]){			
			if ((substr($_FILES["ADMINER_UPLOAD"]["name"],0,7) != "adminer") or (substr($_FILES["ADMINER_UPLOAD"]["name"],-3) != "php")) {
				$adminer_message = "Invalid adminer file uploaded (not adminer-x.x.x.php, adminer-x.x.x-mysql.php, or adminer-x.x.x-en.php).";
			} elseif (!move_uploaded_file($_FILES["ADMINER_UPLOAD"]["tmp_name"], $adminer_path)){
				$adminer_message = "There was an error moving adminer into location, potentially due to restrictive security settings. Please check with your system administrator.";
			} else {
				$adminer_error = false;
			}
		} else {
			$adminer_message = "No adminer file uploaded.";
		}
	}
?>
<html>
	<head>
		<title>SLAM installer - Step 5/5</title>
		<link type='text/css' href='css/install.css' rel='stylesheet' />
	</head>
	<body><div id='container'>
		<div id='installerTitle'><span style='font-family:Impact'>SLAM</span> installer - Step 5/5</div>
		<div id='installerVer'>Version: <?php print($slam_version) ?></div>
<?php
	foreach( $fail as $text ) {
		print "<div class='fatalFail'>$text</div>\n";		
	}
?>		
			<table id='configTable'>
				<tr>
					 <td class='helpHeader' colspan="2">For assistance, please refer to the SLAM documentation [<a href='http://steelsnowflake.com/projects/SLAM/installation' target='_blank'>here</a>].</td>
				</tr>
				<tr>
					<td class='inputCategory' colspan='2'>Database editor setup</td>
				</tr>
				<tr>
					<td class='categoryInfo' colspan="2">
						During setup, it is strongly encouraged to install a database editor so that the superuser can add / modify categories and other administrative tasks. If you already have a database editor available, like phpMyAdmin, you can safely skip this step.
						<br />
						<br />
						The free and easy-to-use database editor <a href="https://www.adminer.org/">Adminer</a> is recommended for use with SLAM, and can be installed below.</td>
					</td>
				</tr>
			</table>
<?php
if (file_exists($adminer_path)) {
	echo <<<EOL
			<table id='successTable'>
				<tr>
					<td class='categoryInfo' colspan="2">Adminer has been installed successfully.</td>
					<br />
					<br />
				</tr>
			</table>
EOL;
} elseif (!$adminer_error) {
	echo <<<EOL
			<form name='installForm' action='step_5.php' method='post'>
				<input type='hidden' name='STEP' value='5' />
				<input type='hidden' name='TYPE' value='auto' />
				<table id='installTable'>
					<tr>
						<td class='inputCategory' colspan='2'>Automatic Installation</td>
					</tr>
					<tr>
						<td class='categoryInfo' colspan="2">SLAM can sometimes automatically install the latest version of Adminer, but this may be restricted by server settings.</td>
					</tr>
				</table>
				<div class='actionButtons'>
					<input type='submit' class='submitButton' value='Attempt to install Adminer' />
					<br />
					<br />
				</div>
			</form>
EOL;
} else {
	echo <<<EOL
			<form name='installForm' action='step_5.php' method='post' enctype="multipart/form-data">
				<input type='hidden' name='STEP' value='5' />
				<input type='hidden' name='TYPE' value='manual' />
				<table id='installTable'>
					<tr>
						<td class='inputCategory' colspan='2'>Manual Installation</td>
					</tr>
					<tr>
						<td class='categoryInfo' colspan="2">
							The installer could not configure Adminer properly.
							<br />
							<br />
							The error message provided was "{$adminer_message}"
							<br />
							<br />
							Please go to <a href="$adminer_web_url">{$adminer_web_url}</a> to download the latest version (e.g. "adminer-4.7.0-mysql-en.php") and upload it in the form below.
						</td>
					</tr>
					<tr>
						<td class='inputField'>Adminer:</td>
						<td class='inputValue'><input type="file" name="ADMINER_UPLOAD" id="ADMINER_UPLOAD"></td>
					</tr>
				</table>
				<div class='actionButtons'>
					<input type='submit' class='submitButton' value='Attempt to install Adminer' />
					<br />
					<br />
				</div>
			</form>
EOL;
}
?>
		<form name='forward' action='confirm.php' method='post'>
			<input type='hidden' name='STEP' value='5' />
			<div class='actionButtons'>
				<input type='submit' class='submitButton' value='Save these settings and Continue' />
			</div>
		</form>
		<form name='back' action='step_4.php' method='post'>
			<div class='actionButtons'>
				<input type='submit' class="submitButton" value='Save these settings and Go Back' />
			</div>
		</form>
	</div></body>
</html>