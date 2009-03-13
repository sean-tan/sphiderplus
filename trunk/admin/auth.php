<?php
	error_reporting(E_ERROR | E_PARSE);	
	$admin = "admin";
	$admin_pw = "admin";

    define("_SECURE",1) ;    // define secure constant
	session_start();
	
    if (isset($_POST['user']) && isset($_POST['pass'])) {

    	$username = $_POST['user'];
    	$password = $_POST['pass'];
    	if (($username == $admin) && ($password ==$admin_pw)) {
    		$_SESSION['admin'] = $username;
    		$_SESSION['admin_pw'] = $password;
    	}
    	header("Location: admin.php");
    } elseif ((isset($_SESSION['admin']) && isset($_SESSION['admin_pw']) &&$_SESSION['admin'] == $admin && $_SESSION['admin_pw'] == $admin_pw ) || (getenv("REMOTE_ADDR")=="")) {

    } else {
        echo "
            <div id='main'>
            <h1 class='cntr'>Sphider-plus v.$plus_nr. The Open-Source PHP Search Engine</h1>
            <div id='admin'>
            <div class='panel x2'>
            	<form class='txt' action='auth.php' method='post'>
            	<fieldset><legend>[ Sphider Admin Login ]</legend>
            	<label for='user'>[ Name ]</label>
            	<input type='text' name='user' id='user' size='15' maxlength='15'
            	title='Required - Enter your user name here' onfocus='this.value=\"\"' value=''
            	/>
            	<label for='pass'>[ Password ]</label>
            	<input type='password' name='pass' id='pass' size='15' maxlength='15'
            	title='Required - Enter your password here' onfocus='this.value=\"\"' value=''
            	/></fieldset>
            	<fieldset><legend>[ Log In ]</legend>
            	<input class='sbmt' type='submit' id='submit' value='&nbsp;Login &raquo;&raquo; ' title='Click to confirm'
            	/>
            	</fieldset>
            	</form>
            </div></div>
            </div>
            </body>
            </html>
        ";
        exit();
    }
    $settings_dir = "../settings";
    include "$settings_dir/database.php";
?>