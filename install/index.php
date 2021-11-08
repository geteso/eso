<?php
// index.php
// An installer wrapper which sets up the "Install" controller and displays the installer interface.

define("IN_ESO", 1);

// Unset the page execution time limit.
@set_time_limit(0);

// Require essential files.
require "../lib/functions.php";
require "../lib/classes.php";
require "../lib/database.php";

// Start a session if one does not already exist.
if (!session_id()) session_start();

// Undo register_globals.
undoRegisterGlobals();

// If magic quotes is on, strip the slashes that it added.
if (get_magic_quotes_gpc()) {
	$_GET = array_map("undoMagicQuotes", $_GET);
	$_POST = array_map("undoMagicQuotes", $_POST);
	$_COOKIE = array_map("undoMagicQuotes", $_COOKIE);
}

// Sanitize the request data using htmlentities.
$_POST = sanitizeHTML($_POST);
$_GET = sanitizeHTML($_GET);
$_COOKIE = sanitizeHTML($_COOKIE);

// Set up the Install controller, which will perform all installation tasks.
require "install.controller.php";
$install = new Install();
$install->init();

?>
<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
<title>esoInstaller</title>
<script type='text/javascript' src='../js/eso.js'></script>
<link type='text/css' rel='stylesheet' href='install.css'/>
</head>

<body>
	
<form action='' method='post'>
<div id='container'>

<?php

