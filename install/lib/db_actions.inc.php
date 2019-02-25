<?php

function checkDbOptions( $server, $port, $dbname, $dbuser, $dbpass, $charset )
{
	global $pdo_options;
	
	$fail = array();

	if ($server == '')
		$fail[] = "Please specify a database server IP or 'localhost'";
	if ($port == '')
		$fail[] = "Please specify a database server port.";
	if( $dbname == '')
		$fail[] = "Please specify a database name.";
	if( $charset == '')
		$fail[] = "Please specify a database characterset.";
	if( $dbuser == '')
		$fail[] = "Please specify a database user.";
	if( $dbpass == '')
		$fail[] = "Please provide a password for the database user.";
	
	if( count($fail) == 0 )
	{
		try{
			$pdo = new PDO("mysql:host={$server};port={$port};dbname={$dbname};charset={$charset}",$dbuser,$dbpass,$pdo_options);
		}
		catch (PDOException $e)
		{
			$fail[] = "Error connecting to the database server and database name with the provided credentials.";
			return $fail;
		}
		
		if (!$pdo)
			$fail[] = "Could not connect to the server '$server' with the provided username '$dbuser'.";
		elseif( !checkUserPermissions($pdo) )
			$fail[] = "The specified user does not have all SELECT,INSERT,UPDATE, or DELETE permissions.";
		elseif( checkForSLAMTables($pdo) > 0)
			$fail[] = "A SLAM installation already exists on this database.";
		
		$pdo = null;
	}
	
	if( count($fail) == 0 )
		return true;
	
	return $fail;
}

function checkForSLAMTables( $pdo )
{
	/* returns a numeric value containing the suitability of the specified database for installing SLAM
	0 - no existing required SLAM tables
	1 - SLAM_Category table exists
	2 - SLAM_Researchers table exists
	4 - SLAM_Permissions table exists
	7 - all tables exist
	*/
		
	$ret = 7;
	try{
		$pdo->query("SELECT 1 FROM SLAM_Researchers LIMIT 1");
	}catch (PDOException $e){
		$ret-=1;
	}
	try{
		$pdo->query("SELECT 1 FROM SLAM_Categories LIMIT 1");
	}catch (PDOException $e){
		$ret-=2;
	}
	try{
		$pdo->query("SELECT 1 FROM SLAM_Permissions LIMIT 1");
	}catch (PDOException $e){
		$ret-=4;
	}
	
	return $ret;
}

function checkUserPermissions($pdo)
{
	/* TODO: this is still hacky and mysql version dependent. */

	/* 
	try{
		$result = $pdo->query("SHOW GRANTS FOR CURRENT_USER;")->fetchAll()[0];
	}catch (PDOException $e){
		return false;
	}

	if (stripos($result, 'SELECT') === false)
		return false;	
	if (stripos($result, 'INSERT') === false)
		return false;	
	if (stripos($result, 'UPDATE') === false)
		return false;	
	if (stripos($result, 'DELETE') === false)
		return false;	
	*/
	return true;
}

function SLAM_write_to_table($pdo, $table, $data)
{
	$fields = array();
	$values = array();
	
	foreach( $data as $field=>$value ){
		$fields[] = "`$field`";
		$values[] = $pdo->quote($value);
	}
	
	$fields = '('.implode( ',', $fields ).')';
	$values = '('.implode( ',', $values ).')';

	return $pdo->query("INSERT INTO `{$table}` $fields VALUES $values");
}

?>
