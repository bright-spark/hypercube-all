<?php
//
//  Amnesty(TM) Hypercube
//  Copyright (c) 2007 Mesa Dynamics, LLC. All rights reserved.
//

require_once 'demoinclude.php';

$html = <<<EndHereDoc
<html>
<head>
	<meta http-equiv='content-type' content='text/html; charset=utf-8'>
	<style type='text/css'>
		@import 'main.css';
	</style>
</head>

<body>
<div id='header'>
<a href='#'>widgets em sua mesa</a> |
<a href='#'>licença</a> |
<a href='#'>ajuda</a>
</div>
EndHereDoc;

echo $html;

echo "<div id='add'>";
echo "<a href='#'><span>incluir widget</span></a>";
echo "</div>";

$widgetcount = 0;

$ishidden = false;
$currenthash = 0;

$result = query("select title, widget_id, hash from widgets where cube_id is null order by title");
while ($row = mysql_fetch_assoc($result)) {
	$widgetcount++;
	
	if ($widgetcount == 1) {
		echo "<div id='tabs1'><ul>";
	}
	else if(($widgetcount % 6) == 0) {
		//echo '</ul></div><div id='tabs1'><ul>';
	}
	
	$title = mysql_real_escape_string($row['title']);

	if ($widgetcount == 1) {
		$currentwidget = $row['widget_id'];
		$currenthash = $row['hash'];
		
		echo sprintf("<li id='current'><a href='#'><span>%s</span></a></li>", $title);
	}
	else
		echo sprintf("<li><a href='#'><span>%s</span></a></li>", $title);		
}

if ($widgetcount > 0) {
	echo '</ul></div>';
}

$html2 = <<<EndHereDoc2
<div id='message'>
<h1>Instale Já o Hypercube!</h1>
    O Amnesty&trade; Hypercuben é um aplicativo do OpenSocial que permite que você armazene e compartilhe widgets da web com seus amigos.  O que é um widget da web?  Um widget da web é um mini-aplicativo que pode ser incluído em um blog ou em uma página da web.  Exemplos incluem apresentações de fotos, tocadores de audio, jogos em flash e videos. 
</div>
EndHereDoc2;

echo $html2;

if (isset($currentwidget)) {
	echo "<div id='widgetframe'>";

	echo sprintf("<iframe src='widgetpeek.php?widget_id=%s&hash=%s' frameborder='0' width='100%%' height='520' scrolling=no />",
		$currentwidget, $currenthash);

	echo "</div>";
}

echo "</body></html>";

?>