switch ($install->step) {


// Fatal checks.
case "fatalChecks": ?>
<h1><img src='logo.svg' data-fallback='logo.png' alt='Forum logo'/>Uh oh, something's not right!</h1>
<p>The following errors were found with your forum's setup. They must be resolved before you can continue the installation.</p>
<hr/>
<ul>
<?php foreach ($install->errors as $error) echo "<li>$error</li>"; ?>
</ul>
<p>If you run into any other problems or just want some help with the installation, feel free to join <a href='https://geteso.org'>geteso.org</a> where a bunch of friendly people will be happy to help you out.</p>
<hr/>
<p id='footer'><input class='button' value='Try again' type='submit'/></p>
<?php break;


// Warning checks.
case "warningChecks": ?>
<h1><img src='logo.svg' data-fallback='logo.png' alt='Forum logo'/>Warning!</h1>
<p>The following errors were found with your forum's setup. You can continue the installation without resolving them, but some functionality may be limited.</p>
<hr/>
<ul>
<?php foreach ($install->errors as $error) echo "<li>$error</li>"; ?>
</ul>
<p>If you run into any other problems or just want some help with the installation, feel free to join <a href='https://geteso.org'>geteso.org</a> where a bunch of friendly people will be happy to help you out.</p>
<hr/>
<p id='footer'><input class='button' value='Next step &#155;' type='submit' name='next'/></p>
<?php break;


// Specify setup information.
case "info": ?>
<h1><img src='logo.svg' alt=''/>Specify setup information</h1>
<p>Welcome to the installer.  We need a few details from you so we can get your forum set up and ready to go.</p>
<p>If you have any trouble, get help on <a href='https://geteso.org'>geteso.org</a>.</p>
<hr/>

<ul class='form'>
<li><label>Forum title</label> <input id='forumTitle' name='forumTitle' tabindex='1' type='text' class='text' placeholder="e.g. Simon's Krav Maga Forum" value='<?php echo @$_POST["forumTitle"]; ?>'/>
<?php if (isset($install->errors["forumTitle"])): ?><div class='warning msg'><?php echo $install->errors["forumTitle"]; ?></div><?php endif; ?></li>

<li><label>Forum description</label> <input id='forumDescription' name='forumDescription' tabindex='1' type='text' class='text' placeholder="e.g. Learn about Krav Maga." value='<?php echo @$_POST["forumDescription"]; ?>'/>
<?php if (isset($install->errors["forumDescription"])): ?><div class='warning msg'><?php echo $install->errors["forumDescription"]; ?></div><?php endif; ?></li>

<li><label>Default language</label> <div><select id='language' name='language' tabindex='2'>
<?php foreach ($install->languages as $language) echo "<option value='$language'" . ((!empty($_POST["language"]) ? $_POST["language"] : "English (casual)") == $language ? " selected='selected'" : "") . ">$language</option>"; ?>
</select><br/>
<small>More language packs are <a href='https://geteso.org/languages'>available for download</a>.</small></div></li>
</ul>

<hr/>
<p>The software needs a database to store all your forum's data in, such as conversations and posts. If you're unsure of any of these details, you may need to ask your hosting provider.</p>

<?php if (isset($install->errors["mysql"])): ?><div class='warning msg'><?php echo $install->errors["mysql"]; ?></div><?php endif; ?>

<ul class='form'>
<li><label>MySQL host address</label> <input id='mysqlHost' name='mysqlHost' tabindex='3' type='text' class='text' autocomplete='off' value='<?php echo isset($_POST["mysqlHost"]) ? $_POST["mysqlHost"] : "localhost"; ?>'/></li>

<li><label>MySQL username</label> <input id='mysqlUser' name='mysqlUser' tabindex='4' type='text' class='text' placeholder='esoman' autocomplete='off' value='<?php echo @$_POST["mysqlUser"]; ?>'/></li>

<li><label>MySQL password</label> <input id='mysqlPass' name='mysqlPass' tabindex='5' type='password' class='text' autocomplete='off' value='<?php echo @$_POST["mysqlPass"]; ?>'/></li>

<li><label>MySQL database</label> <input id='mysqlDB' name='mysqlDB' tabindex='6' type='text' class='text' placeholder='esodb' autocomplete='off' value='<?php echo @$_POST["mysqlDB"]; ?>'/></li>
</ul>

<hr/>
<p>The software will use the following information to set up your administrator account on your forum.</p>

<ul class='form'>
<li><label>Administrator username</label> <input id='adminUser' name='adminUser' tabindex='7' type='text' class='text' placeholder='Simon' autocomplete='username' value='<?php echo @$_POST["adminUser"]; ?>'/>
<?php if (isset($install->errors["adminUser"])): ?><div class='warning msg'><?php echo $install->errors["adminUser"]; ?></div><?php endif; ?></li>
	
<li><label>Administrator email</label> <input id='adminEmail' name='adminEmail' tabindex='8' type='text' class='text' placeholder='simon@example.com' autocomplete='email' value='<?php echo @$_POST["adminEmail"]; ?>'/>
<?php if (isset($install->errors["adminEmail"])): ?><span class='warning msg'><?php echo $install->errors["adminEmail"]; ?></span><?php endif; ?></li>
	
<li><label>Administrator password</label> <input id='adminPass' name='adminPass' tabindex='9' type='password' class='text' autocomplete='new-password' value='<?php echo @$_POST["adminPass"]; ?>'/>
<?php if (isset($install->errors["adminPass"])): ?><span class='warning msg'><?php echo $install->errors["adminPass"]; ?></span><?php endif; ?></li>
	
<li><label>Confirm password</label> <input id='adminConfirm' name='adminConfirm' tabindex='10' type='password' class='text' autocomplete='new-password' value='<?php echo @$_POST["adminConfirm"]; ?>'/>
<?php if (isset($install->errors["adminConfirm"])): ?><span class='warning msg'><?php echo $install->errors["adminConfirm"]; ?></span><?php endif; ?></li>
</ul>

<br/>
<a href='#advanced' onclick='toggleAdvanced();return false' title='What, you&#39;re too cool for the normal settings?' tabindex='11'>Advanced options</a>
<hr class='aboveToggle'/>
<div id='advanced'>

<?php if (isset($install->errors["tablePrefix"])): ?><p class='warning msg'><?php echo $install->errors["tablePrefix"]; ?></p><?php endif; ?>

<ul class='form'>
<li><label>MySQL table prefix</label> <input name='tablePrefix' id='tablePrefix' tabindex='12' type='text' class='text' autocomplete='off' value='<?php echo isset($_POST["tablePrefix"]) ? $_POST["tablePrefix"] : "et_"; ?>'/></li>

<li><label>Base URL</label> <input name='baseURL' type='text' tabindex='13' class='text' autocomplete='off' value='<?php echo isset($_POST["baseURL"]) ? $_POST["baseURL"] : $install->suggestBaseUrl(); ?>'/></li>

<li><label>Use friendly URLs</label> <input name='friendlyURLs' type='checkbox' tabindex='14' class='checkbox' value='1' checked='<?php echo (!empty($_POST["friendlyURLs"]) or $install->suggestFriendlyUrls()) ? "checked" : ""; ?>'/></li>
</ul>

<hr/>

<input type='hidden' name='showAdvanced' id='showAdvanced' value='<?php echo $_POST["showAdvanced"]; ?>'/>
<script type='text/javascript'>
// <![CDATA[
function toggleAdvanced() {
	toggle(document.getElementById("advanced"), {animation: "verticalSlide"});
	document.getElementById("showAdvanced").value = document.getElementById("advanced").showing ? "1" : "";
	if (document.getElementById("advanced").showing) {
		animateScroll(document.getElementById("advanced").offsetTop + document.getElementById("advanced").offsetHeight + getClientDimensions()[1]);
		document.getElementById("tablePrefix").focus();
	}
}
<?php if (empty($_POST["showAdvanced"])): ?>hide(document.getElementById("advanced"));<?php endif; ?>
// ]]>
</script>
</div>

<p id='footer' style='margin:0'><input type='submit' tabindex='15' value='Next step &#155;' class='button'/></p>
<?php break;


// Show an installation error.
case "install": ?>
<h1><img src='logo.svg' alt=''/>Uh oh! It's a fatal error...</h1>
<p class='warning msg'>The forum installer encountered an error.</p>
<p>The installer has encountered a nasty error which is making it impossible to install a forum on your server. But don't feel down, <strong>here are a few things you can try</strong>:</p>
<ul>
<li><strong>Try again.</strong> Everyone makes mistakes: maybe the computer made one this time.</li>
<li><strong>Go back and check your settings.</strong> In particular, make sure your database information is correct.</li>
<li><strong>Get help.</strong> Go on <a href='https://geteso.org'>geteso.org</a> to see if anyone else is having the same problem as you are. If not, open a new issue, including the error details below.</li>
</ul>

<a href='#' onclick='toggleError();return false'>Show error information</a>
<hr class='aboveToggle'/>
<div id='error'>
<?php echo $install->errors[1]; ?>
<hr/>
</div>
<script type='text/javascript'>
// <![CDATA[
function toggleError() {
	toggle(document.getElementById("error"), {animation: "verticalSlide"});
}
hide(document.getElementById("error"));
// ]]>
</script>
<p id='footer' style='margin:0'>
<input type='submit' class='button' value='&#139; Go back' name='back'/>
<input type='submit' class='button' value='Try again'/>
</p>
<?php break;


// Finish!
case "finish": ?>
<h1><img src='logo.svg' alt=''/>Congratulations!</h1>
<p>Your forum has been installed, and it should be ready to go.</p>
<p>It's highly recommended that you <strong>remove the <code>install</code> folder</strong> to secure your forum.</p>

<a href='javascript:toggleAdvanced()'>Show advanced information</a>
<hr class='aboveToggle'/>
<div id='advanced'>
<strong>Queries run</strong>
<pre>
<?php if (isset($_SESSION["queries"]) and is_array($_SESSION["queries"]))
	foreach ($_SESSION["queries"] as $query) echo sanitizeHTML($query) . ";<br/><br/>"; ?>
</pre>
<hr/>
</div>
<script type='text/javascript'>
// <![CDATA[
function toggleAdvanced() {
	toggle(document.getElementById("advanced"), {animation: "verticalSlide"});
}
hide(document.getElementById("advanced"));
// ]]>
</script>
<p style='text-align:center' id='footer'><input type='submit' class='button' value='Take me to my forum!' name='finish'/></p>
<?php break;

}
?>

</div>
</form>

</body>
</html>
