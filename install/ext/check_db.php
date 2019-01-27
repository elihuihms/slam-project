<?php

require('../lib/db_actions.inc.php');

$server = ($_REQUEST['SLAM_DB_HOST']);
$port = ($_REQUEST['SLAM_DB_PORT']);
$dbname = ($_REQUEST['SLAM_DB_NAME']);
$charset = ($_REQUEST['SLAM_DB_CHARSET']);
$dbuser = ($_REQUEST['SLAM_DB_USER']);
$dbpass = ($_REQUEST['SLAM_DB_PASS']);

if ( ($ret = checkDbOptions( $server, $port, $dbname, $dbuser, $dbpass, $charset )) === true)
	print "<span style='color:green'>These settings are OK.</span>";
else
	print "<span style='color:red'>{$ret[0]}</span>";
?>