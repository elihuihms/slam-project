<?php
	if (file_exists(dirname(__DIR__).DIRECTORY_SEPARATOR.'configuration.ini')) {
		die("Installation complete. Go <a href='../index.php'>here</a> to access it.");
	}

	require('lib/constants.inc.php');
	require('lib/actions.inc.php');
			
	# Read the default settings either from the previously-entered options, or from the default file
	if (file_exists('step_1.ini')) {
		$defaults = parse_ini_file('step_1.ini');
	} else {
		$defaults = parse_ini_file('defaults.ini');
		update_auto_defaults($defaults);
	}

?>
<html>
	<head>
		<title>SLAM installer - Step 1/5</title>
		<link type='text/css' href='css/install.css' rel='stylesheet' />
		<script type='text/javascript' src='js/check.js'></script>
		<script type='text/javascript' src='js/validate.js'></script>
	</head>
	<body><div id='container'>
		<div id='installerTitle'><span style='font-family:Impact'>SLAM</span> installer - Step 1/5</div>
		<div id='installerVer'>Version: <?php print($slam_version) ?></div>
		
		<form name='forward' action='step_2.php'  method='post'>
			<input type='hidden' name='STEP' value='1' />
			<table id='configTable'>
				<tr>
					 <td class='helpHeader' colspan="2">For assistance, please refer to the SLAM documentation [<a href='http://steelsnowflake.com/projects/SLAM/installation' target='_blank'>here</a>].</td>
				</tr>
				<tr>
					<td class='inputCategory' colspan='2'>General Settings</td>
				</tr>
				<tr>
					<td class='inputField'>Installation path:</td>
					<td class='inputValue'><input type='text' value='<?php print $defaults['SLAM_CONF_PATH'] ?>' size='50' id='SLAM_CONF_PATH' name='SLAM_CONF_PATH' /></td>
					
				</tr>
				<tr>
					<td class='inputField'>Lab name:</td>
					<td class='inputValue'><input type='text' value='<?php print $defaults['SLAM_CONF_NAME'] ?>' size='20' id='SLAM_CONF_NAME' name='SLAM_CONF_NAME' /></td>
				</tr>
				<tr>
					<td class='inputField'>Lab prefix:</td>
					<td class='inputValue'><input type='text' value='<?php print $defaults['SLAM_CONF_PREFIX'] ?>' size='2' maxlength='2' id='SLAM_CONF_PREFIX' name='SLAM_CONF_PREFIX' onkeyup="validatePos( this, '[a-zA-Z]')"/></td>
				</tr>
				<tr>
					<td class='inputField'>Mail header:</td>
					<td class='inputValue'><input type='text' value='<?php print $defaults['SLAM_CONF_HEADER'] ?>' size='50' id='SLAM_CONF_HEADER' name='SLAM_CONF_HEADER' /></td>
				</tr>
				<tr>
					<td class='checkCategory' colspan='2'><input type='button' value='Check these values' onClick='checkGeneralForm()'/></td>
				</tr>
				<tr>
					<td class='inputCategory' colspan='2'>Database Settings</td>
				</tr>
				<tr>
					<td class='inputField'>Server:</td>
					<td class='inputValue'><input type='text' value='<?php print $defaults['SLAM_DB_HOST'] ?>' size='20' id='SLAM_DB_HOST' name='SLAM_DB_HOST' /></td>
				</tr>
				<tr>
					<td class='inputField'>Port:</td>
					<td class='inputValue'><input type='text' value='<?php print $defaults['SLAM_DB_PORT'] ?>' size='20' id='SLAM_DB_PORT' name='SLAM_DB_PORT' /></td>
				</tr>
				<tr>
					<td class='inputField'>Database name:</td>
					<td class='inputValue'><input type='text' value='<?php print $defaults['SLAM_DB_NAME'] ?>' size='20' id='SLAM_DB_NAME' name='SLAM_DB_NAME' /></td>
				</tr>
				<tr>
					<td class='inputField'>Database characterset:</td>
					<td class='inputValue'><input type='text' value='<?php print $defaults['SLAM_DB_CHARSET'] ?>' size='20' id='SLAM_DB_CHARSET' name='SLAM_DB_CHARSET' /></td>
				</tr>
				<tr>
					<td class='inputField'>Login name:</td>
					<td class='inputValue'><input type='text' value='<?php print $defaults['SLAM_DB_USER'] ?>' size='20' id='SLAM_DB_USER' name='SLAM_DB_USER' /></td>
				<tr>
				</tr>
					<td class='inputField'>Login password:</td>
					<td class='inputValue'><input type='password' value='<?php print $defaults['SLAM_DB_PASS'] ?>' size='20' id='SLAM_DB_PASS' name='SLAM_DB_PASS' /></td>
				</tr>
				<tr>
					<td class='checkCategory' colspan='2'><input type='button' value='Check these values' onClick='checkDatabaseForm()'/></td>
				</tr>
				<tr>
					<td class='inputCategory' colspan='2'>Attached File Settings</td>
				</tr>
				</tr>
					<td class='inputField'>Attachment directory:</td>
					<td class='inputValue'><input type='text' value='<?php print $defaults['SLAM_FILE_ARCH_DIR'] ?>' size='50' id='SLAM_FILE_ARCH_DIR' name='SLAM_FILE_ARCH_DIR' /></td>
				</tr>
				</tr>
					<td class='inputField'>Temporary directory:</td>
					<td class='inputValue'><input type='text' value='<?php print $defaults['SLAM_FILE_TEMP_DIR'] ?>' size='50' id='SLAM_FILE_TEMP_DIR' name='SLAM_FILE_TEMP_DIR' /></td>
				</tr>
				<tr>
					<td class='checkCategory' colspan='2'><input type='button' value='Check these values' onClick='checkFilesForm()' /></td>
				</tr>
			</table>
			<br />
			<div class='actionButtons'>
				<input type='submit' class='submitButton' value='Save these settings and Continue' />
			</div>
		</form>
		<form name='back' action='index.php' method='post'>
			<div class='actionButtons'>
				<input type='submit' class="submitButton" value='Save these settings and Go Back' />
			</div>
		</form>
	</div></body>
</html>