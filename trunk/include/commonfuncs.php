<?php 

	$includes = array('./include', 'include', '../include');
	if( !in_array($include_dir, $includes) )  {
       die("Illegal include.");
	} 


	/**
	* Returns the result of a query as an array
	* 
	* @param string $query SQL p‰ring stringina
	* @return array|null massiiv
	 */
	function sql_fetch_all($query) {
		$result = mysql_query($query);
		if($mysql_err = mysql_errno()) {
			print $query.'<br>'.mysql_error();
		} else {
			while($row=mysql_fetch_array($result)) {
				$data[]=$row;
			}	
		}		
		return $data;
	}



	/*
	Removes duplicate elements from an array
	*/
	function distinct_array($arr) {
		rsort($arr);
		reset($arr);
		$newarr = array();
		$i = 0;
		$element = current($arr);

		for ($n = 0; $n < sizeof($arr); $n++) {
			if (next($arr) != $element) {
				$newarr[$i] = $element;
				$element = current($arr);
				$i++;
			}
		}

		return $newarr;
	}

	function get_cats($parent) {
		global $mysql_table_prefix;
		$query = "SELECT * FROM ".$mysql_table_prefix."categories WHERE parent_num=$parent";
		$result = mysql_query($query);
		echo mysql_error();        
		$arr[] = $parent;
		if (mysql_num_rows($result) <> '') {
			while ($row = mysql_fetch_array($result)) {
				$id = $row[category_id];
				$arr = add_arrays($arr, get_cats($id));
			}
		}

		return $arr;
	}
	
	function add_arrays($arr1, $arr2) {
		foreach ($arr2 as $elem) {
			$arr1[] = $elem;
		}
		return $arr1;
	}

	$entities = array
		(
		"&amp" => "&",
		"&apos" => "'",
		"&THORN;"  => "ﬁ",
		"&szlig;"  => "ﬂ",
		"&agrave;" => "‡",
		"&aacute;" => "·",
		"&acirc;"  => "‚",
		"&atilde;" => "„",
		"&auml;"   => "‰",
		"&aring;"  => "Â",
		"&aelig;"  => "Ê",
		"&ccedil;" => "Á",
		"&egrave;" => "Ë",
		"&eacute;" => "È",
		"&ecirc;"  => "Í",
		"&euml;"   => "Î",
		"&igrave;" => "Ï",
		"&iacute;" => "Ì",
		"&icirc;"  => "Ó",
		"&iuml;"   => "Ô",
		"&eth;"    => "",
		"&ntilde;" => "Ò",
		"&ograve;" => "Ú",
		"&oacute;" => "Û",
		"&ocirc;"  => "Ù",
		"&otilde;" => "ı",
		"&ouml;"   => "ˆ",
		"&oslash;" => "¯",
		"&ugrave;" => "˘",
		"&uacute;" => "˙",
		"&ucirc;"  => "˚",
		"&uuml;"   => "¸",
		"&yacute;" => "˝",
		"&thorn;"  => "˛",
		"&yuml;"   => "ˇ",
		"&THORN;"  => "ﬁ",
		"&szlig;"  => "ﬂ",
		"&Agrave;" => "‡",
		"&Aacute;" => "·",
		"&Acirc;"  => "‚",
		"&Atilde;" => "„",
		"&Auml;"   => "‰",
		"&Aring;"  => "Â",
		"&Aelig;"  => "Ê",
		"&Ccedil;" => "Á",
		"&Egrave;" => "Ë",
		"&Eacute;" => "È",
		"&Ecirc;"  => "Í",
		"&Euml;"   => "Î",
		"&Igrave;" => "Ï",
		"&Iacute;" => "Ì",
		"&Icirc;"  => "Ó",
		"&Iuml;"   => "Ô",
		"&ETH;"    => "",
		"&Ntilde;" => "Ò",
		"&Ograve;" => "Ú",
		"&Oacute;" => "Û",
		"&Ocirc;"  => "Ù",
		"&Otilde;" => "ı",
		"&Ouml;"   => "ˆ",
		"&Oslash;" => "¯",
		"&Ugrave;" => "˘",
		"&Uacute;" => "˙",
		"&Ucirc;"  => "˚",
		"&Uuml;"   => "¸",
		"&Yacute;" => "˝",
		"&Yhorn;"  => "˛",
		"&Yuml;"   => "ˇ"
		);

	//Apache multi indexes parameters
	$apache_indexes = array (  
		"N=A" => 1,
		"N=D" => 1,
		"M=A" => 1,
		"M=D" => 1,
		"S=A" => 1,
		"S=D" => 1,
		"D=A" => 1,
		"D=D" => 1,
		"C=N;O=A" => 1,
		"C=M;O=A" => 1,
		"C=S;O=A" => 1,
		"C=D;O=A" => 1,
		"C=N;O=D" => 1,
		"C=M;O=D" => 1,
		"C=S;O=D" => 1,
		"C=D;O=D" => 1);


	function remove_accents($string) {
		return (strtr($string, "¿¡¬√ƒ≈∆‡·‚„‰ÂÊ“”‘’’÷ÿÚÛÙıˆ¯»… ÀËÈÍÎ«Á–ÃÕŒœÏÌÓÔŸ⁄€‹˘˙˚¸—Òﬁﬂˇ˝",
					  "aaaaaaaaaaaaaaoooooooooooooeeeeeeeeecceiiiiiiiiuuuuuuuunntsyy"));
	}

	function lower_case($string) {
		return (strtr($string, "ABCDEFGHIJKLMNOPQRSTUVWXYZƒ÷‹",
					  "abcdefghijklmnopqrstuvwxyz‰ˆ¸"));
	}
    
	$common = array
		(
		);

	$lines = @file($include_dir.'/common.txt');

	if (is_array($lines)) {
		while (list($id, $word) = each($lines))
			$common[trim($word)] = 1;
	}

	$ext = array
		(
		);

	$lines = @file('ext.txt');

	if (is_array($lines)) {
		while (list($id, $word) = each($lines))
			$ext[] = trim($word);
	}

	function is_num($var) {
	   for ($i=0;$i<strlen($var);$i++) {
		   $ascii_code=ord($var[$i]);
		   if ($ascii_code >=49 && $ascii_code <=57){
			   continue;
		   } else {
			   return false;
		   }
	   }
  		   return true;
	}

	function getHttpVars() {
		$superglobs = array(
			'_POST',
			'_GET',
			'HTTP_POST_VARS',
			'HTTP_GET_VARS');

		$httpvars = array();

		// extract the right array
		foreach ($superglobs as $glob) {
			global $$glob;
			if (isset($$glob) && is_array($$glob)) {
				$httpvars = $$glob;
			 }
			if (count($httpvars) > 0)
				break;
		}
		return $httpvars;

	}
