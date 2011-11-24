<?php

	$fail = false;
	
	if( file_exists('./configuration.ini') )
		$fail = "A SLAM configuration file is already present, please delete the 'configuration.ini' file in the SLAM directory before reinstalling.";

	$pathinfo = pathinfo($_SERVER['SCRIPT_FILENAME']);
	$defaults = parse_ini_file('./defaults.ini');
	
	if ($defaults['SLAM_CONF_PATH'] == 'auto')
		$defaults['SLAM_CONF_PATH'] = $pathinfo['dirname'];
		
	if ($defaults['SLAM_CONF_HEADER'] == 'auto')
		$defaults['SLAM_CONF_HEADER'] = 'From: SLAM <'.$_SERVER['SERVER_ADMIN'].'>';
	
?>
<html>
	<head>
		<title>SLAM installer</title>
		<link type='text/css' href='install.css' rel='stylesheet' />
		<script type='text/javascript' src='check.js'></script>
		<script type='text/javascript' src='base64.js'></script>
	</head>
	<body>
<?php

//	if (fail !== false)
//		print $fail;		
?>
		<div id='installerTitle'><span style='font-family:Impact'>SLAM</span> installer</div>
		<div id='installerVer'>Version: 1.x.x</div>

		<form name='config'>

			<table id='configTable'>
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
					<td class='inputValue'><input type='text' value='<?php print $defaults['SLAM_CONF_PREFIX'] ?>' size='2' maxlength='2' id='SLAM_CONF_PREFIX' name='SLAM_CONF_PREFIX' /></td>
				</tr>
				<tr>
					<td class='inputField'>Mail header:</td>
					<td class='inputValue'><input type='text' value='<?php print $defaults['SLAM_CONF_HEADER'] ?>' size='50' id='SLAM_CONF_HEADER' name='SLAM_CONF_HEADER' /></td>
				</tr>
				<tr>
					<td class='checkCategory' colspan='2'><input type='button' value='Check these values' onClick='showPopupDiv("./check_gen.php?SLAM_CONF_PATH="+base64_encode(document.getElementById("SLAM_CONF_PATH").value),"checkGeneral",[])'/></td>
				</tr>
				<tr>
					<td class='inputCategory' colspan='2'>Database Settings</td>
				</tr>
				<tr>
					<td class='inputField'>Server:</td>
					<td class='inputValue'><input type='text' value='<?php print $defaults['SLAM_DB_HOST'] ?>' size='20' id='SLAM_DB_HOST' name='SLAM_DB_HOST' /></td>
				</tr>
				<tr>
					<td class='inputField'>Database name:</td>
					<td class='inputValue'><input type='text' value='<?php print $defaults['SLAM_DB_NAME'] ?>' size='20' id='SLAM_DB_NAME' name='SLAM_DB_NAME' /></td>
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
					<td class='checkCategory' colspan='2'><input type='button' value='Check these values' onClick='showPopupDiv("./check_db.php?SLAM_DB_HOST="+base64_encode(document.getElementById("SLAM_DB_HOST").value)+"&SLAM_DB_NAME="+base64_encode(document.getElementById("SLAM_DB_NAME").value)+"&SLAM_DB_USER="+base64_encode(document.getElementById("SLAM_DB_USER").value)+"&SLAM_DB_PASS="+base64_encode(document.getElementById("SLAM_DB_PASS").value),"checkGeneral",[])'/></td>
				</tr>
				<tr>
					<td class='inputCategory' colspan='2'>Attached File Settings</td>
				</tr>
				</tr>
					<td class='inputField'>Attachment directory:</td>
					<td class='inputValue'><input type='text' value='<?php print $defaults['SLAM_FILE_ARCH_DIR'] ?>' size='50' /></td>
				</tr>
				</tr>
					<td class='inputField'>Temporary directory:</td>
					<td class='inputValue'><input type='text' value='<?php print $defaults['SLAM_FILE_TEMP_DIR'] ?>' size='50' /></td>
				</tr>
				<tr>
					<td class='checkCategory' colspan='2'><input type='button' value='Check these values' /></td>
				</tr>
			</table>
		</form>
	</body>
</html>