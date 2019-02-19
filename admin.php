<?php
require('lib/slam_index.inc.php');

$config	= new SLAMconfig();
$db		= new SLAMdb($config);

function adminer_object() {
	class AdminerSoftware extends Adminer {

		function name() {
			return "SLAM Database Admin";
		}

		function credentials() {
			return array($config->values['db_server'], $config->values['db_user'], $config->values['db_pass']);
		}

		function database() {
			return $config->values['db_name'];
		}

		function login($login, $password){
			$user = new SLAMuser($config,$db);
			return ($user->authenticated && $user->superuser);
		}

	}
	return new AdminerSoftware;
}

include "lib/adminer-4.7.0-mysql.php";
?>