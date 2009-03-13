<?php
error_reporting(E_ALL);
$settings_dir = "../settings";
include "$settings_dir/conf.php";
include "$settings_dir/database.php";
$template_dir = "../templates";
$template_path = "$template_dir/$template";
echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1' />
<meta http-equiv='Content-Style-Type' content='text/css' />
<title>Sphider-plus installation script</title>
<link rel='stylesheet' href='$template_path/thisstyle.css' type='text/css' />
</head>
<body>
<h1>Sphider-plus installation script to create all tables.</h1>
<p>
";

$error = 0;

mysql_query("create table `".$mysql_table_prefix."addurl`(
  url varchar(255) not null primary key,
  title varchar(255),
  description varchar(255),
  category_id int(11),
  account varchar(255),
  created timestamp NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
if (mysql_errno() > 0) {
	print "Error: ";
	print mysql_error();
	print "<br />\n";
	$error += mysql_errno();
}

mysql_query("create table `".$mysql_table_prefix."banned` (
  domain varchar(255),
  created timestamp NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
if (mysql_errno() > 0) {
	print "Error: ";
	print mysql_error();
	print "<br />\n";
	$error += mysql_errno();
}

// Structure for table 'real_log'
mysql_query("create table `".$mysql_table_prefix."real_log`(
  url varchar(255) not null,
  real_log mediumtext,
  refresh integer not null primary key,  
  created timestamp NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
if (mysql_errno() > 0) {
	print "Error: ";
	print mysql_error();
	print "<br />\n";
	$error += mysql_errno();
}

mysql_query("create table `".$mysql_table_prefix."sites`(
	site_id int auto_increment not null primary key,
	url varchar(255),
	title varchar(255),
	short_desc text,
	indexdate date,
	spider_depth int default 2,
	required text not null,
	disallowed text not null,
	can_leave_domain bool)");
if (mysql_errno() > 0) {
	print "Error: ";
	print mysql_error();
	print "<br />\n";
	$error += mysql_errno();
}
mysql_query("create table `".$mysql_table_prefix."links` (
	link_id int auto_increment primary key not null,
	site_id int,
	url varchar(255) not null,
	title varchar(200),
	description varchar(255),
	fulltxt mediumtext,
	indexdate date,
	size float(2),
	md5sum varchar(32),
	key url (url),
	key md5key (md5sum),
	visible int default 0, 
	level int,
    click_counter INT NULL DEFAULT 0,    
    last_click INT NULL DEFAULT 0,
    last_query VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_bin NULL DEFAULT NULL)");

if (mysql_errno() > 0) {
	print "Error: ";
	print mysql_error();
	print "<br />\n";
	$error += mysql_errno();
}
mysql_query("create table `".$mysql_table_prefix."keywords`	(
	keyword_id int primary key not null auto_increment,
	keyword varchar(255) not null,
	unique kw (keyword),
	key keyword (keyword(10)))");

if (mysql_errno() > 0) {
	print "Error: ";
	print mysql_error();
	print "<br />\n";
	$error += mysql_errno();
}

for ($i=0;$i<=15; $i++) {
	$char = dechex($i);
	mysql_query("create table `".$mysql_table_prefix."link_keyword$char` (
		link_id int not null,
		keyword_id int not null,
		weight int(3),
		domain int(4),
        hits int(3),
		key linkid(link_id),
		key keyid(keyword_id))");

	if (mysql_errno() > 0) {
		print "Error: ";
		print mysql_error();
		print "<br />\n";
		$error += mysql_errno();
	}
}

mysql_query("create table `".$mysql_table_prefix."categories` (
	category_id integer not null auto_increment primary key, 
	category text,
	parent_num integer
	)");

if (mysql_errno() > 0) {
	print "Error: ";
	print mysql_error();
	print "<br />\n";
	$error += mysql_errno();
}

mysql_query("create table `".$mysql_table_prefix."site_category` (
	site_id integer,
	category_id integer
	)");

if (mysql_errno() > 0) {
	print "Error: ";
	print mysql_error();
	print "<br />\n";
	$error += mysql_errno();
}

mysql_query("create table `".$mysql_table_prefix."temp` (
	link varchar(255),
	level integer,
	id varchar (32)
	)");

if (mysql_errno() > 0) {
	print "Error: ";
	print mysql_error();
	print "<br />\n";
	$error += mysql_errno();
}

mysql_query("create table `".$mysql_table_prefix."pending` (
	site_id integer,
	temp_id varchar(32),
	level integer,
	count integer,
	num integer
)");

if (mysql_errno() > 0) {
	print "Error: ";
	print mysql_error();
	print "<br />\n";
	$error += mysql_errno();
}

mysql_query("create table `".$mysql_table_prefix."query_log` (
	query varchar(255),
	time timestamp(14),
	elapsed float(2),
	results int, 
	key query_key(query))");

if (mysql_errno() > 0) {
	print "Error: ";
	print mysql_error();
	print "<br />\n";
	$error += mysql_errno();
}

mysql_query("create table `".$mysql_table_prefix."domains` (
	domain_id int auto_increment primary key not null,	
	domain varchar(255))");

if (mysql_errno() > 0) {
	print "Error: ";
	print mysql_error();
	print "<br />\n";
	$error += mysql_errno();
}


if ($error >0) {
	echo "</p>\n<p class='warn em'>Creating tables failed. Consult the above error messages.</p>\n";
} else {
	echo "</p>\n<p class='warnok em'>Creating tables successfully completed.<br /><br />Go to <a href=\"admin.php\">admin.php</a> to start indexing.</p>\n";
}
echo "</body>
</html>";

?>