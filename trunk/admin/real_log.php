<?php
/***********************************************************
If 'Real-time Logging' is enabled, this script takes over to display latest logging data.
Requesting fresh data from the JavaScript file 'real_ping.js' ,
all new logging data will always been placed into <div id='realLogContainer'  />
***********************************************************/

    error_reporting (E_ALL ^ E_NOTICE);
    $include_dir  = "../include";
    $settings_dir = "../settings";
    include "$settings_dir/conf.php";
    set_time_limit (0);
    $template_dir = "../templates";
    $template_path = "$template_dir/$template";

    echo "
<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
    <html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
    <head>
     <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
     <title>Log File real-time output</title>
     <link rel='stylesheet' href='$template_path/thisstyle.css' media='screen' type='text/css' />
     <link rel='stylesheet' href='../$template_path/thisstyle.css' media='all' type='text/css' />
     <meta http-equiv='cache-control' content='no-cache'>
     <meta http-equiv='pragma' content='no-cache'>        
     <script type='text/javascript' src='real_ping.js'></script>   
    </head>
    <body onload='process()'>   
     <div class='submenu cntr y3'>Sphider-plus v.$plus_nr - Real-time Logging.
     <br /><br />     
     Update every $refresh seconds.</div>     
     <div id='realLogContainer'  /> 
  </body>
</html>
    ";
?>

