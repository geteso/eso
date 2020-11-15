<?php
// index.php
// Fires events required for page contents to be deciphered and shown.

define("IN_ESO", 1);

// Basic page initialization.
require "lib/init.php";

// Set up the action controller.
$q1 = strtolower(@$_GET["q1"]);

// If the first address parameter is numeric, assume the conversation controller.
if (is_numeric($q1)) {
	$_GET["q4"] = @$_GET["q3"];
	$_GET["q3"] = @$_GET["q2"];
	$_GET["q2"] = @$_GET["q1"];
	$_GET["q1"] = $q1 = "conversation";
}
// Does this controller exist?  If not, just use the search action.
$eso->action = in_array($q1, $eso->allowedActions) ? $q1 : $eso->action = "search";

// Include and set up the controller corresponding to the chosen action.
require "controllers/$eso->action.controller.php";
$className = str_replace("-", "", $eso->action);
$eso->controller = new $className;
$eso->controller->eso =& $eso;

// Include the custom.php file.
if (file_exists("config/custom.php")) include "config/custom.php";

// Run plugin init() functions.  These will hook onto controllers and add things like language definitions.
foreach ($eso->plugins as $plugin) $plugin->init();

// Initialize eso.
$eso->init();

// Initialize the controller.
$eso->controller->init();

// Show the page.
header("Content-type: text/html; charset={$language["charset"]}");
if (!empty($config["gzipOutput"]) and !ob_start("ob_gzhandler")) ob_start();
$eso->render();
ob_flush();

// Clear messages from the session.
$_SESSION["messages"] = array();

?>
