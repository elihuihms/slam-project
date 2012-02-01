<?php

function SLAM_makeInsertionStatement( $db, $f, $table, $array )
{
	$a = implode("`,`",mysql_real_escape(array_keys($array),$db->link));
	$b = implode("','",mysql_real_escape(array_values($array),$db->link));
	return "$f INTO `$table` (`$a`) VALUES ('$b')";
}

function SLAM_makePermsQuery($config, $db, $user, $return, $table, $match=false, $order=false, $limit=false)
{
	if (!$match)
		$match = '1=1';
	
	$group_match = '';
	foreach( $user->groups as $group )
		$group_match .= "OR ( MATCH (`Group`) AGAINST ('$group' IN BOOLEAN MODE) AND `Group_access` > 0)\n";
	
	$query=<<<EOL
SELECT $return FROM `$table`
WHERE(
	$match
	AND
	(
		(`Identifier` NOT IN (SELECT `Identifier` FROM `{$config->values['perms_table']}`))
		OR
		(`Identifier` IN (SELECT `Identifier` FROM `{$config->values['perms_table']}` WHERE(
			(`Default_access` > 0)
OR (`Owner` = "{$user->username}" AND `Owner_access` > 0)
$group_match
		)))
	)
	AND
	(
		`Removed` < 1
	)
)
EOL;

	if ($user->superuser)
		$query = "SELECT * FROM `$table` WHERE $match";

	if ($order)
		$query .= "ORDER BY $order\n";
	
	if ($limit)
		$query .= "LIMIT $limit\n";
				
	return $query;
}

?>