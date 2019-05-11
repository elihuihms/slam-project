<?php

function SLAM_doUserAction(&$config,$db,&$user)
{
	/*
		performs the requested user action
	*/
	
	switch($_REQUEST['user_action'])
	{
		case 'set_preferences':
			SLAM_setUserPreferences($config,$db,$user);
			/* pop up the user prefs panel to show the user that the changes have been applied */
			//$config->html['onload'][] = 'showPopupDiv("pub/user_prefs.php","userActionPopup",{"noclose":true})';

			return;
		case 'change_password':
			if(SLAM_saveUserPassword($config,$db,$user) === true)
				return;
			else
				$config->html['onload'][] = 'showPopupDiv("pub/password_change.php?bad_password=true","userActionPopup",{})';
		
			return;
		case 'reset_send':
			SLAM_sendUserResetMail($config,$db);
			
			return;
		case 'reset_change':
			$config->html['onload'][] = "showPopupDiv(\"pub/password_choose.php?user_name={$_REQUEST['user_name']}&secret={$_REQUEST['secret']}\",\"userActionPopup\",{})";

			return;
		case 'reset_save':
			SLAM_saveUserResetPass($config,$db);
			
			return;
		case 'show_tools':
			$config->html['onload'][] = "showPopupDiv(\"pub/user_tools.php\",\"userActionPopup\",{})";

			return;
		case 'tool_action':
			SLAM_userToolAction($config,$db,$user);
			
			return;
		case 'create_user':
			if( ($msg = SLAM_createNewUser($config,$db,$user)) !== true)
				$config->html['onload'][] = 'showPopupDiv("pub/user_create.php?error=true&error_text='.rawurlencode($msg).'","userActionPopup",{})';
			else
				return;
		default:
			return;
	}

	return;
}

function SLAM_userToolAction(&$config,$db,$user){

	/* $user is the currently logged in user */
	if (!$user->superuser) {
		$config->errors[] = "User is not administrator";
		return;
	}

	if ($_REQUEST['tool_action'] == "reset") {
		
		$username = $db->Quote($_REQUEST['user_username']);
		$reset_user = new SLAMuser($config,$db,$username,''); /* will fail login, but user->prefs will still be loaded */

		if (!array_key_exists('failed_logins', $reset_user->prefs)) {
			$config->errors[] = "User cannot be reset.";
		} else {
			$reset_user->prefs['failed_logins'] = 0;
			$reset_user->savePrefs($config,$db);
			SLAM_sendUserResetMail($config,$db);
		}
	} elseif ($_REQUEST['tool_action'] == "disable") {
		
		$username = $db->Quote($_REQUEST['user_username']);

		if ($username == $user->username) {
			$config->errors[] = "User cannot disable themselves.";
		} else {
			$result = $db->Query("UPDATE `{$config->values['user_table']}` SET `crypt`='' WHERE `username`='$disable_username' LIMIT 1");
			if ($result === false) {
				$config->errors[] = 'Database error: Could not disable user, could not access user table:'.$db->ErrorState();
			} elseif ($result->rowCount() == 0) {
				$config->errors[] = 'Database error: Could not disable user, invalid username provided.';
			}
		}
	} else {
		$config->errors[] = "Unrecognized tool action";
	}

	return;
}

function SLAM_setUserPreferences(&$config,$db,&$user)
{	
	$user->prefs['default_project'] = $_REQUEST['defaultProject'];
	
	/* interpret the permission menu selections */
	switch( $_REQUEST['defaultReadable'] )
	{
		case 1:
			$user->prefs['default_project_access'] = 1;
			$user->prefs['default_access'] = 0;
			break;
		case 2:
			$user->prefs['default_project_access'] = 1;
			$user->prefs['default_access'] = 1;
			break;
		default:
			$user->prefs['default_project_access'] = 0;
			$user->prefs['default_access'] = 0;
	}
	
	switch( $_REQUEST['defaultEditable'] )
	{
		case 1:
			$user->prefs['default_project_access'] = 2;
			break;
		case 2:
			$user->prefs['default_project_access'] = 2;
			$user->prefs['default_access'] = 2;
			break;
	}

	$user->savePrefs($config,$db);

	return;
}

