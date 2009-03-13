<?php
    error_reporting (E_ALL ^ E_NOTICE);
    $include_dir  = "../include";
    $settings_dir = "../settings";
    include "$settings_dir/conf.php";
    set_time_limit (0);
    $template_dir = "../templates";
    $template_path = "$template_dir/$template";

    echo "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
    <html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
    <head>
     <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
     <title>Sphider-plus administrator tools</title>
     <link rel='stylesheet' type='text/css' href='$template_path/thisstyle.css' />   
     <script type='text/javascript' src='dbase.js'></script>
     <script type='text/javascript'>     
        function JumpBottom () {
            window.scrollTo(0,1000);
        } 
     </script>    
    </head>
    <body>
    <noscript>
        <div id='main'>
            <h1 class='cntr warn'>
                <br />
                Attention: Your browser does not support JavaScript.
                <br /><br />
                You will not get full functionality of Sphider-plus Administrator.
                <br /><br />
            </h1>
        </div>
    </noscript>
    ";
    $php_vers = phpversion();
    if (preg_match('/^4\./', trim($php_vers)) == '1') {
        echo "<br />
            <div id='main'>
            <h1 class='cntr'>
            Sphider-plus. The Open-Source PHP Search Engine
            </h1>
                <div class='cntr warnadmin'>
                    <br />
                    Your current PHP version is $php_vers
                    <br /><br />
                    Sorry, but Sphider-plus v. $plus_nr requires PHP 5.x
                    <br /><br />
                </div>
            </div>
            </body>
            </html>
        ";
        die ('');
    }
    include "auth.php";    