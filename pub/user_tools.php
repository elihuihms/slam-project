<?php
	set_include_path(get_include_path().PATH_SEPARATOR.'../');
	require('../lib/slam_index.inc.php');

	/* obtain and initialize session objects */
	$config	= new SLAMconfig();
	$db		= new SLAMdb($config);
	$user	= new SLAMuser($config,$db);

	if (!$user->superuser)
		exit("Not an administrator.");

	$user_list = $db->GetRecords("SELECT * FROM `{$config->values['user_table']}`");
	if ($user_list === false)
		$config->errors[] = 'Database error: Could not send reset email, could not access user table:'.$db->ErrorState();

?>
<div id='userTools'>
	<form name="userToolForm" id="userToolForm" action='' method='POST'>
		<input type='hidden' name='a' value='user' />
		<input type='hidden' name='tool_action' value='' id="tool_action"/>
		<table>
			<tr>
				<td colspan="2">
					<input type='button' value='Add User' onClick="showPopupDiv('pub/user_create.php','userActionPopup',{'noclose':true});" style="width:230px;"/>
				</td>
			</tr>
			<tr>
				<td>
					<select name="user_username" style="width:120px;">
						<option value="" selected='selected'></option>
						<?php
							foreach ($user_list as $user) {
								if ($user['crypt'] != '') {
									echo '<option value="'.$user['username'].'">'.$user['username'].'</option>';
								}
							}
						?>
					</select>						
				</td>
				<td>
					<input type='submit' value='Reset User' style="width:100px;" onClick="document.getElementById('tool_action').value='reset';"/>
				</td>
			</tr>
			<tr>
				<td></td>
				<td>
					<input type='submit' value='Disable User' style="width:100px;" onClick="document.getElementById('tool_action').value='disable';"/>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<input type='button' value='Cancel' onClick="removeBodyId('userActionPopup')" style="width:230px"/>
				</td>
			</tr>
		</table>
	</form>
	<?php
		if($_REQUEST['error'] == 'true'){
			echo "<span style='color:red'>".rawurldecode($_REQUEST['error_text'])."</span><br />\n";
		}
	?>
</div>