function countSubstrs($haystack, $needle) {
	$count = 0;
	while(strpos($haystack,$needle) !== false) {
	   $haystack = substr($haystack, (strpos($haystack,$needle) + 1));
	   $count++;
	}
	return $count;
}

function quote_replace($str) {

		$str = str_replace("\"", "&quot;", $str);
		return str_replace("'","&apos;", $str);
}


function fst_lt_snd($version1, $version2) {

	$list1 = explode(".", $version1);
	$list2 = explode(".", $version2);

	$length = count($list1);
	$i = 0;
	while ($i < $length) {
		if ($list1[$i] < $list2[$i])
			return true;
		if ($list1[$i] > $list2[$i])
			return false;
		$i++;
	}
	
	if ($length < count($list2)) {
		return true;
	}
	return false;

}

function get_dir_contents($dir) {
	$contents = Array();
	if ($handle = opendir($dir)) {
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != "..") {
				$contents[] = $file;
			}
		}
		closedir($handle);
	}
	return $contents;
}

function replace_ampersand($str) {
	return str_replace("&", "%26", $str);
}



    /**
	* Stemming algorithm
    * Copyright (c) 2005 Richard Heyes (http://www.phpguru.org/)
    * All rights reserved.
    * This script is free software.
	* Modified to work with php versions prior 5 by Ando Saabas
    */

	/**
	* Regex for matching a consonant
	*/
	$regex_consonant = '(?:[bcdfghjklmnpqrstvwxz]|(?<=[aeiou])y|^y)';


	/**
	* Regex for matching a vowel
	*/
	$regex_vowel = '(?:[aeiou]|(?<![aeiou])y)';

	/**
	* Stems a word. Simple huh?
	*
	* @param  string $word Word to stem
	* @return string       Stemmed word
	*/
	function stem($word)
	{
		if (strlen($word) <= 2) {
			return $word;
		}

		$word = step1ab($word);
		$word = step1c($word);
		$word = step2($word);
		$word = step3($word);
		$word = step4($word);
		$word = step5($word);

		return $word;
	}


	/**
	* Step 1
	*/
	function step1ab($word)
	{
		global $regex_vowel, $regex_consonant;
		// Part a
		if (substr($word, -1) == 's') {

			   replace($word, 'sses', 'ss')
			OR replace($word, 'ies', 'i')
			OR replace($word, 'ss', 'ss')
			OR replace($word, 's', '');
		}

		// Part b
		if (substr($word, -2, 1) != 'e' OR !replace($word, 'eed', 'ee', 0)) { // First rule
			$v = $regex_vowel;
			// ing and ed
			if (   preg_match("#$v+#", substr($word, 0, -3)) && replace($word, 'ing', '')
				OR preg_match("#$v+#", substr($word, 0, -2)) && replace($word, 'ed', '')) { // Note use of && and OR, for precedence reasons

				// If one of above two test successful
				if (    !replace($word, 'at', 'ate')
					AND !replace($word, 'bl', 'ble')
					AND !replace($word, 'iz', 'ize')) {

					// Double consonant ending
					if (    doubleConsonant($word)
						AND substr($word, -2) != 'll'
						AND substr($word, -2) != 'ss'
						AND substr($word, -2) != 'zz') {

						$word = substr($word, 0, -1);

					} else if (m($word) == 1 AND cvc($word)) {
						$word .= 'e';
					}
				}
			}
		}

		return $word;
	}


	/**
	* Step 1c
	*
	* @param string $word Word to stem
	*/
	function step1c($word)
	{
		global $regex_vowel, $regex_consonant;
		$v = $regex_vowel;

		if (substr($word, -1) == 'y' && preg_match("#$v+#", substr($word, 0, -1))) {
			replace($word, 'y', 'i');
		}

		return $word;
	}


	/**
	* Step 2
	*
	* @param string $word Word to stem
	*/
	function step2($word)
	{
		switch (substr($word, -2, 1)) {
			case 'a':
				   replace($word, 'ational', 'ate', 0)
				OR replace($word, 'tional', 'tion', 0);
				break;

			case 'c':
				   replace($word, 'enci', 'ence', 0)
				OR replace($word, 'anci', 'ance', 0);
				break;

			case 'e':
				replace($word, 'izer', 'ize', 0);
				break;

			case 'g':
				replace($word, 'logi', 'log', 0);
				break;

			case 'l':
				   replace($word, 'entli', 'ent', 0)
				OR replace($word, 'ousli', 'ous', 0)
				OR replace($word, 'alli', 'al', 0)
				OR replace($word, 'bli', 'ble', 0)
				OR replace($word, 'eli', 'e', 0);
				break;

			case 'o':
				   replace($word, 'ization', 'ize', 0)
				OR replace($word, 'ation', 'ate', 0)
				OR replace($word, 'ator', 'ate', 0);
				break;

			case 's':
				   replace($word, 'iveness', 'ive', 0)
				OR replace($word, 'fulness', 'ful', 0)
				OR replace($word, 'ousness', 'ous', 0)
				OR replace($word, 'alism', 'al', 0);
				break;

			case 't':
				   replace($word, 'biliti', 'ble', 0)
				OR replace($word, 'aliti', 'al', 0)
				OR replace($word, 'iviti', 'ive', 0);
				break;
		}

		return $word;
	}


	/**
	* Step 3
	*
	* @param string $word String to stem
	*/
	function step3($word)
	{
		switch (substr($word, -2, 1)) {
			case 'a':
				replace($word, 'ical', 'ic', 0);
				break;

			case 's':
				replace($word, 'ness', '', 0);
				break;

			case 't':
				   replace($word, 'icate', 'ic', 0)
				OR replace($word, 'iciti', 'ic', 0);
				break;

			case 'u':
				replace($word, 'ful', '', 0);
				break;

			case 'v':
				replace($word, 'ative', '', 0);
				break;

			case 'z':
				replace($word, 'alize', 'al', 0);
				break;
		}

		return $word;
	}


	/**
	* Step 4
	*
	* @param string $word Word to stem
	*/
	function step4($word)
	{
		switch (substr($word, -2, 1)) {
			case 'a':
				replace($word, 'al', '', 1);
				break;

			case 'c':
				   replace($word, 'ance', '', 1)
				OR replace($word, 'ence', '', 1);
				break;

			case 'e':
				replace($word, 'er', '', 1);
				break;

			case 'i':
				replace($word, 'ic', '', 1);
				break;

			case 'l':
				   replace($word, 'able', '', 1)
				OR replace($word, 'ible', '', 1);
				break;

			case 'n':
				   replace($word, 'ant', '', 1)
				OR replace($word, 'ement', '', 1)
				OR replace($word, 'ment', '', 1)
				OR replace($word, 'ent', '', 1);
				break;

			case 'o':
				if (substr($word, -4) == 'tion' OR substr($word, -4) == 'sion') {
				   replace($word, 'ion', '', 1);
				} else {
					replace($word, 'ou', '', 1);
				}
				break;

			case 's':
				replace($word, 'ism', '', 1);
				break;

			case 't':
				   replace($word, 'ate', '', 1)
				OR replace($word, 'iti', '', 1);
				break;

			case 'u':
				replace($word, 'ous', '', 1);
				break;

			case 'v':
				replace($word, 'ive', '', 1);
				break;

			case 'z':
				replace($word, 'ize', '', 1);
				break;
		}

		return $word;
	}


	/**
	* Step 5
	*
	* @param string $word Word to stem
	*/
	function step5($word)
	{
		// Part a
		if (substr($word, -1) == 'e') {
			if (m(substr($word, 0, -1)) > 1) {
				replace($word, 'e', '');

			} else if (m(substr($word, 0, -1)) == 1) {

				if (!cvc(substr($word, 0, -1))) {
					replace($word, 'e', '');
				}
			}
		}

		// Part b
		if (m($word) > 1 AND doubleConsonant($word) AND substr($word, -1) == 'l') {
			$word = substr($word, 0, -1);
		}

		return $word;
	}


	/**
	* Replaces the first string with the second, at the end of the string. If third
	* arg is given, then the preceding string must match that m count at least.
	*
	* @param  string $str   String to check
	* @param  string $check Ending to check for
	* @param  string $repl  Replacement string
	* @param  int    $m     Optional minimum number of m() to meet
	* @return bool          Whether the $check string was at the end
	*                       of the $str string. True does not necessarily mean
	*                       that it was replaced.
	*/
	function replace(&$str, $check, $repl, $m = null)
	{
		$len = 0 - strlen($check);

		if (substr($str, $len) == $check) {
			$substr = substr($str, 0, $len);
			if (is_null($m) OR m($substr) > $m) {
				$str = $substr . $repl;
			}

			return true;
		}

		return false;
	}


	/**
	* What, you mean it's not obvious from the name?
	*
	* m() measures the number of consonant sequences in $str. if c is
	* a consonant sequence and v a vowel sequence, and <..> indicates arbitrary
	* presence,
	*
	* <c><v>       gives 0
	* <c>vc<v>     gives 1
	* <c>vcvc<v>   gives 2
	* <c>vcvcvc<v> gives 3
	*
	* @param  string $str The string to return the m count for
	* @return int         The m count
	*/
	function m($str)
	{
		global $regex_vowel, $regex_consonant;
		$c = $regex_consonant;
		$v = $regex_vowel;

		$str = preg_replace("#^$c+#", '', $str);
		$str = preg_replace("#$v+$#", '', $str);

		preg_match_all("#($v+$c+)#", $str, $matches);

		return count($matches[1]);
	}


	/**
	* Returns true/false as to whether the given string contains two
	* of the same consonant next to each other at the end of the string.
	*
	* @param  string $str String to check
	* @return bool        Result
	*/
	function doubleConsonant($str)
	{
		global $regex_consonant;
		$c = $regex_consonant;

		return preg_match("#$c{2}$#", $str, $matches) AND $matches[0]{0} == $matches[0]{1};
	}


	/**
	* Checks for ending CVC sequence where second C is not W, X or Y
	*
	* @param  string $str String to check
	* @return bool        Result
	*/
	function cvc($str)
	{
		$c = $regex_consonant;
		$v = $regex_vowel;

		return     preg_match("#($c$v$c)$#", $str, $matches)
			   AND strlen($matches[1]) == 3
			   AND $matches[1]{2} != 'w'
			   AND $matches[1]{2} != 'x'
			   AND $matches[1]{2} != 'y';
	}

	function list_cats($parent, $lev, $color, $message) {
		global $mysql_table_prefix;
		if ($lev == 0) {
			echo "<div class='submenu'>
			<ul>
				<li><a href='admin.php?f=add_cat'>Add category</a></li>
			</ul>
			</div>
	";
			echo $message;
			echo "<div class='panel'>
		<table width='100%'>
		<tr>
			<td class='tblhead' colspan='3'>Categories</td>
		</tr>
		";
		}
		$space = "";
		for ($x = 0; $x < $lev; $x++) {
			$space .= "<span class='tree'>&raquo;</span>&nbsp;";
		}

		$query = "SELECT * FROM ".$mysql_table_prefix."categories WHERE parent_num=$parent ORDER BY category";
		$result = mysql_query($query);
		echo mysql_error();

		if (mysql_num_rows($result) <> '') {
			while ($row = mysql_fetch_array($result)) {
				if ($color =="odrow") {
					$color = "evrow";
				} else {
					$color = "odrow";
				}
				$id = $row['category_id'];
				$cat = $row['category'];
				echo "<tr class='$color'>
			";
				if (!$space=="") {
					echo "<td width='90%'>
			<div>$space<a class='options' href='admin.php?f=edit_cat&amp;cat_id=$id'
				title='Edit this Sub-Category'>".stripslashes($cat)."</a></div></td>
			<td class='options'><a href='admin.php?f=edit_cat&amp;cat_id=$id' class='options' title='Edit this Sub-Category'>Edit</a></td>
			<td class='options'><a href='admin.php?f=11&amp;cat_id=$id' title='Delete this Sub-Category'
				onclick=\"return confirm('Are you sure you want to delete? Subcategories will be lost.')\" class='options'>Delete</a></td>
		</tr>
		";
				} else {
					echo"<td width='90%'><a class='options' href='admin.php?f=edit_cat&amp;cat_id=$id'
				title='Edit this Category'>".stripslashes($cat)."</a></td>
			<td class='options'><a href='admin.php?f=edit_cat&amp;cat_id=$id' class='options' title='Edit this Category'>Edit</a></td>
			<td class='options'><a href='admin.php?f=11&amp;cat_id=$id' title='Delete this Category'
				onclick=\"return confirm('Are you sure you want to delete? Subcategories will be lost.')\" class='options'>Delete</a></td>
		</tr>
	";
				}
				$color = list_cats($id, $lev + 1, $color, "");
			}
		}
		if ($lev == 0) {
			echo "</table>
	</div>
	";
		}
		return $color;
	}

    
	function list_catsform($parent, $lev, $color, $message, $category_id) {

		global $mysql_table_prefix;
		if ($lev == 0) {
			print "\n";
		}
		$space = "";
		for ($x = 0; $x < $lev; $x++)
			$space .= "&nbsp;&nbsp;&nbsp;-&nbsp;";

		$query = "SELECT * FROM ".$mysql_table_prefix."categories WHERE parent_num=$parent ORDER BY category LIMIT 0 , 300";
		$result = mysql_query($query);
		echo mysql_error();
		
		if (mysql_num_rows($result) <> '')
			while ($row = mysql_fetch_array($result)) {
	
				$id = $row['category_id'];
				$cat = $row['category'];
				$selected = " selected ";
				if ($category_id != $id) { $selected = ""; }
				print "<option ".$selected." value=\"".$id."\">".$space.stripslashes($cat)."</option>\n";
	
				$color = list_catsform($id, $lev + 1, $color, "", $category_id);
			}
		return $color;
	}
    
    function getmicrotime(){
        list($usec, $sec) = explode(" ",microtime());
        return ((float)$usec + (float)$sec);
        }

    function saveToLog ($query, $elapsed, $results) {
            global $mysql_table_prefix;
        if ($results =="") {
            $results = 0;
        }
        $query =  "insert into ".$mysql_table_prefix."query_log (query, time, elapsed, results) values ('$query', now(), '$elapsed', '$results')";
    	mysql_query($query);
                        
    	echo mysql_error();
                            
    }
        
    function validate_url($input) {
        global $mytitle;
        //	Standard Url test        
    	if (! preg_match('=(https?|ftp)://[a-z0-9]([a-z0-9-]*[a-z/0-9])?\.[a-z0-9]=i', ($input))) {       
            echo "<h1>$mytitle</h1>
                <br />
                <p class='warnadmin cntr'>
                Invalid input for 'Url'
                </p>
                <a class='bkbtn' href='addurl.php' title='Go back to Submission Form'>Back</a>                                                                    
                </body>
                </html>
            ";
            die ('');
        }
        
        //      Do we have a valid DNS ? This test is disabled for localhost application as checkdnsrr needs internet access    
        $localhost = strstr(htmlspecialchars(@$_SERVER['HTTP_REFERER']), "localhost");        
        if (!$localhost) { 
        	if (preg_match("/www/i", $input)){
        		$input = ereg_replace ('http://','',$input);
                $input1 = $input;
                $pos = strpos($input1,"/");
                if ($pos != '') $input1 = substr($input1,0,$pos);
        		if(!checkdnsrr($input1, "A")) {
                    echo "<h1>$mytitle</h1>
                        <br />
                        <p class='warnadmin cntr'>                       
                        Invalid url input. No DNS resource available for this url
                        <a class='bkbtn' href='addurl.php' title='Go back to Submission Form'>Back</a>                                                                    
                        </body>
                        </html>
                    ";
                    die ('');
                }           
        		$input = str_replace("www","http://www",$input);
               
        	}	
    	}
        return ($input);
    }
    
    function validate_email($input) {
        //	Standard e-mail test
        if(!preg_match('/^[\w.+-]{2,}\@[\w.-]{2,}\.[a-z]{2,6}$/', $input)) {
            echo "<h1>$mytitle</h1>
                <br />           
                <p class='warnadmin cntr'>
                Invalid input for 'e-mail account'
                </p>
                <a class='bkbtn' href='addurl.php' title='Go back to Submission Form'>Back</a>                                                                    
                </body>
                </html>
            ";
            die ('');
        }
        
        //      Check if Mail Exchange Resource Record (MX-RR)  is valid and also is stored in Domain Name System (DNS) 
        //      This test is disabled for localhost applications as getmxrr needs internet access 
        $localhost = strstr(htmlspecialchars(@$_SERVER['HTTP_REFERER']), "localhost");        
        if (!$localhost) { 
            if(!getmxrr(substr(strstr($input, '@'), 1), $mxhosts)) {
                echo "<h1>$mytitle</h1>
                    <br />
                    <p class='warnadmin cntr'>
                    Invald e-mail account.<br />
                    There is no valid Mail Exchange Resource Record (MX-RR)<br />
                    on the Domain Name System (DNS)
                    </p>
                    <a class='bkbtn' href='addurl.php' title='Go back to Submission Form'>Back</a>                                                                    
                    </body>
                    </html>
                ";
                die ('');   
            }	
        }        
        return ($input);
    }
    

	function cleanup_text ($input='', $preserve='', $allowed_tags='') {
		if (empty($preserve)) 
			{ 
				$input = strip_tags($input, $allowed_tags);
			}
		$input = htmlspecialchars($input, ENT_QUOTES);
		return $input;
	}
    
    function cleaninput($input) {
        if (get_magic_quotes_gpc()) {
            $input = stripslashes($input);          //      delete quotes
        }
        $input = mysql_real_escape_string($input);  //      place backslash in front of special characters
        
        //	prevent SQL-injection, XSS-attack and Shell-execute
        $input = eregi_replace("cmd|CREATE|DELETE|DROP|eval|EXEC|File|INSERT","",$input);
        $input = eregi_replace("LOCK|PROCESSLIST|SELECT|shell|SHOW|SHUTDOWN","",$input);
        $input = eregi_replace("SQL|SYSTEM|TRUNCATE|UNION|UPDATE|DUMP","",$input);        
        
        return $input;    
    }

    function footer () {
        global $add_url, $most_pop, $mysql_table_prefix;
        
    	echo "<p class='stats'>
            <!-- The following should only be removed if you contribute (donate) to the Sphider-plus project.
            Note that this is a requirement under the GPL licensing agreement, which Sphider-plus acknowledges. -->	
    		<a href='http://code.google.com/p/sphiderplus/' title='Link: Visit Sphider-plus site in new window' target='rel'>Visit
    		<img class='mid' src='sphider-logo.png' alt='Visit Sphider site in new window' height='15' width='80'
    		/> -plus
            </a>                
            </p> 
        ";
    }

    function error_handler($errNo, $errStr, $errFile, $errLine){
        if(ob_get_length()) ob_clean();             // clear any output that has already been generated

        $error_message = 'ERRNO: ' . $errNo . chr(10) .
                        'TEXT: ' . $errStr . chr(10) .
                        'LOCATION: ' . $errFile . 
                        ', line ' . $errLine;
        echo $error_message;
        exit;       // stop executing any script
    }
    
?>
