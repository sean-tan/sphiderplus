<?php 

	set_time_limit (0);
    
	$include_dir = "../include";
	include "auth.php";
	require_once ("$include_dir/commonfuncs.php");
    require_once ("../converter/ConvertCharset.class.php");    
	$all = 0; 
	extract (getHttpVars());
	$settings_dir =  "../settings";
	require_once ("$settings_dir/conf.php");
	$template_dir = "../templates";
	$template_path = "$template_dir/$template";
	include "messages.php";
	include "spiderfuncs.php";
	error_reporting (E_ALL ^ E_NOTICE ^ E_WARNING);

	$delay_time = 0;
	$command_line = 0;
    $copy = '1';    
	$tmp_urls  = Array(); 
    
	if (isset($_SERVER['argv']) && $_SERVER['argc'] >= 2) {
		$command_line = 1;
		$ac = 1; //argument counter
		while ($ac < (count($_SERVER['argv']))) {
			$arg = $_SERVER['argv'][$ac];

			if ($arg  == '-all') {
				$all = 1;
				break;
			} else if ($arg  == '-u') {
				$url = $_SERVER['argv'][$ac+1];
				$ac= $ac+2;
			} else if ($arg  == '-f') {
				$soption = 'full';
				$ac++;
			} else if ($arg == '-d') {
				$soption = 'level';
				$maxlevel =  $_SERVER['argv'][$ac+1];;
				$ac= $ac+2;
			} else if ($arg == '-l') {
				$domaincb = 1;
				$ac++;
			} else if ($arg == '-r') {
				$reindex = 1;
				$ac++;
			} else if ($arg  == '-m') {
				$in =  str_replace("\\n", chr(10), $_SERVER['argv'][$ac+1]);
				$ac= $ac+2;
			} else if ($arg  == '-n') {
				$out =  str_replace("\\n", chr(10), $_SERVER['argv'][$ac+1]);
				$ac= $ac+2;
			} else {
				commandline_help();
				die();
			}
		
		}
	}
	
	if (isset($soption) && $soption == 'full') {
		$maxlevel = -1;

	}

	if (!isset($domaincb)) {
		$domaincb = 0;
	}

	if(!isset($reindex)) {
		$reindex=0;
	}

	if(!isset($use_robot)) {
		$use_robot=0;
	}
    
	if(!isset($maxlevel)) {
		$maxlevel=0;
	}
    
	if ($keep_log) { 		
        //  prepare current log file
        $log_file =  $log_dir."/".Date("ymdHi").".html";     		
        if (!$log_handle = fopen($log_file, 'w')) {             //      create a new log file     
            $logdir = mkdir($log_dir);                          //      try to create a log directory
            if ($logdir != '1') {
                die ("Logging option is set, but cannot create folder for logging files.");
            } else {
                if (!$log_handle = fopen($log_file, 'w')) {     //      try again to create a log file
                    die ("Logging option is set, folder was created, but cannot open a file for logging.");
                }
            }
        }      
	}
  
    printHTMLHeader($omit, $url, $cl) ;	
    
	if ($all ==  '1') {
		index_all();      
	} 
        
    if ($all ==  '2') {
		index_new();         
	} 

    if ($all != '1' && $all != '2') {   
		if ($reindex == 1 && $command_line == 1) {        
			$result=mysql_query("select url, spider_depth, required, disallowed, can_leave_domain from ".$mysql_table_prefix."sites where url='$url'");
			echo mysql_error();
			if($row=mysql_fetch_row($result)) {
				$url = $row[0];
				$maxlevel = $row[1];
				$in= $row[2];
				$out = $row[3];
				$domaincb = $row[4];
				if ($domaincb=='') {
					$domaincb=0;
				}
				if ($maxlevel == -1) {
					$soption = 'full';
				} else {
					$soption = 'level';
				}
			}
            clean_resource($result) ;            
		}               
        
		if (!isset($in)) {
			$in = "";
		}
		if (!isset($out)) {
			$out = "";
		}
             
		index_site($url, $reindex, $maxlevel, $soption, $in, $out, $domaincb, $use_robot);
	}

	printStandardReport('quit',$command_line);
    
	if ($email_log) {
		$indexed = ($all==1) ? 'ALL' : $url;
		$log_report = "";
        
		if ($log_handle) {
			$log_report = "Log saved into $log_file";
		}
		mail($admin_email, "Sphider indexing report", "Sphider has finished indexing $indexed at ".date("y-m-d H:i:s").". ".$log_report);
	}
    
	if ( $log_handle) {
		fclose($log_handle);
	}
	
	function microtime_float(){
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}
	
	function index_url($url, $level, $site_id, $md5sum, $domain, $indexdate, $sessid, $can_leave_domain, $reindex) {
		global $entities, $min_delay, $link_check, $command_line, $min_words_per_page, $dup_content, $dup_url; 
		global $min_words_per_page,  $supdomain, $smp, $follow_sitemap,  $max_links, $realnum, $local;     
		global $mysql_table_prefix, $user_agent, $tmp_urls, $delay_time, $domain_arr, $utf8, $home_charset, $url_status;
        
		$needsReindex = 1;
		$deletable = 0;

		$url_status = url_status($url);
		$thislevel = $level - 1;
        
        if ($data['nofollow'] != 1) {               
            if ($smp != 1 && $follow_sitemap == 1) {        //  enter here if we don't already know a valid sitemap and if admin settings allowed us to do so
                $tmp_urls = get_temp_urls($sessid);         //  reload previous temp                  
                $url2 = remove_sessid(convert_url($url));
                
                // get folder where sitemap should be and if exists, cut existing filename, suffix and subfolder 
//                $local = "http://localhost/publizieren/";   //  your base adress for your local server
                $sitemap_name = "sitemap.xml";              //  could be individualized
                $host = parse_url($url2);
                $hostname = $host[host];
             
                if ($hostname == 'localhost') $host1 = str_replace($local,'',$url2);
                $pos = strpos($host1, "/");                //      on local server delete all behind the /
                
                if ($pos) $host1 = substr($host1,0,$pos);   //      build full adress again, now only until host   
                if ($hostname == 'localhost') {
                    $url2 = ("$local$host1");
                }else {
                    $url2 = ("$host[scheme]://$hostname");
                } 
                $input_file = "$url2/$sitemap_name";        // create path to sitemap 
                      
                if ($handle = fopen($input_file, "r")) { // happy times, we found a new sitemap
                    $links = get_sitemap($input_file, $mysql_table_prefix); // now extract links from sitemap.xml 

                    if ($links !='') {      //  if links were extracted from sitemap.xml
                        reset ($links);
                        
                        while ($thislink = each($links)) {
                            //  check if we already know this link as a site url
            				$result = mysql_query("select url from ".$mysql_table_prefix."sites where url like '$thislink[1]%'");
            				echo mysql_error();
            				$rows = mysql_num_rows($result);

                            if ($rows == '0') {     // for all new links: save in temp table
                                mysql_query ("insert into ".$mysql_table_prefix."temp (link, level, id) values ('$thislink[1]', '$level', '$sessid')");
                                echo mysql_error();
                            }                            
                        }   
                        
                        clean_resource($result) ; 
                        $smp = '1';  //     there was a valid sitemap and we stored the new links 
                    } 
                    unset ($links, $input_file);
                    fclose ($handle);                   
                } 
            }
        } else {                
            printStandardReport('noFollow',$command_line);
        }        
                
		if (strstr($url_status['state'], "Relocation")) {
			$url = eregi_replace(" ", "", url_purify($url_status['path'], $url, $can_leave_domain));

			if ($url <> '') {
				$result = mysql_query("select link from ".$mysql_table_prefix."temp where link='$url' && id = '$sessid'");
				echo mysql_error();
				$rows = mysql_num_rows($result);
				if ($rows == 0) {
					mysql_query ("insert into ".$mysql_table_prefix."temp (link, level, id) values ('$url', '$level', '$sessid')");
					echo mysql_error();
				}
                clean_resource($result) ;
			}
			$url_status['state'] == "redirected";
		}

		ini_set("user_agent", $user_agent);
		if ($url_status['state'] == 'ok') {
			$OKtoIndex = 1;
			$file_read_error = 0;
			
			if (time() - $delay_time < $min_delay) {
				sleep ($min_delay- (time() - $delay_time));
			}
			$delay_time = time();
			if (!fst_lt_snd(phpversion(), "4.3.0")) {            
				$file = file_get_contents($url);
				if ($file === FALSE) {
					$file_read_error = 1;
				}
			} else {
				$fl = @fopen($url, "r");
				if ($fl) {
					while ($buffer = @fgets($fl, 4096)) {
						$file .= $buffer;
					}
                    unset ($buffer);
				} else {
					$file_read_error = 1;
				}

				fclose ($fl);
			}
                       
			if ($file_read_error || $utf8 ==1) {
                unset ($file);
				$contents = getFileContents($url);          // parse_url to get charset
				$file = $contents['file'];
			}
			
			$pageSize = number_format(strlen($file)/1024, 2, ".", "");
			printPageSizeReport($pageSize);

			if ($url_status['content'] != 'text') {                                 
				$file = extract_text($file, $url_status['content']);     //for DOCs, PDFs etc we need special converter                
                if ($file == 'ERROR') {     //      if error, suppress further indexing
                    $OKtoIndex = 0;
                    $file_read_error = 1; 
                }
            }

            if ($utf8 == 1) {                               //   enter here if file should be translated into utf-8      
                $charSet =$contents['charset'];                 
                if ($charSet == '') {                       // if we did not find any charset, we will use our own
                    $charSet = $home_charset;
                }
                $charSet = strtoupper(trim($charSet));

                if (strpos($charSet, '8859')) {
                    $conv_file = html_entity_decode($file);                   
                } else {
                    $conv_file = $file;     //  pure code
                }
              
                if($charSet != "UTF-8"){                    //  enter here only, if site / file is not jet UTF-8 coded              
                    $iconv_file = iconv($charSet,"UTF-8",$conv_file);   //      if installed, first try to use PHP function iconv                           
                    if(trim($iconv_file) == ""){            // iconv is not installed or input charSet not available. We need to use class ConvertCharset
                        $charSet = str_ireplace ('iso-','',$charSet);
                        $charSet = str_ireplace ('iso','',$charSet);                       
                        $NewEncoding = new ConvertCharset($charSet, "utf-8"); 
                        $NewFileOutput = $NewEncoding->Convert($conv_file); 
                        $file = $NewFileOutput;                                                 
                     }else{ 
                        $file = $iconv_file;
                    }
                    unset ($conv_file, $iconv_file, $NewEncoding, $NewFileOutput);
                }                             
            }

			$newmd5sum = md5($file);            
			if ($md5sum == $newmd5sum) {
				printStandardReport('md5notChanged',$command_line);
				$OKtoIndex = 0;
                $realnum -- ;
			} 
            
            if ($dup_content == '1') {
                //     check for duplicate page content
            	$result = mysql_query("select link_id from ".$mysql_table_prefix."links where md5sum='$newmd5sum'");
            	echo mysql_error();
                
            	if (mysql_num_rows($result) > 0) {  //  display warning message and urls with duplicate content

                    printStandardReport('duplicate',$command_line);                
                    $num_rows = mysql_num_rows($result);
                    for ($i=0; $i<$num_rows; $i++) {
                        $link_id = mysql_result($result, $i, "link_id");
                        $num = $i+1;                    
                        $res = mysql_query("select url from ".$mysql_table_prefix."links where link_id like '$link_id'");
                        echo mysql_error();
                        $row = mysql_fetch_row($res);
                        $dup_url = $row[0]; 
                        clean_resource($res) ;                        
                        printDupReport($dup_url,$command_line);
                    }               
                    if ($dup_content == '0') {    //  enter here, if pages with duplicate content should not be indexed/re-indexed
                        $OKtoIndex = 0;
                        $realnum -- ;
                    } else {
                        $OKtoIndex = 1;
                    }
                }
                clean_resource($result) ;               
            }
                                    
			if (($md5sum != $newmd5sum || $reindex ==1) && $OKtoIndex == 1) {
				$urlparts = parse_url($url);
				$newdomain = $urlparts['host'];
				$type = 0;	
             
				$data = clean_file($file, $url, $url_status['content']);

				if ($data['noindex'] == 1) {
					$OKtoIndex = 0;
					$deletable = 1;
					printStandardReport('metaNoindex',$command_line);
				}

				$wordarray = unique_array(explode(" ", $data['content']));  
              
                if ($smp != 1) { 
                
                    if ($data['nofollow'] != 1) { 
                        $links = get_links($file, $url, $can_leave_domain, $data['base']); 
                        $links = distinct_array($links); 
                        $all_links = count($links); 

                        if ($all_links > $max_links) $all_links = $max_links; 
                        $links = array_slice($links,0,$max_links); 

                        if ($realnum < $max_links) { 
                            $numoflinks = 0; 
                            //if there are any, add to the temp table, but only if there isnt such url already 
                            if (is_array($links)) { 
                                reset ($links); 

                                while ($thislink = each($links)) { 
                                    if ($tmp_urls[$thislink[1]] != 1) { 
                                        $tmp_urls[$thislink[1]] = 1; 
                                        $numoflinks++; 

                                        if ($numoflinks <= $max_links) mysql_query ("insert into ".$mysql_table_prefix."temp (link, level, id) values ('$thislink[1]', '$level', '$sessid')"); 
                                        echo mysql_error(); 
                                    } 
                                } 
                            } 
                        } 

                    } else {                     
    					printStandardReport('noFollow',$command_line);
    				}
                    unset ($file);
                }
                
				if ($OKtoIndex == 1) {
					if ($link_check == 0) {
					
					$title = $data['title'];
					$host = $data['host'];
					$path = $data['path'];
					$fulltxt = $data['fulltext'];
					$desc = substr($data['description'], 0,254);
					$url_parts = parse_url($url);
					$domain_for_db = $url_parts['host'];

					if (isset($domain_arr[$domain_for_db])) {
						$dom_id = $domain_arr[$domain_for_db];
					} else {
						mysql_query("insert into ".$mysql_table_prefix."domains (domain) values ('$domain_for_db')");
						$dom_id = mysql_insert_id();
						$domain_arr[$domain_for_db] = $dom_id;
					}

					$wordarray = calc_weights ($wordarray, $title, $host, $path, $data['keywords'], $url_parts);

					//if there are words to index, add the link to the database, get its id, and add the word + their relation
					if (is_array($wordarray) && count($wordarray) > $min_words_per_page) {
						if ($md5sum == '') {
							mysql_query ("insert into ".$mysql_table_prefix."links (site_id, url, title, description, fulltxt, indexdate, size, md5sum, level) values ('$site_id', '$url', '$title', '$desc', '$fulltxt', curdate(), '$pageSize', '$newmd5sum', $thislevel)");
							echo mysql_error();
							$result = mysql_query("select link_id from ".$mysql_table_prefix."links where url='$url'");
							echo mysql_error();
							$row = mysql_fetch_row($result);
							$link_id = $row[0];
                            clean_resource($result) ;                             
							save_keywords($wordarray, $link_id, $dom_id);
							
							printStandardReport('indexed', $command_line);
						}else if (($md5sum <> '') && ($md5sum <> $newmd5sum)) { //if page has changed, start updating

							$result = mysql_query("select link_id from ".$mysql_table_prefix."links where url='$url'");
							echo mysql_error();
							$row = mysql_fetch_row($result);
							$link_id = $row[0];
							for ($i=0;$i<=15; $i++) {
								$char = dechex($i);
								mysql_query ("delete from ".$mysql_table_prefix."link_keyword$char where link_id=$link_id");
								echo mysql_error();
							}
                            clean_resource($result) ;                             
							save_keywords($wordarray, $link_id, $dom_id);
							$query = "update ".$mysql_table_prefix."links set title='$title', description ='$desc', fulltxt = '$fulltxt', indexdate=now(), size = '$pageSize', md5sum='$newmd5sum', level=$thislevel where link_id=$link_id";
							mysql_query($query);
							echo mysql_error();
							printStandardReport('re-indexed', $command_line);
						}
						}else {
							printStandardReport('minWords', $command_line);
                            $realnum -- ; 
						}
					} else {
						printStandardReport('link_okay', $command_line);
					}
                    unset ($wordarray, $title, $fulltxt, $desc);
				}
			}
		} else {
			$deletable = 1;
			printUrlStatus($url_status['state'], $command_line);

		}
		if ($reindex ==1 && $deletable == 1) {
			check_for_removal($url); 
		} else if ($reindex == 1) {
			
		}
		if (!isset($all_links)) {
			$all_links = 0;
		}
		if (!isset($numoflinks)) {
			$numoflinks = 0;
		}
        if ($smp != 1 ) {   //      if valid sitemap found, no LinkReport
            printLinksReport($numoflinks, $all_links, $command_line);
        }
	}


	function index_site($url, $reindex, $maxlevel, $soption, $url_inc, $url_not_inc, $can_leave_domain, $use_robot) {
        global $mysql_table_prefix, $command_line, $mainurl,  $tmp_urls, $domain_arr, $all_keywords, $smp, $follow_sitemap;
		global $link_check, $smap_dir;
        global $max_links, $realnum; 
        
        printStandardReport('starting',$command_line);
        $smp = '0';        
		if (!isset($all_keywords)) {
			$result = mysql_query("select keyword_ID, keyword from ".$mysql_table_prefix."keywords");
			echo mysql_error();
			while($row=mysql_fetch_array($result)) {
				$all_keywords[addslashes($row[1])] = $row[0];
			}
            clean_resource($result) ;            
		}
		$compurl = parse_url($url);
    
		if ($compurl['path'] == '') {
			$url = $url . "/";
        }    

		$t = microtime();
		$a =  getenv("REMOTE_ADDR");
		$sessid = md5 ($t.$a);

        if ($url != '/') {      //      ignore dummies
    		$urlparts = parse_url($url);

    		$domain = $urlparts['host'];
    		if (isset($urlparts['port'])) {
    			$port = (int)$urlparts['port'];
    		}else {
    			$port = 80;
    		}

    		$result = mysql_query("select site_id from ".$mysql_table_prefix."sites where url='$url'");
    		echo mysql_error();
    		$row = mysql_fetch_row($result);
    		$site_id = $row[0];
            clean_resource($result) ;                

    		if ($site_id != "" && $reindex == 1) {
    			mysql_query ("insert into ".$mysql_table_prefix."temp (link, level, id) values ('$url', 0, '$sessid')");
    			echo mysql_error();
    			$result = mysql_query("select url, level from ".$mysql_table_prefix."links where site_id = $site_id");
    			while ($row = mysql_fetch_array($result)) {
    				$site_link = $row['url'];
    				$link_level = $row['level'];
    				if ($site_link != $url) {
    					mysql_query ("insert into ".$mysql_table_prefix."temp (link, level, id) values ('$site_link', $link_level, '$sessid')");
    				}
    			}
                clean_resource($result) ;                
    			
    			$qry = "update ".$mysql_table_prefix."sites set indexdate=now(), spider_depth = $maxlevel, required = '$url_inc'," .
    					"disallowed = '$url_not_inc', can_leave_domain=$can_leave_domain where site_id=$site_id";
    			mysql_query ($qry);
    			echo mysql_error();                
    		} else if ($site_id == '') {
    			mysql_query ("insert into ".$mysql_table_prefix."sites (url, indexdate, spider_depth, required, disallowed, can_leave_domain) " .
    					"values ('$url', now(), $maxlevel, '$url_inc', '$url_not_inc', $can_leave_domain)");
    			echo mysql_error();
    			$result = mysql_query("select site_ID from ".$mysql_table_prefix."sites where url='$url'");
    			$row = mysql_fetch_row($result);
    			$site_id = $row[0];
                clean_resource($result) ;                                
    		} else {
    			mysql_query ("update ".$mysql_table_prefix."sites set indexdate=now(), spider_depth = $maxlevel, required = '$url_inc'," .
    					"disallowed = '$url_not_inc', can_leave_domain=$can_leave_domain where site_id=$site_id");
    			echo mysql_error();
    		}

    		$result = mysql_query("select site_id, temp_id, level, count, num from ".$mysql_table_prefix."pending where site_id='$site_id'");
    		echo mysql_error();
    		$row = mysql_fetch_row($result);
    		$pending = $row[0];
    		$level = 0;
            clean_resource($result) ;                            
    		$domain_arr = get_domains();
    		if ($pending == '') {
    			mysql_query ("insert into ".$mysql_table_prefix."temp (link, level, id) values ('$url', 0, '$sessid')");
    			echo mysql_error();
    		} else if ($pending != '') {
    			printStandardReport('continueSuspended',$command_line);
    			$result = mysql_query("select temp_id, level, count from ".$mysql_table_prefix."pending where site_id='$site_id'");
    			echo mysql_error();
                $row = mysql_fetch_row($result);                
    			$sessid = $row[1];
    			$level = $row[2];
    			$pend_count = $row[3] + 1;
    			$num = $row[4];
    			$pending = 1;
    			$tmp_urls = get_temp_urls($sessid);
                clean_resource($result) ;                
    		}
            
    		if ($reindex != 1) {
    			mysql_query ("insert into ".$mysql_table_prefix."pending (site_id, temp_id, level, count) values ('$site_id', '$sessid', '0', '0')");
    			echo mysql_error();                
    		}

    		$time = time();             
            $robots = ("robots.txt"); // standardname of file 
            if ($use_robot != '1') { 
            $robots = ("no_robots.txt"); // Sphider never will find this file and ignore the contents of robots.txt 
            } 
            $omit = check_robot_txt($url, $robots);             

    		printHeader ($omit, $url, $command_line);

    		if ($link_check == 1) printStandardReport('start_link_check', $command_line);
    		if ($link_check == 0 && $reindex == 1 ) printStandardReport('start_reindex', $command_line); 
    		if ($link_check == 0 && $reindex == 0 ) printStandardReport('starting', $command_line); 
    		
    		$mainurl = $url;
    		$realnum = 0; 

    		while (($level <= $maxlevel && $soption == 'level') || ($soption == 'full')) {
    			if ($pending == 1) {
    				$count = $pend_count;
    				$pending = 0;
    			} else
    				$count = 0;

    			$links = array();

    			$result = mysql_query("select distinct link from ".$mysql_table_prefix."temp where level=$level && id='$sessid' order by link");
    			echo mysql_error();
    			$rows = mysql_num_rows($result);

    			if ($rows == 0) {
    				break;
    			}

    			$i = 0;

    			while ($row = mysql_fetch_array($result)) {
    				$links[] = $row['link'];
    			}
                clean_resource($result) ;
    			reset ($links);


    			while ($count < count($links)) {
    				$num++;                
                    $realnum ++ ; 
                    if ($realnum > $max_links+1) {    //  if max. links per page reached
                        mysql_query ("delete from ".$mysql_table_prefix."temp"); 
                        echo mysql_error();                        
                        mysql_query ("delete from ".$mysql_table_prefix."pending"); 
                        echo mysql_error();                        

                        printMaxLinks($max_links);
                        printStandardReport('completed',$command_line); 
                        return; 
                    } 
                    
    				$thislink = $links[$count];
    				$urlparts = parse_url($thislink);
    				reset ($omit);
    				$forbidden = 0;
    				foreach ($omit as $omiturl) {
    					$omiturl = trim($omiturl);

    					$omiturl_parts = parse_url($omiturl);
    					if ($omiturl_parts['scheme'] == '') {
    						$check_omit = $urlparts['host'] . $omiturl;
    					} else {
    						$check_omit = $omiturl;
    					}

    					if (strpos($thislink, $check_omit)) {
    						printRobotsReport($num, $thislink, $command_line);
                            $realnum -- ; 
    						check_for_removal($thislink); 
    						$forbidden = 1;
    						break;
    					}
    				}

    				if (!check_include($thislink, $url_inc, $url_not_inc )) {
    					printUrlStringReport($num, $thislink, $command_line);
    					check_for_removal($thislink); 
    					$forbidden = 1;
    				} 

    				if ($forbidden == 0) {
    					printRetrieving($num, $thislink, $command_line);
    					$query = "select md5sum, indexdate from ".$mysql_table_prefix."links where url='$thislink'";
    					$result = mysql_query($query);
    					echo mysql_error();
    					$rows = mysql_num_rows($result);
    					if ($rows == 0) {
    						index_url($thislink, $level+1, $site_id, '',  $domain, '', $sessid, $can_leave_domain, $reindex);

    						mysql_query("update ".$mysql_table_prefix."pending set level = $level, count=$count, num=$num where site_id=$site_id");
    						echo mysql_error();
                            
    					}else if ($rows <> 0 && $reindex == 1) {
    						$row = mysql_fetch_array($result);
    						$md5sum = $row['md5sum'];
    						$indexdate = $row['indexdate'];
                            
    						if ($link_check == 1 && $reindex == 1) link_check($thislink, $level+1, $sessid, $can_leave_domain, $reindex);
    						else {
    							index_url($thislink, $level+1, $site_id, $md5sum,  $domain, $indexdate, $sessid, $can_leave_domain, $reindex);
    						}
    						
    						mysql_query("update ".$mysql_table_prefix."pending set level = $level, count=$count, num=$num where site_id=$site_id");
    						echo mysql_error();                                           
    					}else {
    						printStandardReport('inDatabase',$command_line);
                            $realnum -- ; 
    					}
                        clean_resource($result) ;
    				}
    				$count++;
    			}
    			$level++;
    		}
    	
    		mysql_query ("delete from ".$mysql_table_prefix."temp where id = '$sessid'");
    		echo mysql_error();
    		mysql_query ("delete from ".$mysql_table_prefix."pending where site_id = '$site_id'");
    		echo mysql_error();
            
            create_sitemap($site_id, $url);
            
    		printStandardReport('completed',$command_line);
            
        	$stats = get_Stats();
            $stats_sites = $stats['sites'];
            $stats_links = $stats['links'];
            $stats_categories = $stats['categories'];
            $stats_keywords = $stats['keywords'];
            
            printDatabase($stats_sites, $stats_links, $stats_categories, $stats_keywords);           
    	}
    }

	function index_all() {
		global $mysql_table_prefix, $reindex, $command_line, $omit, $url, $cl, $real_log;
        
		$result=mysql_query("select url, spider_depth, required, disallowed, can_leave_domain from ".$mysql_table_prefix."sites");
		echo mysql_error();
		while ($row=mysql_fetch_row($result)) {
			$url = $row[0];
	   		$depth = $row[1];
			$include = $row[2];
			$not_include = $row[3];
			$can_leave_domain = $row[4];
			if ($can_leave_domain=='') {
				$can_leave_domain=0;
			}
			if ($depth == -1) {
				$soption = 'full';
			} else {
				$soption = 'level';
			}
            
            index_site($url, 1, $depth, $soption, $include, $not_include, $can_leave_domain, $use_robot);            
		}
        clean_resource($result) ;        
        printStandardReport('ReindexFinish',$command_line);
        create_footer();        
	}

	function get_temp_urls ($sessid) {
		global $mysql_table_prefix;
		$result = mysql_query("select link from ".$mysql_table_prefix."temp where id='$sessid' limit 0,100");
		echo mysql_error();
		$tmp_urls = Array();
		while ($row=mysql_fetch_row($result)) {
			$tmp_urls[$row[0]] = 1;
		}
        clean_resource($result) ;
		return $tmp_urls;

	}

	function get_domains () {
		global $mysql_table_prefix;
		$result = mysql_query("select domain_id, domain from ".$mysql_table_prefix."domains");
		echo mysql_error();
		$domains = Array();
		while ($row=mysql_fetch_row($result)) {
			$domains[$row[1]] = $row[0];
		}
        clean_resource($result) ;        
		return $domains;

	}

	function commandline_help() {
		print "Usage: php spider.php <options>\n\n";
		print "Options:\n";
		print " -all\t\t Reindex everything in the database\n";
		print " -u <url>\t Set url to index\n";
		print " -f\t\t Set indexing depth to full (unlimited depth)\n";
		print " -d <num>\t Set indexing depth to <num>\n";
		print " -l\t\t Allow spider to leave the initial domain\n";
		print " -r\t\t Set spider to reindex a site\n";
		print " -m <string>\t Set the string(s) that an url must include (use \\n as a delimiter between multiple strings)\n";
		print " -n <string>\t Set the string(s) that an url must not include (use \\n as a delimiter between multiple strings)\n";
	}

	function link_check($url, $level, $sessid, $can_leave_domain, $reindex) {
		global $command_line, $mysql_table_prefix, $user_agent;

		$needsReindex = 1;
		$deletable = 0;
		$local_url = 0;

		$local_url = strpos($url, 'localhost');
		if ($local_url != '7') {
			$url_status = url_status($url);
			$thislevel = $level - 1;

			if (strstr($url_status['state'], "Relocation")) {
				$url = eregi_replace(" ", "", url_purify($url_status['path'], $url, $can_leave_domain));
				if ($url <> '') {
					$result = mysql_query("select link from ".$mysql_table_prefix."temp where link='$url' && id = '$sessid'");
					echo mysql_error();
					$rows = mysql_num_rows($result);
					if ($rows == 0) {
						mysql_query ("insert into ".$mysql_table_prefix."temp (link, level, id) values ('$url', '$level', '$sessid')");
						echo mysql_error();
					}
				}
				$url_status['state'] == "redirected";
                clean_resource($result) ;        
                
			}

			ini_set("user_agent", $user_agent);
			if ($url_status['state'] == 'ok') {		   
				printStandardReport('link_okay', $command_line); 
			} else {
				$deletable = 1;
				printUrlStatus($url_status['state'], $command_line); 
			}
		}
		
		if ($local_url == '7') {		   
			printStandardReport('link_local', $command_line); 
		}
				
		if ($reindex ==1 && $deletable == 1) {
			check_for_removal($url); 
		} else if ($reindex == 1) {
			
		}
		if (!isset($all_links)) {
			$all_links = 0;
		}
		if (!isset($numoflinks)) {
			$numoflinks = 0;
		}
	}

	function get_Stats() {
		global $mysql_table_prefix;
		$stats = array();
		$keywordQuery = "select count(keyword_id) from ".$mysql_table_prefix."keywords";
		$linksQuery = "select count(url) from ".$mysql_table_prefix."links";
		$siteQuery = "select count(site_id) from ".$mysql_table_prefix."sites";
		$categoriesQuery = "select count(category_id) from ".$mysql_table_prefix."categories";

		$result = mysql_query($keywordQuery);
		echo mysql_error();
		if ($row=mysql_fetch_array($result)) {
			$stats['keywords']=$row[0];
		}
        clean_resource($result) ;
        
		$result = mysql_query($linksQuery);
		echo mysql_error();
		if ($row=mysql_fetch_array($result)) {
			$stats['links']=$row[0];
		}        
		for ($i=0;$i<=15; $i++) {
			$char = dechex($i);
			$res1 = mysql_query("select count(link_id) from ".$mysql_table_prefix."link_keyword$char");
			echo mysql_error();
			if ($row=mysql_fetch_array($res1)) {
				$stats['index']+=$row[0];
			}
        clean_resource($res1) ;            
		}
        clean_resource($result) ;
        
		$result = mysql_query($siteQuery);
		echo mysql_error();
		if ($row=mysql_fetch_array($result)) {
			$stats['sites']=$row[0];
		}
        clean_resource($result) ;
        
		$result = mysql_query($categoriesQuery);
		echo mysql_error();
		if ($row=mysql_fetch_array($result)) {
			$stats['categories']=$row[0];
		}
        clean_resource($result) ;        
		return $stats;
	}
    
    function index_new() {
        global $mysql_table_prefix, $command_line;
        $reindex == 0;
        include "admin_header.php";        
        printStandardReport('NewStart',$command_line);        
    
        $result=mysql_query("select url, indexdate, spider_depth, required, disallowed, can_leave_domain from ".$mysql_table_prefix."sites");            
        echo mysql_error();
        while ($row=mysql_fetch_row($result)) {
            $url = $row[0];
            $indexdate = $row[1];
            $depth = $row[2];
            $include = $row[3];
            $not_include = $row[4];
            $can_leave_domain = $row[5];
            if ($can_leave_domain=='') {
                $can_leave_domain=0;
            }
            if ($depth == -1) {
                $soption = 'full';
            } else {
                $soption = 'level';
            }
;               
            if ($indexdate == '') {
            index_site($url, 1, $depth, $soption, $include, $not_include, $can_leave_domain, $use_robot);
            }
        }
        clean_resource($result);         
        printStandardReport('NewFinish',$command_line);
        create_footer();    
    }
   
    function create_footer() {
    	global $plus_nr, $log_handle, $log_file;
        $footer_msg = "<p class='bd'>
                    <span class='em'>
                    <br /><br />Indexing / Re-indexing finished.<br /><br />
                    </span></p>
                ";
              
    	LogUpdate($log_handle, $footer_msg);
    }
            
    function LogUpdate($log_handle, $log_msg){
        if (!$log_handle) {
            die ("Cannot open file for realtime logging. ");
        }

        if (fwrite($log_handle, $log_msg) === FALSE) {
            die ("Cannot write to file for realtime logging. ");
        }        
    }
    

?>