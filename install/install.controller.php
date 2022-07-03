<?php
/**
 * This file is part of the eso project, a derivative of esoTalk.
 * It has been modified by several contributors.  (contact@geteso.org)
 * Copyright (C) 2022 geteso.org.  <https://geteso.org>
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */
if (!defined("IN_ESO")) exit;

/**
 * Install controller: performs all installation tasks - checks server
 * environment, runs installation queries, creates configuration files...
 */
class Install extends Database {

var $step;
var $config;
var $errors = array();
var $queries = array();

// Initialize: perform an action depending on what step the user is at in the installation.
function init()
{
	// Determine which step we're on:
	// If there are fatal errors, then remain on the fatal error step.
	// Otherwise, use the step in the URL if it's available.
	// Otherwise, default to the warning check step.
	if ($this->errors = $this->fatalChecks()) $this->step = "fatalChecks";
	elseif (@$_GET["step"]) $this->step = $_GET["step"];
	else $this->step = "warningChecks";
	
	switch ($this->step) {
		
		// If on the warning checks step and there are no warnings or the user has clicked "Next", go to the next step.
		case "warningChecks":
			if (!($this->errors = $this->warningChecks()) or isset($_POST["next"])) $this->step("info");
			break;
			
		
		// On the "Specify setup information" step, handle the form processing.
		case "info":
		
			// Prepare a list of language packs in the ../languages folder.
			$this->languages = array();
			if ($handle = opendir("../languages")) {
			    while (false !== ($v = readdir($handle))) {
					if (!in_array($v, array(".", "..")) and substr($v, -4) == ".php" and $v[0] != ".") {
						$v = substr($v, 0, strrpos($v, "."));
						$this->languages[] = $v;
					}
				}
			}
			
			// If the form has been submitted...
			if (isset($_POST["forumTitle"])) {
				
				// Validate the form data - do not continue if there were errors!
				if ($this->errors = $this->validateInfo()) return;
				
				// Put all the POST data into the session and proceed to the install step.
				$_SESSION["install"] = array(
					"forumTitle" => $_POST["forumTitle"],
					"forumDescription" => $_POST["forumDescription"],
					"language" => $_POST["language"],
					"mysqlHost" => $_POST["mysqlHost"],
					"mysqlUser" => $_POST["mysqlUser"],
					"mysqlPass" => $_POST["mysqlPass"],
					"mysqlDB" => $_POST["mysqlDB"],
					"adminUser" => $_POST["adminUser"],
					"adminEmail" => $_POST["adminEmail"],
					"adminPass" => $_POST["adminPass"],
					"adminConfirm" => $_POST["adminConfirm"],
					"tablePrefix" => $_POST["tablePrefix"],
					"baseURL" => $_POST["baseURL"],
					"friendlyURLs" => $_POST["friendlyURLs"]
				);
				$this->step("install");
			}
			
			// If the form hasn't been submitted but there's form data in the session, fill out the form with it.
			elseif (isset($_SESSION["install"])) $_POST = $_SESSION["install"];
			break;
			
		
		// Run the actual installation.
		case "install":
		
			// Go back to the previous step if it hasn't been completed.
			if (isset($_POST["back"]) or empty($_SESSION["install"])) $this->step("info");
			
			// Fo the installation. If there are errors, do not continue.
			if ($this->errors = $this->doInstall()) return;
			
			// Log queries to the session and proceed to the final step.
			$_SESSION["queries"] = $this->queries;
			$this->step("finish");
			break;
			
		
		// Finalise the installation and redirect to the forum.
		case "finish":
		
			// If they clicked the 'go to my forum' button, log them in as the administrator and redirect to the forum.
			if (isset($_POST["finish"])) {
				include "../config/config.php";
				$user = $_SESSION["user"];
				session_destroy();
				session_name("{$config["cookieName"]}_Session");
				session_start();
				$_SESSION["user"] = $user;
				header("Location: ../");
				exit;
			}
			// Lock the installer.
			if (($handle = fopen("lock", "w")) === false)
				$this->errors[1] = "Your forum can't seem to lock the installer. Please manually delete the install folder, otherwise your forum's security will be vulnerable.";
			else fclose($handle);
	}

}

// Obtain the hardcoded version of eso (ESO_VERSION).
function getVersion()
{
	include "../config.default.php";
	$version = ESO_VERSION;
	return $version;
}

// Generate a default value for the baseURL based on server environment variables.
function suggestBaseUrl()
{
	$dir = substr($_SERVER["PHP_SELF"], 0, strrpos($_SERVER["PHP_SELF"], "/"));
	$dir = substr($dir, 0, strrpos($dir, "/"));
	if (array_key_exists("HTTPS", $_SERVER) and $_SERVER["HTTPS"] === "on") $baseURL = "https://{$_SERVER["HTTP_HOST"]}{$dir}/";
	else $baseURL = "http://{$_SERVER["HTTP_HOST"]}{$dir}/";
	return $baseURL;
}

// Generate a default value for whether or not to use friendly URLs, depending on if the REQUEST_URI variable is available.
function suggestFriendlyUrls()
{
	return !empty($_SERVER["REQUEST_URI"]);
}

// Perform a MySQL query, and log it.
function query($query)
{	
	$result = mysql_query($query, $this->link);
	$this->queries[] = $query;
	return $result;
}

// Perform the installation: run installation queries, and write configuration files.
function doInstall()
{
	global $config;
	
	// Make sure the base URL has a trailing slash.
	if (substr($_SESSION["install"]["baseURL"], -1) != "/") $_SESSION["install"]["baseURL"] .= "/";
	
	// Make sure the language exists.
	if (!file_exists("../languages/{$_SESSION["install"]["language"]}.php"))
		$_SESSION["install"]["language"] = "English (casual)";
	
	// Prepare the $config variable with the installation settings.
	$config = array(
		"mysqlHost" => desanitize($_SESSION["install"]["mysqlHost"]),
		"mysqlUser" => desanitize($_SESSION["install"]["mysqlUser"]),
		"mysqlPass" => desanitize($_SESSION["install"]["mysqlPass"]),
		"mysqlDB" => desanitize($_SESSION["install"]["mysqlDB"]),
		"tablePrefix" => desanitize($_SESSION["install"]["tablePrefix"]),
		"forumTitle" => $_SESSION["install"]["forumTitle"],
		"forumDescription" => $_SESSION["install"]["forumDescription"],
		"language" => $_SESSION["install"]["language"],
		"baseURL" => $_SESSION["install"]["baseURL"],
		"emailFrom" => "do_not_reply@{$_SERVER["HTTP_HOST"]}",
		"cookieName" => preg_replace(array("/\s+/", "/[^\w]/"), array("_", ""), desanitize($_SESSION["install"]["forumTitle"])),
		"useFriendlyURLs" => !empty($_SESSION["install"]["friendlyURLs"]),
		"useModRewrite" => !empty($_SESSION["install"]["friendlyURLs"]) and function_exists("apache_get_modules") and in_array("mod_rewrite", apache_get_modules())
	);
	
	// Connect to the MySQL database.
	$this->connect($config["mysqlHost"], $config["mysqlUser"], $config["mysqlPass"], $config["mysqlDB"]);
	
	// Run the queries one by one and halt if there's an error!
	include "queries.php";
	foreach ($queries as $query) {
		if (!$this->query($query)) return array(1 => "<code>" . sanitizeHTML($this->error()) . "</code><p><strong>The query that caused this error was</strong></p><pre>" . sanitizeHTML($query) . "</pre>");
	}
	
	// Write the $config variable to config.php.
	writeConfigFile("../config/config.php", '$config', $config);
	
	// Write the plugins.php file, which contains plugins enabled by default.
	$enabledPlugins = array("Emoticons");
	if ((extension_loaded("gd") or extension_loaded("gd2")) and function_exists("imagettftext"))
		$enabledPlugins[] = "Captcha";
	if (!file_exists("../config/plugins.php")) writeConfigFile(PATH_CONFIG."/plugins.php", '$config["loadedPlugins"]', $enabledPlugins);
	
	// Write the skin.php file, which contains the enabled skin, and custom.php.
	if (!file_exists("../config/skin.php")) writeConfigFile(PATH_CONFIG."/skin.php", '$config["skin"]', "Plastic");
	if (!file_exists("../config/custom.php")) writeFile(PATH_CONFIG."/custom.php", '<?php
if (!defined("IN_ESO")) exit;
// Any language declarations, messages, or anything else custom to this forum goes in this file.
// Examples:
// $language["My settings"] = "Preferences";
// $messages["incorrectLogin"]["message"] = "Oops! The login details you entered are incorrect. Did you make a typo?";
?>');
	// Write custom.css and index.html as empty files (if they're not already there.)
	if (!file_exists("../config/custom.css")) writeFile(PATH_CONFIG."/custom.css", "");
	if (!file_exists("../config/index.html")) writeFile(PATH_CONFIG."/index.html", "");
	
	// Write the versions.php file with the current version.
	include "../config.default.php";
	writeConfigFile("../config/versions.php", '$versions', array("eso" => ESO_VERSION));
	
	// Write a .htaccess file if they are using friendly URLs (and mod_rewrite).
	if ($config["useModRewrite"]) {
		writeFile(PATH_ROOT."/.htaccess", "# Generated by eso (https://geteso.org)
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^(.*)$ index.php/$1 [QSA,L]
</IfModule>");
	}
	
	// Write a robots.txt file.
	writeFile(PATH_ROOT."/robots.txt", "User-agent: *
Disallow: /search/
Disallow: /online/
Disallow: /join/
Disallow: /forgot-password/
Disallow: /conversation/new/
Disallow: /site.webmanifest/
Sitemap: {$config["baseURL"]}sitemap.php");
	
	// Prepare to log in the administrator.
	// Don't actually log them in, because the current session gets renamed during the final step.
	$_SESSION["user"] = array(
		"memberId" => 1,
		"name" => $_SESSION["install"]["adminUser"],
		"account" => "Administrator",
		"color" => $color,
		"emailOnPrivateAdd" => false,
		"emailOnStar" => false,
		"language" => $_SESSION["install"]["language"],
		"avatarAlignment" => "alternate",
		"avatarFormat" => "",
		"disableJSEffects" => false
	);
}

// Validate the information entered in the 'Specify setup information' form.
function validateInfo()
{
	$errors = array();

	// Forum title must contain at least one character.
	if (!strlen($_POST["forumTitle"])) $errors["forumTitle"] = "Your forum title must consist of at least one character";

	// Forum description also must contain at least one character.
	if (!strlen($_POST["forumDescription"])) $errors["forumDescription"] = "Your forum description must consist of at least one character";
	
	// Username must not be reserved, and must not contain special characters.
	if (in_array(strtolower($_POST["adminUser"]), array("guest", "member", "members", "moderator", "moderators", "administrator", "administrators", "suspended", "everyone", "myself"))) $errors["adminUser"] = "The name you have entered is reserved and cannot be used";
	if (!strlen($_POST["adminUser"])) $errors["adminUser"] = "You must enter a name";
	if (preg_match("/[" . preg_quote("!/%+-", "/") . "]/", $_POST["adminUser"])) $errors["adminUser"] = "You can't use any of these characters in your name: ! / % + -";
	
	// Email must be valid.
	if (!preg_match("/^[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i", $_POST["adminEmail"])) $errors["adminEmail"] = "You must enter a valid email address";
	
	// Password must be at least 6 characters.
	if (strlen($_POST["adminPass"]) < 6) $errors["adminPass"] = "Your password must be at least 6 characters";
	
	// Password confirmation must match.
	if ($_POST["adminPass"] != $_POST["adminConfirm"]) $errors["adminConfirm"] = "Your passwords do not match";
	
	// Try and connect to the database.
	if (!$this->connect($_POST["mysqlHost"], $_POST["mysqlUser"], $_POST["mysqlPass"], $_POST["mysqlDB"])) $errors["mysql"] = "The installer could not connect to the MySQL server. The error returned was:<br/> " . $this->error();
	
	// Check to see if there are any conflicting tables already in the database.
	// If there are, show an error with a hidden input. If the form is submitted again with this hidden input,
	// proceed to perform the installation regardless.
	elseif ($_POST["tablePrefix"] != @$_POST["confirmTablePrefix"] and !count($errors)) {
		$theirTables = array();
		$result = $this->query("SHOW TABLES");
		while (list($table) = $this->fetchRow($result)) $theirTables[] = $table;
		$ourTables = array("{$_POST["tablePrefix"]}conversations", "{$_POST["tablePrefix"]}posts", "{$_POST["tablePrefix"]}status", "{$_POST["tablePrefix"]}members", "{$_POST["tablePrefix"]}tags");
		$conflictingTables = array_intersect($ourTables, $theirTables);
		if (count($conflictingTables)) {
			$_POST["showAdvanced"] = true;
			$errors["tablePrefix"] = "The installer has detected that there is another installation of the software in the same MySQL database with the same table prefix. The conflicting tables are: <code>" . implode(", ", $conflictingTables) . "</code>.<br/><br/>To overwrite this installation, click 'Next step' again. <strong>All data will be lost.</strong><br/><br/>If you wish to create another installation alongside the existing one, <strong>change the table prefix</strong>.<input type='hidden' name='confirmTablePrefix' value='{$_POST["tablePrefix"]}'/>";
		}
	}
	
	if (count($errors)) return $errors;
}

// Redirect to a specific step.
function step($step)
{
	header("Location: index.php?step=$step");
	exit;
}

// Check for fatal errors.
function fatalChecks()
{
	$errors = array();
	
	// Make sure the installer is not locked.
	if (@$_GET["step"] != "finish" and file_exists("lock")) $errors[] = "<strong>Your forum is already installed.</strong><br/><small>To reinstall your forum, you must remove <strong>install/lock</strong>.</small>";
	
	// Check the PHP version.
	if (!version_compare(PHP_VERSION, "4.3.0", ">=")) $errors[] = "Your server must have <strong>PHP 4.3.0 or greater</strong> installed to run your forum.<br/><small>Please upgrade your PHP installation (preferably to version 5) or request that your host or administrator upgrade the server.</small>";
	
	// Check for the MySQL extension.
	if (!extension_loaded("mysql")) $errors[] = "You must have <strong>MySQL 5.7 or greater</strong> installed and the <a href='http://php.net/manual/en/mysql.installation.php' target='_blank'>MySQL extension enabled in PHP</a>.<br/><small>Please install/upgrade both of these requirements or request that your host or administrator install them.</small>";
	
	// Check file permissions.
	$fileErrors = array();
	$filesToCheck = array("", "avatars/", "plugins/", "skins/", "config/", "install/", "upgrade/");
	foreach ($filesToCheck as $file) {
		if ((!file_exists("../$file") and !@mkdir("../$file")) or (!is_writable("../$file") and !@chmod("../$file", 0777))) {
			$realPath = realpath("../$file");
			$fileErrors[] = $file ? $file : substr($realPath, strrpos($realPath, "/") + 1) . "/";
		}
	}
	if (count($fileErrors)) $errors[] = "The following files/folders are not writeable: <strong>" . implode("</strong>, <strong>", $fileErrors) . "</strong>.<br/><small>To resolve this, you must navigate to these files/folders in your FTP client and <strong>chmod</strong> them to <strong>777</strong> or <strong>755</strong> (recommended).</small>";
	
	// Check for PCRE UTF-8 support.
	if (!@preg_match("//u", "")) $errors[] = "<strong>PCRE UTF-8 support</strong> is not enabled.<br/><small>Please ensure that your PHP installation has PCRE UTF-8 support compiled into it.</small>";
	
	// Check for the gd extension.
	if (!extension_loaded("gd") and !extension_loaded("gd2")) $errors[] = "The <strong>GD extension</strong> is not enabled.<br/><small>This is required to save avatars and generate captcha images. Get your host or administrator to install/enable it.</small>";
	
	if (count($errors)) return $errors;
}

// Perform checks which will throw a warning.
function warningChecks()
{
	$errors = array();
	
	// We don't like register_globals!
	if (ini_get("register_globals")) $errors[] = "PHP's <strong>register_globals</strong> setting is enabled.<br/><small>While your forum can run with this setting on, it is recommended that it be turned off to increase security and to prevent your forum from having problems.</small>";
	
	// Can we open remote URLs as files?
	if (!ini_get("allow_url_fopen")) $errors[] = "The PHP setting <strong>allow_url_fopen</strong> is not on.<br/><small>Without this, avatars cannot be uploaded directly from remote websites.</small>";
	
	// Check for safe_mode.
	if (ini_get("safe_mode")) $errors[] = "<strong>Safe mode</strong> is enabled.<br/><small>This could potentially cause problems with your forum, but you can still proceed if you cannot turn it off.</small>";
	
	if (count($errors)) return $errors;
}

}

?>
