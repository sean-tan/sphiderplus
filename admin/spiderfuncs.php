<?php 
    function getFileContents($url) { 
        global $user_agent, $utf8, $url_status;
        
        $urlparts = parse_url($url); 
        $path = $urlparts['path']; 
        $host = $urlparts['host']; 
        
        if ($urlparts['query'] != "") 
            $path .= "?".$urlparts['query']; 
        if (isset ($urlparts['port'])) { 
            $port = (int) $urlparts['port']; 
        } else 
            if ($urlparts['scheme'] == "http") { 
            $port = 80; 
        } else 
            if ($urlparts['scheme'] == "https") { 
            $port = 443; 
        } 

        if ($port == 80) { 
            $portq = ""; 
        } else { 
            $portq = ":$port"; 
        } 

        $all = "*/*"; 
        $request = "GET $path HTTP/1.0\r\nHost: $host$portq\r\nAccept: $all\r\nAccept-Encoding: identity\r\nUser-Agent: $user_agent\r\n\r\n"; 
        $fsocket_timeout = 30; 
        
        if (substr($url, 0, 5) == "https") { 
            $target = "ssl://".$host; 
        } else { 
            $target = $host; 
        } 

        $errno = 0; 
        $errstr = ""; 
        $fp = @ fsockopen($target, $port, $errno, $errstr, $fsocket_timeout);         

    	$contents = array ();
        if (!$fp) { 
            $contents['state'] = "NOHOST";  
            return $contents; 
        } else { 
            if (!fputs($fp, $request)) { 
                $contents['state'] = "Cannot send request"; 
                return $contents; 
            } 
            $data = null; 
            $pageSize = 0; 
            socket_set_timeout($fp, $fsocket_timeout); 
            $status = socket_get_status($fp); 

            while ((!feof($fp) && !$status['timed_out']) && ($pageSize < 5100) ) { 
                $data .= fgets($fp, 8192); 
                $pageSize = number_format(strlen($data)/1024, 2, ".", ""); 
            } 

            fclose($fp); 
            if ($status['timed_out'] == 1) { 
                $contents['state'] = "timeout"; 
            } else { 
                $contents['state'] = "ok"; 
                $contents['file'] = substr($data, strpos($data, "\r\n\r\n") + 4); 
                         
                if ($utf8 == 1 && $url_status['content'] == 'text'){        //      do not search if pdf, doc, rtf, xls etc.              
                    $hedlen = strlen($data) - strlen($contents['file']); 
                    $contents['header'] = substr($data,0,$hedlen); 

                    //  search for "charset" in the file description 
                    $inp = strtoupper($contents['header']);                      
                    $start=strpos($inp,"CHARSET"); 
                    $end=strpos($inp,"\r\n",$start); 
                    $lines =explode("\r\n",substr($inp,$start,$end)); 
                    $lines = explode("=",$lines[0]); 
                    $chrSet = $lines[1];                      
                    if(trim($chrSet) != ""){ 
                        $contents['charset'] = $chrSet; 
                    } else { //not found, need to search in file (header)
                        $inp = strtoupper($contents['file']);                         
                        $start = strpos($inp," CHARSET="); 
                        $end = strpos($inp,"\"",$start); 
                        $chrSet = substr($inp,$start,($end-$start));                                                 
                        $chrSet = str_replace("'","",$chrSet); 
                        $chrSet = str_replace('"','',$chrSet); 
                        $lines = explode("=",$chrSet); 
                        $chrSet = $lines[1];                        
                        $contents['charset'] = $chrSet;                         
                    }
                }        
            } 
        } 
        unset ($data, $inp, $urlparts, $lines, $chrSet, $request, $status);
        return $contents; 
    } 

    // check if file is available and in readable form
    function url_status($url) {
    	global $user_agent, $index_pdf, $index_doc, $index_rtf, $index_xls, $index_ppt, $realnum;
        
    	$urlparts = parse_url($url);
    	$path = $urlparts['path'];
    	$host = $urlparts['host'];
    	if (isset($urlparts['query']))
    		$path .= "?".$urlparts['query'];

    	if (isset ($urlparts['port'])) {
    		$port = (int) $urlparts['port'];
    	} else
    		if ($urlparts['scheme'] == "http") {
    			$port = 80;
    		} else
    			if ($urlparts['scheme'] == "https") {
    				$port = 443;
    			}

    	if ($port == 80) {
    		$portq = "";
    	} else {
    		$portq = ":$port";
    	}

    	$all = "*/*"; //just to prevent "comment effect" in get accept
    	$request = "HEAD $path HTTP/1.1\r\nHost: $host$portq\r\nAccept: $all\r\nUser-Agent: $user_agent\r\n\r\n";

    	if (substr($url, 0, 5) == "https") {
    		$target = "ssl://".$host;
    	} else {
    		$target = $host;
    	}

    	$fsocket_timeout = 30;
    	$errno = 0;
    	$errstr = "";
    	$fp = fsockopen($target, $port, $errno, $errstr, $fsocket_timeout);
    	print $errstr;
    	$linkstate = "ok";
    	if (!$fp) {
    		$status['state'] = "NOHOST";
    	} else {
    		socket_set_timeout($fp, 30);
    		fputs($fp, $request);
    		$answer = fgets($fp, 4096);
    		$regs = Array ();
    		if (ereg("HTTP/[0-9.]+ (([0-9])[0-9]{2})", $answer, $regs)) {
    			$httpcode = $regs[2];
    			$full_httpcode = $regs[1];

    			if ($httpcode <> 2 && $httpcode <> 3) {
    				$status['state'] = "Unreachable: http $full_httpcode";
    				$linkstate = "Unreachable";
                    $realnum -- ; 
    			}
    		}

    		if ($linkstate <> "Unreachable") {
    			while ($answer) {
    				$answer = fgets($fp, 4096);

    				if (ereg("Location: *([^\n\r ]+)", $answer, $regs) && $httpcode == 3 && $full_httpcode != 302) {
    					$status['path'] = $regs[1];
    					$status['state'] = "Relocation: http $full_httpcode";
    					fclose($fp);
    					return $status;
    				}

    				if (eregi("Last-Modified: *([a-z0-9,: ]+)", $answer, $regs)) {
    					$status['date'] = $regs[1];
    				}

    				if (eregi("Content-Type:", $answer)) {
    					$content = $answer;
    					$answer = '';
    					break;
    				}
    			}
                
    			$socket_status = socket_get_status($fp);                
    			if (eregi("Content-Type: *([a-z/.-]*)", $content, $regs)) {
    				if ($regs[1] == 'text/html' || $regs[1] == 'text/' || $regs[1] == 'text/plain') {
    					$status['content'] = 'text';
    					$status['state'] = 'ok';
    				} else if ($regs[1] == 'application/pdf' && $index_pdf == 1) {
    					$status['content'] = 'pdf';
    					$status['state'] = 'ok';                                 
    				} else if ($regs[1] == 'application/pdf' && $index_pdf == 0) {
    					$status['content'] = 'pdf';
    					$status['state'] = 'Indexing of PDF files is not activated in Admin Settings';                                 
    				} else if (($regs[1] == 'application/msword' || $regs[1] == 'application/vnd.ms-word') && $index_doc == 1) {
    					$status['content'] = 'doc';
    					$status['state'] = 'ok';
    				} else if (($regs[1] == 'application/msword' || $regs[1] == 'application/vnd.ms-word') && $index_doc == 0) {
    					$status['content'] = 'doc';
    					$status['state'] = 'Indexing of DOC files is not activated in Admin Settings';
    				} else if (($regs[1] == 'text/rtf') && $index_rtf == 1) {
    					$status['content'] = 'rtf';
    					$status['state'] = 'ok';
    				} else if (($regs[1] == 'text/rtf') && $index_rtf == 0) {
    					$status['content'] = 'rtf';
    					$status['state'] = 'Indexing of RTF files is not activated in Admin Settings';
    				} else if (($regs[1] == 'application/excel' || $regs[1] == 'application/vnd.ms-excel') && $index_xls == 1) {
    					$status['content'] = 'xls';
    					$status['state'] = 'ok';
    				} else if (($regs[1] == 'application/excel' || $regs[1] == 'application/vnd.ms-excel') && $index_xls == 0) {
    					$status['content'] = 'xls';
    					$status['state'] = 'Indexing of XLS files is not activated in Admin Settings';
    				} else if (($regs[1] == 'application/mspowerpoint' || $regs[1] == 'application/vnd.ms-powerpoint') && $index_ppt == 1) {
    					$status['content'] = 'ppt';
    					$status['state'] = 'ok';
    				} else if (($regs[1] == 'application/mspowerpoint' || $regs[1] == 'application/vnd.ms-powerpoint') && $index_ppt == 0) {
    					$status['content'] = 'ppt';
    					$status['state'] = 'Indexing of PPT files is not activated in Admin Settings';
                    } else {
    					$status['state'] = "Not text or html";
                        $realnum -- ; 
    				}
    			} else
    				if ($socket_status['timed_out'] == 1) {
    					$status['state'] = "Timed out (no reply from server)";
                        $realnum -- ; 
    				} else
    					$status['state'] = "Not text or html";
    		}
    	}
    	fclose($fp);
        unset ($urlparts, $answer);        
    	return $status;
    }
                
    function check_robot_txt($url, $robots) { 
        global $user_agent; 
        $urlparts = parse_url($url); 
        $url = 'http://'.$urlparts['host']."/$robots"; 
    	$url_status = url_status($url);
    	$omit = array ();

    	if ($url_status['state'] == "ok") {
    		$robot = file($url);
    		if (!$robot) {
    			$contents = getFileContents($url);
    			$file = $contents['file'];
    			$robot = explode("\n", $file);
    		}

    		$regs = Array ();
    		$this_agent= "";
    		while (list ($id, $line) = each($robot)) {
    			if (eregi("^user-agent: *([^#]+) *", $line, $regs)) {
    				$this_agent = trim($regs[1]);
    				if ($this_agent == '*' || $this_agent == $user_agent)
    					$check = 1;
    				else
    					$check = 0;
    			}

    			if (eregi("disallow: *([^#]+)", $line, $regs) && $check == 1) {
    				$disallow_str = eregi_replace("[\n ]+", "", $regs[1]);
    				if (trim($disallow_str) != "") {
    					$omit[] = $disallow_str;
    				} else {
    					if ($this_agent == '*' || $this_agent == $user_agent) {
    						return null;
                            unset ($urlparts, $contents, $file, $robot, $regs);                                   
    					}
    				}
    			}
    		}
    	}
        unset ($urlparts, $contents, $file, $robot, $regs);        
    	return $omit;
    }

    // Remove the file part from an url (to build an url from an url and given relative path)
    function remove_file_from_url($url) {
    	$url_parts = parse_url($url);
    	$path = $url_parts['path'];

    	$regs = Array ();
    	if (preg_match('/([^\/]+)$/i', $path, $regs)) {
    		$file = $regs[1];
    		$check = $file.'$';
    		$path = preg_replace("/$check"."/i", "", $path);
    	}

    	if ($url_parts['port'] == 80 || $url_parts['port'] == "") {
    		$portq = "";
    	} else {
    		$portq = ":".$url_parts['port'];
    	}

    	$url = $url_parts['scheme']."://".$url_parts['host'].$portq.$path;
        if ($clear == '1') {
            unset ($url_parts, $regs, $file);                
    	}
        unset ($urlparts);         
        return $url;       
    }

    // Extract links from html
    function get_links($file, $url, $can_leave_domain, $base) {
    	$chunklist = array ();
        // The base URL comes from either the meta tag or the current URL.
        if (!empty($base)) {
            $url = $base;
        }
        
    	$links = array ();
    	$regs = Array ();
    	$checked_urls = Array();
        
    	$file = preg_replace("@<!--.*?-->@si", " ",$file);
    	preg_match_all("/href\s*=\s*[\'\"]?([+:%\/\?~=&;\\\(\),._a-zA-Z0-9-]*)(#[.a-zA-Z0-9-]*)?[\'\" ]?(\s*rel\s*=\s*[\'\"]?(nofollow)[\'\"]?)?/i", $file, $regs, PREG_SET_ORDER);
    	foreach ($regs as $val) {
    		if ($checked_urls[$val[1]]!=1 && !isset ($val[4])) { //if nofollow is not set
    			if (($a = url_purify($val[1], $url, $can_leave_domain)) != '') {
    				$links[] = $a;
    			}
    			$checked_urls[$val[1]] = 1;
    		}
    	}
    	preg_match_all("/(frame[^>]*src[[:blank:]]*)=[[:blank:]]*[\'\"]?(([[a-z]{3,5}:\/\/(([.a-zA-Z0-9-])+(:[0-9]+)*))*([+:%\/?=&;\\\(\),._ a-zA-Z0-9-]*))(#[.a-zA-Z0-9-]*)?[\'\" ]?/i", $file, $regs, PREG_SET_ORDER);
    	foreach ($regs as $val) {
    		if ($checked_urls[$val[1]]!=1 && !isset ($val[4])) { //if nofollow is not set
    			if (($a = url_purify($val[1], $url, $can_leave_domain)) != '') {
    				$links[] = $a;
    			}
    			$checked_urls[$val[1]] = 1;
    		}
    	}
    	preg_match_all("/(window[.]location)[[:blank:]]*=[[:blank:]]*[\'\"]?(([[a-z]{3,5}:\/\/(([.a-zA-Z0-9-])+(:[0-9]+)*))*([+:%\/?=&;\\\(\),._ a-zA-Z0-9-]*))(#[.a-zA-Z0-9-]*)?[\'\" ]?/i", $file, $regs, PREG_SET_ORDER);
    	foreach ($regs as $val) {
    		if ($checked_urls[$val[1]]!=1 && !isset ($val[4])) { //if nofollow is not set
    			if (($a = url_purify($val[1], $url, $can_leave_domain)) != '') {
    				$links[] = $a;
    			}
    			$checked_urls[$val[1]] = 1;
    		}
    	}
    	preg_match_all("/(http-equiv=['\"]refresh['\"] *content=['\"][0-9]+;url)[[:blank:]]*=[[:blank:]]*[\'\"]?(([[a-z]{3,5}:\/\/(([.a-zA-Z0-9-])+(:[0-9]+)*))*([+:%\/?=&;\\\(\),._ a-zA-Z0-9-]*))(#[.a-zA-Z0-9-]*)?[\'\" ]?/i", $file, $regs, PREG_SET_ORDER);
    	foreach ($regs as $val) {
    		if ($checked_urls[$val[1]]!=1 && !isset ($val[4])) { //if nofollow is not set
    			if (($a = url_purify($val[1], $url, $can_leave_domain)) != '') {
    				$links[] = $a;
    			}
    			$checked_urls[$val[1]] = 1;
    		}
    	}

    	preg_match_all("/(window[.]open[[:blank:]]*[(])[[:blank:]]*[\'\"]?(([[a-z]{3,5}:\/\/(([.a-zA-Z0-9-])+(:[0-9]+)*))*([+:%\/?=&;\\\(\),._ a-zA-Z0-9-]*))(#[.a-zA-Z0-9-]*)?[\'\" ]?/i", $file, $regs, PREG_SET_ORDER);
    	foreach ($regs as $val) {
    		if ($checked_urls[$val[1]]!=1 && !isset ($val[4])) { //if nofollow is not set
    			if (($a = url_purify($val[1], $url, $can_leave_domain)) != '') {
    				$links[] = $a;
    			}
    			$checked_urls[$val[1]] = 1;
    		}
    	}
        unset ($chunklist, $regs, $checked_urls);
    	return $links;
    }

    // Function to build a unique word array from the text of a webpage, together with the count of each word 
    function unique_array($arr) {
    	global $min_word_length, $common, $word_upper_bound;
    	global $index_numbers, $stem_words, $utf8, $case_sensitive;
    	
    	if ($stem_words == 1) {
    		$newarr = Array();
    		foreach ($arr as $val) {
    			$newarr[] = stem($val);
    		}
    		$arr = $newarr;
    	}
    	sort($arr);
    	reset($arr);
    	$newarr = array ();
    	$i = 0;
    	$counter = 1;
        
        if ($case_sensitive == '0') {
            $element = lower_case(current($arr));
        } else {
            $element = current($arr);
        }
        if ($utf8 == '1') {     //  build array with utf8 support
        	if ($index_numbers == 0) {
        		$pattern = "/[0-9]+/";
        	} else {
        		$pattern = "/[ ]+/";
            }
        
        	$regs = Array ();
        	for ($n = 0; $n < sizeof($arr); $n ++) {
        		//check if word is long enough, does not contain characters as defined in $pattern and is not a common word
        		//to eliminate/count multiple instance of words
        		$next_in_arr = next($arr);
                               
                if (strlen($next_in_arr) >= $min_word_length ) {                           
            		if ($next_in_arr != $element) {            
            			if (strlen($element) >= $min_word_length && !preg_match($pattern, $element) && (@ $common[$element] <> 1)) {
            				if (preg_match("/^(-|\\\')(.*)/", $element, $regs))
            					$element = $regs[2];
     
            				if (preg_match("/(.*)(\\\'|-)$/", $element, $regs))
            					$element = $regs[1];

            				$newarr[$i][1] = $element;
            				$newarr[$i][2] = $counter;
            				if ($case_sensitive == '0') {
                                $element = lower_case(current($arr));
                            } else {
                                $element = current($arr);
                            }
            				$i ++;
            				$counter = 1;
            			} else {
            				$element = $next_in_arr;
            			}
            		} else {
            			if ($counter < $word_upper_bound)
            			$counter ++;
            		}
                }                    
        	}
            
        } else {       //  build array without utf8 support              
        	if ($index_numbers == 1) {
        		$pattern = "/[a-z0-9]+/";
        	} else {
        		$pattern = "/[a-z]+/";
        	}
            $pattern2 = "/[a-z0-9]+/";      // kill all non-alphanumerical characters
        	$regs = Array ();
        	for ($n = 0; $n < sizeof($arr); $n ++) {
        		//check if word is long enough, contains alphabetic characters and is not a common word
        		//to eliminate/count multiple instance of words
        		$next_in_arr = next($arr);
                               
                if (strlen($next_in_arr) >= $min_word_length ) {                                
            		if ($next_in_arr != $element) {          
            			if (strlen($element) >= $min_word_length && preg_match($pattern, remove_accents($element)) && preg_match($pattern2, $element)&& (@ $common[$element] <> 1)) {
            				if (preg_match("/^(-|\\\')(.*)/", $element, $regs))
            					$element = $regs[2];

            				if (preg_match("/(.*)(\\\'|-)$/", $element, $regs))
            					$element = $regs[1];
//print "element1: $element<br />";
//$element = quote_replace($element);
//$element = htmlentities($element);


//$element = html_entity_encode($element);
//print "element2: $element<br />";

//$newarr[$i][1] = html_entity_decode($element);  //  Sphider-plus likes it pure
            				$newarr[$i][1] = $element;  
            				$newarr[$i][2] = $counter;
                            if ($case_sensitive == '0') {
                                $element = lower_case(current($arr));
                            } else {
                                $element = current($arr);
                            }
            				$i ++;
            				$counter = 1;
            			} else {
            				$element = $next_in_arr;
            			}
            		} else {
                        if ($counter < $word_upper_bound)
                            $counter ++;
            		}
                } 
            }
        }                     
        unset ($element, $arr);
//echo "<br>newArray:<br><pre>";print_r($newarr);echo "</pre>";        
    	return $newarr;
    }

    // Check if url is legal, relative to the main url.
    function url_purify($url, $parent_url, $can_leave_domain) {
    	global $ext, $mainurl, $apache_indexes, $strip_sessids;

        $original_parent_url_parts = parse_url(); 
    	$urlparts = parse_url($url);

    	$main_url_parts = parse_url($mainurl);
    	if ($urlparts['host'] != "" && $urlparts['host'] != $main_url_parts['host']  && $can_leave_domain != 1) {
    		return '';
    	}
    	
    	reset($ext);
    	while (list ($id, $excl) = each($ext))
    		if (preg_match("/\.$excl$/i", $url))
    			return '';

    	if (substr($url, -1) == '\\') {
    		return '';
    	}

    	if (isset($urlparts['query'])) {
    		if ($apache_indexes[$urlparts['query']]) {
    			return '';
    		}
    	}

    	if (preg_match("/[\/]?mailto:|[\/]?javascript:|[\/]?news:/i", $url)) {
    		return '';
    	}
    	if (isset($urlparts['scheme'])) {
    		$scheme = $urlparts['scheme'];
    	} else {
    		$scheme ="";
    	}

    	//only http and https links are followed
    	if (!($scheme == 'http' || $scheme == '' || $scheme == 'https')) {
    		return '';
    	}

    	//parent url might be used to build an url from relative path
    	$parent_url = remove_file_from_url($parent_url);
    	$parent_url_parts = parse_url($parent_url);


    	if (substr($url, 0, 1) == '/') {
    		$url = $parent_url_parts['scheme']."://".$parent_url_parts['host'].$url;
    	} else
    		if (!isset($urlparts['scheme'])) {
    			$url = $parent_url.$url;
    		}

    	$url_parts = parse_url($url);

    	$urlpath = $url_parts['path'];

    	$regs = Array ();
    	
    	while (preg_match("/[^\/]*\/[.]{2}\//", $urlpath, $regs)) {
    		$urlpath = str_replace($regs[0], "", $urlpath);
    	}

    	//remove relative path instructions like ../ etc 
    	$urlpath = preg_replace("/\/+/", "/", $urlpath);
    	$urlpath = preg_replace("/[^\/]*\/[.]{2}/", "",  $urlpath);
    	$urlpath = str_replace("./", "", $urlpath);
    	$query = "";
    	if (isset($url_parts['query'])) {
    		$query = "?".$url_parts['query'];
    	}
    	if ($main_url_parts['port'] == 80 || $url_parts['port'] == "") {
    		$portq = "";
    	} else {
    		$portq = ":".$main_url_parts['port'];
    	}
        
        if (!$urlpath) $urlpath = "/";  //     if not exists, add slash instead of real urlpath
    	$url = $url_parts['scheme']."://".$url_parts['host'].$portq.$urlpath.$query;

        //added to address <a href="?id=1"> syntax 
        if (strstr($url, "/?")) { 
            $page = str_replace($main_url_parts['path'], null, $original_parent_url_parts['path']); 
            if (substr(trim($mainurl), -1) !== "/" and substr(trim($page), 0, 1) !== "/") { 
                $page = "/" . $page; 
            } 
            $url = $mainurl . $page . $query; 
        }
        
    	//  if we index sub-domains
    	if ($can_leave_domain == 1) {
    		return $url;
    	}

    	$mainurl = remove_file_from_url($mainurl);
    	
    	if ($strip_sessids == 1) {
    		$url = remove_sessid($url);
    	}
    	//  only urls in staying in the starting domain/directory are followed	
    	$url = convert_url($url);
    	if (strstr($url, $mainurl) == false) {
            unset ($ext, $mainurl, $original_parent_url_parts, $url_parts, $urlparts, $urlpath, $query, $page);
    		return '';
    	} else {
            unset ($ext, $mainurl, $original_parent_url_parts, $url_parts, $urlparts, $urlpath, $query, $page);        
    		return $url;
        }
    }

    function save_keywords($wordarray, $link_id, $domain) {
    	global $mysql_table_prefix, $all_keywords;
    	reset($wordarray);
    	while ($thisword = each($wordarray)) {
    		$word = $thisword[1][1];
    		$wordmd5 = substr(md5($word), 0, 1);
            $hits = $thisword[1][2];            
    		$weight = $thisword[1][3];
          
    		if (strlen($word)<= 255) {
    			$keyword_id = $all_keywords[$word];
    			if ($keyword_id  == "") {
                    mysql_query("insert into ".$mysql_table_prefix."keywords (keyword) values ('$word')");
    				if (mysql_errno() == 1062) { 
    					$result = mysql_query("select keyword_ID from ".$mysql_table_prefix."keywords where keyword='$word'");
    					echo mysql_error();
    					$row = mysql_fetch_row($result);
    					$keyword_id = $row[0];
                        clean_resource($result);                        
    				} else{
        				$keyword_id = mysql_insert_id();
        				$all_keywords[$word] = $keyword_id;
        				echo mysql_error();
        			} 
    			} 
    			$inserts[$wordmd5] .= ",($link_id, $keyword_id, $weight, $domain, $hits)"; 
    		}
    	}

    	for ($i=0;$i<=15; $i++) {
    		$char = dechex($i);
    		$values= substr($inserts[$char], 1);
    		if ($values!="") {
    			$query = "insert into ".$mysql_table_prefix."link_keyword$char (link_id, keyword_id, weight, domain, hits) values $values";
    			mysql_query($query);
    			echo mysql_error();
    		}    		    	
    	}
        unset ($values, $char, $inserts, $all_keywords, $weight, $word, $wordarray);
    }

    function get_head_data($file) {
    	$headdata = "";
               
    	preg_match("@<head[^>]*>(.*?)<\/head>@si",$file, $regs);	
    	
    	$headdata = $regs[1];

    	$description = "";
    	$robots = "";
    	$keywords = "";
        $base = "";
    	$res = Array ();
    	if ($headdata != "") {
    		preg_match("/<meta +name *=[\"']?robots[\"']? *content=[\"']?([^<>'\"]+)[\"']?/i", $headdata, $res);
    		if (isset ($res)) {
    			$robots = $res[1];
    		}

    		preg_match("/<meta +name *=[\"']?description[\"']? *content=[\"']?([^<>\"]+)[\"']?/i", $headdata, $res);
    		if (isset ($res)) {
    			$description = $res[1];
    		}

    		preg_match("/<meta +name *=[\"']?keywords[\"']? *content=[\"']?([^<>\"]+)[\"']?/i", $headdata, $res);
    		if (isset ($res)) {
    			$keywords = $res[1];
    		}
            // e.g. <base href="http://www.consil.co.uk/index.php" />
    		preg_match("/<base +href *= *[\"']?([^<>'\"]+)[\"']?/i", $headdata, $res);
    		if (isset ($res)) {
    			$base = $res[1];
    		}
    		$keywords = preg_replace("/[, ]+/", " ", $keywords);
    		$robots = explode(",", strtolower($robots));
    		$nofollow = 0;
    		$noindex = 0;
    		foreach ($robots as $x) {
    			if (trim($x) == "noindex") {
    				$noindex = 1;
    			}
    			if (trim($x) == "nofollow") {
    				$nofollow = 1;
    			}
    		}
    		$data['description'] = addslashes($description);
    		$data['keywords'] = addslashes($keywords);
    		$data['nofollow'] = $nofollow;
    		$data['noindex'] = $noindex;
    		$data['base'] = $base;
    	}
        unset ($headdata, $res, $keywords, $robots);
    	return $data;
    }

    function clean_file($file, $url, $type) {
    	global $entities, $index_host, $index_meta_keywords, $utf8, $case_sensitive;

    	$urlparts = parse_url($url);
    	$host = $urlparts['host'];
    	//remove filename from path
    	$path = eregi_replace('([^/]+)$', "", $urlparts['path']);
    	$file = preg_replace("/<link rel[^<>]*>/i", " ", $file);
    	$file = preg_replace("@<!--sphider_noindex-->.*?<!--\/sphider_noindex-->@si", " ",$file);	
    	$file = preg_replace("@<!--.*?-->@si", " ",$file);	
    	$file = preg_replace("@<script[^>]*?>.*?</script>@si", " ",$file);
    	$headdata = get_head_data($file);
    	$regs = Array ();
    	if (preg_match("@<title *>(.*?)<\/title*>@si", $file, $regs)) {
    		$title = trim($regs[1]);
    		$file = str_replace($regs[0], "", $file);
    	} else if ($type == 'pdf' || $type == 'doc' || $type == 'ppt' || $type == 'rtf' || $type == 'xls') { //create title for a non-html files 
            //$title = substr($file, 0, strrpos(substr($file, 0, 40), " "));
            $offset = strrpos ($url, '/');      //      get document name
            $title = substr ($url, $offset+1);
    	}

    	$file = preg_replace("@<style[^>]*>.*?<\/style>@si", " ", $file);

    	//create spaces between tags, so that removing tags doesnt concatenate strings
    	$file = preg_replace("/<[\w ]+>/", "\\0 ", $file);
    	$file = preg_replace("/<\/[\w ]+>/", "\\0 ", $file);
    	$file = strip_tags($file);
    	$file = preg_replace("/&nbsp;/", " ", $file);  
    	$fulltext = $file;
    	$file .= " ".$title;
       
    	if ($index_host == 1) {
            //  separate words in host and path     
            $host_sep =preg_replace("/\.|\/|\\\/", " ", $host);           
            $path_sep =preg_replace("/\.|\/|\\\/", " ", $path);
            
    		$file = $file." ".$host." ".$host_sep." ".ucwords($host_sep);
            $file = $file." ".$path." ".$path_sep." ".ucwords($path_sep);           
    	}
        
    	if ($index_meta_keywords == 1) {
    		$file = $file." ".$headdata['keywords'];
    	}
    	    	
    	//replace codes with ascii chars
    	$file = preg_replace('~&#x([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $file);
        $file = preg_replace('~&#([0-9]+);~e', 'chr("\\1")', $file);
        
        if ($utf8 != 1 ) {  // if we want to buiild a UTF8 coded database, we also need the upper-case characters
            $file = lower_case($file);
        }
        
    	reset($entities);
    	while ($char = each($entities)) {
    		$file = preg_replace("/".$char[0]."/i", $char[1], $file);
    	}
    	$file = preg_replace("/&[a-z]{1,6};/", " ", $file); 
      
        $trash   = array("\r\n", "\n", "\r");       // kill 'LF' and the others
        $replace = ' ';
        $file = str_replace($trash, $replace, $file);        

        $trash   = array("\\r\\n", "\\n", "\\r");       // kill 'LF' and the others
        $replace = ' ';
        $file = str_replace($trash, $replace, $file);        

        if ($utf8 == '0') {
            $file = preg_replace("/\s+/", " ", $file);  //  kill whitespace character
            $fulltext = html_entity_decode($fulltext);  //  compatible with Suggest Framework
        }
        
    	$data['fulltext'] = addslashes($fulltext);
    	$data['content'] = addslashes($file);
    	$data['title'] = addslashes($title);
    	$data['description'] = $headdata['description'];
    	$data['keywords'] = $headdata['keywords'];
    	$data['host'] = $host;
    	$data['path'] = $path;
    	$data['nofollow'] = $headdata['nofollow'];
    	$data['noindex'] = $headdata['noindex'];
    	$data['base'] = $headdata['base'];

        unset ($char, $file, $fulltext, $path_sep, $headdata, $regs, $urlparts, $host);
    	return $data;
    }

    function calc_weights($wordarray, $title, $host, $path, $keywords, $url_parts) {
    	global $index_host, $index_meta_keywords, $sort_results, $domain_mul;
                
    	$hostarray = unique_array(explode(" ", preg_replace("/[^[:alnum:]-]+/i", " ", strtolower($host))));
    	$patharray = unique_array(explode(" ", preg_replace("/[^[:alnum:]-]+/i", " ", strtolower($path))));
    	$titlearray = unique_array(explode(" ", preg_replace("/[^[:alnum:]-]+/i", " ", strtolower($title))));
    	$keywordsarray = unique_array(explode(" ", preg_replace("/[^[:alnum:]-]+/i", " ", strtolower($keywords))));
    	$path_depth = countSubstrs($path, "/");
        $main_url_factor = '1';
        
        if ($sort_results == '2') {         //      enter here if 'Main URLs (domains) on top'  is selected 
            $act_host = $host;           
            $act_path =  $url_parts['path'];
            $act_query =  $url_parts['query'];
            
            //      try to find main URL for localhost systems
            if ($act_host == 'localhost' && substr_count($act_path, ".") == '0' && substr_count($act_path, "/") <= '3') {  
                $main_url_factor = $domain_mul;     //      if localhost: increase weight for domains in path
            }
/*
            if ($act_host == 'localhost' && substr_count($act_path, ".") == '1' && substr_count($act_path, "/") <= '3') {  
                $main_url_factor = $domain_mul/2;     //      if localhost: increase weight for sub-domains in path slightly               
            }
*/
            //      only these files are exepted as valid part of the url path
            $act_path = str_replace ('index.php', '', $act_path);
            $act_path = str_replace ('index.html', '', $act_path);
            $act_path = str_replace ('index.htm', '', $act_path);

            //      try to find main URL in the wild
            if ($act_host != 'localhost'  && substr_count($act_host, ".") == '2' && strlen($act_path) <= '1' && !$url_parts['query']) {     
                $main_url_factor = $domain_mul;     //      increase weight for main URLs (domains)
           }
        }

    	while (list ($wid, $word) = each($wordarray)) {
    		$word_in_path = 0;
    		$word_in_domain = 0;
    		$word_in_title = 0;
    		$meta_keyword = 0;
            
    		if ($index_host == 1) {
    			while (list ($id, $path) = each($patharray)) {
               
    				if ($path[1] == $word[1]) {
                        $word_in_path = 1;
    					break;
    				}
    			}
    			reset($patharray);

    			while (list ($id, $host) = each($hostarray)) {
    				if ($host[1] == $word[1]) {
    					$word_in_domain = 1;
    					break;
    				}
    			}
    			reset($hostarray);
    		}

    		if ($index_meta_keywords == 1) {
    			while (list ($id, $keyword) = each($keywordsarray)) {
    				if ($keyword[1] == $word[1]) {
    					$meta_keyword = 1;
    					break;
    				}
    			}
    			reset($keywordsarray);
    		}
    		while (list ($id, $tit) = each($titlearray)) {
    			if ($tit[1] == $word[1]) {
    				$word_in_title = 1;
    				break;
    			}
    		}
    		reset($titlearray);             
    		$wordarray[$wid][3] = (int) (calc_weight($wordarray[$wid][2], $word_in_title, $word_in_domain, $word_in_path, $path_depth, $meta_keyword, $main_url_factor));
    	}
        unset ($titlearray, $keywordsarray, $hostarray, $patharray, $act_path, $act_host, $act_query);
    	reset($wordarray);
    	return $wordarray;
    }

    function isDuplicateMD5($md5sum) {
    	global $mysql_table_prefix;
    	$result = mysql_query("select link_id from ".$mysql_table_prefix."links where md5sum='$md5sum'");
    	echo mysql_error();
    	if (mysql_num_rows($result) > 0) {
    		return true;
    	}
        clean_resource($result) ;
    	return false;
    }

    function check_include($link, $inc, $not_inc) {
    	$url_inc = Array ();
    	$url_not_inc = Array ();
    	if ($inc != "") {
    		$url_inc = explode("\n", $inc);
    	}
    	if ($not_inc != "") {
    		$url_not_inc = explode("\n", $not_inc);
    	}
    	$oklinks = Array ();

    	$include = true;
    	foreach ($url_not_inc as $str) {
    		$str = trim($str);
    		if ($str != "") {
    			if (substr($str, 0, 1) == '*') {
    				if (preg_match(substr($str, 1), $link)) {
    					$include = false;
    					break;
    				}
    			} else {
    				if (!(strpos($link, $str) === false)) {
    					$include = false;
    					break;
    				}
    			}
    		}
    	}
    	if ($include && $inc != "") {
    		$include = false;
    		foreach ($url_inc as $str) {
    			$str = trim($str);
    			if ($str != "") {
    				if (substr($str, 0, 1) == '*') {
    					if (preg_match(substr($str, 1), $link)) {
    						$include = true;
    						break 2;
    					}
    				} else {
    					if (strpos($link, $str) !== false) {
    						$include = true;
    						break;
    					}
    				}
    			}
    		}
    	}
        unset ($str, $link, $url_not_inc, $url_inc, $oklinks);
    	return $include;
    }

    function check_for_removal($url) {
    	global $mysql_table_prefix;
    	global $command_line;
    	$result = mysql_query("select link_id, visible from ".$mysql_table_prefix."links"." where url='$url'");
    	echo mysql_error();
    	if (mysql_num_rows($result) > 0) {
    		$row = mysql_fetch_row($result);
    		$link_id = $row[0];
    		$visible = $row[1];
    		if ($visible > 0) {
    			$visible --;
    			mysql_query("update ".$mysql_table_prefix."links set visible=$visible where link_id=$link_id");
    			echo mysql_error();
    		} else {
    			mysql_query("delete from ".$mysql_table_prefix."links where link_id=$link_id");
    			echo mysql_error();
    			for ($i=0;$i<=15; $i++) {
    				$char = dechex($i);
    				mysql_query("delete from ".$mysql_table_prefix."link_keyword$char where link_id=$link_id");
    				echo mysql_error();
    			}
    			printStandardReport('pageRemoved',$command_line);
    		}
    	}
        clean_resource($result) ;
        unset ($char, $link_id, $visible);
    }

    function convert_url($url) {
    	$url = str_replace("&amp;", "&", $url);
    	$url = str_replace(" ", "%20", $url);
    	return $url;
    }

    function extract_text($contents, $source_type) {
    	global $tmp_dir, $pdftotext_path, $catdoc_path, $xls2csv_path, $catppt_path, $home_charset, $command_line;

        $home_charset1 = str_ireplace ('iso-','',$home_charset);
        $charset_int = str_ireplace ('iso','',$home_charset1);        
    	$temp_file = "tmp_file";
    	$filename = $tmp_dir."/".$temp_file ;
    	if (!$handle = fopen($filename, 'w')) {
    		die ("Cannot open file $filename");
    	}

    	if (fwrite($handle, $contents) === FALSE) {
    		die ("Cannot write to file $filename");
    	}
    	
    	fclose($handle);
        
    	if ($source_type == 'pdf') {
            $command = $pdftotext_path." $filename -";            
    		$a = exec($command,$result, $retval);
            
            if ($retval != '0') {  //   error handler for .pdf files  
                if ($retval > '3') {                             
                    printStandardReport('ufoError',$command_line);
                }           
                if ($retval == '1') {           
                    printStandardReport('errorOpenPDF',$command_line);
                }
                if ($retval == '3') {           
                    printStandardReport('permissionError',$command_line);
                }
                $result = array();               
                $result[] = 'ERROR';
            }
            
    	} else if ($source_type == 'doc') {
    		$command = $catdoc_path." -s $charset_int -d $charset_int -x $filename";           
    		$a = exec($command,$result, $retval);
            
    	} else if ($source_type == 'rtf') {
    		$command = $catdoc_path." -s $charset_int -d $charset_int -x $filename";          
    		$a = exec($command,$result, $retval);
            
    	} else if ($source_type == 'xls') {
    		$command = $xls2csv_path." -s $charset_int -d $charset_int -x $filename";
    		$a = exec($command,$result, $retval);
            
    	} else if ($source_type == 'ppt') {
    		$command = $catppt_path." -s $charset_int -d $charset_int $filename";             
    		$a = exec($command,$result, $retval);
    	}
        
        $result = implode(' ', $result);
        $count = strlen($result);
        
        if ($count =='0'){          //      if there was not one word found, print warning message
            printStandardReport('nothingFound',$command_line);
            $result = 'ERROR';
        }
        
    	unlink ($filename);
        unset ($command, $retval, $a, $contents, $count);
    	return $result; 
    }

    //function to calculate the weight of pages
    function calc_weight ($words_in_page, $word_in_title, $word_in_domain, $word_in_path, $path_depth, $meta_keyword, $main_url_factor) {
    	global $title_weight, $domain_weight, $path_weight, $meta_weight;

    	$weight =   ( (   $words_in_page
                        + $word_in_title * $title_weight
                        + $word_in_domain * $domain_weight
                        + $word_in_path * $path_weight
                        + $meta_keyword * $meta_weight
                      ) * 10 
                      / (0.2 + 0.8*$path_depth)
                    )*$main_url_factor;

    	return $weight;
    }
     
    function  remove_sessid($url) {
    	return preg_replace("/(\?|&)(PHPSESSID|JSESSIONID|ASPSESSIONID|sid)=[0-9a-zA-Z]+$/", "", $url);
    }    
      
    function get_sitemap ($input_file, $mysql_table_prefix) { 
        global $command_line;
        $s_map = simplexml_load_file ($input_file); 
        if ($s_map != '') { // if sitemap.xml was conform to XML version 1.0 
            $links = array (); 
            foreach($s_map as $url) { 
                $the_url = str_replace("&amp;","&",$url->loc);
                //$the_url = substr($the_url, 0, strrpos($the_url,'/'));                 
                $lastmod = strtotime($url->lastmod); // get lastmod date only for this page from sitemap 
                $del=mysql_query("delete from ".$mysql_table_prefix."temp"); // function get_sitemap will build a new temp table 
                $res=mysql_query("select indexdate from ".$mysql_table_prefix."links where url like '%$the_url%'"); 
                $num_rows = mysql_num_rows($res); // do we already know this link?               
                $indexdate = 0; 
                if ($num_rows > 0) $indexdate = strtotime(mysql_result($res,"indexdate"));
                
                $new = $lastmod - $indexdate;                  
                if ($new > '0') $links[] =($url->loc); // add new link only if date from sitemap.xml is newer than date of last index 
            }
            clean_resource($res) ;                        
            $links = explode(",",(implode(",",$links))); // destroy SimpleXMLElement Object and get link array 
        }
            
        if ($links) {
            printStandardReport('validSitemap',$command_line);            
        } else {
            printStandardReport('invalidSitemap',$command_line);  
        }
        //echo "<br>Link´ Array:<br><pre>";print_r($links);echo "</pre>";  
        return($links); 
    }
    
    function create_sitemap($site_id, $url) {
        global $mysql_table_prefix, $smap_dir;
        
        $changefreq = "monthly";   //      individualize this variable
        $priority   = "0.50";      //      individualize this variable
        
        //      Below this only change something, if you are sure to remain compatible to http://www.sitemaps.org/schemas/sitemap/0.9
        $date       = date("Y-m-d");
        $time       = date("h:i:s");
        $modtime    = "T$time+01:00";       
        $version    = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>" ;
        $urlset     = "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://www.google.com/schemas/sitemap/0.84 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd\">";
        $copyright  = "<!-- Generated by Sphider-plus created by Tec (v.1.2 rev.3) -->" ;
        $update     = "<!-- Last update of this sitemap: $date / $time -->" ;
        
        $all_links  = '';
        $res=mysql_query("select * from ".$mysql_table_prefix."links where site_id = $site_id"); 
        echo mysql_error(); 
        $num_rows = mysql_num_rows($res);    //      Get all links of the current domain
        
        for ($i=0; $i<$num_rows; $i++) {    //      Create individual rows for XML-file
            $link = mysql_result($res, $i, "url");
            $link = str_replace("&", "&amp;", $link);   // URL should become XML conform
            $all_links = "$all_links<url><loc>$link</loc><lastmod>$date$modtime</lastmod><changefreq>$changefreq</changefreq><priority>$priority</priority></url>\n";           
        }
        clean_resource($res) ;
         
        $name = parse_url($url);                    //      Create filename and open file 
        $hostname = $name[host];
     
        if ($hostname == 'localhost'){              //  if we run a localhost system extract the domain
            $pathname = $name[path];                //  get path, domain and filename               
            $pos = strpos($pathname,"/",1);         //  extract domain from path and forget first / by +1 offset               
            $pathname = substr($pathname,$pos+1);   // suppress /localhost/              
            $pos = strrpos($pathname,"/");
            
            if ($pos) {
                $pathname = substr(str_replace("/", "_", $pathname),0,$pos);   // if exists, suppress folder, filename and suffix
            }
        
            if (!is_dir($smap_dir)) {
                mkdir($smap_dir, 0766);     // if new, create directory
            }
            $filename   = "./$smap_dir/sitemap_localhost_$pathname.xml";             
            if (!$handle = fopen($filename, "w")) {
                printInvalidFile($filename);
                die;
            }
            
        } else {    //  if we run in the wild
            if (!is_dir($smap_dir)) {
                mkdir($smap_dir, 0766);     // if new, create directory
            }        
            $filename   = "./$smap_dir/sitemap_$hostname.xml"; 
            if (!$handle = fopen($filename, "w")) {
                printInvalidFile($filename);
                die ('');
            }
        }
        
        //      Now write all to XML-file
        if (!fwrite($handle, "$version\n$urlset\n$copyright\n$update\n$all_links</urlset>\n")) {
            printInvalidFile($filename);
            die ('');
        } 
        fclose($handle);
        
        //      sitemap.xml done! Now final printout      
        printSitemapCreated($filename);            
    
    }
        
    function clean_resource($result) {
        global $clear;
       
        if ($clear == '1') {         
            if ($result == '') {      
                echo "<br />Unable to free resources.<br />Invalid or no resource available<br />";
                die ('Index / Re-index aborted');
            }
            
            $free = mysql_free_result($result) ;  
            if ($free != '1') {      
                echo "<br />Unable to free resources. MySQL connection is still in use.<br />Resource: $result<br />";
                die ('Index / Re-index aborted');
            }
            //  DO NOT USE THE NEXT ROW ON SHARED HOSTING SYSTEMS ! ! !   'flush query cache' could be forbidden.            
            mysql_query("FLUSH QUERY CACHE");
            echo mysql_error();
        }
    }

?>
