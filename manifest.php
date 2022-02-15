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
 * Manifest: generates a web app manifest and outputs site.webmanifest.
 */

// Include our config files.
require "config.default.php";
@include "config/config.php";
@include "config/skin.php";
if (!isset($config)) exit;
// Combine config.default.php and config/config.php into $config.  The latter will override the former.
$config = array_merge($defaultConfig, $config);

require "lib/functions.php";
require "lib/classes.php";

if (!$config["forumIcon"]) $icon = "skins/{$config["skin"]}/icon.png";
else $icon = "{$config["forumIcon"]}";

// If site.webmanifest is recent then we'll just use the cached version.
// Otherwise, we'll regenerate the manifest.
if (!file_exists("site.webmanifest") or filemtime("site.webmanifest") < time() - $config["manifestCacheTime"] - 200) {
		writeFile("site.webmanifest",
		"{
		\"name\": \"{$config["forumTitle"]}\",
		\"description\": \"{$config["forumDescription"]}\",
		\"icons\": [
		{
				\"src\": \"{$config["baseURL"]}" . $icon . "\",
				\"sizes\": \"256x256\"
		}
		],
		\"start_url\": \"{$config["baseURL"]}\",
		\"display\": \"{$config["manifestDisplay"]}\"
}");
}

header("Content-type: application/manifest+json");
$handle = fopen("site.webmanifest", "r");
echo fread($handle, filesize("site.webmanifest"));
fclose($handle);

?>
