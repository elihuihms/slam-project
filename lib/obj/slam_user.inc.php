<?php

class SLAMuser
{
	public $username;
	public $prefs;
	public $groups;
	public $authenticated;
	public $superuser;
	
	function __construct(&$config=false,$db=false,$username=false,$password=false)
	{
		if ((!$config) || (!$db))
			return;

		/* loaduser will return false if username/password are bad */
		if(($ret = $this->loaduser($config,$db,$username,$password)) !== false)
		{
			$this->authenticated = true;
			
			/* extract user groups */
			$this->groups = split(',',$ret['groups']);
			if(count($this->groups) == 0)
				$this->groups = array( $this->values['username'] );

			/* extract user prefs */
			$this->prefs = unserialize($ret['prefs']);
			if(empty($this->prefs['default_entryReadable']))
				$this->prefs['default_entryReadable'] = 2;
			if(empty($this->prefs['default_entryEditable']))
				$this->prefs['default_entryEditable'] = 1;
		}
			
		return;
	}

	private function loaduser(&$config,$db,$username,$password)
	{
		if ($_REQUEST['logout']){
			setcookie("{$config->values['name']}_slam",'',time()-3600,'/');
			return false;
		}
		
		$this->username = $username;
		
		/* is the user attempting to log in? */
		if (($_REQUEST['login_username']) && ($_REQUEST['login_password']))
		{
			$this->username = urldecode($_REQUEST['login_username']);
			$password = urldecode($_REQUEST['login_password']);
		}
		elseif($_REQUEST['auth']) /* is the user sending an auth variable? */
		{
			list($this->username,$password) = explode(':',base64_decode(rawurldecode($_REQUEST['auth'])));
		}
		elseif($_COOKIE["{$config->values['name']}_slam"]) /* does the user possess an auth cookie? */
		{
			$crypt = mysql_real_escape(urldecode($_COOKIE["{$config->values['name']}_slam"]),$db->link);
			$auth = $db->GetRecords("SELECT * FROM `{$config->values['user_table']}` WHERE `crypt`='$crypt' LIMIT 1");
			
			if ($auth === false) //GetRecords returns false on error
				die('Database error: could not check user crypt key: '.mysql_error());
			elseif (count($auth) == 1)
			{
				/* refresh the cookie */
				setcookie("{$config->values['name']}_slam",$auth[0]['crypt'],time()+$config->values['cookie_expire'],'/');
				
				$this->username = $auth[0]['username'];
				return $auth[0];
			}
			
			$config->errors[] = 'Auth error: Invalid crypt key.';
			return false;
		}
		
		/* attempt to check out the username and password */
		$auth = $this->checkPassword($config,$db,$password);
		
		/* set the cookie to keep the user logged in and copy the user prefs, etc to the user */
		if ($auth !== false)
		{
			setcookie("{$config->values['name']}_slam",sha1($auth[0]['salt'].urldecode($_REQUEST['login_password'])),time()+$config->values['cookie_expire'],'/');
			return $auth[0];
		}
		else
			$config->errors[] = 'Auth error: Incorrect password provided.';
		
		return false;
	}
	
	function checkPassword($config,$db,$password)
	{
		$auth = $db->GetRecords("SELECT * FROM `{$config->values['user_table']}` WHERE `username`='".mysql_real_escape($this->username,$db->link)."' LIMIT 1");
		
		/* compare the salt+password hash with that stored in the db */
		if ($auth === false) //GetRecords returns false on error
			die('Database error: could not check user passphrase: '.mysql_error());
		if ((count($auth) == 1) && (sha1($auth[0]['salt'].$password) == $auth[0]['crypt']))
			return $auth;

		$config->errors[] = 'Auth error: Invalid username provided.';
		return false;
	}
	
	function savePrefs(&$config,$db)
	{
		$prefs = mysql_real_escape(serialize($this->prefs),$db->link);
		$q = "UPDATE `{$config->values['user_table']}` SET `prefs`='$prefs' WHERE `username`='$this->username' LIMIT 1";
		if (!$db->Query($q))
		{
			$config->errors[] = 'Error updating user preferences: '.mysql_error();
			return false;
		}
		return true;
	}
}

?>