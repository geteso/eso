<?php
// ajax.php
// Fires events required for pages to load and display.

define("IN_ESO", 1);
define("AJAX_REQUEST", 1);

// Basic page initialization.
require "lib/init.php";

// Set up the action controller.
if (isset($_GET["controller"])) {
	$eso->action = strtolower($_GET["controller"]);
	
	// Does this controller exist?
	if (!in_array($eso->action, $eso->allowedActions) or !file_exists(dirname(__FILE__) . "/controllers/$eso->action.controller.php")) exit;
	
	// Require and set it up.
	require_once "controllers/$eso->action.controller.php";
	$eso->controller = new $eso->action;
	$eso->controller->eso =& $eso;
}

// If none was specified, use the main controller.
else {
	$eso->controller =& $eso;
	$eso->action = "eso";
}

// Include the custom.php file.
if (file_exists("config/custom.php")) include "config/custom.php";

// Run plugin init() functions.  These will hook onto controllers and add things like language definitions.
foreach ($eso->plugins as $plugin) $plugin->init();

// Forum software initialization.
$eso->init();

// Now we're going to collect the result from the controller's ajax() function.
$controllerResult = null;

// Are we still logged in?  If not, display a "been logged out" message/form.
if (!empty($_POST["loggedInAs"]) and empty($eso->user["name"])) {
	$eso->message("beenLoggedOut", false, array($_POST["loggedInAs"], "<input id='loginMsgPassword' type='password' class='text' onkeypress='if(event.keyCode==13)document.getElementById(\"loginMsgSubmit\").click()'/> 
<input type='submit' value='{$language["Log in"]}' onclick='Ajax.login(document.getElementById(\"loginMsgPassword\").value);return false' id='loginMsgSubmit'/> <input type='button' value='{$language["Cancel"]}' onclick='Ajax.dismissLoggedOut()'/>"));
}

// Everything's fine; we're still logged in.  Proceed with normal page actions.
else {
	// Initialize the controller.
	$eso->controller->init();
	// Run the controller's ajax function.
	$controllerResult = $eso->controller->ajax();
}

// $result is the variable that we will pass through json() and output for parsing.
$result = array("messages" => array(), "result" => $controllerResult);

// If the token the user has is invalid, send them a new one.
if (isset($_POST["token"]) and $_POST["token"] != $_SESSION["token"]) $result["token"] = $_SESSION["token"];

$eso->callHook("ajaxFinish", array(&$result));

// Format and collect all messages from the session into the $result["messages"] array.
if (count($_SESSION["messages"])) {
	foreach ($_SESSION["messages"] as $k => $v) {
		if (!isset($messages[$v["message"]])) continue;
		$m = $messages[$v["message"]];
		if (!empty($v["arguments"])) $m["message"] = is_array($v["arguments"]) ? vsprintf($m["message"], $v["arguments"]) : sprintf($m["message"], $v["arguments"]);
		$result["messages"][$v["message"]] = array($m["class"], $m["message"], $v["disappear"]);
	}
}

// Output the array.
header("Content-type: text/plain; charset={$language["charset"]}");
if (!empty($config["gzipOutput"]) and !ob_start("ob_gzhandler")) ob_start();
echo json($result);
ob_flush();

// Clear the messages array.
$_SESSION["messages"] = array();

?>
