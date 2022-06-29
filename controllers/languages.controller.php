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
 * Languages controller: allows admins to upload language
 * packs to the forum.
 */

class languages extends Controller {
	
var $view = "admin/languages.php";

function init()
{	
	global $language, $config;
	$this->title = $language["Languages"];
	
	// If the "add a new language pack" form has been submitted, attempt to install the uploaded pack.
	if (isset($_FILES["installLanguage"]) and $this->eso->validateToken(@$_POST["token"])) $this->installLanguage();

    $newConfig = array();
    $this->languages = $this->eso->getLanguages();

    if (in_array(@$_POST["forumLanguage"], $this->languages)) $newConfig["language"] = $_POST["forumLanguage"];

    if (count($newConfig)) $this->writeSettingsConfig($newConfig);
}

function writeSettingsConfig($newConfigElements)
{
	include "config/config.php";
	writeConfigFile("config/config.php", '$config', array_merge($config, $newConfigElements));
	global $config;
	$config = array_merge($config, $newConfigElements);
    $this->eso->message("changesSaved");
}

// Install an uploaded language pack.
function installLanguage()
{
	// If the uploaded file has any errors, don't proceed.
	if ($_FILES["installLanguage"]["error"]) {
		$this->eso->message("invalidLanguagePack");
		return false;
	}
	
	// Move the uploaded language pack into the languages directory.
	if (!move_uploaded_file($_FILES["installLanguage"]["tmp_name"], "languages/{$_FILES["installLanguage"]["name"]}")) {
		$this->eso->message("notWritable", false, "languages/");
		return false;
	}
			
	// Everything worked correctly - success!
	// todo
	$this->eso->message("languagePackAdded");
		
	// Hmm, something went wrong. Show an error.
	else $this->eso->message("invalidLanguagePack");
}
	
}

?>