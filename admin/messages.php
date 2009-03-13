<?php
	error_reporting (E_ALL);

    $messages = Array (
     "validSitemap" => Array (
    	0 => "  <br /><span class='warnok'> 
                >>> Valid sitemap.xml found for this site <<< 
                </span><br /><br />  ",
    	1 => "Valid sitemap.xml found for this site.\n"
     ),
     "invalidSitemap" => Array (
    	0 => "  <br /><span class='warn'>
                >>> sitemap.xml found for this site, but unable to extract any links from that file. <<< <br />
                >>> Continue index/redindex with links as stored in Sphiders database for this site. <<<
                </span><br /><br />",
    	1 => "Invalid Sitemap found.\nContinue index/redindex with links as stored in Sphiders database for this site.\n"
     ),
     "ReindexFinish" => Array (
    	0 => "  <p class='bd'><span class='em'><br /><br />Re-Indexing finished.<br /><br /></span></p>
                <p class='evrow'><a class='bkbtn' href='admin.php' title='Go back to Admin'>Back</a></p>",
    	1 => "Re-Indexing finished.\n"
     ),
     "NewStart" => Array (
    	0 => "  <div class='submenu cntr'>
                <span class='em'>
                Now indexing all new sites<br /><br /><br />
                </span></div>",
    	1 => "Now indexing all new sites.\n"
     ),   
     "NewFinish" => Array (
    	0 => "  <p class='bd'><span class='em'><br /><br />Indexing of new sites finished.<br /><br /></span></p>
                <p class='evrow'><a class='bkbtn' href='admin.php' title='Go back to Admin'>Back</a></p>",
    	1 => "Index the new sites finished.\n"
     ),   
     "errorOpenPDF" => Array (
    	0 => " <br />\n<span class='warnadmin'>Error opening this PDF file. Perhaps because it is corrupted.</span>\n",
    	1 => " Error opening this PDF file. Perhaps because it is corrupted.\n"
     ),    
     "permissionError" => Array (
    	0 => " <br />\n<span class='warnadmin'>Error related to PDF permissions.</span>\n",
    	1 => " Error related to PDF permissions.\n"
     ),    
     "ufoError" => Array (
    	0 => " <br />\n<span class='warnadmin'> Unable to process this PDF document.<br />Converter didn't pass back the ready status or any known error message.</span>\n",
    	1 => " Unable to process this PDF document.\nNot passed back the ready status or any known error message.\n"
     ),        
     "nothingFound" => Array (
    	0 => " <br />\n<span class='warnadmin'> Converter did not sent any error message,<br />but was unable to extract any word from this file.</span>\n",
    	1 => " Converter did not sent any error message, but was unable to extract any word from that file.\n"
     ),        
     "noFollow" => Array (
    	0 => " <span class='warnadmin'>No-follow flag set</span><br />\n",
    	1 => " No-follow flag set."
     ),
     "inDatabase" => Array (
    	0 => " <br />\n<span class='warnadmin'>already in database</span></p>\n",
    	1 => " already in database\n"
     ),
     "completed" => Array (
    	0 => "	<p class='alert'><span class='em'>Indexing completed at: </span> %cur_time.</p>\n
                <p>[Go back to <span  class='em'><a href='admin.php'>admin</a></span>]</p>\n",
    	1 => "Completed at %cur_time.\n"
     ),
     "starting" => Array (
    	0 => " <p class='alert'><span class='em'>Start indexing at %cur_time.</p>\n",
    	1 => " Starting indexing at %cur_time.\n"
    	 ),
     "quit" => Array (
    	0 => "\n    \n</div>\n</body>\n</html>",
    	1 => ""
     ),
     "pageRemoved" => Array (
    	0 => "<br />\n	<span class='warnadmin'>Page removed from index</span><br />\n",
    	1 => " Page removed from index.\n"
     ),
      "continueSuspended" => Array (
    	0 => "	<span class='bd'>Continuing suspended indexing.</span>\n",
    	1 => "Continuing suspended indexing.\n"
     ),
      "indexed" => Array (
    	0 => "<span class='bd warnok'><br />Indexed</span>\n",
    	1 => " \nIndexed\n"
     ),
    "duplicate" => Array (
    	0 => "<br /><span class='warnadmin'>Content of page is duplicate with:</span><br />\n",
    	1 => " Content of page is duplicate with:\n"
     ),    
    "md5notChanged" => Array (
    	0 => "<br /><span class='warnadmin'>MD5 sum checked. Page content not changed</span>\n",
    	1 => " MD5 sum checked. Page content not changed.\n"
     ),
    "metaNoindex" => Array (
    	0 => "<br /><span class='warnadmin'>No-Index flag set in meta tags.</span>\n",
    	1 => " No-Index flag set in meta tags.\n"
     ),
      "re-indexed" => Array (
    	0 => "<br /><span class='warnok em'>Re-indexed</span><br />\n",
    	1 => " Re-indexed\n"
     ),
    "start_reindex" => Array (
    	0 => "	<p class='bd'>Starting Re-index.</p>\n",
    	1 => " Starting re-index.\n"
    	 ), 
    "start_link_check" => Array (
    	0 => "	<p class='bd'>Starting Link-check.</p>\n",
    	1 => " Starting Link-check.\n"
    	 ), 
      "link_okay" => Array (
    	0 => "<br /><span class='warnok em'>Okay, page is available.</span><br />\n",
    	1 => " Okay, page is available.\n"
     ),
      "link_local" => Array (
    	0 => "<br /><span class='warnok em'>Not checked, local link.</span><br />\n",
    	1 => " Not checked, local link.\n"
     ),     
    "minWords" => Array (
    	0 => " <br /><span class='warnadmin'>Page contains less than $min_words_per_page words</span><br />\n",
    	1 => " Page contains less than $min_words_per_page words.\n"
     )
    );

    function printDupReport($dup_url, $cl) {
    	global $print_results, $log_format;
    	$log_msg_txt = "$dup_url\n";
    	$log_msg_html = "<span><a href='$dup_url' target='_blank'\n	title='Open this link in new window'>$dup_url</a></span><br />\n";
    	if ($print_results) {
    		if ($cl==0) {
    			print $log_msg_html; 
    		} else {
    			print $log_msg_txt;
    		}
    		ob_flush();
    		flush();
    	}
    	if ($log_format=="html") {
    		writeToLog($log_msg_html);
    	} else {
    		writeToLog($log_msg_txt);
    	}

    }

    function printRobotsReport($num, $thislink, $cl) {
    	global $print_results, $log_format;
    	$num = rtrim(trim($num), '.');
    	$log_msg_txt = "$num. Link $thislink: File checking forbidden in robots.txt file.\n";
    	$log_msg_html = "<p class='alert'>\n<span class='em'>$num</span>. Link <span class='em'>$thislink</span><br /><span class='warnadmin'>File checking forbidden in robots.txt file</span></p>\n";
    	if ($print_results) {
    		if ($cl==0) {
    			print $log_msg_html; 
    		} else {
    			print $log_msg_txt;
    		}
    		ob_flush();
    		flush();
    	}
    	if ($log_format=="html") {
    		writeToLog($log_msg_html);
    	} else {
    		writeToLog($log_msg_txt);
    	}

    }

    function printUrlStringReport($num, $thislink, $cl) {
    	global $print_results, $log_format;
    	$num = rtrim(trim($num), '.');
    	$log_msg_txt = "$num. Link $thislink: \n File checking forbidden  by required/disallowed string rule.\n";
    	$log_msg_html = "<p class='alert'>\n<span class='em'>$num</span>. Link <span class='em'>$thislink</span><br /> <span class='warnadmin'>\n File checking forbidden by required/disallowed string rule</span></p>";
    	if ($print_results) {
    		if ($cl==0) {
    			print $log_msg_html;
    		} else {
    			print $log_msg_txt;
    		}
    		ob_flush();
    		flush();
    	}

    	if ($log_format=="html") {
    		writeToLog($log_msg_html);
    	} else {
    		writeToLog($log_msg_txt);
    	}
    }

    function printRetrieving($num, $thislink, $cl) {
    	global $print_results, $log_format;
    	$num = rtrim(trim($num), '.');
    	$log_msg_txt = "$num. Retrieving: $thislink at " . date("H:i:s").".\n";
    		if ($num & 1) {
    			$log_msg_html = "	<p class='evrow'>\n";
    		} else {
    			$log_msg_html = "	<p class='odrow'>\n";
    		}
    	$log_msg_html .="	<span class='em'>$num</span>. Retrieving: <span class='em'>";
    	$log_msg_html .="<a href='$thislink' target='_blank'\n	title='Open link in new window'>$thislink</a></span><br />\n";
    	if ($print_results) {
    		if ($cl==0) {
    			print $log_msg_html;
    		} else {
    			print $log_msg_txt;
    		}
    		ob_flush();
    		flush();
    	}

    	if ($log_format=="html") {
    		writeToLog($log_msg_html);
    	} else {
    		writeToLog($log_msg_txt);
    	}
    }

    function printLinksReport($numoflinks, $all_links, $cl) {
    	global $print_results, $log_format;
    	$log_msg_txt = " Legit links found: $all_links - New links found: $numoflinks\n";
    	$log_msg_html = "<span class='bd'><br /> Links found: $all_links - New links: $numoflinks</span><br /><br />\n";
    	if ($print_results) {
    		if ($cl==0) {
    			print $log_msg_html;
    		} else {
    			print $log_msg_txt;
    		}
    		ob_flush();
    		flush();
    	}

    	if ($log_format=="html") {
    		writeToLog($log_msg_html);
    	} else {
    		writeToLog($log_msg_txt);
    	}
    }

    function printHTMLHeader($omit, $url, $cl) {
    	global $print_results, $log_format, $template_path, $copy, $plus_nr;
        
    	$log_msg_html_0 = "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
    <html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
    <head>\n<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
     <title>Sphider-plus Log File output</title>
     <link rel='stylesheet' href='$template_path/thisstyle.css' media='screen' type='text/css' />
     <link rel='stylesheet' href='../$template_path/thisstyle.css' media='all' type='text/css' />
    </head>
    <body>
     <div class='submenu cntr'>Sphider-plus v.$plus_nr -  Log File output</div> <br />      
     <div id='report'>
     <br />
     <p>[Go back to <span  class='em'><a href='admin.php'>admin</a></span>]</p>
     <br />
    ";
        
    	if ($print_results) {
    		if ($cl==0) {
    			echo $log_msg_html_0;
    		}
    		ob_flush();
    		flush();
    	}

    	if ($log_format=="html") {
            $copy = '0';
    		writeToLog($log_msg_html_0);
            $copy = '1';
    	} 
    }
    
    
    function printHeader($omit, $url, $cl) {
    	global $print_results, $log_format, $template_path;
        ob_start();
    	if (count($omit) > 0 ) {
    		$urlparts = parse_url($url);
    		foreach ($omit as $dir) {
    			if ($cl==0) {
    			$omits[] = "         <li>".$urlparts['scheme']."://".$urlparts['host'].$dir."</li>";
    			}else{
    			$omits[] = $urlparts['scheme']."://".$urlparts['host'].$dir;
    			}
    		}
    	}

    	$log_msg_txt = "\nSpidering $url\n";
    	if (count($omit) > 0) {
    		$log_msg_txt .= "Disallowed files and directories in robots.txt:\n";
    		$log_msg_txt .= implode("\n", $omits);
    		$log_msg_txt .= "\n\n";
    	}


    	$log_msg_html_1 .= "     <h1>Spidering: <span class='warnok'>$url</span></h1>\n";
    	
    	$log_msg_html_link = "	<p>[Go back to <span  class='em'><a href='admin.php'>admin</a></span>]</p>";

    	if (count($omit) > 0) {
    		$log_msg_html_2 .=  "     <div class='alert'>\n		<p class='em'>Disallowed files and directories in robots.txt:</p>\n		<ul class='txt'>\n";
    		$log_msg_html_2 .=  implode("\n", $omits);
    		$log_msg_html_2 .=  "\n</ul>\n</div>\n";
    	}

    	if ($print_results) {
    		if ($cl==0) {
    			echo $log_msg_html_1.$log_msg_html_2;
    		} else {
    			print $log_msg_txt;
    		}
            
    		ob_flush();
    		flush();
    	}

    	if ($log_format=="html") {
    		writeToLog($log_msg_html_1.$log_msg_html_2);
    	} else {
    		writeToLog($log_msg_txt);
    	}
    }

    function printSitemapCreated($filename) {
    	global $print_results, $log_format;
    	$log_msg_html = "<p class='bd'>Sitemap created: $filename</p>";
    	$log_msg_txt = "Sitemap created: $filename\n";

    	if ($print_results) {
    		if ($cl==0) {
    			echo $log_msg_html;
    		} else {
    			print $log_msg_txt;
    		}
            
    		ob_flush();
    		flush();
    	}

    	if ($log_format=="html") {
    		writeToLog($log_msg_html);
    	} else {
    		writeToLog($log_msg_txt);
    	}
    }
    
    function printInvalidFile($filename) {
    	global $print_results, $log_format;
    	$log_msg_html = "<p class='warn'>Unable to open $filename</p>";
    	$log_msg_txt = "Unable to open $filename\n";

    	if ($print_results) {
    		if ($cl==0) {
    			echo $log_msg_html;
    		} else {
    			print $log_msg_txt;
    		}
            
    		ob_flush();
    		flush();
    	}

    	if ($log_format=="html") {
    		writeToLog($log_msg_html);
    	} else {
    		writeToLog($log_msg_txt);
    	}
    }

    function printMaxLinks($max_links) {
    	global $print_results, $log_format;
    	$log_msg_html = "   
                            <p class='em'>
                            Reached the limit of max. links = $max_links for this site.
                            <br />
                            </p>  
                        ";
    	$log_msg_txt = "Reached the limit of max. links = $max_links for this site.\n";

    	if ($print_results) {
    		if ($cl==0) {
    			echo $log_msg_html;
    		} else {
    			print $log_msg_txt;
    		}
            
    		ob_flush();
    		flush();
    	}

    	if ($log_format=="html") {
    		writeToLog($log_msg_html);
    	} else {
    		writeToLog($log_msg_txt);
    	}
    }

    function printDatabase($stats_sites, $stats_links, $stats_categories, $stats_keywords) {
    	global $print_results, $log_format;
    	$log_msg_html = "   <p class='stats'>
                            <span class='em'>Database contains: </span>".$stats_sites." sites, ".$stats_links." links, ".$stats_categories." categories and ".$stats_keywords." keywords</p><br />
                        ";
    	$log_msg_txt = "Unable to open $filename\n";

    	if ($print_results) {
    		if ($cl==0) {
    			echo $log_msg_html;
    		} else {
    			print $log_msg_txt;
    		}
            
    		ob_flush();
    		flush();
    	}

    	if ($log_format=="html") {
    		writeToLog($log_msg_html);
    	} else {
    		writeToLog($log_msg_txt);
    	}
    }

    
    function printPageSizeReport($pageSize) {
    	global $print_results, $log_format;
    	$log_msg_txt = "	Size of page: $pageSize"."kb. ";
    	if ($print_results) {
    		print $log_msg_txt;
    		ob_flush();
    		flush();
    	}

    	writeToLog($log_msg_txt);
    }

    function printUrlStatus($report, $cl) {
    	global $print_results, $log_format;
    	$log_msg_txt = "$report\n";
    	$log_msg_html = " <span class='warnadmin'>$report</span><br />\n";
    	if ($print_results) {
    		if ($cl==0) {
    			print $log_msg_html; 
    		} else {
    			print $log_msg_txt;
    		}
    		ob_flush();
    		flush();
    	}
    	if ($log_format=="html") {
    		writeToLog($log_msg_html);
    	} else {
    		writeToLog($log_msg_txt);
    	}

    }

    function printConnectErrorReport($errmsg) {
    	global $print_results, $log_format;
    	$log_msg_txt = "Establishing connection with socket failed. ";
    	$log_msg_txt .= $errmsg;

    	if ($print_results) {
    		print $log_msg_txt;
    		ob_flush();
    		flush();
    	}

    	writeToLog($log_msg_txt);
    }

    function writeToLog($msg, $copy) {
    	global $keep_log, $log_handle, $real_handle, $real_log, $copy,  $mysql_table_prefix, $refresh;
    	if($keep_log) {
    		if (!$log_handle) {
    			die ("Cannot open file for logging. ");
    		}

    		if (fwrite($log_handle, $msg) === FALSE) {
    			die ("Cannot write to file for logging. ");
    		}
                      
         	if($real_log == '1' && $copy == '1') {              //      if selected,, update also the real-time log 
                //$msg = preg_replace("@\[.*?\]@si", "<br /><p><a href='javascript:ScrollDown()'>Jump to end of page</a></p><br />",$msg);   //      replace 'Back to Admin' links with 'Jump to end of page' for rel-time output 
                $msg = preg_replace("@\[.*?\]@si", "",$msg);   //      supress 'Back to Admin' links  for rel-time output 
                $msg = preg_replace("@<p class='evrow'><a class='bkbtn'.*?Back</a></p>@si", "",$msg);   //      supress 'Back' links for rel-time output                 
                if (strpos($msg, "</html>") ) {

                    //      create 'close window' button for real-time output. 
                    $msg = " 
     <a class='navup'  href='javascript:JumpUp()' title='Jump to Page Top'>Top</a><br /><br />       
    <form  class='cntr'>   
        <table class='searchBox'>
            <tr>
                <td>                 
                <input type='submit' value='Close this window' 'title='Return to Log File output' onclick='window.close()'>
                </td>
            </tr>
        </table>    
    </form>
    <br /><br />
    </div>
                    ";
                }            
 
    			$result = mysql_query("select real_log from ".$mysql_table_prefix."real_log  LIMIT 1");
    			echo mysql_error();
                $old_log = stripslashes(mysql_result($result, '0'));    //  get previous real-log data
                $msg = addslashes("$old_log$msg");                      // build updated real-log data and save it              
    			mysql_query ("update ".$mysql_table_prefix."real_log set `real_log`='$msg' LIMIT 1");
				echo mysql_error();
                clean_resource($result);
                unset ($old_log, $msg);
            }            
    	}    
    }

    function printStandardReport($type, $cl) {
    	global $print_results, $log_format, $messages;
    	if ($print_results) {
    		print str_replace('%cur_time', date("H:i:s"), $messages[$type][$cl]);
    		ob_flush();
    		flush();
    	}

    	if ($log_format=="html") {
    		writeToLog(str_replace('%cur_time', date("H:i:s"), $messages[$type][0]));
    	} else {
    		writeToLog(str_replace('%cur_time', date("H:i:s"), $messages[$type][1]));
    	}
    }


?>
