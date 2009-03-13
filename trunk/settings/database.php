<?php

	$database="sphider";
	$mysql_user = "root";
	$mysql_password = ""; 
	$mysql_host = "localhost";
	$mysql_table_prefix = "";

    
	$success = mysql_pconnect ($mysql_host, $mysql_user, $mysql_password);
	if (!$success)
		die ('<br />Cannot connect to database, check if username, password and host are correct.<br />' . mysql_error());
    $success = mysql_select_db ($database, $success);
	if (!$success) {
        die ('<br />Cannot choose database, check if database name is correct:<br />'  . mysql_error());
	}

?>

