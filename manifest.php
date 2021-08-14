<?php
// manifest.php
// Generates a web app manifest and outputs the file.

define("IN_ESO", 1);

// Include our config files.
require "config.default.php";
@include "config/config.php";
if (!isset($config)) exit;
// Combine config.default.php and config/config.php into $config.  The latter will override the former.
$config = array_merge($defaultConfig, $config);

require "lib/functions.php";
require "lib/classes.php";

// If site.webmanifest is recent then we'll just use the cached version.
// Otherwise, we'll regenerate the manifest.
if (!file_exists("site.webmanifest") or filemtime("site.webmanifest") < time() - $config["manifestCacheTime"] - 200) {
	writeFile("site.webmanifest",
	"{
	\"name\": \"{$config["forumTitle"]}\",
	\"description\": \"{$config["forumDescription"]}\",
	\"icons\": [
		{
			\"src\": \"{$config["baseURL"]} . {$eso->skin->getForumIcon()}\",
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
