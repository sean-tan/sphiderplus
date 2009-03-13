<?php

/******************************************************
This script updates the columns click_counter and last_click in table 'links'
after a user clicked  a link on the result listing.
*******************************************************/

    $url    = $_GET['url']; 
    $query  = $_GET['query']; 

    $time   = time();
    
    header("Location: $url");       //  this is where the user really wants to get when clicking the link. 
                                    //  Okay, we will let him go. But also we will store the destination.   
    $include_dir  = "../include";
    $settings_dir = "../settings";
    include "$settings_dir/conf.php";
    include "$settings_dir/database.php";        

    $result = mysql_query("select last_click from ".$mysql_table_prefix."links  where url = '$url' LIMIT 1");
    echo mysql_error();
    $last_click = mysql_result($result, '0');   //  get time of last click

    if ($last_click+$click_wait < $time) {      //  prevent  promoted clicks, else remember this click     
        mysql_query ("update ".$mysql_table_prefix."links set click_counter=click_counter+1, last_click='$time', last_query='$query' where url = '$url' LIMIT 1");
        echo mysql_error();                        
    }
    exit ('');      //  Good-bye, we've got your click.

?>