function SLAM_saveAssetTags($config,$db,&$user,$request)
{
	$identifiers = array();

	/* append the tagged identifiers to the user's preferences' identifier array */
	foreach($request->categories as $category=>$assets)
	{
		if (!array_key_exists($category,$user->prefs['identifiers']) || !is_array($user->prefs['identifiers'][$category]))
			$user->prefs['identifiers'][$category] = array();
			
		$user->prefs['identifiers'][$category] = array_unique(array_merge($user->prefs['identifiers'][$category],$assets));
	}

	/* sort the identifiers */
	if(!ksort($user->prefs['identifiers']))
		$config->errors[] = 'Could not sort user tagged assets.';

	/* safety check to remove any reset secret still hanging around */
	if (array_key_exists('reset_secret',$user->prefs))
		unset($user->prefs['reset_secret']);
		
	/* save the modified list back to the user's record */
	$user->savePrefs($config,$db);
	
	return;
}

function SLAM_dropAssetTags($config,$db,&$user,$request)
{
	foreach($request->categories as $category=>$identifiers)
		if(is_array($user->prefs['identifiers'][$category]) && is_array($identifiers))
			$user->prefs['identifiers'][$category] = array_diff($user->prefs['identifiers'][$category],$identifiers);
			
	/* remove any empty categories */
	foreach($user->prefs['identifiers'] as $category=>$identifiers)
		if (empty($identifiers))
			unset($user->prefs['identifiers'][$category]);

	$user->savePrefs($config,$db);
	
	return;
}

function SLAM_changeUserPassword(&$config,$db,$username,$newpass)
{
	$username = $db->Quote($username);
	$salt = substr(str_shuffle(MD5(microtime())), 0, 8);
	$crypt = sha1($salt.$newpass);

	/* attempt to update the salt and crypt */
	$auth = $db->Query("UPDATE `{$config->values['user_table']}` SET `salt`='$salt',`crypt`='$crypt' WHERE `username`='$username' LIMIT 1");
	
	if ($auth === false) {
		$config->errors[] = 'Database error: Could not update password, could not access user table:'.$db->ErrorState();
		return false;
	} elseif ($auth->rowCount() == 0) {
		$config->errors[] = 'Database error: Could not update password, invalid username provided.';
		return false;
	}
	
	return true;
}

function SLAM_saveUserPassword(&$config,$db,$user)
{
	if(!$user->authenticated)
		$config->errors[] = 'You must be logged in to change your password.';
	
	$old_password = $_REQUEST['old_password'];
	$new_password = $_REQUEST['new_password'];

	if ($user->checkPassword($config,$db,$old_password))
		return SLAM_changeUserPassword($config,$db,$user->username,$new_password);

	return false;
}

function SLAM_sendUserResetMail(&$config,$db)
{
	if (array_key_exists('user_email', $_REQUEST)) {
		$email = $db->Quote($_REQUEST['user_email']);
		$auth = $db->GetRecords("SELECT * FROM `{$config->values['user_table']}` WHERE `email`='$email'");
	} elseif (array_key_exists('user_username', $_REQUEST)) {
		$username = $db->Quote($_REQUEST['user_username']);
		$auth = $db->GetRecords("SELECT * FROM `{$config->values['user_table']}` WHERE `username`='$username'");
	} else {
		$auth = array();
	}
	
	//GetRecords returns false on error
	if ($auth === false) {
		$config->errors[] = 'Database error: Could not send reset email, could not access user table:'.$db->ErrorState();
		return;
	} elseif (count($auth) < 1) {
		$config->errors[] = 'Could not send reset email, search key is not valid.';
		return;
	}
	
	$reset_urls = '';
	foreach($auth as $user) {
		/* make the secret key the user will use to reset his/her password */
		$secret = substr(str_shuffle(MD5(microtime())), 0, 10);
	
		/* save the secret to the user */
		$prefs = unserialize($user['prefs']);
		$prefs['reset_secret'] = $secret;
		$prefs = $db->Quote(serialize($prefs));
	
		/* attempt to save the secret back to the user */
		$result = $db->Query("UPDATE `{$config->values['user_table']}` SET `prefs`='$prefs' WHERE `username`='{$user['username']}' LIMIT 1");
		
		if ($result === false) {
			$config->errors[] = 'Database error: Could not send reset email, could not update user table:'.$db->ErrorState();
			return;
		} elseif ($result->rowCount() == 0) {
			$config->errors[] = 'Could not send reset email, username is not valid.';
			return;
		}
	
		$referrer = explode('?',$_SERVER['HTTP_REFERER']);
		$reset_urls.= "For the account: \"{$user['username']}\":\n";
		$reset_urls.= $referrer[0]."?action=user&user_action=reset_change&user_name=".urlencode($user['username'])."&secret=$secret\n\n";
	}
	
	$message = <<<EOL
Someone from the IP address {$_SERVER['REMOTE_ADDR']} has requested that your account password be reset.
If you did not request this, you can safely ignore this message.

If you would like to reset your password, please click or copy/paste this address into your browser:

EOL;

	if (mail($email,'SLAM Password reset',wordwrap($message,70).$reset_urls,$config->values['mail_header']) !== true)
		$config->errors[]='Could not send password reset email.';
		
	return;
}

