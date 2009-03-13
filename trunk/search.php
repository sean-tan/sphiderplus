<?php
/********************************************
* Sphider-plus
* Version 1.6a  created 2008-09-08

* Based on original Sphider version 1.3.4
* released: 2008-04-29
* by Ando Saabas     http://www.sphider.eu
*
* This program is licensed under the GNU GPL by:
* Rolf Kellner  [Tec]   sphider(a t)ibk-kellner.de
* Original Sphider GNU GPL licence by:
* Ando Saabas   ando(a t)cs.ioc.ee
********************************************/

    //error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING); 
    error_reporting(E_ALL);    
    define("_SECURE",1);    // define secure constant
    
    $include_dir    = "./include"; 
    $template_dir   = "./templates"; 
    $settings_dir   = "./settings"; 
    $language_dir   = "./languages";

    require_once("$settings_dir/database.php");    	 
    include "$settings_dir/conf.php"; 
    include ("$include_dir/commonfuncs.php");
    
    $start_links = '';
    
    if ($utf8 == 1) {
        $home_charset = 'utf-8';        // set HTTP header character encoding to UTF-8        
    }    
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

    if (isset($_GET['query']))
    	$query = cleaninput($_GET['query']);
    if (isset($_GET['search']))
    	$search = cleaninput($_GET['search']);
    if (isset($_GET['domain'])) 
    	$domain = cleaninput($_GET['domain']);
    if (isset($_GET['type'])) 
    	$type = cleaninput($_GET['type']);
    if (isset($_GET['catid'])) 
    	$catid = cleaninput($_GET['catid']);
    if (isset($_GET['category'])) 
    	$category = cleaninput($_GET['category']);        
    if (isset($_GET['mark'])) 
    	$mark = cleaninput($_GET['mark']);        
    if (isset($_GET['results'])) 
    	$results = cleaninput($_GET['results']);
    if (isset($_GET['start'])) 
    	$start = cleaninput($_GET['start']);
    if (isset($_GET['start_links'])) 
    	$start_links = cleaninput($_GET['start_links']);
    if (isset($_GET['adv'])) 
    	$adv = cleaninput($_GET['adv']);
        
    require_once("$include_dir/searchfuncs.php");
    require_once("$include_dir/categoryfuncs.php");

    include "$language_dir/$language-language.php";

    if ($mark == $sph_messages['markbold']) $mark = 'markbold';    
    if ($mark == $sph_messages['markyellow']) $mark = 'markyellow';    
    if ($mark == $sph_messages['markgreen']) $mark = 'markgreen';    
    if ($mark == $sph_messages['markblue']) $mark = 'markblue';    
    
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN"> 
<HTML> 
<HEAD> 
    <meta http-equiv="content-type" content="text/html; charset=<?php print $home_charset?>"> 
    <meta name="public" content="all">
    <meta name="robots" content="index, follow">
    <?php 
    if ($catid && is_numeric($catid)){ 
        $cattree = array(" ",$sph_messages['Categories']); 
        $cat_info = get_category_info($catid); 
        foreach ($cat_info['cat_tree'] as $_val){ 
            $thiscat = $_val['category']; 
            array_push($cattree," > ",$thiscat); 
        } 
        $cattree = implode($cattree); 
    } 
    ?> 
    <title><?php if ($start < '2') $start = '1'; print $mytitle; if ($catid && is_numeric($catid)) print "$cattree" ; if ($query !='') print " Your search term: '$query'. Results from page: $start";?></title> 
    <link rel="stylesheet" href="<?php print "$template_dir/$template/thisstyle.css";?>" type="text/css" /> 
    <!-- suggest script --> 
    <script type="text/javascript" src="include/js_suggest/SuggestFramework.js"></script> 
    <script type="text/javascript">window.onload = initializeSuggestFramework;</script> 
    <!-- /suggest script --> 
    <script type='text/javascript'>     
        function JumpBottom () {
            window.scrollTo(0,100000);
        } 
    </script>    
