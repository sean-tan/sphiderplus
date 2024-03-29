<?
    error_reporting(0); // Any notices/warnings will cause errors in suggest javascript

    require_once('../../settings/database.php'); 
    require_once('../../settings/conf.php');

    if (get_magic_quotes_gpc()==1) {
    	$_GET['q'] = stripslashes($_GET['q']);
    } 

    if ($utf8 == '1') {   
        $_GET['q'] = addslashes(($_GET['q']));
        //$_GET['q'] = utf8_encode(addslashes(($_GET['q'])));         
        if ($case_sensitive == 0) {
            $_GET['q'] = lower_case($_GET['q']);
        }
    } else {
        $_GET['q'] = html_entity_decode(addslashes(lower_case($_GET['q'])));
    }
    
    //  if search string too small, do not search for keywords/phrases
    if (strlen($_GET['q'])<3) {
    	$suggest_phrases = false;
    	$suggest_keywords = false;
    }

    //check if search string is phrase
    if (!strpos($_GET['q'],' ')) {
    	$suggest_phrases = false;
    }
    
    //     remove control character
    $_GET['q'] = preg_replace("/!|\"|\*/", "",$_GET['q']);
    
    //  searches from saved queries (query_log table)
    if ($suggest_history && $_GET['q']!='"') {
    	$result = mysql_query($sql = "
    	SELECT 	query as keyword, max(results) as results
    	FROM {$mysql_table_prefix}query_log 
    	WHERE results > 0 AND (query LIKE '{$_GET['q']}%' OR query LIKE '\"{$_GET['q']}%') 
    	GROUP BY query ORDER BY results DESC
    	LIMIT $suggest_rows
    	");
    	if($result && mysql_num_rows($result))
    	{
    	    while($row = mysql_fetch_array($result))
    	    {
    	        $values[$row['keyword']] = $row['results'];
    	    }    
    	}
    }
    
    //      for phrase search enter here
    if ($suggest_phrases) {
    	//$_GET['q'] = str_replace('"','',$_GET['q'] );
    	$_words = substr_count($_GET['q'],' ') + 1; 

        
        if ($utf8 == '0') {
        	$result = mysql_query($sql = "
        	SELECT count(link_id) as results, SUBSTRING_INDEX(SUBSTRING(fulltxt,LOCATE('{$_GET['q']}',LOWER(fulltxt))), ' ', '$_words') as keyword FROM {$mysql_table_prefix}links where LOWER(fulltxt) like '%{$_GET['q']}%' 
        	GROUP BY SUBSTRING_INDEX( SUBSTRING( LOWER(fulltxt), LOCATE( '{$_GET['q']}', LOWER(fulltxt) ) ) , ' ', '$_words' ) LIMIT $suggest_rows
        	");       
        }
    	
        if ($utf8 == '1' && $case_sensitive == '0') {
        	$result = mysql_query($sql = "
        	SELECT count(link_id) as results, SUBSTRING_INDEX(SUBSTRING(fulltxt,LOCATE('{$_GET['q']}',CONVERT(LOWER(fulltxt)USING utf8))), ' ', '$_words') as keyword FROM {$mysql_table_prefix}links where CONVERT(LOWER(fulltxt)USING utf8) like '%{$_GET['q']}%' 
        	GROUP BY SUBSTRING_INDEX( SUBSTRING( CONVERT(LOWER(fulltxt)USING utf8), LOCATE( '{$_GET['q']}', CONVERT(LOWER(fulltxt)USING utf8) ) ) , ' ', '$_words' ) LIMIT $suggest_rows
        	");       
        }
        
        if ($utf8 == '1' && $case_sensitive == '1') {        
        	$result = mysql_query($sql = "
        	SELECT count(link_id) as results, SUBSTRING_INDEX(SUBSTRING(fulltxt,LOCATE('{$_GET['q']}',CONVERT((fulltxt)USING utf8))), ' ', '$_words') as keyword FROM {$mysql_table_prefix}links where CONVERT((fulltxt)USING utf8) like '%{$_GET['q']}%' 
        	GROUP BY SUBSTRING_INDEX( SUBSTRING( CONVERT((fulltxt)USING utf8), LOCATE( '{$_GET['q']}', CONVERT((fulltxt)USING utf8) ) ) , ' ', '$_words' ) LIMIT $suggest_rows
        	");
        }
        
    	if($result && mysql_num_rows($result))
    	{
    	    while($row = mysql_fetch_array($result))
    	    {
    	    	//$row['keyword'] = preg_replace("/[^\s\w]/ims",'',$row['keyword']);//array('.',',','?')$row['keyword']);
    	         $values[$row['keyword']] = $row['results'];
    	    }    
    	}

    //  for single keyword search  enter here       
    } elseif ($suggest_keywords) {
    	for ($i=0;$i<=15; $i++) {
    		$char = dechex($i);
    		$result = mysql_query($sql = "
    		SELECT keyword, count(keyword) as results 
    		FROM {$mysql_table_prefix}keywords INNER JOIN {$mysql_table_prefix}link_keyword$char USING (keyword_id) 
    		WHERE keyword LIKE '{$_GET['q']}%'  
    		GROUP BY keyword 
    		ORDER BY results desc
    		LIMIT $suggest_rows
    		");
    		if($result && mysql_num_rows($result)) {		
    		    while($row = mysql_fetch_array($result)) {
    		        $values[$row['keyword']] = $row['results'];
    		    }    
    		}
    	}
    	arsort($values);
    	$values = array_slice($values, 0, $suggest_rows);
    }

    //  show suggestion
    if (is_array($values)) {
    	arsort($values); 
    	if (is_array($values)) foreach ($values as $_key => $_val) {
    		$js_array[] = 'new Array("' .str_replace('"','',$_key)  . '", " <small><b>' . $_val . '</b> results</small>")';
    	}
        
        if ($utf8 == '1') {
            print ("new Array(" . implode(", ", $js_array) . ")");
        } else {
            print utf8_encode("new Array(" . implode(", ", $js_array) . ")");        
        }
        
    }
    
	function lower_case($string) {
		return (strtr($string, "ABCDEFGHIJKLMNOPQRSTUVWXYZ���",
					  "abcdefghijklmnopqrstuvwxyz���"));
	}
    
    
    

?>
