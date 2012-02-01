<?php

require('../lib/slam_files.inc.php');

$config	= new SLAMconfig();
$db		= new SLAMdb($config);
$user	= new SLAMuser($config,$db);

$slam_file_errors['zip_errors'] = array('No error','No error','Unexpected end of zip file','A generic error in the zipfile format was detected.','zip was unable to allocate itself memory','A severe error in the zipfile format was detected','Entry too large to be split with zipsplit','Invalid comment format','zip -T failed or out of memory','The user aborted zip prematurely','zip encountered an error while using a temp file','Read or seek error','zip has nothing to do','Missing or empty zip file','Error writing to a file','zip was unable to create a file to write to','bad command line parameters','zip could not open a specified file to read'); //exit status descriptions from the zip man page 
$slam_file_errors['unzip_errors'] = array('No error','One or more warning errors were encountered, but processing completed successfully anyway','A generic error in the zipfile format was detected','A severe error in the zipfile format was detected.','unzip was unable to allocate itself memory.','unzip was unable to allocate memory, or encountered an encryption error','unzip was unable to allocate memory during decompression to disk','unzip was unable allocate memory during in-memory decompression','unused','The specified zipfiles were not found','Bad command line parameters','No matching files were found','50'=>'The disk is (or was) full during extraction',51=>'The end of the ZIP archive was encountered prematurely.',80=>'The user aborted unzip prematurely.',81=>'Testing or extraction of one or more files failed due to unsupported compression methods or unsupported decryption.',82=>'No files were found due to bad decryption password(s)');

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0//EN">
<html>
	<head>
		<title>SLAM File Manager</title>
		<link type='text/css' href='../css/files.css' rel='stylesheet' />
		<script type='text/javascript' src='../js/files.js'></script>
	</head>
	<body>
		<div id='fileListContainer'>
<?php

if ($user->authenticated)
{
	$request = new SLAMrequest($config,$db,$user);
	
	/* get the category and identifier from the request object */
	if(!empty($request->categories))
	{
		$category = array_shift(array_keys($request->categories));
		$identifier = array_shift($request->categories[$category]);
		
		/* is the current user qualified to make changes to the archive? */
		$editable = SLAM_checkAssetOwner($config,$db,$user,$category,$identifier);

		/* attempt to retrieve the asset's archive path*/
		if(($path = SLAM_getArchivePath($config,$category,$identifier)) !== false)
		{
			/* retrieve info on the files in the archive */
			if(($files = SLAM_getArchiveFiles($config,$path)) !== false)
			{
				echo "<div id='assetTitle'>$identifier</div>\n";
				echo "<div id='fileListScrollbox'>\n";
				echo "<form name='assetFileRemove' id='assetFileRemove' method='POST' action='delete.php'>\n";
				echo "<input type='hidden' name='i' value='$identifier' />\n";
				echo SLAM_makeArchiveFilesHTML($config,$db,$category,$identifier,$files,$editable);
				echo "</div>\n";
				if ($editable)
					echo "<input type='button' id='deleteButton' value='Delete' onClick=\"delete_submit('assetFileRemove')\" />\n";
				echo "</form>\n";
				echo "</div>\n";
			}
			else
				echo "<div id='fileEmpty'>No files to show</div>\n";
		}
		else
			echo "<div id='fileEmpty'>No files to show</div>\n";
	}
	else
		$config->errors[] = 'Invalid identifier provided';
}
else
{
	echo "<div id='fileEmpty'>You are not logged in</div>\n</div>\n";
	echo "</body>\n</html>";
	return;
}

if(!empty($config->values['debug']))
{
	echo "<div class='error'>\n";
	for($i=0; $i<count($config->errors); $i++)
		echo "$i) {$config->errors[$i]}<br />\n";		
	echo "</div>\n";
}

if ($editable)
	SLAM_updateArchiveFileList($config,$db,$category,$identifier,$files);
else
{
	echo "</div>\n</body>\n</html>\n";
	exit;
}

?>
</form>
		</div>
		<div id='fileUploadContainer'>
			<form name="assetFileUpload" id="assetFileUpload" method="POST" action="upload.php" enctype="multipart/form-data">
				<input type="hidden" name="i" value="<?php echo($identifier); ?>" />
				<table id='fileUploadTable'>
					<tr>
						<td style='width:250px;font-family:monospace;text-align: center'>
							<input name="asset_file[]" type="file">
							<input name="asset_file[]" type="file">
							<input name="asset_file[]" type="file">
							<input name="asset_file[]" type="file">
						</td>
						<td style='width:250px;text-align: center; border-left:1px solid gray'>
							<input type="button" name="action" value="Attach Files" class='actionButton' onClick="fileUploadSubmit('assetFileUpload','asset_file[]')" />
							<input type="Reset" value="Clear Form" class='actionButton' />
						</td>
					</tr>
				</table>
			</form>
		</div>
	</body>
</html>