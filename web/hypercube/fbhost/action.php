<?php
//
//  Amnesty(TM) Hypercube
//  Copyright (c) 2007 Mesa Dynamics, LLC. All rights reserved.
//

require_once 'dbappinclude.php';

if (isset($_REQUEST['create'])) {
	unset($currentwidget);
	query("update facebook set widget_id = NULL where fb_sig_user = " . $user);
	
	$title = mysql_real_escape_string($_REQUEST['title']);
	
	if(strpos($_REQUEST['code'], 'gmodules.com') && strpos($_REQUEST['code'], 'synd=open')) {
		$newcode = str_replace('synd=open', 'synd=amnesty', $_REQUEST['code']);
		$code = mysql_real_escape_string($newcode);
	}
	else
		$code = mysql_real_escape_string($_REQUEST['code']);
	
	if (isset($code) && isset($title) && $code != '' && $title != '') {
		$query = sprintf("select widget_id from widgets where title = '%s' and cube_id = %s",
			$title, $currentcube);
		$result = query($query);
		
		if (mysql_fetch_assoc($result)) {
			$facebook->redirect("error.php?errtitle=Widget creation error&errmsg=Please enter a unique name for this widget.");
			exit;
		}
		else {
			$hash = md5($code);
			$query = sprintf("insert into widgets (code, title, cube_id, hash) values ('%s', '%s', %s, '%s')",
				$code, $title, $currentcube, $hash);
			query($query);

			$result = query("select widget_id from widgets where widget_id = last_insert_id()");
			if ($row = mysql_fetch_assoc($result)) {
				$currentwidget = $row['widget_id'];
				query("update facebook set widget_id = " . $currentwidget . " where fb_sig_user = " . $user);

				$tokens = sprintf("{\"widget\":\"%s\", \"wid\":\"%s\", \"hash\":\"%s\"}",  mysql_real_escape_string($title), $currentwidget, $hash);
				$facebook->api_client->feed_publishUserAction("64763567314", $tokens);
				
				$facebook->redirect('index.php?update');
				exit;
			}
		}
	}
	else {
		$facebook->redirect("error.php?errtitle=Widget creation error&errmsg=Please fill out both the Name and Code fields.");
		exit;
	}
}
else if (isset($_REQUEST['copy'])) {
	$result = query("select cube_id, code from widgets where widget_id = " . $_REQUEST['copy']);
	if ($row = mysql_fetch_assoc($result)) {
		$cube_id = $row['cube_id'];
		
		if($cube_id == $currentcube) {
			$currentwidget = $_REQUEST['copy'];
			query("update facebook set widget_id = " . $currentwidget . " where fb_sig_user = " . $user);
		}
		else {
			$title = mysql_real_escape_string($_REQUEST['title']);
			
			if(!isset($title) || $title == '') {
				$facebook->redirect("error.php?errtitle=Widget copy error&errmsg=Please enter a name into the Name field.");
				exit;
			}

			$query = sprintf("select widget_id from widgets where title = '%s' and cube_id = %s",
				$title, $currentcube);
			$result = query($query);
			
			if (mysql_fetch_assoc($result)) {
				$facebook->redirect("error.php?errtitle=Widget copy error&errmsg=Please enter a unique name for this widget.");
				exit;
			}
			else {
				if(strpos($row['code'], 'gmodules.com') && strpos($row['code'], 'synd=open')) {
					$newcode = str_replace('synd=open', 'synd=amnesty', $row['code']);
					$code = mysql_real_escape_string($newcode);
				}
				else
					$code = mysql_real_escape_string($row['code']);

				if(strpos($code, 'synd=open')) {
					$newcode = str_replace('synd=open', 'synd=amnesty', $code, $count);
					$code = $newcode;
				}

				$query = sprintf("insert into widgets (code, title, cube_id, hash) values ('%s', '%s', %s, '%s')",
					$code, $title, $currentcube, md5($code));
				query($query);

				$result = query("select widget_id from widgets where widget_id = last_insert_id()");
				if ($row = mysql_fetch_assoc($result)) {
					$currentwidget = $row['widget_id'];
					query("update facebook set widget_id = " . $currentwidget . " where fb_sig_user = " . $user);

					$facebook->redirect('index.php?update');
					exit;
				}
			}
		}
	}
}
else if (isset($_REQUEST['edit'])) {
	$result = query("select cube_id, title, hidden from widgets where widget_id = " . $_REQUEST['edit']);
	if ($row = mysql_fetch_assoc($result)) {
		$title = mysql_real_escape_string($_REQUEST['title']);
		
		if (!isset($title) || $title == '') {
			$facebook->redirect("error.php?errtitle=Widget edit error&errmsg=Please enter a name into the Name field.");
			exit;
		}
		
		$didUpdate = false;

		if (strcmp($title, $row['title']) != 0) {
			$currentwidget = $_REQUEST['edit'];
			$query = sprintf("update widgets set title = '%s' where widget_id = %s", $title, $_REQUEST['edit']);
			query($query);
			
			$didUpdate = true;
		}

		if (isset($_REQUEST['hidden']) && $row['hidden'] == 0) {
			$query = sprintf("update widgets set hidden = 1 where widget_id = %s", $_REQUEST['edit']);
			query($query);
			
			$didUpdate = true;
		}
		else if (!isset($_REQUEST['hidden']) && $row['hidden'] == 1) {
			$query = sprintf("update widgets set hidden = 0 where widget_id = %s", $_REQUEST['edit']);
			query($query);
			
			$didUpdate = true;
		}	
	
		if ($didUpdate) {
			$facebook->redirect('index.php?update');
			exit;
		}
	}
}

else if (isset($_REQUEST['remove'])) {
	unset($currentwidget);
	query("update facebook set widget_id = NULL where fb_sig_user = " . $user);
	
	query("update widgets set cube_id = 0 where widget_id = " . $_REQUEST['remove'] . " and cube_id = " . $currentcube);

	$facebook->redirect('index.php?update');
	exit;
}

$facebook->redirect('index.php');

?>
