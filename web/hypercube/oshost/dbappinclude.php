<?php
//
//  Amnesty(TM) Hypercube
//  Copyright (c) 2007 Mesa Dynamics, LLC. All rights reserved.
//

require_once 'appinclude.php';

if (!isset($user)) {
	exit;
} 

$template = sprintf("host=%s&userid=%s&userkey=%s", $host, $user, $userkey);

$newuser = 'false';
$currentcube = NULL;
$currentwidget = NULL;

$conn = mysql_connect('mysql50-12.wc1.dfw1.stabletransit.com', '386731_root', 'boinkboink');
mysql_select_db('386731_hypercube', $conn);

function query($q) {
	global $conn;
	$result = mysql_query($q, $conn);
	if (!$result) {
		die("Invalid query -- $q -- " . mysql_error());
	}
	return $result;
}

function redirect($url)
{
	$location = sprintf("Location: %s", $url); 
	header($location);
}

$query = sprintf("select cube_id, widget_id from opensocial where id = '%s' and host = '%s'", $user, $host); 
$result = query($query);
if ($row = mysql_fetch_assoc($result)) {
	$currentcube = $row['cube_id'];
	$currentwidget = $row['widget_id'];
} else {
	$newuser = 'true';
	
	query("insert into cubes (user_id, serve_count) values (0, 0)");
	$result = query("select cube_id from cubes where cube_id = last_insert_id()");
	$row = mysql_fetch_assoc($result);
	$cubeid = $row['cube_id'];
	
	$query = sprintf("insert into opensocial (id, cube_id, host) values ('%s', %s, '%s')", $user, $cubeid, $host);
	query($query);
	
	$result = query("select code, title from widgets where cube_id is null");
	while ($row = mysql_fetch_assoc($result)) {
		$code = mysql_real_escape_string($row['code']);
		$title = mysql_real_escape_string($row['title']);
		$query = sprintf("insert into widgets (code, title, cube_id, hash) values ('%s', '%s', %s, '%s')",
			$code, $title, $cubeid, md5($code));
		query($query);
	}
		
	$currentcube = $cubeid;
}

?>