</HEAD> 
<BODY>
    <noscript>
        <div id='main'>
            <h1 class='cntr warn'>
                <br />
                <?php print $sph_messages['Java1']?>
                <br /><br />
                <?php print $sph_messages['Java2']?>
                <br /><br />
            </h1>
        </div>
    </noscript>
 
    <h1><?php print $mytitle; ?></h1>
    <center>
    <br />
    <?php
 
    if ($type != "or" && $type != "and" && $type != "phrase" && $type != "tol") { 
    	$type = "and";
    }

    if (preg_match("/[^a-z0-9-.]+/", $domain)) {
    	$domain="";
    }

    if ($results != "") {
    	$results_per_page = $results;
    }

    if (!is_numeric($catid)) {
    	$catid = "";
    }

    if (!is_numeric($category)) {
    	$category = "";
    } 

    if ($catid && is_numeric($catid)) {

    	$tpl_['category'] = sql_fetch_all('SELECT category FROM '.$mysql_table_prefix.'categories WHERE category_id='.(int)$_REQUEST['catid']);
    }
    	
    $count_level0 = sql_fetch_all('SELECT count(*) FROM '.$mysql_table_prefix.'categories WHERE parent_num=0');
    $has_categories = 0;

    if ($count_level0) {
    	$has_categories = $count_level0[0][0];
    }
    
    $type_rem   = $type; 
    $result_rem = $results_per_page;
    $mark_rem   = $mark;
    $catid_rem  = $catid;
    $cat_rem    = $category;
    
    //   This will present the Search-form. First the query-field and submit-button
    ?>
    <form action="search.php" method="get">
        <table class="searchBox">
            <tr>
                <td>
                <input type="text" name="query" id="query" size="40" value="<?php   print quote_replace($query);?>" action="include/js_suggest/suggest.php" columns="2" autocomplete="off" delay="500">	
                </td>
                <td>
                <input type="hidden" name="search" value="1">
                <input type="submit" value="<?php print $sph_messages['Search']?>">
                </td>
            </tr>
        </table>
        	   
        <?php  
        if ($adv==1 || $advanced_search==1) {    //  if Advanced-search should be shown enter here 
            ?>
            <table class="searchBox">
            	<tr>
            		<td>
                    <input type="radio" name="type" value="and" <?php print $type=='and'?'checked':''?>><?php print $sph_messages['andSearch']?>
                    </td>
            		<td><input type="radio" name="type" value="or" <?php print $_REQUEST['type']=='or'?'checked':''?>><?php print $sph_messages['orSearch']?>
                    </td>
                </tr>
            	<tr>
            		<td>
                    <input type="radio" name="type" value="phrase" <?php print $_REQUEST['type']=='phrase'?'checked':''?>><?php print $sph_messages['phraseSearch']?>
                    </td>
                    <td>
                    <input type="radio" name="type" value="tol" <?php print $_REQUEST['type']=='tol'?'checked':''?>><?php print $sph_messages['tolSearch']?>
                    </td>                    
            	</tr>
            </table>
            <?php
            if ($show_categories<>0){   //  Show part of the Search-form :  Cat-search
                ?>     

                <table class="searchBox">
                    <tr>
                        <td>
                        <?php print $sph_messages['Search']?>: <input type="radio" name="category" value="<?php print $catid?>"><?php print $sph_messages['Only in category']?> "<?php print $tpl_['category'][0]['category']?>" <input type="radio" name="category" value="-1" checked><?php print $sph_messages['All sites']?>
                        <input type="hidden" name="catid" value="<?php print $catid?>">             
                        </td>
                    </tr>
            	    <?php if ($has_categories && $search==1 && $show_categories){?>
                    <tr>
                        <td>
                        <a href="search.php"><?php print $sph_messages['Categories']?></a>
                        </td>
                    </tr>
                    <?php  }?>
                </table>
                <?php 
            } 
        //      Show method of highlighting
        ?>
        <table class="searchBox">
            <tr>
        		<td><?php print $sph_messages['mark']?>
        			<select name='mark'>
        		      <option <?php  if ($mark=='markbold') echo "selected";?>><?php print $sph_messages['markbold']?></option>
        			  <option <?php  if ($mark=='markyellow') echo "selected";?>><?php print $sph_messages['markyellow']?></option>
        		      <option <?php  if ($mark=='markgreen') echo "selected";?>><?php print $sph_messages['markgreen']?></option>
        		      <option <?php  if ($mark=='markblue') echo "selected";?>><?php print $sph_messages['markblue']?></option>
        			</select>  
        	  	</td>
            </tr>
        </table>

        <?php
        }
        //      Show results per page
        ?>        
        <table class="searchBox">
            <tr>
        		<td><?php print $sph_messages['show']?>
        			<select name='results'>
        		      <option <?php  if ($results_per_page==5) echo "selected";?>>5</option>
        		      <option <?php  if ($results_per_page==10) echo "selected";?>>10</option>
        			  <option <?php  if ($results_per_page==20) echo "selected";?>>20</option>
        		      <option <?php  if ($results_per_page==30) echo "selected";?>>30</option>
        		      <option <?php  if ($results_per_page==50) echo "selected";?>>50</option>
        			</select>
        				
        	  		<?php print $sph_messages['resultsPerPage']?>   
        	  	</td>
            </tr>
        </table><br />
    </form>
    </center>
    <?php 
    //      End of Search-form

    switch ($search) {
    	case 1:
        if (!isset($results)) {
            $results = "";
        }
          
        //      If you want to search for all pages of a site by: site:abc.de
        $pos = strstr(strtolower($query),"site:");        
        if (strlen($pos) > 5) include ("$include_dir/search_links.php"); 

        //      For all other  search modes     
        $strictpos = strpos($query, '!');       
        $wildcount = substr_count($query, '*'); 
        
        if ($wildcount || $strictpos === 0) {
            $type = 'and';      //      if wildcard, or strict search mode, switch to AND search
        }
        
        if ($wildcount || $strictpos === 0 || $type =='tol') {  //  if wildcard, strict or tolerant search mode, we have to search a lot but only for the first word
            $first = strpos($query, ' ');
            if ($first) {
                $query = substr($query, '0', $first);
            }          
        }        
       
        $search_results = get_search_results($query, $start, $category, $type, $results, $domain);          
		extract($search_results);   // get the results      
	
		if ($search_results['ignore_words'] && $type !='phrase'){
			echo "<div id='summary' class='cntr'>
            ";
			while ($thisword=each($ignore_words)) {
				$ignored .= " ".$thisword[1];
			}
			$msg = str_replace ('%ignored_words', $ignored, $sph_messages["ignoredWords"]);
			echo $msg;
			echo "</div>
            ";
		}
        
        //      Now show the result
		echo "<div class='mainlist'>
		";
		if ($search_results['total_results']==0){   //      if query did not match any keyword          
			$msg = str_replace ('%query', $ent_query, $sph_messages["noMatch"]);
			echo "<div class='warnadmin cntr'>$msg
                </div>
            ";
		}
       
        if ($search_results['did_you_mean']){   //      if Sphider-plus found a suggestion
            echo "<div id='didumean'>
                ".$sph_messages['DidYouMean'].": 
                <a href=\"search.php?query=".quote_replace(addmarks($search_results['did_you_mean']))."&search=1&type=$type_rem&results=$result_rem&mark=$mark_rem&category=$cat_rem&catid=$catid_rem\">
                ".$search_results['did_you_mean_b']."</a>?
                </div>
            "; 	          
        }

		if ($total_results != 0 && $from <= $to){   // this is the standard results header      
			echo "
                <p class='cntr'>
                <a class='navdown'  href='javascript:JumpBottom()' title='Jump to bottom of this page'>Down </a>
            ";
			$result = $sph_messages['Results'];
			$result = str_replace ('%from', $from, $result);
			$result = str_replace ('%to', $to, $result);
			$result = str_replace ('%all', $total_results, $result);
            
            if ($advanced_search == 1 && $show_categories == 1 && $category != '-1') {    // additional headline for category search results
                $catname = $tpl_['category'][0]['category'];
                if ($catname != '') {
                    $result = "$result<br />";
                    $catsearch = $sph_messages['catsearch']; 
                    $result = "$result $catsearch $catname";
                } else {
                    $result = $sph_messages['catselect'];
                }            
            }
            
			$matchword = $sph_messages["matches"];
			if ($total_results== 1) {
				$matchword= $sph_messages["match"];
			} else {
				$matchword= $sph_messages["matches"];
			}
			$result = str_replace ('%matchword', $matchword, $result);
			$result = str_replace ('%secs', $time, $result);
			echo $result;
            
            if ($show_sort == '1' && $wildcount != '1') {
                $res_order = $sph_messages['ResultOrder'];    // show order of result listing            
                if ($sort_results == '1') {
                    $this_list = $sph_messages['order1'];
                }
                if ($sort_results == '2') {
                    $this_list = $sph_messages['order2'];
                }
                if ($sort_results == '3') {
                    $this_list = $sph_messages['order3'];
                }                
                if ($sort_results == '4') {
                    $this_list = $sph_messages['order4'];
                }
                if ($sort_results == '5') {
                    $this_list = $sph_messages['order5'];
                }
                
                echo "<br />$res_order $this_list
                ";
            }
            echo "</p>
            ";
		}
		echo "</div>
        ";

		if (isset($qry_results)) {  //  start of result listing
			echo "<div id='results'>
            ";
			foreach ($qry_results as $_key => $_row){
				$last_domain = $domain_name;
				extract($_row);
				if ($show_query_scores == 0 || $sort_results > '2' || ($wildcount == '1' && $query_hits =='0')) {
					$weight = '';
				} else {
                    if ($query_hits == '1') {
                        $high_hits = "span class='mak_1 blue'";
                        $text = $sph_messages['queryhits'];
                        $weight = "<$high_hits>[$text $weight]</$high_hits>";
                    } else {
                        $weight = "<b>[$weight %]</b>";
                    }
				}
				if ($num & 1) {
					echo "<div class='odrow'>
                    ";
				} else {
					echo "<div class='evrow'>
                    ";
				}

                if(ceil($num/10) == $num/10) {      // this routine places a "to page top" link on every 10th record
					echo "<a class='navup' href='#top' title='Jump to Page Top'>Top</a>
                    ";
				}

				$title1 = strip_tags($title);
                $url    = "$include_dir/click_counter.php?url=$url&query=$query";   //  redirect users click in order to update Most Popular Links
                $urlx   = $url2;
			
				echo '<div class="title">
            		<em class="sml">'.$num.'</em>
            		<a href="'.$url.'" title="'.$sph_messages['New_window'].'"  target="_blank">'.($title?$title:$sph_messages['Untitled']).'</a></div>
            		<div class="description">'.$fulltxt.'</div>
                    <div class="url">'.$weight.' | '.$urlx.' - '. $page_size.'</div>
                    </div>
                ';
            }       //  end of result listing
            echo '</div>';
            
            if (isset($other_pages)) {  //  links to other result pages
                if ($adv==1) {
                    $adv_qry = "&adv=1";
                }
                if ($type != "") {
                    $type_qry = "&type=$type";
                }
                ?>
                <div id="other_pages" class="tblhead">
                <?php print $sph_messages["Result page"]?>:
                <?php if ($start >1){     // if we do have more than 1 result page
                    ?>
                     <a href="<?php print 'search.php?query='.quote_replace(addmarks($query)).'&start='.$prev.'&search=1&category='.$category.'&catid='.$catid.'&mark='.$mark.'&results='.'&results='.$results_per_page.$type_qry.$adv_qry.'&domain='.$domain?>"><?php print $sph_messages['Previous']?></a>
                    <?php  
                }
                foreach ($other_pages as $page_num) {
                    if ($page_num !=$start){
                        ?>
                        <a href="<?php print 'search.php?query='.quote_replace(addmarks($query)).'&start='.$page_num.'&search=1&category='.$category.'&catid='.$catid.'&mark='.$mark.'&results='.'&results='.$results_per_page.$type_qry.$adv_qry.'&domain='.$domain?>"><?php print $page_num?></a>
                        <?php 
                    } else {
                        ?>	
                        <b><?php print $page_num?></b>
                        <?php  
                    }  
                }
                if ($next <= $pages){
                    ?>	
                    <a href="<?php print 'search.php?query='.quote_replace(addmarks($query)).'&start='.$next.'&search=1&category='.$category.'&catid='.$catid.'&mark='.$mark.'&results='.$results_per_page.$type_qry.$adv_qry.'&domain='.$domain?>"><?php print $sph_messages['Next']?></a>
                    <?php  
                }
                ?>	
                </div>
                <?php 
        }
        }
    	break;
        
    	default:
		if ($show_categories) {
			if ($_REQUEST['catid']  && is_numeric($catid)) {
				$cat_info = get_category_info($catid);                
			} else {
				$cat_info = get_categories_view();               
			}
		}

		if ($catid && is_numeric($catid)){ //-- category tree
			echo "<div id='results'>
                <p class='mainlist'>".$sph_messages['Back'].":
                <a href='search.php?setcss1=$thestyle' title='".$sph_messages['tipBackCat']."'>".$sph_messages['Categories']."</a></p>
                <div class='odrow'><p class='title'>
            ";
			$acats = "";
			$i = 0;
			foreach ($cat_info['cat_tree'] as $_val){
				$i++;
				$acats .= "<a href='?catid=".$_val['category_id']."&amp;setcss1=$thestyle' title='".$sph_messages['tipSelCat']."'>".$_val['category']."</a> &raquo; ";
				if ($i > 5) {
					$i = 0;
					$acats = substr($acats,0,strlen($acats)-9)."<br /> &raquo; ";
				}
			}
			$acats = substr($acats,0,strlen($acats)-9);
			echo "$acats</p></div>
            ";

			if ($cat_info['subcats']){  // list of sub-categories
				echo "<p class='mainlist'>".$sph_messages['SubCats']."</p>
                    <div class='odrow'><p class='title'>
                ";
				$bcats = "";
				foreach ($cat_info['subcats'] as $_key => $_val){
					$bcats .= "<a href='search.php?catid=".$_val['category_id']."&amp;setcss1=$thestyle' title='".$sph_messages['tipSelBCat']."'>".$_val['category']."</a> (".$_val['count'][0][0].") &raquo; ";
				}
			$bcats = substr($bcats,0,strlen($bcats)-9);
			echo "$bcats</p></div>
                </div>
            ";
			} else {
                echo "</div>
                ";
			}
            
            //  get name of current category            
            $result = mysql_query("select category from ".$mysql_table_prefix."categories where category_id = '$catid'");
            echo mysql_error();
            $catname = mysql_result($result, 0);
                 
            if (!$cat_info['cat_sites']) {   // if no site is attached to this cat       
				echo "<br /><p class='mainlist'><a href='search.php' title='".$sph_messages['tipBackCat']."'>
                        ".$sph_messages['noSites']." $catname</a></p>
                    ";

            } else {  // list of web pages in current category
				echo "<p class='mainlist'>".$sph_messages['Web pages'] . $catname."</p>
                ";
       
				foreach ($cat_info['cat_sites'] as $_key => $_val){
					if ($_key & 1) {
						echo "<div class='odrow'>
                        ";
					} else {
                        echo "<div class='evrow'>
                        ";
					}
                    $count = ($_key+1);
                    echo "<p class='title'>
                        <span class='em sml'>".$count.".</span> <a href='".$_val['url']."'>".$_val['title']."</a></p>
                        <p class='description'>".$_val['short_desc']."</p>
                        <p class='url'>".$_val['url']."</p>
                        </div>
                    ";
				}
                echo "</div></div>
                ";
			}
            
		} else {

    		if ($cat_info['main_list']){    // category selection
    			echo "<div id='results'>
                    <div class='headline cntr'><em>".$sph_messages['Categories']."</em></div>
                ";
    			foreach ($cat_info['main_list'] as $_key => $_val){
    				if ($_key & 1) {
    					echo "<div class='odrow'>
                        ";
    				} else {
    					echo "<div class='evrow'>
                        ";
    				}
    				echo "<p class='title'>
                        <a class='em' href='search.php?catid=".$_val['category_id']."&amp;setcss1=$thestyle' title='".$sph_messages['tipSelCat']."'>".$_val['category']."</a><br />
                    ";
    				if (is_array($_val['sub'])) {
    					$ccats = "";
    					foreach ($_val['sub'] as $__key => $__val){
    						$ccats .= "<a href='search.php?catid=".$__val['category_id']."&amp;setcss1=$thestyle' title='".$sph_messages['tipSelBCat']."'>".$__val['category']."</a> &raquo; ";
    					}
    					echo $ccats;
    				}
    				echo "</p></div>
                    ";
    			}
    			echo "</div>
                ";
    		}
        }

    	break;
    }
        
    if ($most_pop == 1 ) {  // if selected in Admin settings, show most popular searches    
        $bgcolor='odrow';
        echo "<br /><center><div id=\"footer\" class='tblhead cntr'>
            ".$sph_messages['mostpop']."
            <table cellpadding=\"3\" cellspacing = \"1\">
                <tr class='tblhead'>
                    <td>".$sph_messages['query']."</td>
                    <td>".$sph_messages['count']."</td>
                    <td> ".$sph_messages['results']." </td>
                    <td>".$sph_messages['lastquery']."</td>
                </tr>
        ";
        $result=mysql_query("select query, count(*) as c, date_format(max(time), '%Y-%m-%d %H:%i:%s'), avg(results)  from ".$mysql_table_prefix."query_log group by query order by c desc");
        echo mysql_error();
        $count = 0;
        while (($row=mysql_fetch_row($result)) && $count < $pop_rows) {                           
            $count++;
            $word = $row[0];            
            $times = $row[1];
            $date = $row[2];
            $avg = intval($row[3]);
            $word = str_replace("\"", "", $word);
            echo "<tr class='$bgcolor cntr'>
                    <td>
                    <a href=\"search.php?query=$word&amp;search=1&type=$type&category=$category&catid=$catid&mark=$mark&results=$results\">".$word."</a></td>
                    <td> ".$times."</td>
                    <td > ".$avg."</td><td align=\"center\"> ".$date."</td>
                </tr>
            ";
            
            
            if ($bgcolor=='odrow') {
                $bgcolor='evrow';
            } else {
                $bgcolor='odrow';
            }
        }			
        echo "</table>
            </div>
            </center>
        ";
    }        
    
    if ($add_url ==1) { //  if selected in Admin settings, allow user to suggest a Url to be indexed
        echo "<p class='tblhead'>
            
            <a href='./addurl.php' title='User suggestion for a new Url to be indexed' target='rel'>".$sph_messages['suggest']."</a>
            </p>
        ";
    }    
// The following should only be removed if you contribute (donate) to the Sphider-plus project.
// Note that this is a requirement under the GPL licensing agreement, which Sphider-plus acknowledges.	    
    footer()
?>
</div>
</body>
</html>