function SLAM_saveUserResetPass(&$config,$db)
{	
	if (empty($_REQUEST['user_name']) || empty($_REQUEST['new_password']))
		return false;

	$username = $db->Quote($_REQUEST['user_name']);		
	$password = $db->Quote($_REQUEST['new_password']);
	$secret = $_REQUEST['secret'];
	
	$auth = $db->GetRecords("SELECT * FROM `{$config->values['user_table']}` WHERE `username`='$username' LIMIT 1");
	if ($auth === false){ //GetRecords returns false on error
		$config->errors[] = 'Database error: Could not save new password, could not access user table:'.$db->ErrorState();
		return;
	}
	elseif (count($auth) < 1){
		$config->errors[] = 'Database error: Could not save new password, specified user was not found:';
		return;
	}
	
	$prefs = unserialize($auth[0]['prefs']);
	
	/* check the provided secret string against the one the user possesses */
	if ($prefs['reset_secret'] != $secret){
		$config->errors[] = 'User secrets did not match! New password was not saved.';
		return;
	}

	/* if we made it this far we're good */
	if(SLAM_changeUserPassword($config,$db,$username,$password) !== true)
		 return;
		
	/* remove the secret key from the user's prefs */
	unset($prefs['reset_secret']);
	$prefs = $db->Quote(serialize($prefs));
	
	$result = $db->Query("UPDATE `{$config->values['user_table']}` SET `prefs`='$prefs' WHERE `username`='$username' LIMIT 1");
	if ($result === false) {
		$config->errors[] = 'Database error:  Could not remove secret key from user record:'.$db->ErrorState();
	}
	
	return;
}

function SLAM_createNewUser( &$config, $db, $user )
{
	if( ! $user->superuser )
		return "Only superusers can add a new user.";
	
	$username	= $db->Quote($_REQUEST['new_user_name']);		
	$email		= $db->Quote($_REQUEST['new_user_email']);		
	$password	= $db->Quote($_REQUEST['new_user_password']);
	$projects	= $db->Quote($_REQUEST['new_user_projects']);		
	
	$auth = $db->GetRecords("SELECT * FROM `{$config->values['user_table']}` WHERE `username`='$username' LIMIT 1");
	if ($auth === false){ //GetRecords returns false on error
		$config->errors[] = 'Database error: Could not save new password, could not access user table:'.$db->ErrorState();
		return;
	}
	elseif (count($auth) > 0){
		return "A user with that username already exists.";
	}
	
	$result = $db->Query("INSERT INTO `{$config->values['user_table']}` (`username`,`email`,`projects`) VALUES ('$username','$email','$projects')");
	if( $result === false) {
		$config->errors[] = 'Database error:  Could not create the new user:'.$db->ErrorState();
		return "Could not create the user.";
	}
	
	if( ! SLAM_changeUserPassword($config,$db,$username,$password) ){
		return "Created user, but could not set password!";
	}
	
	return true;
}

?>