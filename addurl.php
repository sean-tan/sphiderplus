<?php;
 
	$admin_dir 		= "./admin";
	$include_dir 	= "./include"; 
	$template_dir 	= "./templates"; 
	$settings_dir 	= "./settings"; 
	$language_dir 	= "./languages";
    
	include 		("$settings_dir/conf.php"); 
	
	require_once	("$settings_dir/database.php");    
	require_once    ("$language_dir/en-language.php");    
	require_once	("$include_dir/searchfuncs.php");
	require_once	("$include_dir/categoryfuncs.php");	
	require_once    ("$include_dir/commonfuncs.php");
 
    $date           = strftime("%d.%m.%Y");                                 //      Format for date
    $time           = date("H:i");                                          //      Format for time    
    $mailer         = "$mytitle Addurl-mailer";                             //      Name of mailer 
    $subject1       = "A new site suggestion arrived for Sphider-plus";     //      Subject for administrator e-mail when a new suggestion arrived
    $category_id    = '';

    if ($auto_lng == 1) {   //  if enabled in Admin settings get country code of calling client               
        if ( isset ( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) { 
            $cc = substr( htmlspecialchars($_SERVER['HTTP_ACCEPT_LANGUAGE']), 0, 2);            
            $handle = @fopen ("$language_dir/$cc-language.php","r"); 
            if ($handle) { 
                $language = $cc; // if available set language to users slang 
                
            } 
            else { 
                include "$language_dir/$language-language.php"; 
            } 
        @fclose($handle); 
        } 
        else { 
            include "$language_dir/$language-language.php"; 
        }                 
    }
	require_once    ("$language_dir/$language-language.php");   
	extract(getHttpVars());

    echo "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
        <html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
        <head>
        <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
        <title>$mytitle. Suggest a new site</title>
        <link rel='stylesheet' type='text/css' href='$template_dir/$template/thisstyle.css' />
        <script type='text/javascript' src='dbase.js'></script>
        </head>
        <body>
    ";    
    			
	if ($B1 == $sph_messages['submit']) {        
        if($captcha == 1) {     // if Admin selected, evaluate Captcha 
            error_reporting(E_ERROR);    
            session_start();
            
            if (($_POST['captext']) != $_SESSION['currentcaptcha']) {    
                echo "<h1>$mytitle</h1><br />
                    <p class='em cntr warnadmin'> 
                    ".$sph_messages['invalidCaptcha']."
                    <br />               
                    </p>
                    <br />
                    <a class='bkbtn' href='addurl.php' title='Go back to Suggest form'>".$sph_messages['BackToSubForm']."</a>                
                    </body>
                    </html>
                ";
                die ('');
            }       
            error_reporting(E_ALL);
            session_destroy();    
        }
        
		// 	clean input			
		$url 		= 	cleaninput(cleanup_text(trim(substr ($url, 0,100))));
		$title 		= 	cleaninput(cleanup_text(trim(substr ($title, 0,100))));
		$description = 	cleaninput(cleanup_text(nl2br(trim(substr ($description, 0,250)))));
		$email 		= 	cleaninput(cleanup_text(trim(substr ($email, 0,100))));
		
		//	check Url
		$input  = $url;        
		validate_url($input);
        $url = $input;
			
		//	check Title
		if(!preg_match('/^[[:print:]]{5,100}$/', $title)) {
            echo "<h1>$mytitle</h1><br />
                <p class='em cntr warnadmin'> 
                ".$sph_messages['InvTitle']."
                <br />               
                </p>
                <br />
                <a class='bkbtn' href='addurl.php' title='Go back to Suggest form'>".$sph_messages['BackToSubForm']."</a>                
                </body>
                </html>
            ";
            die ('');
        }
        
		//	check Description input
		if(!preg_match('/^[[:print:]]{5,250}$/', $description)) {
            echo "<h1>$mytitle</h1><br />
                <p class='em cntr warnadmin'> 
                ".$sph_messages['InvDesc']."
                <br />               
                </p>
                <br />
                <a class='bkbtn' href='addurl.php' title='Go back to Suggest form'>".$sph_messages['BackToSubForm']."</a>                
                </body>
                </html>
            ";
            die ('');
        }
        
        //	check e-mail account 
		$input  = $email;
		validate_email($input);
        $email = $input;
                		
		//	Is the new URL banned ?			
		$res = 0;
		$Burl = 0;
		$Bquery = "SELECT * FROM ".$mysql_table_prefix."banned LIMIT 0 , 30000";
		$Bresult = mysql_query($Bquery);
		echo mysql_error();

		if (mysql_num_rows($Bresult) <> '') {
			while ($Brow = mysql_fetch_array($Bresult)) {
				if (!eregi($Brow['domain'],$url)){
					$Burl = 0;
				} else {
					$Burl = 1;
                    echo "<h1>$mytitle</h1><br />
                        <p class='em'>
                        Sorry to tell you.<br />
                        But the site you suggested is banned from this search engine.<br />
                        We will not index that site.<br />
                        </p>
                        <a class='bkbtn' href='search.php' title='Go back to Sphider-plus'>Back to Sphider-plus</a>                                                                    
                        </body>
                        </html>
                    ";
					die();
				}
			}
		} else { $Burl = 0; }

		//	suggested URL is already indexed?
        $new_url = 0;
		$query = "SELECT * FROM ".$mysql_table_prefix."sites where url like '%$url%'";
		$result = mysql_query($query);
		echo mysql_error();
				
		if (mysql_num_rows($result) <> '') {			
			$new_url = 0;
            echo "<h1>$mytitle</h1><br />
                <p class='em'>
                Thank you for your suggestion.<br />
                But the suggested site is already indexed by this search engine.<br />
                </p>
                <a class='bkbtn' href='search.php' title='Go back to Sphider-plus'>Back to Sphider-plus</a>                                            
        		</body>
                </html>
            ";
			die();
		}

		//	check if new URL was already suggested before
		$new_url = 0;
		$query = "SELECT * FROM ".$mysql_table_prefix."addurl LIMIT 0 , 300";
		$result = mysql_query($query);
		echo mysql_error();
				
		if (mysql_num_rows($result) <> '') {
			while ($row = mysql_fetch_array($result)) {
				if ($url != $row['url']){
					$new_url = 1;
				} else {
					$new_url = 0;
                        echo "<h1>$mytitle</h1><br />
                            <p class='em'>
                            Thank you for your suggestion.<br />                            
                            But this Url was already suggested by someone else before.<br />
                            </p>
                            <a class='bkbtn' href='search.php' title='Go back to Sphider-plus'>Back to Sphider-plus</a>                                            
                            </body>
                            </html>
                    ";
					die();
				}
			}
		} else { 
            $new_url = 1; 
        }       

        if ($new_url == 1) {                
            //	Time to store all into database and output a thanks for suggestion		
    		mysql_query("INSERT INTO ".$mysql_table_prefix."addurl (url, title, description, category_id,account) VALUES ('".$url."', '".$title."', '".$description."', '".$category_id."', '".$email."')");
            echo mysql_error();

    		echo "<h1>$mytitle</h1><br />
                <p class='em'>                
                Thank you very much.<br />                
                We will check your suggestion " .$url. " within the next future.<br />                
                If the new site fulfills all requirements of this search engine, it will be indexed immediately.<br />               
                About our decission we will inform you by e-mail.<br />               
                Thanks again for your effort.<br />               
                </p>
                <a class='bkbtn' href='search.php' title='Go back to Sphider-plus'>Back to Sphider-plus</a>                
            ";
                        
            //	Finally inform the administrator about the new suggestion								
    		$title  = str_replace ('\\','',$title);			//	recover title
    		$title	= str_replace ('&quot','"',$title);	
    		
    		$description	= str_replace ('\\','',$description);   //	recover description
    		$description	= str_replace ('&quot','"',$description);
            $cat ='';
            
            if ($category_id != 0) {
                $query = "SELECT * FROM ".$mysql_table_prefix."categories WHERE category_id = $category_id";
                $result = mysql_query($query);
                echo mysql_error();
                mysql_close();

                $cat ='';
                if ($result !=0) {
                    $row = mysql_fetch_array($result);
                    $cat = $row['category'];            //      fetch name of category   
                }
            }            
    		$header = "from: $mailer<".$dispatch_email.">\r\n";
    		$header .= "Reply-To: ".$dispatch_email."\r\n";            
            $subject1    = "A new site suggestion arrived for Sphider-plus";  //  Subject for e-mail to administrator when suggestion arrived
            
            if ($addurl_info == 1) { //  should we inform the admin by e-mail?
//      Text for e-mail to administrator when suggestion arrived    
$text1 = "On $date at $time a new site was suggested!\n
The following dates were submitted:\n\n
URL           : $url\n
Titel         : $title\n
Description   : $description\n
Category      : $cat\n
E-mail account: $email\n\n
This mail was automatically generated by: $mailer.\n\n";
									
                if (mail($admin_email,$subject1,$text1,$header) or die ("<br /><br /><br />Error to inform the administrator of this site ( $admin_email )<br /><br />Never the less your data was stored on our database.<br /><br />They will be checked within the next future.<br /><br />About the result you will be informed as soon as possible by e-mail.<br /><br />"));		
            }
        }
        
	} else {    //  Here we start the output of the Submission form                   
        echo " 
            <h1> $mytitle 
            <br /><br />
            ".$sph_messages['SubForm']."</h1>  
            <br />    
            <p class='advsrch'>
            ".$sph_messages['SubmitHeadline']."            
            </p>
            <p class='advsrch'>
            ( ".$sph_messages['AllFields']." ! )
             </p>
            <br />
            <div class='panel w75'>
            <form  class='txt' name='add_url' action='addurl.php'  method='post'>
            <table  class='searchBox'>
                <tr >
                    <td>".$sph_messages['New_url']."</td>
                    <td><input type='text' name='url' value='http://' size='52' maxlength='100'></td>
                </tr>
                <tr>
                    <td>".$sph_messages['Title']."</td>
                    <td><input type='text' name='title' size='52' maxlength='100'></td>
                </tr>
                <tr>
                    <td><br /><br />".$sph_messages['Description']."</td>
                    <td><textarea wrap='physical' class='farbig' rows='5' name='description' cols='40'></textarea></td>
                </tr>
        ";
        
        if($show_categories =='1') {     // if Admin selected, show categories
            echo "<tr>
                    <td>".$sph_messages['Category']."</td>
                    <td><select name=\"category_id\" size=\"1\">
            ";
            list_catsform (0, 0, "white", "");
            echo "</select>            
                </td>
                </tr>
            ";
        }
        
        echo "
            <tr>
            	<td>".$sph_messages['Account']."</td>
                <td><input type='text' name='email' size='52' maxlength='100'></td>
            </tr>
        ";

        if($captcha == 1) {     // if Admin selected, show Captcha
            echo "
            <tr>
                <td>
                ".$sph_messages['enterCaptcha']."</td>        
                <td>
                <br />
            	<img src='$include_dir/make_captcha.php' name='capimage' border='0'>
                <br /><br />    
            	<input type='text' value='' name='captext' /></textarea>
                <br /><br />    
                </td>
            </tr> 
            ";
        }
        $submit = $sph_messages['submit'];
        echo "
            <tr>
                <td>
                </td>
                <td>
                <input class='submit-button' type='submit' value='$submit' name='B1'>
                <br /><br />
                <td>
            </tr>
            </table>		
            </form>
            </div>
            <br />
        ";
    }
    
// The following should only be removed if you contribute to the Sphider project..
// Note that this is a requirement under the GPL licensing agreement, which Sphider-plus acknowledges.	
    footer();
    
    echo "
        </div>
        </body>
        </html>
    ";
?>

