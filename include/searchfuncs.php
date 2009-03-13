<?php 

    error_reporting(E_ALL ^ E_NOTICE);
	
	function swap_max (&$arr, $start, $domain) {
		$pos  = $start;
		$maxweight = $arr[$pos]['weight'];
		for  ($i = $start; $i< count($arr); $i++) {       
			if ($arr[$i]['domain'] == $domain) {
				$pos = $i;
				$maxweight = $arr[$i]['weight'];
				break;
			}
			if ($arr[$i]['weight'] > $maxweight) {
				$pos = $i;
				$maxweight = $arr[$i]['weight'];
			}
		}
		$temp = $arr[$start];
		$arr[$start] = $arr[$pos];
		$arr[$pos] = $temp;
	}

	function sort_with_domains (&$arr) {
		$domain = -1;
		for  ($i = 0; $i< count($arr)-1; $i++) {
			swap_max($arr, $i, $domain);
			$domain = $arr[$i]['domain'];
		}
	}
	
	function sort_by_bestclick (&$arr) {     
		$click_counter = -1;
		for  ($i = 0; $i< count($arr)-1; $i++) {
			swap_click($arr, $i, $click_counter);
			$click_counter = $arr[$i]['click_counter'];           
		}        
	}
    
	function swap_click (&$arr, $start, $click_counter) {
		$pos  = $start;
		$maxclick = $arr[$pos]['click_counter'];
		for  ($i = $start; $i< count($arr); $i++) {       
			if ($arr[$i]['click_counter'] == $domain) {
				$pos = $i;
				$maxclick = $arr[$i]['click_counter'];
				break;
			}
			if ($arr[$i]['click_counter'] > $maxclick) {
				$pos = $i;
				$maxclick = $arr[$i]['click_counter'];
			}
		}
		$temp = $arr[$start];
		$arr[$start] = $arr[$pos];
		$arr[$pos] = $temp;
	}
    
	function cmp_weight($a, $b) {
		if ($a['weight'] == $b['weight'])
			return 0;          
		return ($a['weight'] > $b['weight']) ? -1 : 1;
	}

	function cmp_dom_dot($a, $b) {
        $dots_a = substr_count($a['domain'], ".");
        $dots_b = substr_count($b['domain'], ".");   
        
		if ($dots_a == $dots_b)
			return 0;
            
		return ($dots_a < $dots_b) ? -1 : 1;         
	}

	function cmp_path_dot($a, $b) {
        $path_a = eregi_replace('([^/]+)$', "", $a['path']);    //      get path without filename
        $path_b = eregi_replace('([^/]+)$', "", $b['path']);    //      get path without filename  
        
        $dots_a = substr_count($path_a, ".");
        $dots_b = substr_count($path_b, ".");   
        
		if ($dots_a == $dots_b)
			return 0;
            
		return ($dots_a < $dots_b) ? -1 : 1;         
	}
    
	function cmp_path_slash($a, $b) {
        $path_a = eregi_replace('([^/]+)$', "", $a['path']);    //      get path without filename
        $path_b = eregi_replace('([^/]+)$', "", $b['path']);    //      get path without filename    

        $slash_a = substr_count($a['path'], "/");
        $slash_b = substr_count($b['path'], "/");
        
		if ($slash_a == $slash_b)
			return 0;
            
		return ($slash_a < $slash_b) ? -1 : 1;         
	}

	function addmarks($a) {
		$a = eregi_replace("[ ]+", " ", $a);
		$a = str_replace(" +", "+", $a);
		$a = str_replace(" ", "+", $a);
		return $a;
	}

	function makeboollist($a) {
		global $entities, $stem_words, $utf8, $case_sensitive;
		while ($char = each($entities)) {
			$a = eregi_replace($char[0], $char[1], $a);
		}
		$a = trim($a);

		$a = eregi_replace("&quot;", "\"", $a);
		$returnWords = array();
        
		//get all phrases
		$regs = Array();
		while (eregi("([-]?)\"([^\"]+)\"", $a, $regs)) {
			if ($regs[1] == '') {
				$returnWords['+s'][] = $regs[2];
				$returnWords['hilight'][] = $regs[2];
			} else {
				$returnWords['-s'][] = $regs[2];
			}
			$a = str_replace($regs[0], "", $a);
		}
 
        if ($case_sensitive == 1) { 
            $a = eregi_replace("[ ]+", " ", $a);
        } else {
            $a = lower_case(eregi_replace("[ ]+", " ", $a));
        }
        
        //  $a = remove_accents($a);
		$a = trim($a);
		$words = explode(' ', $a);
		if ($a=="") {
			$limit = 0;
		} else {
		$limit = count($words);
		}


		$k = 0;
		//get all words (both include and exlude)
		$includeWords = array();
		while ($k < $limit) {
			if (substr($words[$k], 0, 1) == '+') {
				$includeWords[] = substr($words[$k], 1);
				if (!ignoreWord(substr($words[$k], 1))) {
					$returnWords['hilight'][] = substr($words[$k], 1);
					if ($stem_words == 1) {
						$returnWords['hilight'][] = stem(substr($words[$k], 1));
					}
				}
			} else if (substr($words[$k], 0, 1) == '-') {
				$returnWords['-'][] = substr($words[$k], 1);
			} else {
				$includeWords[] = $words[$k];
				if (!ignoreWord($words[$k])) {
					$returnWords['hilight'][] = $words[$k];
					if ($stem_words == 1) {
						$returnWords['hilight'][] = stem($words[$k]);
					}
				}
			}
			$k++;
		}
        
		//add words from phrases to includes
		if (isset($returnWords['+s'])) {
			foreach ($returnWords['+s'] as $phrase) {
                if ($case_sensitive == '0') {
                    $phrase = lower_case(eregi_replace("[ ]+", " ", $phrase));
                } else {
                    $phrase = eregi_replace("[ ]+", " ", $phrase);
                }

				$phrase = trim($phrase);              
				$temparr = explode(' ', $phrase);
				foreach ($temparr as $w)
					$includeWords[] = $w;
			}
		}

		foreach ($includeWords as $word) {
			if (!($word =='')) {
				if (ignoreWord($word)) {

					$returnWords['ignore'][] = $word;
				} else {
					$returnWords['+'][] = $word;
				}	
			}

		}
		return $returnWords;

	}

	function ignoreword($word) {
		global $common;
		global $min_word_length;
		global $index_numbers;
        
		if ($index_numbers == 1) {
			$pattern = "[a-z0-9]+";
		} else {
			$pattern = "[a-z]+";
		}
		if (strlen($word) < $min_word_length || (!eregi($pattern, remove_accents($word))) || ($common[$word] == 1)) {
			return 1;
		} else {
			return 0;
		}
	}

	function search($searchstr, $category, $start, $per_page, $type, $domain) {
		global $length_of_link_desc,$mysql_table_prefix, $show_meta_description, $sort_results, $query_hits;
        global $stem_words, $did_you_mean_enabled, $relevance, $query, $utf8, $wildcount, $type, $case_sensitive;
	
		$possible_to_find = 1;
		$result = mysql_query("select domain_id from ".$mysql_table_prefix."domains where domain = '$domain'");
		if (mysql_num_rows($result)> 0) {
			$thisrow = mysql_fetch_array($result);
			$domain_qry = "and domain = ".$thisrow[0];
		} else {
			$domain_qry = "";
		}
        
		//find all sites that should not be included in the result
		if (count($searchstr['+']) == 0) {
			return null;
		}
		$wordarray = $searchstr['-'];
		$notlist = array();
		$not_words = 0;
		while ($not_words < count($wordarray)) {
			if ($stem_words == 1) {
				$searchword = addslashes(stem($wordarray[$not_words]));
			} else {
				$searchword = addslashes($wordarray[$not_words]);
			}
			$wordmd5 = substr(md5($searchword), 0, 1);

            $query1 = "SELECT link_id from ".$mysql_table_prefix."link_keyword$wordmd5, ".$mysql_table_prefix."keywords where ".$mysql_table_prefix."link_keyword$wordmd5.keyword_id= ".$mysql_table_prefix."keywords.keyword_id and keyword='$searchword'";

			$result = mysql_query($query1);

			while ($row = mysql_fetch_row($result)) {	
				$notlist[$not_words]['id'][$row[0]] = 1;
			}
			$not_words++;
		}
	
		//find all sites containing the search phrase
		$wordarray = $searchstr['+s'];;         
		$phrase_words = 0;       
		while ($phrase_words < count($wordarray)) {
			$searchword = addslashes($wordarray[$phrase_words]);
            $phrase_query = $searchword;                   

            if ($case_sensitive =='1') {           
                $query1 = "SELECT link_id from ".$mysql_table_prefix."links where fulltxt like '% $searchword%'";
            }
            
            if ($case_sensitive =='0') {
                $searchword = lower_case($searchword);
                $query1 = "SELECT link_id from ".$mysql_table_prefix."links where CONVERT(LOWER(fulltxt)USING utf8) like '% $searchword%'";
            } 
/*
            if ($utf8 =='0') {
                $searchword = lower_case($searchword);
                $query1 = "SELECT link_id from ".$mysql_table_prefix."links where LOWER(fulltxt) like '% $searchword%'";
            } 
 */                       
			$result = mysql_query($query1);
			echo mysql_error();            
			$num_rows = mysql_num_rows($result);
       
			if ($num_rows == 0) {
				$possible_to_find = 0;
				break;
			}
			while ($row = mysql_fetch_row($result)) {	
            $value =$row[0];
				$phraselist[$phrase_words]['id'][$row[0]] = 1;            
				$phraselist[$phrase_words]['val'][$row[0]] = $value;                
			}          
			$phrase_words++;
		}
    
		if (($category> 0) && $possible_to_find==1) {
			$allcats = get_cats($category);
			$catlist = implode(",", $allcats);
			$query1 = "select link_id from ".$mysql_table_prefix."links, ".$mysql_table_prefix."sites, ".$mysql_table_prefix."categories, ".$mysql_table_prefix."site_category where ".$mysql_table_prefix."links.site_id = ".$mysql_table_prefix."sites.site_id and ".$mysql_table_prefix."sites.site_id = ".$mysql_table_prefix."site_category.site_id and ".$mysql_table_prefix."site_category.category_id in ($catlist)";
			$result = mysql_query($query1);
			echo mysql_error();
			$num_rows = mysql_num_rows($result);
			if ($num_rows == 0) {
				$possible_to_find = 0;
			}
			while ($row = mysql_fetch_row($result)) {	
				$category_list[$row[0]] = 1;
			}
		}

		//find all sites that include the search word		
		$wordarray = $searchstr['+'];
		$words = 0;
		$starttime = getmicrotime();
        $searchword = addslashes($wordarray[$words]);   //  get only first word of search query   
        $strictpos = strpos($searchword, '!'); //   if  ! is in position 0, we have to search strict
   
        if ($strictpos === 0) {   //    ****        for 'Strict search' enter here     
            $searchword = str_replace('!', '', $searchword);      
            $query = "SELECT keyword_id, keyword from ".$mysql_table_prefix."keywords where keyword = '$searchword'";
            echo mysql_error();
            $result = mysql_query($query);
            $num_rows = mysql_num_rows($result);

            if ($num_rows == 0) {   // if there was no searchword in table keywords                
                $possible_to_find = 0;
                $break = 1;
            }
            if ($num_rows !=0) {
                  // get all searchwords as keywords from table keywords
                $keyword_id = mysql_result($result, $i, "keyword_id");   
                $keyword = mysql_result($result, $i, "keyword"); 
                $wordmd5 = substr(md5($keyword), 0, 1);     // calculate attribute for link_keyword table  

                if ($query_hits == '1') {   //      get query hit results
                    $query1 = "SELECT distinct link_id, hits, domain from ".$mysql_table_prefix."link_keyword$wordmd5, ".$mysql_table_prefix."keywords where ".$mysql_table_prefix."link_keyword$wordmd5.keyword_id= ".$mysql_table_prefix."keywords.keyword_id and keyword='$searchword' $domain_qry order by hits desc";                       
                } else {        // get weight results
                    $query1 = "SELECT link_id, weight, domain from ".$mysql_table_prefix."link_keyword$wordmd5  where keyword_id = '$keyword_id' order by weight desc";
                }

                echo mysql_error();
                $reso = mysql_query($query1);
                $lines = mysql_num_rows($reso);
               
                if ($lines != 0) {                        
                    $indx =$words;
                }

                while ($row = mysql_fetch_row($reso)) {	
                    $linklist[$indx]['id'][] = $row[0];
                    $domains[$row[0]] = $row[2];
                    $linklist[$indx]['weight'][$row[0]] = $row[1];
                    
                    if ($query_hits == '1') {   //      ensure that result is also available in full text                                                      
                        $txt_res = mysql_query("SELECT fulltxt FROM ".$mysql_table_prefix."links where link_id = '$row[0]'");
                        echo mysql_error();
                        $full_txt = mysql_result($txt_res, 0);          //       get fulltxt  of this link ID      

                        if ($utf8 == '0') {
                            $full_txt = lower_case($full_txt);
                        }
                
                        $foundit = strpos($full_txt, $searchword);  //      get first hit
                        if ($foundit) {                      
                            $page_hits = $linklist[$indx]['weight'][$row[0]] ;
                            $i = '0';

                            while ($i < $page_hits) {       //      find out if all results in full text are really strict                                
                                $found_in = strpos($full_txt, $searchword);                                
                                $tmp_front = substr($full_txt, $found_in-1, 20); //  one character before found match position                     
                                $pos = $found_in+strlen($searchword);                               
                                $tmp_behind = substr($full_txt, $pos, 20); //  one character behind found match position                     
                                $full_txt = substr($full_txt, $pos);  //  get rest of fulltxt                             
                                //  check whether found match is realy strict
                                $found_before = preg_match("/[(a-z)-_*.\/\:&@\w]/", substr($tmp_front, 0, 1));                      
                                $found_behind = preg_match("/[(a-z)-_*.,\/\:&@\w]/", substr($tmp_behind, 0, 1));

                                if ($found_before == 1 || $found_behind == 1) {          //      correct count of hits    
                                    $linklist[$indx]['weight'][$row[0]] = $linklist[$indx]['weight'][$row[0]] - 1;
                                }
                                $i++;
                            }           
                        } else {
                            $linklist[$indx]['weight'][$row[0]] = '0';  //      nothing found in full text. Hits = 0                          
                        }
                    }                     
                }
                $words++;                
            }
        } else {    //****       if not strict-search try here
            $wild_correct = 0;             
            $wildcount = substr_count($searchword, '*');      
                    
            if ($wildcount) {       //  ****        for * wildcard , enter here               
                $searchword = str_replace('*','%', $searchword); 
                $words = '0';
                $query = "SELECT keyword_id, keyword from ".$mysql_table_prefix."keywords where keyword like '$searchword'";
        		echo mysql_error();
        		$result = mysql_query($query);
        		$num_rows = mysql_num_rows($result);

        		if ($num_rows == 0) {   // if there was no searchword in table keywords                
                    $possible_to_find = 0;
                    $break = 1;
        		}
                if ($num_rows !=0) {
                    global $all_wild;
                    $all_wild = '';
                    for ($i=0; $i<$num_rows; $i++) {        // get all searchwords as keywords from table keywords
                        $keyword_id = mysql_result($result, $i, "keyword_id");   
                        $keyword = mysql_result($result, $i, "keyword"); 
                        $all_wild =("$all_wild $keyword");

                        $wordmd5 = substr(md5($keyword), 0, 1);     // calculate attribute for link_keyword table              

                        if ($query_hits == '1') {   //      get query hit results
                            $query1 = "SELECT link_id, hits, domain from ".$mysql_table_prefix."link_keyword$wordmd5  where keyword_id = '$keyword_id' order by hits desc";                        
                        } else {        // get weight results
                            $query1 = "SELECT link_id, weight, domain from ".$mysql_table_prefix."link_keyword$wordmd5  where keyword_id = '$keyword_id' order by weight desc";
            			}

                        echo mysql_error();
                        $reso = mysql_query($query1);
                        $lines = mysql_num_rows($reso);
                         
            			if ($lines == 0) {
            				if ($type != "or") {
            					$possible_to_find = 0;
            					break;
            				}
            			}
            			if ($type == "or" && $query_hits == '0') {
            				$indx = 0;
            			} else {
            				$indx = $words;
            			}

            			while ($row = mysql_fetch_row($reso)) {	
            				$linklist[$indx]['id'][] = $row[0];
            				$domains[$row[0]] = $row[2];
            				$linklist[$indx]['weight'][$row[0]] = $row[1];
                    
                            if ($query_hits == '1') {   //      ensure that result is also available in fulltxt  
                                $searchword =str_replace("%", '', $searchword);
                                $txt_res = mysql_query("SELECT fulltxt FROM ".$mysql_table_prefix."links where link_id = '$row[0]'");
                                echo mysql_error();
                                $full_txt = mysql_result($txt_res, 0);          //       get fulltxt  of this link ID                               
                                $foundit = strpos($full_txt, $searchword);;
                                if (!$foundit) {
                                    $linklist[$indx]['weight'][$row[0]] = '0';  //      nothing found in full text. Hits = 0
                                }
                            }                             
            			}
                    }  
                    $words++;                    
                }

            } else {    //      if no wildcard, try here            
                if ($type == 'tol') {       //  *****         if tolerant search, enter here  
                    $acct_a = array("�", "�", "Ã¤", "ä", "Ã\"�", "Ä", "�", "�", "á", "�", 
                                "&agrave;", "�", "&aacute;", "�", "&Agrave;", "�", "&Aacute;");
                    $base_a = array("a", "a", "a", "a", "A", "A", "A", "a", "a", "a", 
                                "a", "a", "a", "A", "A", "A", "A");
                    $searchword = str_ireplace($acct_a, $base_a, $searchword);                                                      

                    $acct_e = array("�", "é", "�", "&egrave;", "�", "&eacute;", "�", "&Egrave;", "�", "&Eacute;");                                
                    $base_e = array("e", "e", "e", "e", "e", "e", "E", "E", "E", "E");                
                    $searchword = str_ireplace($acct_e, $base_e, $searchword);
                               
                    $acct_i = array("�", "&igrave;", "�", "&iacute;", "�", "&Igrave;", "�", "&Iacute;", 
                                "ñ", "¡", "�'", "¿" );                               
                    $base_i = array("i", "i", "i", "i", "I", "I", "I", "I", 
                                "�", "�", "�", "�");                
                    $searchword = str_ireplace($acct_i, $base_i, $searchword);

                    $acct_o = array("�", "�", "�", "�", "�", "�", "Ö", "Ã¶", "ö", "�", 
                                "ó", "�","&ograve;", "�", "&oacute;", "�", "&Ograve;", "�", "&Oacute;");                                
                    $base_o = array( "o", "O", "o", "o", "o", "o", "O", "o", "o", "o", 
                                "�", "�", "O", "o", "o", "O", "O", "O", "O");                
                    $searchword = str_ireplace($acct_o, $base_o, $searchword);
             
                    $acct_u = array("�", "�", "�", "Ã¼", "ü", "Ã�\�", "Ü", "�", "�", "ú", 
                                "�", "&ugrave;", "�", "&uacute;", "�", "&Ugrave;", "�", "&Uacute;");                           
                    $base_u = array("u", "u", "u", "u", "u", "U", "U", "U", "u", "u", 
                                "u", "u", "u", "u", "U", "U", "U", "U"); 
                    $searchword = str_ireplace($acct_u, $base_u, $searchword); 
                  
                    $get = array("a", "e", "i", "o", "u");
                    $out = array("%", "%", "%", "%", "%");                
                    $searchword = str_ireplace($get, $out, $searchword);
                 
                    $query = "SELECT keyword_id, keyword from ".$mysql_table_prefix."keywords where keyword like '$searchword'";
            		echo mysql_error();
            		$result = mysql_query($query);
            		$num_rows = mysql_num_rows($result);

            		if ($num_rows == 0) {   // if there was no searchword in table keywords                
                        $possible_to_find = 0;
                        $break = 1;
            		}
                    if ($num_rows !=0) {
                        global $all_wild;
                        $all_wild = '';
                        for ($i=0; $i<$num_rows; $i++) {        // get all searchwords as keywords from table keywords
                            $keyword_id = mysql_result($result, $i, "keyword_id");   
                            $keyword = mysql_result($result, $i, "keyword"); 
                            $all_wild =("$all_wild $keyword");
                            
                            $wordmd5 = substr(md5($keyword), 0, 1);     // calculate attribute for link_keyword table   

                            if ($query_hits == '1') {   //      get query hit results
                                $query1 = "SELECT link_id, hits, domain from ".$mysql_table_prefix."link_keyword$wordmd5 where keyword_id = '$keyword_id' order by hits desc";                        
                            } else {        // get weight results
                                $query1 = "SELECT link_id, weight, domain from ".$mysql_table_prefix."link_keyword$wordmd5 where keyword_id = '$keyword_id' order by weight desc";
                			}
                            
                            echo mysql_error();
                            $reso = mysql_query($query1);
                            $lines = mysql_num_rows($reso);
                             
                            if ($lines != 0) {                        
                                $indx =$words;
                            }

                            while ($row = mysql_fetch_row($reso)) {	
                                $linklist[$indx]['id'][] = $row[0];
                                $domains[$row[0]] = $row[2];
                                $linklist[$indx]['weight'][$row[0]] = $row[1];                                  
                            }
                            //$words++;                            
                        }
                        $words++;      
                    }                    
                } else {    //      finally standard search  
                    $words = 0;   
                   
                    while (($words < count($wordarray)) && $possible_to_find == 1) {
                        if ($stem_words == 1) {
                            $searchword = addslashes(stem($wordarray[$words]));                       
                        } else {
                            $searchword = addslashes($wordarray[$words]);
                        }  
               
                        $wordmd5 = substr(md5($searchword), 0, 1); 
                       
                        if ($query_hits == '1') {   //      get query hit results
                            $query1 = "SELECT distinct link_id, hits, domain from ".$mysql_table_prefix."link_keyword$wordmd5, ".$mysql_table_prefix."keywords where ".$mysql_table_prefix."link_keyword$wordmd5.keyword_id= ".$mysql_table_prefix."keywords.keyword_id and keyword='$searchword' $domain_qry order by hits desc";                        
                        } else {        // get weight results
                            $query1 = "SELECT distinct link_id, weight, domain from ".$mysql_table_prefix."link_keyword$wordmd5, ".$mysql_table_prefix."keywords where ".$mysql_table_prefix."link_keyword$wordmd5.keyword_id= ".$mysql_table_prefix."keywords.keyword_id and keyword='$searchword' $domain_qry order by weight desc";
                        }
                        echo mysql_error();
                        $result = mysql_query($query1);
                        $num_rows = mysql_num_rows($result);
            
                        if ($num_rows == 0) {
                            if ($type != "or") {
                                $possible_to_find = 0;
                                break;
                            }
                        }
                        if ($type == "or" && $query_hits == '0') {
                            $indx = 0;
                        } else {
                            $indx = $words;
                        }

                        while ($row = mysql_fetch_row($result)) {	
                            $linklist[$indx]['id'][] = $row[0];
                            $domains[$row[0]] = $row[2];
                            $linklist[$indx]['weight'][$row[0]] = $row[1];               
                        
                            if ($query_hits == '1') {   //      ensure that result is also available in fulltxt                            
                                if ($type == 'phrase') {
                                    if ($utf8 =='0') {
                                        $searchword = lower_case($phrase_query);    //      get the whole phrase   
                                    } else {
                                        $searchword = $phrase_query;    
                                    }                                    
                                }                               
                                $linklist[$indx]['weight'][$row[0]] = '0';                            
                                $txt_res = mysql_query("SELECT fulltxt FROM ".$mysql_table_prefix."links where link_id = '$row[0]'");
                                echo mysql_error();
                                $full_txt = mysql_result($txt_res, 0);          //       get fulltxt  of this link ID   
                                if ($case_sensitive == '0') {
                                    $full_txt = lower_case($full_txt);
                                }
                               
                                if (substr_count($full_txt, $searchword)) {       //  found complete phrase in full text?                                    
                                    $linklist[$indx]['weight'][$row[0]] = substr_count($full_txt, $searchword);  //      number of hits found in this full text
                                } 
                            }                            
                        }
                        $words++;
                    }
                }
               
            }     
        }       //  ***** end  different search modes         
        
		if ($type == "or") {
			$words = 1;
		}
   
		$result_array_full = Array();                                                        
		if ($words == 1 && $not_words == 0 && $category < 1) { // for OR-Sarch without query_hits and one word query, we already have the result            
			$result_array_full = $linklist[0]['weight'];
         
		} else {    //     otherwise build an intersection of all the results
			$j= 1;
			$min = 0;
			while ($j < $words) {
				if (count($linklist[$min]['id']) > count($linklist[$j]['id'])) {
					$min = $j;
				}
				$j++;
			}                             
            
			$j = 0;
			$temp_array = $linklist[$min]['id'];
			$count = 0;      
			while ($j < count($temp_array)) {
				$k = 0; //and word counter
				$n = 0; //not word counter
				$o = 0; //phrase word counter
                if ($query_hits == '1') {
                    $weight = 0;
                } else {
                    $weight = 1;
                }
                
				$break = 0;                         
                if ($type =='phrase' && $query_hits == '1') {    // for PHRASE search: find out how often the phrase was found in fulltxt (not for weighting %  scores)               
    				while ($k < $words && $break== 0) {                    
    					if ($linklist[$k]['weight'][$temp_array[$j]] > 0) {                           
                            $weight = $linklist[$k]['weight'][$temp_array[$j]];                            
    					} else {                   
    						$break = 1;
    					}
    					$k++;
    				}
                    
                } else {    // calculate weight for all other search modes                   
    				while ($k < $words && $break== 0) {
    					if ($linklist[$k]['weight'][$temp_array[$j]] > 0) {                
    						$weight = $weight + $linklist[$k]['weight'][$temp_array[$j]];                          
    					} else {                   
    						$break = 1;
    					}
    					$k++;
    				}
                
                }               
                
				while ($n < $not_words && $break== 0) {
					if ($notlist[$n]['id'][$temp_array[$j]] > 0) {
						$break = 1;
					}
					$n++;
				}				

				while ($o < $phrase_words && $break== 0) {
					if ($phraselist[$n]['id'][$temp_array[$j]] != 1) {
						$break = 1;
					}
					$o++;
				}
				if ($break== 0 && $category > 0 && $category_list[$temp_array[$j]] != 1) {
					$break = 1;
				}

				if ($break == 0) {
					$result_array_full[$temp_array[$j]] = $weight;
					$count ++;
				}
				$j++;
			}
		}//word == 1      

		$end = getmicrotime()- $starttime;     
		if ((count($result_array_full) == 0 || $possible_to_find == 0) && $did_you_mean_enabled == 1) {
			reset ($searchstr['+']);
			foreach ($searchstr['+'] as $word) {           
                $word2 = str_ireplace("�", "�", addslashes("$word"));             
				$result = mysql_query("select keyword from ".$mysql_table_prefix."keywords where soundex(keyword) = soundex('$word2%')");
 				$max_distance = 100;
				$near_word ="";
				while ($row=mysql_fetch_row($result)) {                
                    $distance = levenshtein($row[0], $word);                    
					if ($distance < $max_distance && $distance <10) {
						$max_distance = $distance;
						$near_word = ($row[0]);                       
					}
				}

				if ($near_word != "" && $word != $near_word) {            
					$near_words[$word] = $near_word;
				}
			}            
			$res['did_you_mean'] = $near_words;            
			return $res;
		}
       
		if (count($result_array_full) == 0) {
			return null;
		}
		arsort ($result_array_full);

        if ($sort_results == 4 && $domain_qry == "") {    // output alla Google)
            while (list($key, $value) = each($result_array_full)) {
                if (!isset($domains_to_show[$domains[$key]])) {
                    $result_array_temp[$key] = $value;
                    $domains_to_show[$domains[$key]] = 1;
                } else if ($domains_to_show[$domains[$key]] ==  1) {
                    $domains_to_show[$domains[$key]] = Array ($key => $value);
                }
            }
        } else {       
            $result_array_temp = $result_array_full;
        }
	
		while (list($key, $value) = each ($result_array_temp)) {
			$result_array[$key] = $value;
			if (isset ($domains_to_show[$domains[$key]]) && $domains_to_show[$domains[$key]] != 1) {
				list ($k, $v) = each($domains_to_show[$domains[$key]]);
				$result_array[$k] = $v;
			}
		}
        
		$keys = array_keys($result_array);
		$maxweight = $result_array[$keys[0]];
        $count = '0';
        
        foreach ($result_array as $row) {      
            if ($query_hits =='0') {       //    limit result output to min. relevance level         
                $weight = number_format($row/$maxweight*100, 0);                 
                if ($weight >= $relevance) {                       
                    $count = ($count+1) ;                
                }
            } else {
                if ($row > '0') {   //      present results only if hits in full text
                    $count = ($count+1) ;
                }
            }                
        }

        if ($count != '0') {
            $result_array = array_chunk($result_array, $count, true);   //      limit result output(weight > relevance level OR hits in fulltext > 0)
        }
        $result_array = $result_array[0];               
		$results = count($result_array);
    
		for ($i = ($start -1)*$per_page; $i <min($results, ($start -1)*$per_page + $per_page) ; $i++) {
			$in[] = $keys[$i];
		}
        
		if (!is_array($in)) {
			$res['results'] = $results;
			return $res;
		}

		$inlist = implode(",", $in);
        
		if ($length_of_link_desc == 0) {
			$fulltxt = "fulltxt";
		} else {
			$fulltxt = "substring(fulltxt, 1, $length_of_link_desc)";
		}

		$query1 = "SELECT distinct link_id, url, title, description,  $fulltxt, size, click_counter FROM ".$mysql_table_prefix."links WHERE link_id in ($inlist)";

		$result = mysql_query($query1);
		echo mysql_error();

		$i = 0;
		while ($row = mysql_fetch_row($result)) {                         
            $res[$i]['title'] = $row[2];
            $res[$i]['url'] = $row[1];
            if ($row[3] != null && $show_meta_description == 1)
                $res[$i]['fulltxt'] = $row[3];
            else                        
                $res[$i]['fulltxt'] = $row[4];
            $res[$i]['size'] = $row[5];
            $res[$i]['click_counter'] = $row[6];
            $res[$i]['weight'] = $result_array[$row[0]];
            $dom_result = mysql_query("select domain from ".$mysql_table_prefix."domains where domain_id='".$domains[$row[0]]."'");
            $dom_row = mysql_fetch_row($dom_result);
            $res[$i]['domain'] = $dom_row[0];            
            $urlparts = parse_url($res[$i]['url']);
            //$res[$i]['path'] = $urlparts['path'];    //      get full path 
            $res[$i]['path'] = eregi_replace('([^/]+)$', "", $urlparts['path']);    //      get path without filename

            $i++;                  
		}
        usort($res, "cmp_weight");              //      standard output sorted by relevance (weight)              
        $dom = $res[0]['domain'] ;        
      
		if (($sort_results == '4'  && $domain_qry == "") || $sort_results == '3' ) {    //  output alla Google  OR  by domain name
			sort_with_domains($res);

		} else {     
            if ($sort_results == '2') {         //      enter here if 'Main URLs' on top of listing
                if ($dom == 'localhost') { 
                    //usort($res, "cmp_path_dot");
                    //usort($res, "cmp_path_slash");
                } else {
                    //usort($res, "cmp_dom_dot");     //      sort domains without dots on top
                }
                
            }
            if ($sort_results == '5') {         //      enter here if 'Most Popular Click' on top of listing
            sort_by_bestclick($res);
            }
        }      
        
		echo mysql_error();
		$res['maxweight'] = $maxweight;
		$res['results'] = $results;     
		return $res;
	}

    function get_search_results($query, $start, $category, $searchtype, $results, $domain) {
    	global $sph_messages, $results_per_page, $all_wild, $show_meta_description, $title_length,    
    		$links_to_next, $wildsearch, $show_warning, $mark, $type,
    		$show_query_scores,  $index_host, $url_length, $query_hits,
    		$mysql_table_prefix, $desc_length, $utf8, $case_sensitive;
            
    	if ($results != "") {
    		$results_per_page = $results;
    	}
        
    	if ($searchtype == "phrase") {
    	   $query=str_replace('"','',$query);
           $query = "\"".$query."\"";
    	}

        if ($utf8 == 1  && $case_sensitive == 0 && $searchtype != "phrase") { 
            $query = lower_case($query);
        }
        
    	$starttime = getmicrotime();
    	// catch " if only one time entered
            if (substr_count($query,'"')==1){
               $query=str_replace('"','',$query);
            }  
       
    	$words = makeboollist($query);       
    	$ignorewords = $words['ignore']; 	
    	$full_result['ignore_words'] = $words['ignore'];
        
    	if ($start==0) 
    		$start=1;
    	$result = search($words, $category, $start, $results_per_page, $searchtype, $domain);        
        $query= stripslashes($query);
        $entitiesQuery = htmlspecialchars(str_replace("\"", "",$query));    
    	$full_result['ent_query'] = $entitiesQuery;
    	$endtime = getmicrotime() - $starttime;
    	$rows = $result['results'];
    	$time = round($endtime*100)/100;	
    	$full_result['time'] = $time;	
    	$did_you_mean = "";
    	if (isset($result['did_you_mean'])) {
    		$did_you_mean_b=$entitiesQuery;
    		$did_you_mean=$entitiesQuery;
           
    		while (list($key, $val) = each($result['did_you_mean'])) {
    			if ($key != $val) {
                    $did_you_mean_b = str_replace($key, "<b>$val</b>", $did_you_mean_b);            
    				$did_you_mean = str_replace($key, "$val", $did_you_mean);                  
    			}
    		}
    	}

    	$full_result['did_you_mean'] = $did_you_mean;
    	$full_result['did_you_mean_b'] = $did_you_mean_b;
    	$matchword = $sph_messages["matches"];
        
    	if ($rows == 1) {
    		$matchword= $sph_messages["match"];
    	}

    	$num_of_results = count($result) - 2;	
    	$full_result['num_of_results'] = $num_of_results;

    	if ($start < 2)
    		saveToLog(addslashes($query), $time, $rows);
    	$from = ($start-1) * $results_per_page+1;
    	$to = min(($start)*$results_per_page, $rows);

    	
    	$full_result['from'] = $from;
    	$full_result['to'] = $to;
    	$full_result['total_results'] = $rows;

    	if ($rows>0) {
    		$maxweight = $result['maxweight'];
    		$i = 0;
    		while ($i < $num_of_results && $i < $results_per_page) {
    			$title = $result[$i]['title'];
    			$url = $result[$i]['url'];
    			$fulltxt = $result[$i]['fulltxt'];
    			$page_size = $result[$i]['size'];
    			$domain = $result[$i]['domain'];
    			if ($page_size!="") 
    				$page_size = number_format($page_size, 1)." kb";
                   
                //  If available, enable part of a word highlighting in result report         
                if ($all_wild) $words = makeboollist($all_wild);

    			$txtlen = strlen($fulltxt);
                
                //$refreshed = ereg_replace("[*!]", '',trim($query)); //  works also for *wildcard search       
    			if ($show_meta_description === 1 || $txtlen > $desc_length) {                    
                    $places = array();
                    $strictpos = strpos($query, '!');
               
                    if ($strictpos === 0) {    // if !strict search enter here
                        if ($case_sensitive == '1') {
                            $recovered = str_replace('!', '',trim($query));
                            $tmp =$fulltxt;
                        } else {
                            $recovered = str_replace('!', '',trim(lower_case($query)));
                            $tmp = lower_case($fulltxt);                            
                        }
                        
                        $words['hilight'][0] = "$recovered";  //  replace without ' ! '
                        $strict_length =strlen($recovered);   
                        $found_in = '1';    //  pointer position start
                        $pos_absolut    = '0';
                       
        				foreach($words['hilight'] as $word) {              
        					while (!($found_in =='')) { 
                                $found_in = strpos($tmp, $word);                         
                                $tmp_front = substr($tmp, $found_in-1); //  one character before found match position                     
        						$pos = $found_in+strlen($word);
                                $pos_absolut = $pos_absolut + $found_in;
        						$tmp = substr($tmp, $pos);  //  get rest of fulltxt
                                                                                         
                                //  check weather found match is realy strict
                                $found_before = preg_match("/[(a-z)-_*.\/\:&@\w]/", substr($tmp_front, 0, 1));                      
                                $found_behind = preg_match("/[(a-z)-_*.,\/\:&@\w]/", substr($tmp, 0, 1));

                                if ($found_before ===0 && $found_behind ===0) {
                                    $places[] = $pos_absolut;   //  remind absolut position of match                                   
                                    $found_in = '';
                                }                           
        					}
        				}
                        
                    } else {    // if not !strict search enter here (standard search)
            			foreach($words['hilight'] as $word) {                   
                            if ($case_sensitive == '0') {
                                $tmp = lower_case($fulltxt);
                                $word= lower_case($word);
                            } else { 
                                $tmp = $fulltxt;                                
                            } 

                            $found_in = strpos($tmp, $word);                       
            				$sum = -strlen($word);
            				while (!($found_in =='')) {
            					$pos = $found_in+strlen($word);
            					$sum += $pos;  //FIX!!
            					$tmp = substr($tmp, $pos);
            					$places[] = $sum;
            					$found_in = strpos($tmp, $word);
            				}
            			}
                    }             
                    
    				sort($places);
    				$x = 0;
    				$begin = 0;
    				$end = 0;
    				while(list($id, $place) = each($places)) {
    					while ($places[$id + $x] - $place < $desc_length && $x+$id < count($places) && $place < strlen($fulltxt) -$desc_length) {
    						$x++;
    						$begin = $id;
    						$end = $id + $x;
    					}
    				}

    				$begin_pos = max(0, $places[$begin] - 80);
    				$fulltxt = substr($fulltxt, $begin_pos, $desc_length);

    				if ($places[$begin] > 0) {
    					$begin_pos = strpos($fulltxt, " ");
    				}
    				$fulltxt = substr($fulltxt, $begin_pos, $desc_length);
    				$fulltxt = substr($fulltxt, 0, strrpos($fulltxt, " "));
    				$fulltxt = $fulltxt;                                    
    			}
                
                if ($query_hits == '0') {       //  calculate percentage of weight
                    $weight = number_format($result[$i]['weight']/$maxweight*100, 1);
                } else {
                    $weight = number_format($result[$i]['weight']);
                }
    			if ($title=='')
    			$title = $sph_messages["Untitled"];
    			$regs = Array();
                
    			if (strlen($title) > $title_length) {                   // if necessary shorten length of title in result page
                    $length_tot = strpos($title, " ",$title_length);    // find end of last word for shortened title                   
                    if ($length_tot) {
                        $title = substr($title, 0, $length_tot)." ...";
                    }    				
    			}
                
        		$url2 = $url;
                
                if (strlen($url) > $url_length) {    // if necessary shorten length of URL in result page
                    $url2 = substr($url, 0, $url_length)."..."; 
                } 

                if ($places[0] == '' && $query_hits == 1 && $type != 'tol') {     //  if nothing found in HTML text and query hits as result output
                    $weight = '0';
                }
                
                if ($places[0] == '' && $show_warning == '1' && $type !='tol' || ( $show_warning == '1' && $weight == '0')) {  // if  no HTML text to highlight
                    $warnmessage = $sph_messages['showWarning'];
                    $fulltxt = "<span class='warn'>$warnmessage</span>"; 
                }

                if ($mark == 'markbold') {
                    $highlight = "span class='mak_1'";
                }
                if ($mark == 'markblue') {           
                    $highlight = "span class='mak_2'";
                }
                if ($mark == 'markyellow') {
                    $highlight = "span class='mak_3'";
                }
                if ($mark == 'markgreen') {
                    $highlight = "span class='mak_4'";
                }
              
                foreach($words['hilight'] as $change) {
                    if (!($strictpos === 0)) {  //  no marking in title and url if strict search                    
                        if ($case_sensitive =='1') {    //  if we have to search case sensetive, enter here
                            while (@ereg("[^\>](".$change.")[^\<]", " ".$title." ", $regs)) {
                                $title = ereg_replace($regs[1], "<$highlight>".$regs[1]."</span>", $title);
                            }
                            if ($index_host == '1') { 
                                while (@ereg("[^\>](".$change.")[^\<]", $url2, $regs)) { 
                                    $url2 = ereg_replace($regs[1], "<$highlight>".$regs[1]."</span>", $url2); 
                                }    
                            }
                        } else {        // mark upper and lower case match                                        
                            while (@eregi("[^\>](".$change.")[^\<]", " ".$title." ", $regs)) {
                                $title = eregi_replace($regs[1], "<$highlight>".$regs[1]."</span>", $title);
                            }
                            if ($index_host == '1') { 
                                while (@eregi("[^\>](".$change.")[^\<]", $url2, $regs)) { 
                                    $url2 = eregi_replace($regs[1], "<$highlight>".$regs[1]."</span>", $url2); 
                                } 
                            } 
                        }                    
                    }
                    if ($strictpos === 0) {     //      if strict search mark only the real result with blanks before and behind                
                        $change = " $change ";
                    }
                    if ($case_sensitive == '1') {   //  mark fulltext case sensitive
            			while (@ereg("[^\>](".$change.")[^\<]", " ".$fulltxt." ", $regs)) {                    
            				$fulltxt = ereg_replace($regs[1], "<$highlight>".$regs[1]."</span>", $fulltxt); 
                        }    
        			} else {        //      mark all in fulltext  
            			while (@eregi("[^\>](".$change.")[^\<]", " ".$fulltxt." ", $regs)) {                    
            				$fulltxt = eregi_replace($regs[1], "<$highlight>".$regs[1]."</span>", $fulltxt);                    
            			}
                    }
        		}
                $places = array();
                
    			$num = $from + $i;

    			$full_result['qry_results'][$i]['num'] =  $num;
    			$full_result['qry_results'][$i]['weight'] =  $weight;
    			$full_result['qry_results'][$i]['url'] =  $url;
    			$full_result['qry_results'][$i]['title'] =  $title;
    			$full_result['qry_results'][$i]['fulltxt'] =  $fulltxt;
    			$full_result['qry_results'][$i]['url2'] =  $url2;
    			$full_result['qry_results'][$i]['page_size'] =  $page_size;
    			$full_result['qry_results'][$i]['domain_name'] =  $domain;
    			$i++;
    		}
    	}

    	$pages = ceil($rows / $results_per_page);
    	$full_result['pages'] = $pages;
    	$prev = $start - 1;
    	$full_result['prev'] = $prev;
    	$next = $start + 1;
    	$full_result['next'] = $next;
    	$full_result['start'] = $start;
    	$full_result['query'] = $entitiesQuery;

    	if ($from <= $to) {

    		$firstpage = $start - $links_to_next;
    		if ($firstpage < 1) $firstpage = 1;
    		$lastpage = $start + $links_to_next;
    		if ($lastpage > $pages) $lastpage = $pages;

    		for ($x=$firstpage; $x<=$lastpage; $x++)
    			$full_result['other_pages'][] = $x;

    	}

    	return $full_result;

    }


?>
