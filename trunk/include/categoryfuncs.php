<?php 

    function get_categories_view() {
    	global $mysql_table_prefix;
    	$categories['main_list'] = sql_fetch_all('SELECT * FROM '.$mysql_table_prefix.'categories WHERE parent_num=0 ORDER BY category');
    		
    	if (is_array($categories['main_list'])) {
    		foreach ($categories['main_list'] as $_key => $_val) {
    			$categories['main_list'][$_key]['sub'] =  sql_fetch_all('SELECT * FROM '.$mysql_table_prefix.'categories WHERE parent_num='.$_val['category_id']);
    		}
    	}
    	return $categories;
    }

    function get_category_info($catid) {
    	global $mysql_table_prefix;
    	$categories['main_list'] = sql_fetch_all("SELECT * FROM ".$mysql_table_prefix."categories ORDER BY category");
    	
    	if (is_array($categories['main_list'])) {
    		foreach($categories['main_list'] as $_val) {
    			$categories['categories'][$_val['category_id']] = $_val;
    			$categories['subcats'][$_val['parent_num']][] = $_val;
    		}
    	}
    	
    	$categories['subcats'] = $categories['subcats'][$_REQUEST['catid']];
    	
    	/* count sites */
    	if (is_array($categories['subcats'])) {
    		foreach ($categories['subcats'] as $_key => $_val) {
    			$categories['subcats'][$_key]['count'] = sql_fetch_all('SELECT count(*) FROM '.$mysql_table_prefix.'site_category WHERE 	category_id='.(int)$_val['category_id']);
    		}
    	}
    		
    	/* make tree */	
    	$_parent = $catid;
    	while ($_parent) {
    		$categories['cat_tree'][] = $categories['categories'][$_parent];
    		$_parent = $categories['categories'][$_parent]['parent_num'];
    	}
    	$categories['cat_tree'] = array_reverse($categories['cat_tree']);
    	
    	
    	/* list category sites */
    	$categories['cat_sites'] = sql_fetch_all('SELECT url, title, short_desc FROM '.$mysql_table_prefix.'sites, '.$mysql_table_prefix.'site_category WHERE category_id='.$catid.' AND '.$mysql_table_prefix.'sites.site_id='.$mysql_table_prefix.'site_category.site_id order by title');
      
        $count = '0'; 
        if ($categories['cat_sites'] != '') {     
            foreach ($categories['cat_sites'] as $value) {
                $mytitle = $categories['cat_sites'][$count][1];     // try to fetch title as defined in admin settings for each site
                
                if ($mytitle == '') {   //  if no personal title is available, try to take title and description from HTML header
                $thisurl =  ($categories['cat_sites'][$count][0]);
            		$result = mysql_query("select * from ".$mysql_table_prefix."links where url = '$thisurl'");
             		echo mysql_error();
            		$num_rows = mysql_num_rows($result);       
                       
            		if ($num_rows > 0) {    //      hopefully the webmaster included some title and description into the site header
            			$thisrow = mysql_fetch_array($result);
                        
            			$thistitle = $thisrow[3];
                        if ($thistitle == '' ) {   //   if no HTML title available, alternative output 
                            $thistitle = "No title available for this site.";
                        }
                        
                     	$thisdescr = $thisrow[4];
                        if ($thisdescr == '' ) {   //   if no HTML description available, alternative output 
                            $thisdescr = "No description available for this site.";
                        }
                        
                        //      now include HTML title and description into array, so we may output them
                        $categories['cat_sites'][$count][1] = $thistitle;
                        $categories['cat_sites'][$count]['title'] = $thistitle;
                        $categories['cat_sites'][$count][2] = $thisdescr;
                        $categories['cat_sites'][$count]['short_desc'] = $thisdescr;
                    }
                }
                $count++; 
            }    
        }
    	return $categories;
    }


?>