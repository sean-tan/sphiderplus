<?php
 
    include "auth.php";
    
    if ($_utf8=="") {
    	$_utf8=0;
    }
        
    if ($_case_sensitive=="") {
    	$_case_sensitive=0;
    }
    
    if ($_home_charset=="") {
    	$_home_charset = 'ISO-8859-1';
    }
   
    if ($_follow_sitemap=="") {
    	$_follow_sitemap=0;
    }

    if ($_index_numbers=="") {
    	$_index_numbers=0;
    } 

    if ($_index_xls=="") {
    	$_index_xls=0;
    } 

    if ($_index_ppt=="") {
    	$_index_ppt=0;
    }

    if ($_index_pdf=="") {
    	$_index_pdf=0;
    } 

    if ($_index_doc=="") {
    	$_index_doc=0;
    } 

    if ($_index_rtf=="") {
    	$_index_rtf=0;
    } 

    if ($_mytitle=="") {
    	$_mytitle= "Sphider-plus";
    }

    if ($_smap_dir=="") {
    	$_smap_dir= "sitemaps";
    }

    if ($_max_links=="") {
    	$_max_links= "9999";
    }

    if ($_min_delay=="") {
    	$_min_delay=0;
    } 

    if ($_index_host=="") {
    	$_index_host=0;
    }
    
    if ($_keep_log=="") {
    	$_keep_log=0;
    }
    
    if ($_show_meta_description=="") {
    	$_show_meta_description=0;
    }
    
    if ($_show_warning=="") {
    	$_show_warning=0;
    }

    if ($_show_categories=="") {
    	$_show_categories=0;
    }

    if ($_title_length=="") {
    	$_title_length=80;
    }
    if ($_url_length=="") {
    	$_url_length=80;
    }

    if ($_show_query_scores=="") {
    	$_show_query_scores=0;
    }

    if ($_query_hits=="") {
    	$_query_hits=0;
    }

    if ($_email_log=="") {
    	$_email_log=0;
    }

    if ($_real_log=="") {
    	$_real_log=0;
    }
    
    if ($_real_log=="1") {
    	$_keep_log=1;
        $_log_format ="html";
    }
    
    if ($_refresh=="") {
    	$_refresh=5;
    }
    
    if ($_click_wait=="") {
    	$_click_wait=60;
    }
   if ($_print_results=="") {
    	$_print_results=0;
    }

    if ($_sites_alpha=="") {
    	$_sites_alpha=0;
    }

    if ($_clear=="") {
    	$_clear=0;
    }
    if ($_index_meta_keywords=="") {
    	$_index_meta_keywords=0;
    }

    if ($_index_host=="") {
    	$_index_host=0;
    }

    if ($_advanced_search=="") {
    	$_advanced_search=0;
    }
    
    if ($_sort_results == "") {
    	$_sort_results = 1;
    }

    if ($_domain_mul == "") {
    	$_domain_mul = 4;
    }

    if ($_show_sort == "") {
    	$_show_sort = 0;
    }
    if ($_most_pop == "") {
    	$_most_pop = 0;
    }

    if ($_pop_rows == "") {
    	$_pop_rows = 0;
    }

    if ($_relevance == "") {
    	$_relevance = 0;
    }
    
    if ($_add_url == "") {
    	$_add_url = 0;
    }
    
    if ($_addurl_info == "") {
    	$_addurl_info = 0;
    }
    if ($_captcha == "") {
    	$_captcha = 0;
    }
    
    
    if ($_did_you_mean_enabled == "") {
    	$_did_you_mean_enabled = 0;
    }

    if ($_stem_words == "") {
    	$_stem_words = 0;
    }

    if ($_strip_sessids == "") {
    	$_strip_sessids = 0;
    }

    if ($_link_check == "") {
    	$_link_check = 0;
    }

    if ($_dup_content == "") {
    	$_dup_content = 0;
    }

    if ($_auto_lng == "") {
    	$_auto_lng = 0;
    }

    if ($_suggest_enabled == "") {
    	$_suggest_enabled = 0;
    }

    if ($_suggest_history == "") {
    	$_suggest_history  = 0;
    }

    if ($_suggest_phrases == "") {
    	$_suggest_phrases = 0;
    }

    if ($_suggest_keywords == "") {
    	$_suggest_keywords = 0;
    }

    if ($_suggest_rows == "") {
     $_suggest_rows = 0;
    }

    if (isset($Submit)) {
    	if (!is_writable("../settings/conf.php")) {
    		print "Configuration file is not writable, chmod 666 conf.php under *nix systems";
    	} else {
            if ($real_log=="1") {
                $truncate = mysql_query ("TRUNCATE `".$mysql_table_prefix."real_log`");     //  reset the real_log table
                if (!$truncate) {   //  enter here if the table for real logging was not jet installed
                    echo "
                        <div class='submenu cntr'>
                        <span class='warnadmin'>
                    ";
                    echo mysql_error();
                    echo "<br /><br />
                        Please run the .../admin/install_reallog.php file.
                        <b r />
                        </span>
                        </div>
                        <br /><br />
                    ";
                    die ;
                }
                if ($_refresh < '1') {
                    $_refresh = '1';     //  life belt for invalid admin input
                }
                if ($_refresh > '10') {
                    $_refresh = '10';     //  life belt for invalid admin input
                }               
    			$update = mysql_query("insert into ".$mysql_table_prefix."real_log set url='' , refresh=$_refresh ");
    			echo mysql_error();                 
            }
            
            if ($_utf8 == '0') {
                $_case_sensitive = '0';     //  life belt for invalid admin input
            }

    		$fhandle=fopen("../settings/conf.php","wb");
    		fwrite($fhandle,"<?php \n");
            
    		fwrite($fhandle,"/************************************************\n ");
    		fwrite($fhandle,"Sphider-plus version $_plus_nr configuration file.\n");
    		fwrite($fhandle,"\n > > >  DO NOT EDIT THIS FILE. < < < \n\n");
    		fwrite($fhandle,"Any changes must be done by Admin settings. \n");
    		fwrite($fhandle,"*************************************************/"); 
            
    		fwrite($fhandle,"\n\n\n/*********************** \nGeneral settings \n***********************/");
    		fwrite($fhandle, "\n\n// Sphider-plus version \n");
    		fwrite($fhandle,"$"."plus_nr = '".$_plus_nr. "';");
    		fwrite($fhandle, "\n\n// Original Sphider version \n");
    		fwrite($fhandle,"$"."version_nr = '".$_version_nr. "';");            
     		fwrite($fhandle, "\n\n//Standard charset of your location (e.g. ISO-8859-1)\n");           
    		fwrite($fhandle,"$"."home_charset = '".$_home_charset. "';");                                  
    		fwrite($fhandle, "\n\n//Administrators email address (logs and info mails can be sent there)\n");
    		fwrite($fhandle,"$"."admin_email = '".$_admin_email. "';");
    		fwrite($fhandle, "\n\n//Dispatcher email address (info mails will be transmitted from this account)\n");
    		fwrite($fhandle,"$"."dispatch_email = '".$_dispatch_email. "';");        
    		fwrite($fhandle, "\n\n//Address to localhost document root \n");
    		fwrite($fhandle,"$"."local = '".$_local. "';");        
    		fwrite($fhandle, "\n\n// Sort Sites table in Admin section in alphabetic order\n");
    		fwrite($fhandle,"$"."sites_alpha = ".$_sites_alpha. ";");
    		fwrite($fhandle, "\n\n// Free resources when indexing large amount of URLs \n");
    		fwrite($fhandle,"$"."clear = ".$_clear. ";");
    		fwrite($fhandle, "\n\n// Temporary directory, this should be readable and writable\n");
    		fwrite($fhandle,"$"."tmp_dir = '".$_tmp_dir. "';");

    		fwrite($fhandle,"\n\n\n/*********************** \nLogging settings \n***********************/");
    		fwrite($fhandle, "\n\n//  Enable real-time output of logging data \n");
    		fwrite($fhandle,"$"."real_log = ".$_real_log. ";");
    		fwrite($fhandle, "\n\n//  Interval for real-time Log file update [seconds]\n");
    		fwrite($fhandle,"$"."refresh = ".$_refresh. ";");
    		fwrite($fhandle, "\n\n//  Interval until next click will be accepted to increase popularity of a link [seconds]\n");
    		fwrite($fhandle,"$"."click_wait = ".$_click_wait. ";");
    		fwrite($fhandle, "\n\n// Should log files be kept\n");
    		fwrite($fhandle,"$"."keep_log = ".$_keep_log. ";");
    		fwrite($fhandle, "\n\n//Log directory, this should be readable and writable\n");
    		fwrite($fhandle,"$"."log_dir = '".$_log_dir. "';");
    		fwrite($fhandle, "\n\n// Log format\n");
    		fwrite($fhandle,"$"."log_format = '".$_log_format. "';");
    		fwrite($fhandle, "\n\n// Print spidering results to standard out\n");
    		fwrite($fhandle,"$"."print_results = ".$_print_results. ";");
    		fwrite($fhandle, "\n\n//  Send log file to email \n");
    		fwrite($fhandle,"$"."email_log = ".$_email_log. ";");

    		fwrite($fhandle,"\n\n\n/*********************** \nSpider settings \n***********************/");
    		fwrite($fhandle, "\n\n// Convert Site content into utf-8 charset'\n");
    		fwrite($fhandle,"$"."utf8   = ".$_utf8. ";");                          
    		fwrite($fhandle, "\n\n// Separate between upper- and lower-case queries\n");
    		fwrite($fhandle,"$"."case_sensitive   = ".$_case_sensitive. ";");                          
    		fwrite($fhandle, "\n\n// Sitemap directory, this should be readable and writable \n");
    		fwrite($fhandle,"$"."smap_dir = '".$_smap_dir. "';");        
    		fwrite($fhandle, "\n\n// Max. links to be followed per site \n");
    		fwrite($fhandle,"$"."max_links = '".$_max_links. "';");        
    		fwrite($fhandle, "\n\n// Min words per page required for indexing \n");
    		fwrite($fhandle,"$"."min_words_per_page = ".$_min_words_per_page. ";");
    		fwrite($fhandle, "\n\n// Words shorter than this will not be indexed\n");
    		fwrite($fhandle,"$"."min_word_length = ".$_min_word_length. ";");
    		fwrite($fhandle, "\n\n// Keyword weight depending on the number of times it appears in a page is capped at this value\n");
    		fwrite($fhandle,"$"."word_upper_bound = ".$_word_upper_bound. ";");
    		fwrite($fhandle, "\n\n// If available follow 'sitemap.xml'\n");
    		fwrite($fhandle,"$"."follow_sitemap		= ".$_follow_sitemap. ";");               
    		fwrite($fhandle, "\n\n// Index numbers as well\n");
    		fwrite($fhandle,"$"."index_numbers = ".$_index_numbers. ";");
    		fwrite($fhandle,"\n\n// if this value is set to 1, word in domain name and url path are also indexed,// so that for example the index of www.php.net returns a positive answer to query 'php' even 	// if the word is not included in the page itself.\n");
    		fwrite($fhandle,"$"."index_host = ".$_index_host.";\n");
    		fwrite($fhandle, "\n\n// Whether to index keywords in a meta tag \n");
    		fwrite($fhandle,"$"."index_meta_keywords = ".$_index_meta_keywords. ";");		
    		fwrite($fhandle, "\n\n// Index PDF files\n");
    		fwrite($fhandle,"$"."index_pdf = ".$_index_pdf. ";");
    		fwrite($fhandle, "\n\n// Index DOC files\n");
    		fwrite($fhandle,"$"."index_doc = ".$_index_doc. ";");
    		fwrite($fhandle, "\n\n// Index RTF files\n");
    		fwrite($fhandle,"$"."index_rtf = ".$_index_rtf. ";");
    		fwrite($fhandle, "\n\n// Index XLS files\n");
    		fwrite($fhandle,"$"."index_xls = ".$_index_xls. ";");
    		fwrite($fhandle, "\n\n// Index PPT files\n");
    		fwrite($fhandle,"$"."index_ppt = ".$_index_ppt. ";");
    		fwrite($fhandle, "\n\n//Path to PDF converter\n");
    		fwrite($fhandle,"$"."pdftotext_path = '".$_pdftotext_path	. "';");
    		fwrite($fhandle, "\n\n//Path to DOC converter\n");
    		fwrite($fhandle,"$"."catdoc_path = '".$_catdoc_path. "';");
    		fwrite($fhandle, "\n\n//Path to XLS converter\n");
    		fwrite($fhandle,"$"."xls2csv_path = '".$_xls2csv_path	. "';");
    		fwrite($fhandle, "\n\n//Path to PPT converter\n");
    		fwrite($fhandle,"$"."catppt_path = '".$_catppt_path. "';");
    		fwrite($fhandle, "\n\n// User agent string \n");
    		fwrite($fhandle,"$"."user_agent = '".$_user_agent. "';");
    		fwrite($fhandle, "\n\n// Minimal delay between page downloads \n");
    		fwrite($fhandle,"$"."min_delay = ".$_min_delay. ";");
    		fwrite($fhandle, "\n\n// Use word stemming (e.g. find sites containing runs and running when searching for run) \n");
    		fwrite($fhandle,"$"."stem_words = ".$_stem_words. ";");
    		fwrite($fhandle, "\n\n// Strip session ids (PHPSESSID, JSESSIONID, ASPSESSIONID, sid) \n");
    		fwrite($fhandle,"$"."strip_sessids = ".$_strip_sessids. ";");
            fwrite($fhandle, "\n\n// Enable link-check instead of reindex \n");
    		fwrite($fhandle,"$"."link_check = ".$_link_check. ";");
            fwrite($fhandle, "\n\n// Enable index and re-index for pages with duplicate content \n");
    		fwrite($fhandle,"$"."dup_content = ".$_dup_content. ";");
            
    		fwrite($fhandle,"\n\n\n/*********************** \nSearch settings \n***********************/");
    		fwrite($fhandle, "\n\n//Language of the search page \n");
    		fwrite($fhandle,"$"."language = '".$_language. "';");
    		fwrite($fhandle, "\n\n//Auto detect client language\n");
    		fwrite($fhandle,"$"."auto_lng = '".$_auto_lng. "';");
    		fwrite($fhandle, "\n\n// Template design/Directory in templates dir\n");
    		fwrite($fhandle,"$"."template = '".$_template. "';");        
    		fwrite($fhandle, "\n\n// Title for Result Page\n");
    		fwrite($fhandle,"$"."mytitle = '".$_mytitle. "';");
    		fwrite($fhandle, "\n\n//Type of highlighting for found keywords \n");
    		fwrite($fhandle,"$"."mark = '".$_mark. "';");            
    		fwrite($fhandle, "\n\n// Default for number of results per page\n");
    		fwrite($fhandle,"$"."results_per_page = ".$_results_per_page. ";");
    		fwrite($fhandle, "\n\n// Can speed up searches on large database (should be 0)\n");
    		fwrite($fhandle,"$"."bound_search_result = ".$_bound_search_result. ";");
    		fwrite($fhandle, "\n\n");
    		fwrite($fhandle,"// The length of the description string queried when displaying search results. // If set to 0 (default), makes a query for the whole page text, // otherwise queries this many bytes. Can significantly speed up searching on very slow machines \n");
    		fwrite($fhandle,"$"."length_of_link_desc = ".$_length_of_link_desc. ";");
    		fwrite($fhandle, "\n\n// Number of links shown to next pages\n");
    		fwrite($fhandle,"$"."links_to_next = ".$_links_to_next. ";");
    		fwrite($fhandle, "\n\n// Show meta description in results page if it exists, otherwise show an extract from the page text.\n");
    		fwrite($fhandle,"$"."show_meta_description = ".$_show_meta_description. ";");
    		fwrite($fhandle, "\n\n// Show warning message if search string was found only in title or url.\n");
    		fwrite($fhandle,"$"."show_warning = ".$_show_warning. ";");
    		fwrite($fhandle, "\n\n// Advanced query form, shows and/or buttons\n");
    		fwrite($fhandle,"$"."advanced_search = ".$_advanced_search. ";");
    		fwrite($fhandle, "\n\n// Query scores are not shown if set to 0\n");
    		fwrite($fhandle,"$"."show_query_scores = ".$_show_query_scores. ";	");
    		fwrite($fhandle, "\n\n// Query hits are shown if set to 1\n");
    		fwrite($fhandle,"$"."query_hits= ".$_query_hits. ";	");
    		fwrite($fhandle, "\n\n");
    		fwrite($fhandle, "\n\n // Display category list\n");
    		fwrite($fhandle,"$"."show_categories = ".$_show_categories. ";");
    		fwrite($fhandle, "\n\n// Max length of page title given in results page\n");
    		fwrite($fhandle,"$"."title_length		= ".$_title_length. ";");
    		fwrite($fhandle, "\n\n// Max length of URL given in results page\n");
    		fwrite($fhandle,"$"."url_length		= ".$_url_length. ";");
    		fwrite($fhandle, "\n\n// Length of page description given in results page\n");
    		fwrite($fhandle,"$"."desc_length = ".$_desc_length. ";");
    		fwrite($fhandle, "\n\n// Show order of result listing as headline\n");
    		fwrite($fhandle,"$"."show_sort = ".$_show_sort. ";");
    		fwrite($fhandle, "\n\n// Show 'Most popular searches' at the bottom of result pages\n");
    		fwrite($fhandle,"$"."most_pop = ".$_most_pop. ";");
    		fwrite($fhandle, "\n\n// Number of rows for 'Most popular searches'\n");
    		fwrite($fhandle,"$"."pop_rows = ".$_pop_rows. ";");            
    		fwrite($fhandle, "\n\n// Min. relevance level (%) to be shown at result pages'\n");
    		fwrite($fhandle,"$"."relevance = ".$_relevance. ";");            
    		fwrite($fhandle, "\n\n// Show 'User may suggest a Url' at the bottom of result pages\n");
    		fwrite($fhandle,"$"."add_url = ".$_add_url. ";");
    		fwrite($fhandle, "\n\n// Inform about user suggestion by e-mail\n");
    		fwrite($fhandle,"$"."addurl_info = ".$_addurl_info. ";");             
    		fwrite($fhandle, "\n\n// Use Captcha for Addurl-form\n");
    		fwrite($fhandle,"$"."captcha = ".$_captcha. ";");             
    		fwrite($fhandle, "\n\n// Enable spelling suggestions (Did you mean...)\n");
    		fwrite($fhandle,"$"."did_you_mean_enabled = ".$_did_you_mean_enabled. ";");
    		fwrite($fhandle, "\n\n// Enable Sphider Suggest \n");
    		fwrite($fhandle,"$"."suggest_enabled = ".$_suggest_enabled. ";");		
    		fwrite($fhandle, "\n\n// Search for suggestions in query log \n");
    		fwrite($fhandle,"$"."suggest_history = ".$_suggest_history. ";");		
    		fwrite($fhandle, "\n\n// Search for suggestions in keywords \n");
    		fwrite($fhandle,"$"."suggest_keywords = ".$_suggest_keywords. ";");		
    		fwrite($fhandle, "\n\n// Search for suggestions in phrases \n");
    		fwrite($fhandle,"$"."suggest_phrases = ".$_suggest_phrases. ";");		
    		fwrite($fhandle, "\n\n// Limit number of suggestions \n");
    		fwrite($fhandle,"$"."suggest_rows = ".$_suggest_rows. ";");

    		fwrite($fhandle,"\n\n\n/*********************** \nWeights and result order\n***********************/");
    		fwrite($fhandle, "\n\n// Relative weight of a word in the title of a webpage\n");
    		fwrite($fhandle,"$"."title_weight = ".$_title_weight. ";");
    		fwrite($fhandle, "\n\n// Relative weight of a word in the domain name\n");
    		fwrite($fhandle,"$"."domain_weight = ".$_domain_weight. ";");
    		fwrite($fhandle, "\n\n// Relative weight of a word in the path name\n");
    		fwrite($fhandle,"$"."path_weight = ".$_path_weight. ";");
    		fwrite($fhandle, "\n\n// Relative weight of a word in meta_keywords\n");
    		fwrite($fhandle,"$"."meta_weight = ".$_meta_weight. ";");
    		fwrite($fhandle, "\n\n// Defines multiplier for words in main URLs (domains)\n");
    		fwrite($fhandle,"$"."domain_mul = ".$_domain_mul. ";");            
    		fwrite($fhandle, "\n\n// Defines method of chronological order for result listing\n");
    		fwrite($fhandle,"$"."sort_results = ".$_sort_results. ";");            
    		fwrite($fhandle,"\n\n?>");
    		fclose($fhandle);
 
            
            echo "<div class='submenu cntr'>| Configuration Settings |</div>
                <div class='headline cntr'>Settings for Sphider-plus version $plus_nr based on original Sphider v. $version_nr</div>
                <div class='odrow cntr'>
                <p>\n\n</p>
                <p class='txt blue cntr'>New Settings have been saved!</p>
                <p>\n\n</p>
                <a class='bkbtn' href='admin.php?f=settings' title='Reload Settings'>Complete this process</a>
                <p>\n\n</p>
                </div></div></div>
                </body>
                </html>
            ";
            die ('');          
    	}

    }
    
    include "../settings/conf.php";
    
    echo "<div class='submenu cntr'>| Configuration Settings |</div>
		<div id='settings'>
		<div class='headline cntr'>Settings for Sphider-plus version $plus_nr based on original Sphider v. $version_nr</div>
		<form class='txt' name='form1' method='post' action='admin.php'>
		<fieldset><legend>[ General Settings ]</legend>        
		<label for='home_charset'>Enter your preferred charset:</label>
		<input name='_home_charset' id='home_charset' value='$home_charset' type='text' size='24' title='Enter your local charset (e.g. ISO-8859-1).'
		/>                
		<label for='admin_email'>Administrator e-mail address:</label>
		<input name='_admin_email' id='admin_email' value='$admin_email' type='text' size='24' title='Enter email address for info and log posting'
		/>        
		<label for='dispatch_email'>Dispatcher e-mail address:</label>
		<input name='_dispatch_email' id='dispatch_email' value='$dispatch_email' type='text' size='24' title='Enter email address for log and info mail transmission'
		/>        
		<label for='local'>Address to localhost document root:</label>
		<input name='_local' id='local' value='$local' type='text' size='24' title='Enter the address to your local root folder'
		/>        
		<label for='template'>Template design:</label>
		<select name='_template' size='3'>
    ";
    
    $directories = get_dir_contents($template_dir);
    if (count($directories)>0) {
        for ($i=0; $i<count($directories); $i++) {
            $tdir=$directories[$i];
            echo "<option id='template' value='".$tdir."'";
            if ($tdir==$template) {
                echo " selected='selected'";
            }
            echo ">$tdir</option>
            ";
        }
        echo "</select>
        ";
    }
    echo "<label for='tmp_dir'>Temporary directory (relative to admin directory):</label>
        <input name='_tmp_dir' type='text'  value='$tmp_dir' id='tmp_dir' size='13' title='Enter name of Temporary Folder'
        />
        <label for='sites_alpha'>Admin's Sites Table sorted in alphabetic order</label>
        <input name='_sites_alpha' type='checkbox' id='sites_alpha' value='1' title='Select for Admin's Site Table sorted in alphabetic order'
    ";
    if ($sites_alpha==1) {
        echo " checked='checked'";
    }
    echo "
        />
        <p>(Deselect to sort by indexdate)</p>        
        <label for='clear'>Clean resources during index / re-index</label>
        <input name='_clear' type='checkbox' id='clear' value='1' title='Select, if you index large amount of URLs.'
    ";
    if ($clear==1) {
        echo " checked='checked'";
    }
    echo "
        />
        <p>(Check only if you index large amount of URLs.<br />Selection will reduce performance for index/re-index process)</p>        
        <label for='submit'>Save Sphider-plus Settings:</label>
        <input class='sbmt' type='submit' value='Save settings' id='submit' title='Click once to save these settings'
        />        
        <div class='clear'></div></fieldset>
        <fieldset><legend>[ Index Log Settings ]</legend>
        <label for='keep_log'>Log spidering results?</label>
        <input name='_keep_log' type='checkbox' id='keep_log' value='1' title='Select to enable spider logging'
    ";
    if ($keep_log==1) {
        echo " checked='checked'";
    }
    echo "
        />
        <label for='log_dir'>Log directory (relative to admin directory):</label>
        <input name='_log_dir' type='text'  value='$log_dir' id='log_dir' size='20' title='Enter name of Log Folder'
        />
        <label for='log_format'>Log file format:</label>
        <select name='_log_format' id='log_format' title='Select default log file output option'>
        <option value='text'
    ";
    if ($log_format == "text") {
        echo " selected='selected'";
    }
    echo ">Text</option>
        <option value='html'";
    if ($log_format == "html") {
        echo " selected='selected'";
    }
    echo ">HTML</option></select>
        <label for='real_log'>Enable real-time output of logging data:</label>
        <input name='_real_log' type='checkbox' id='real_log' value='1' title='Select for real-time output of logging data to your browser'
    ";
    if ($real_log==1) {
        echo " checked='checked'";
    }
    echo "
        />
        <label for='refresh'>Update intervall for logging data (max.10 seconds): </label>
        <input name='_refresh' type='text' id='refresh' size='1' maxlength='2' value='$refresh'
        title='Every x seconds the real-time output will be updated.'
        />            
        <label for='print_results'>Print spidering results to standard out?</label>
        <input name='_print_results' type='checkbox' id='print_results' value='1' title='Select for viewing indexing log'
    ";
    if ($print_results==1) {
        echo " checked='checked'";
    }
    echo "
        />        
        <label for='email_log'>Send spidering log to e-mail?</label>
        <input name='_email_log' type='checkbox' id='email_log' value='1' title='Select to auto-send log file by email'
    ";
    if ($email_log==1) {
        echo " checked='checked'";
    }
    echo "
        /> 

        <label for='click_wait'>Timeout in order to prevent promoted clicks until next click <br />for 'Most Popular Links' will be accepted [seconds]: </label>
        <input name='_click_wait' type='text' id='click_wait' size='2' maxlength='2' value='$click_wait'
        title='Every x seconds a new click will be used to increase popularity of a link.'
        />            
        
        <label for='submit2'>Save Sphider-plus Settings:</label>
        <input class='sbmt' type='submit' id='submit2' value='Save settings' title='Click once to save these settings'
        />
        <div class='clear'></div></fieldset>
        <fieldset><legend>[ Spider settings ]</legend><br />
        <legend class='warnadmin'>If you modify any settings in this section after first index, you are obliged to invoke 'Erase & Re-index'.</legend>
        <br /><br />
        <label for='smap_dir'>Sitemap directory (relative to admin directory):</label>
        <input name='_smap_dir' type='text'  value='$smap_dir' id='smap_dir' size='20' title='Enter name of Sitemap Folder'
        />        
        <label for='smap_dir'>Max. links to be followed for each Site:</label>
        <input name='_max_links' type='text'  value='$max_links' id='max_links' size='5' title='Enter max. links to be followed for each url'
        />        
        <label for='min_words_per_page'>Required number of words in a page in order to be indexed:</label>
        <input name='_min_words_per_page' value='$min_words_per_page' type='text' id='min_words_per_page' size='5' maxlength='5'
         title='Enter minimum number of unique words to qualify for indexing'
        />
        <label for='min_word_length'>Minimum word length in order to be indexed:</label>
        <input name='_min_word_length' type='text' value='$min_word_length' id='min_word_length' size='5' maxlength='2'
         title='Enter minimum length of keywords to index'
        />
        <label for='word_upper_bound'>Keyword weight:</label>
        <input name='_word_upper_bound' type='text' value='$word_upper_bound' id='word_upper_bound' size='5' maxlength='3'
         title='Enter capping value of indexing weights'
        />
        <div class='clear'></div>
        <p>(Capped at this value depending on the number of times it appears in a page)</p>  
        <label for='utf8'>Convert all into UTF-8 charset: </label>
        <input name='_utf8' type='checkbox' value='1' id='utf8'
        title='Select if complete text should be translated into UTF-8'
    ";
    if ($utf8==1) {
        echo " checked='checked'";
    }
    echo" />
        <label for='case_sensitive'>Enable distinct results for upper- and lower-case queries: <br />(Valid only for activated UTF-8 support)</label>
        <input name='_case_sensitive' type='checkbox' value='1' id='case_sensitive'
        title='Leave blank for same rusults'
    ";
    if ($case_sensitive==1) {
        echo " checked='checked'";
    }
    echo" />   
        <label for='follow_sitemap'>If available follow sitemap.xml: </label>
        <input name='_follow_sitemap' type='checkbox' value='1' id='follow_sitemap'
        title='Select for indexing and reindexing with sitemap.xml'
    ";
    if ($follow_sitemap==1) {
        echo " checked='checked'";
    }
    echo" />        
        <label for='index_numbers'>Index numbers?</label>
        <input name='_index_numbers' type='checkbox' value='1' id='index_numbers'
        title='Select for indexing of numbers in page text'
    ";
    if ($index_numbers==1) {
        echo " checked='checked'";
    }
    echo"
        />
        <label for='index_host'>Index words in Domain Name and URL path?</label>
        <input name='_index_host' type='checkbox'  value='1' id='index_host'
        title='Select to enable domain name and URL path indexing'
    ";
    if ($index_host==1) {
        echo " checked='checked'";
    }
    echo "
        />
        <label for='index_meta_keywords'>Index keyword Meta Tags?</label>
        <input name='_index_meta_keywords' type='checkbox' value='1' id='index_meta_keywords'
        title='Select to enable indexing of keyword Meta Tags'
    ";
    if ($index_meta_keywords==1) {
        echo " checked='checked'";
    }
    echo "
        />
        <label for='index_pdf'>Index PDF files?</label>
        <input name='_index_pdf' type='checkbox'  value='1' id='index_pdf' title='Select for indexing .pdf files'
     ";
    if ($index_pdf==1) {
        echo " checked='checked'";
    }
    echo "
        />
        <label for='index_doc'>Index DOC files?</label>
        <input name='_index_doc' type='checkbox'  value='1' id='index_doc' title='Select for indexing .doc files. Not available for LINUX/UNIX systems.'
    ";
    if ($index_doc==1) {
        echo " checked='checked'";
    }
    echo"
        />
        <label for='index_rtf'>Index RTF files?</label>
        <input name='_index_rtf' type='checkbox'  value='1' id='index_rtf' title='Select for indexing .rtf files. Not available for LINUX/UNIX systems.'
    ";
    if ($index_rtf==1) {
        echo " checked='checked'";
    }
    echo"
        />
        <label for='index_xls'>Index XLS files?</label>
        <input name='_index_xls' type='checkbox'  value='1' id='index_xls' title='Select for indexing .xls files. Not available for LINUX/UNIX systems.'
    ";
    if ($index_xls==1) {
        echo " checked='checked'";
    }
    echo"
        />
        <label for='index_ppt'>Index PPT files?</label>
        <input name='_index_ppt' type='checkbox'  value='1' id='index_ppt' title='Select for indexing .ppt files. Not available for LINUX/UNIX systems.'
    ";
    if ($index_ppt==1) {
        echo " checked='checked'";
    }
    echo "
        />
        <label for='user_agent'>User agent string:</label>
        <input name='_user_agent' value='$user_agent' type='text' id='user_agent' size='20'
        title='Enter identifier of your spider for remote log files'
        />
        <label for='min_delay'>Minimal delay between page downloads:</label>
        <input name='_min_delay' value='$min_delay' type='text' id='min_delay' size='5'
        title='Enter delay time in seconds between pages downloaded'
        />
        <label for='stem_words'>Use word stemming :</label>
        <input name='_stem_words' type='checkbox'  value='1' id='stem_words' title='Select to enable word-stemming'
    ";
    if ($stem_words==1) {
        echo " checked='checked'";
    }
    echo "
        />

        <p>(e.g. find sites containing 'runs' and 'running' when searching for 'run')</p>
        <label for='strip_sessids'>Strip session ids? (phpsessid, jsessionid, aspsessionid, sid):</label>
        <input name='_strip_sessids' type='checkbox'  value='1' id='strip_sessids' title='Select to enable session ID stripping'
    ";
    if ($strip_sessids==1) {
        echo " checked='checked'";
    }
    echo "
        />
        <label for='link_check'>Perform a link-check instead of re-index: </label>
        <input name='_link_check' type='checkbox'  value='1' id='link_check' title='Select for link-check'
    ";
    if ($link_check==1) {
        echo " checked='checked'";
    }    
    echo "
        />
        <p>(leave blank for standard re-index)</p>
    "; 



    echo "

        <label for='dup_content'>Enable index and re-index for pages with duplicate content: </label>
        <input name='_dup_content' type='checkbox'  value='1' id='dup_content' title='Select to index pages with content already indexed with other pages'
    ";
    if ($dup_content==1) {
        echo " checked='checked'";
    }    
    echo "
        />
        <p>(leave blank if pages with duplicate content should not be indexed)</p>
    ";
    echo "
        <label for='submit'>Save Sphider-plus Settings:</label>
        <input class='sbmt' type='submit' value='Save settings' id='submit' title='Click once to save these settings'
        />
        </fieldset>
        <fieldset><legend>[ Search Settings ]</legend>
        <label for='results_per_page'>Default search results per page :</label>        
        <input type= 'radio' name='_results_per_page' value='10' title='Select default results per search page'
    ";
    if ($results_per_page==10) {
        echo " checked='checked'";
    }
    echo "
        />10<br />
        <input class='ltfloat' type='radio' name='_results_per_page' id='results_per_page' value='20'
    ";
    if($results_per_page==20) {
        echo " checked='checked'";
    }
    echo "
        />20 <br />
        <input class='ltfloat' type='radio' name='_results_per_page' value='30'
    ";
    if($results_per_page==30) {
        echo " checked='checked'";
    }
    echo "
        />30 <br />
        <input class='ltfloat' type='radio' name='_results_per_page' value='50'
    ";
    if ($results_per_page==50) {
        echo " checked='checked'";
    }
    echo "             
        />50<br /><br />
        <label for='language'>Search Page results language: </label>
        <select name='_language' id='language' title='Select your preferred Search Page language'>
        <option value='ar'
    ";
    if ($language == 'ar'){
        echo " checked='checked'";
    }
    echo ">Arabic</option>
        <option value='bg'";
    if ($language == 'bg') {
        echo " selected='selected'";
    }
    echo ">Bulgarian</option>
        <option value='hr'";
    if ($language == 'hr') {
        echo " selected='selected'";
    }
    echo ">Croatian</option>
        <option value='cns'";
    if ($language == 'cns') {
    echo " selected='selected'";
    }
    echo ">Simple Chinese</option>
        <option value='cnt'";
    if ($language == 'cnt') {
        echo " selected='selected'";
    }
    echo ">Traditional Chinese</option>
        <option value='cz'";
    if ($language == 'cz') {
        echo " selected='selected'";
    }
    echo">Czech</option>    
        <option value='dk'";
    if ($language == 'dk') {
        echo " selected='selected'";
    }
    echo ">Danish</option>    
        <option value='nl'";
    if ($language == 'nl') {
        echo " selected='selected'";
    }
    echo ">Dutch</option>
        <option value='en'";
    if ($language == 'en') {
        echo " selected='selected'";
    }
    echo ">English</option>
        <option value='ee'";
    if ($language == 'ee') {
        echo " selected='selected'";
    }
    echo ">Estonian</option>
        <option value='fi'";
    if ($language == 'fi') {
        echo " selected='selected'";
    }
    echo ">Finnish</option>
        <option value='fr'";
    if ($language == 'fr') {
        echo " selected='selected'";
    }
    echo ">French</option>
        <option value='de'";
    if ($language == 'de') {
        echo " selected='selected'";
    }
    echo ">German</option>
        <option value='hu'";
    if ($language == 'hu') {
        echo " selected='selected'";
    }
    echo ">Hungarian</option>
        <option value='it'";
    if ($language == 'it') {
        echo " selected='selected'";
    }
    echo ">Italian</option>
        <option value='lv'";
    if ($language == 'lv') {
        echo " selected='selected'";
    }
    echo ">Latvian</option>
        <option value='pl'";
    if ($language == 'pl') {
        echo " selected='selected'";
    }
    echo ">Polish</option>
        <option value='pt'";
    if ($language == 'pt') {
        echo " selected='selected'";
    }
    echo ">Portuguese</option>
        <option value='ro'";
    if ($language == 'ro') {
        echo " selected='selected'";
    }
    echo ">Romanian</option>
        <option value='ru'";
    if ($language == 'ru') {
        echo " selected='selected'";
    }
    echo ">Russian</option>
        <option value='sr'";
    if ($language == 'sr') {
        echo " selected='selected'";
    }
    echo ">Serbian</option>
        <option value='sk'";
    if ($language == 'sk') {
        echo " selected='selected'";
    }
    echo ">Slovak</option>
        <option value='si'";
    if ($language == 'si') {
        echo " selected='selected'";
    }
    echo ">Slovenian</option>
        <option value='es'";
    if ($language == 'es') {
        echo " selected='selected'";
    }
    echo ">Spanish</option>
        <option value='se'";
    if ($language == 'se') {
        echo " selected='selected'";
    }
    echo ">Swedish</option>
        <option value='tr'";
    if ($language == 'tr') {
        echo " selected='selected'";
    }
    echo ">Turkish</option></select>
    
        <label for='auto_lng'>Automatically detect user dialog language: </label>
        <input name='_auto_lng' type='checkbox' value='1' id='auto_lng'
        title='Select to enable the automatic detection of user dialog language'
    ";
    
    if ($auto_lng==1) {
        echo " checked='checked'";
    }
    echo "
        />    
        <label for='title'>Title for Result Page:</label>
        <input name='_mytitle' type='text' id='mytitle' value='$mytitle' size='19' maxlength='50'
        title='Enter your personal Title for Result Page'
        />
        <label for='mark'>Select method of highlighting for found keywords in result listing: </label>
        <select name='_mark' id='mark' title='Select highlighting for found keywords in result listing'>        
        <option value='markbold'";
    if ($mark == 'markbold'){
        echo " checked='checked'";
    }
    echo ">bold text</option>    
        <option value='markyellow'";
    if ($mark == 'markyellow') {
        echo " selected='selected'";
    }
    echo ">marked yellow</option>
        <option value='markgreen'";
    if ($mark == 'markgreen') {
        echo " selected='selected'";
    }
    echo ">marked green</option>    
        <option value='markblue'";    
    if ($mark == 'markblue') {
        echo " selected='selected'";
    }
    echo ">marked blue</option></select> 
        <label for='bound_search_results'>Bound number of search results:</label>
        <input name='_bound_search_result' type='text' value='$bound_search_result' id='bound_search_results' size='5'
        title='Change to limit total search results found - 0 = unlimited'
        />
        <div class='clear'></div>
        <p>(Can speed up searches on large database - Should be Zero)</p>
        <label for='length_of_link_desc'>Length of description string queried when displaying search results:</label>
        <input name='_length_of_link_desc' type='text' value='$length_of_link_desc' id='length_of_link_desc' size='5' maxlength='4'
        title='Enter value for maximum text length in search results page'
        />
        <div class='clear'></div>
        <p>
        (Can significantly speed up searching on very slow machines)<br />
        (If set to a lower value [e.g. 250 or 1000; 0 is unlimited],<br /> otherwise doesn't have an effect)</p>
        <label for='links_to_next'>Number of links shown to \"Next\" pages:</label>
        <input name='_links_to_next' type='text' value='$links_to_next' id='links_to_next' size='5' maxlength='2'
        title='Enter default number of \"Next\" page links to display'
        />
        <label for='show_meta_description'>Show Description Meta Tags, if they exist, on results page?</label>
        <input name='_show_meta_description' type='checkbox' value='1' id='show_meta_description'
        title='Select to enable display of description meta tag content'
        
    ";
    
    if ($show_meta_description==1) {
        echo " checked='checked'";
    }
    echo "
        />
        <div class='clear'></div>
        <p>(Otherwise show an extract from the page text)</p> 
        
        <label for='show_warning'>Show warning message if Search string was not found<br /> in description, but only in Site title or URL ?</label>
        <input name='_show_warning' type='checkbox' value='1' id='show_warning'
        title='Select to enable the warning message'
    ";
    
    if ($show_warning==1) {
        echo " checked='checked'";
    }
/*    
    echo "
        /> 

        <label for='case_sensitive'>If utf-8 is supported, separate between upper- and lower-case queries ?</label>
        <input name='_case_sensitive' type='checkbox' value='1' id='case_sensitive'
        title='Select to separate'
    ";
    
    if ($case_sensitive==1) {
        echo " checked='checked'";
    }
*/    
    echo "
        />        
        <label for='advanced_search'>Advanced search? (Shows 'AND/OR/PHRASE/TOLERANT'):</label>
        <input name='_advanced_search' type='checkbox'  value='1' id='advanced_search'
        title='Select to enable \"Advanced Search\" in Search Box'
    ";
    
    if ($advanced_search==1) {
        echo " checked='checked'";
    }
    echo "
        />        

        <label for='show_categories'>Show categories?</label>
        <input name='_show_categories' type='checkbox' value='1' id='show_categories'
        title='Select to display Categories on results pages'
    ";
    
    if ($show_categories==1) {
        echo " checked='checked'";
    }
    echo "
        />
        
        <label for='show_query_scores'>Show result scores (weighting %) calculated by Sphider-plus ?</label>
        <input name='_show_query_scores' type='checkbox' value='1' id='show_query_scores'
        title='Select to enable display of Result Scores'
    ";    
    if ($show_query_scores==1) {
        echo " checked='checked'";
    }    
    echo "
        />
        
        <label for='query_hits'>Instead of weighting %, show count of query hits in full text?</label>
        <input name='_query_hits' type='checkbox' value='1' id='query_hits'
        title='Select to enable display of hits in fulltext'
    ";
    
    if ($query_hits==1) {
        echo " checked='checked'";
    }   
    echo "
        />        
        <label for='title_length'>Maximum length of page title displayed in search results:</label>
        <input name='_title_length' type='text' id='title_length' size='5' maxlength='4' value='$title_length'
        title='Enter value to limit maximum number of characters for page title in result listing'
        /> 
        <p>(Title will be broken at the end of the word exceeding the defined length)</p>         
        <label for='desc_length'>Maximum length of page summary displayed in search results:</label>
        <input name='_desc_length' type='text' id='desc_length' size='5' maxlength='4' value='$desc_length'
        title='Enter value to limit maximum number of characters for page summaries in result listing'
        />
        <label for='url_length'>Maximum length of URL displayed in search results:</label>
        <input name='_url_length' type='text' id='url_length' size='5' maxlength='4' value='$url_length'
        title='Enter value to limit maximum number of characters of URL in result listing'
        />        
        <label for='did_you_mean_enabled'>Enable spelling suggestions? (Did you mean?)</label>
        <input name='_did_you_mean_enabled' type='checkbox' value='1' id='did_you_mean_enabled'
        title='Select to enable \"Did You Mean?\" suggestions on results page'
    ";
    
    if ($did_you_mean_enabled==1) {
        echo " checked='checked'";
    }
    echo "
        />

        <label for='show_sort'>Show mode of chronological order for result listing as additional headline?</label>
        <input name='_show_sort' type='checkbox' value='1' id='show_sort'
        title='Select to display the chronological order for results pages'
    ";
    
    if ($show_sort==1) {
        echo " checked='checked'";
    }
    echo "
        />        
        
        <label for='most_pop'>Show 'Most popular searches' table at the bottom of result pages: </label>
        <input name='_most_pop' type='checkbox' value='1' id='most_pop'
        title='Select to enable Most popular searches table displayed at the bottom of result pages'
    ";
    
    if ($most_pop==1) {
        echo " checked='checked'";
    }        
    
    echo "
        />
        <label for='pop_rows'>Define number of rows for 'Most popular searches': </label>
        <input name='_pop_rows' type='text' id='pop_rows' size='2' maxlength='2' value='$pop_rows'
        title='If selected above, define here how many rows should be presented.'
        />

        <label for='relevance'>Define min. relevance level (weight in %) <br /> for results to be presented at results pages: </label>
        <input name='_relevance' type='text' id='relevance' size='2' maxlength='2' value='$relevance'
        title='Enter 0 to get all results.'
        />
    ";        
    
    echo "
        <label for='add_url'>Allow user to suggest a Url to be indexed</label>
        <input name='_add_url' type='checkbox' value='1' id='add_url'
        title='Select to enable User may suggest a new Url,displayed at the bottom of result pages'
    ";
    if ($add_url==1) {
        echo " checked='checked'
    ";
    }        

    echo "
        />
        <label for='captcha'>Captcha protection for URL Submission Form</label>
        <input name='_captcha' type='checkbox' value='1' id='captcha'
        title='Select for user security input when suggesting a new Url'
    ";
    if ($captcha==1) {
        echo " checked='checked'
    ";
    }        
 
    echo "
        />
        <label for='addurl_info'>Inform about user suggestion by e-mail</label>
        <input name='_addurl_info' type='checkbox' value='1' id='addurl_info'
        title='Select to enable e-mail notification for user suggestion of new Url's'
    ";
    if ($addurl_info==1) {
        echo " checked='checked'
        ";
    }        
 
    echo "
        />    
        <label for='submit'>Save Sphider-plus Settings:</label>
        <input class='sbmt' type='submit' value='Save settings' id='submit' title='Click once to save these settings'
        />        
        </fieldset>
        <fieldset><legend>[ Suggest Options ]</legend>
        <label for='suggest_enabled'>Enable Sphider Suggest?</label>
        <input name='_suggest_enabled' type='checkbox' value='1' id='suggest_enabled'
        title='Select to enable Sphider suggest'
    ";
    if ($suggest_enabled==1) {
        echo " checked='checked'
        ";
    }
    echo "
        />
        <label for='suggest_history'>Search for suggestions in query log?</label>
        <input name='_suggest_history' type='checkbox' value='1' id='suggest_history'
        title='Select to enable suggestions from Query Log'
    ";
    if ($suggest_history==1) {
        echo " checked='checked'
        ";
    }
    echo"
        />
        <label for='suggest_keywords'>Search for suggestions in keywords?</label>
        <input name='_suggest_keywords' type='checkbox' value='1' id='suggest_keywords'
        title='Select to enable suggestions from Keywords'
    ";
    if ($suggest_keywords==1) {
        echo " checked='checked'
        ";
    }
    echo "
        />
        <label for='suggest_phrases'>Search for suggestions in phrases?</label>
        <input name='_suggest_phrases' type='checkbox' value='1' id='suggest_phrases'
        title='Select to enable suggestions from Phrases'
    ";
    if ($suggest_phrases==1) {
        echo " checked='checked'
        ";
    }
    echo "
        />
        <label for='suggest_rows'>Limit number of suggestions to:</label>
        <input name='_suggest_rows' type='text' id='suggest_rows' size='3' maxlength='2' value='$suggest_rows'
        title='Enter default number of rows for suggestions'
        /></fieldset>
        <fieldset><legend>[ Page Indexing Weights ]</legend><br />
        <legend class='warnadmin'>If you modify any settings in this section after first index, you are obliged to invoke 'Erase & Re-index'.</legend>
        <br /><br />
        
        <label for='title_weight'>Relative weight of a word in web page Title tag:</label>
        <input name='_title_weight' type='text' id='title_weight' size='5' maxlength='2' value='$title_weight'
        title='Enter default weight for words in a Web page title tag'
        />
        <label for='domain_weight'>Relative weight of a word in the Domain Name:</label>
        <input name='_domain_weight' type='text' id='domain_weight' size='5' maxlength='2' value='$domain_weight'
        title='Enter default weight for words in a Domain Name'
        />
        <label for='path_weight'>Relative weight of a word in the Path Name:</label>
        <input name='_path_weight' type='text' id='path_weight' size='5' maxlength='2' value='$path_weight'
        title='Enter default weight for words in a Path Name'
        />
        <label for='meta_weight'>Relative weight of a word in web page Keywords tag:</label>
        <input name='_meta_weight' type='text' id='meta_weight' size='5' maxlength='2' value='$meta_weight'
        title='Enter default weight for words in Keyword Meta Tags'
        />
        <label for='sort_results'>Define the default chronological order for result listing: </label>
        <select name='_sort_results' id='sort_results' title='Select how to present the result listing'>        
        <option value='1'";
    if ($sort_results == '1'){
        echo " checked='checked'";
    }
    echo ">By relevance (weight / hits) </option>
    
        <option value='2'";
    if ($sort_results == '2') {
        echo " selected='selected'";
    }
    echo ">Main URLs (domains) on top </option>
    
        <option value='3'";    
    if ($sort_results == '3') {
        echo " selected='selected'";
    }
    echo ">By URL names</option>   
   
        <option value='4'";    
    if ($sort_results == '4') {
        echo " selected='selected'";
    }
    echo ">Like Google (Top 2 per URL)</option>  

        <option value='5'";    
    if ($sort_results == '5') {
        echo " selected='selected'";
    }
    echo ">'Most Popular Links' on top</option></select> 
    
        <label for='domain_mul'>Muliplier for words in main URLs (domains): </label>
        <input name='_domain_mul' type='text' id='domain_mul' size='1' maxlength='1' value='$domain_mul'
        title='Defines factor for all words in Domains.'
        />   
        </fieldset>
        
        <fieldset><legend>[ Save These Settings ]</legend>
        
        <input class='hide' type='hidden' name='f' value='settings'
        />
        <input class='hide' type='hidden' name='Submit' value='1'
        />
        <input class='hide' type='hidden' name='_plus_nr' value='$plus_nr'
        />        
        <input class='hide' type='hidden' name='_version_nr' value='$version_nr'
        />
        <input class='hide' type='hidden' name='_pdftotext_path' value='$pdftotext_path'
        />
        <input class='hide' type='hidden' name='_catdoc_path' value='$catdoc_path'
        />
        <input class='hide' type='hidden' name='_xls2csv_path' value='$xls2csv_path'
        />
        <input class='hide' type='hidden' name='_catppt_path' value='$catppt_path'
        />
         <label for='submit'>Save Sphider-plus Settings:</label>
        <input class='sbmt' type='submit' value='Save settings' id='submit' title='Click once to save these settings'
        />
        </fieldset></form>
    	</div><br />
	";
?>