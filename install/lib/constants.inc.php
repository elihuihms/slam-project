<?php

	#
	# SLAM installer
	#

	$slam_version = '1.2';
	
	$req_php_version = '7.0.0';

	$pdo_options = [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_BOTH,
		PDO::ATTR_EMULATE_PREPARES => false,
	];
	
?>
