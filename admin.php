<?php
require('lib/slam_index.inc.php');

$config	= new SLAMconfig();
$db		= new SLAMdb($config);
$user = new SLAMuser($config,$db);

if(!$_GET['db']){ /* keep Adminer focused on the appropriate db */
	$_GET['db'] = urlencode($config->values['db_name']);
}

if (!$user->authenticated or !$user->superuser){
	die("Please log into a superuser account in SLAM before accessing Adminer.");
}

function adminer_object() {

	class AdminerSoftware extends Adminer {

		function name() {
			return "SLAM Admin";
		}

		function credentials() {
			$config	= new SLAMconfig();
			return array($config->values['db_server'], $config->values['db_user'], $config->values['db_pass']);
		}

		function database() {
			$config	= new SLAMconfig();
			return $config->values['db_name'];
		}

		function login($login, $password){
			$config	= new SLAMconfig();
			$db		= new SLAMdb($config);
			$user = new SLAMuser($config,$db);
			if (!$user->superuser)
				return false;
			if (!$user->checkPassword($config,$db,$password)) /* force a reauth */
				return false;
			return true;
		}

	}
	return new AdminerSoftware;
}

include "./lib/adminer-4.7.0-mysql.php";
?>