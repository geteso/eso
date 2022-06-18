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
define("IN_ESO", 1);

/**
 * Index page: fires events requires for the page to load and display.
 * Initializes controllers and plugins, works out what to display, and
 * displays it.
 */

// Make sure a default timezone is set... silly PHP 5.
if (ini_get("date.timezone") == "") date_default_timezone_set("GMT");

// Define directory constants.
if (!defined("PATH_ROOT")) define("PATH_ROOT", dirname(__FILE__));
if (!defined("PATH_CONFIG")) define("PATH_CONFIG", PATH_ROOT."/config");
if (!defined("PATH_CONTROLLERS")) define("PATH_CONTROLLERS", PATH_ROOT."/controllers");
if (!defined("PATH_LANGUAGES")) define("PATH_LANGUAGES", PATH_ROOT."/languages");
if (!defined("PATH_LIBRARY")) define("PATH_LIBRARY", PATH_ROOT."/lib");
if (!defined("PATH_PLUGINS")) define("PATH_PLUGINS", PATH_ROOT."/plugins");
if (!defined("PATH_SKINS")) define("PATH_SKINS", PATH_ROOT."/skins");
if (!defined("PATH_UPLOADS")) define("PATH_UPLOADS", PATH_ROOT."/uploads");
if (!defined("PATH_VIEWS")) define("PATH_VIEWS", PATH_ROOT."/views");

// Basic page initialization.
require PATH_LIBRARY."/init.php";

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
$eso->controller = $factory->make($eso->action);
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
if (!empty($config["gzipOutput"]) or !ob_start("ob_gzhandler")) ob_start();
$eso->render();
ob_flush();

// Clear messages from the session.
$_SESSION["messages"] = array();

?>
