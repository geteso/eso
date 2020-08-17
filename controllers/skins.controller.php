<?php
// skins.controller.php
// Handles the installation and switching of skins.

if (!defined("IN_ESO")) exit;

class skins extends Controller {

var $view = "skins.view.php";
var $skins = array();

// Get all the skins into an array.
function init()
{
	// Non-admins aren't allowed here.
	if (!$this->eso->user["admin"]) redirect("");
	
	global $language, $config;
	$this->title = $language["Skins"];
	
	// If the 'add a new skin' form has been submitted, attempt to install the uploaded skin.
	if (isset($_FILES["installSkin"]) and $this->eso->validateToken(@$_POST["token"])) $this->installSkin();
	
	// Get the installed skins and their details by reading the skins/ directory.
	if ($handle = opendir("skins")) {
	    while (false !== ($file = readdir($handle))) {
		
			// Make sure the skin is valid, and set up its class.
	        if ($file[0] != "." and is_dir("skins/$file") and file_exists("skins/$file/skin.php") and (include_once "skins/$file/skin.php") and class_exists($file)) {
	        	$skin = new $file;
				if (file_exists("skins/$file/preview.jpg")) $preview = "preview.jpg";
				elseif (file_exists("skins/$file/preview.png")) $preview = "preview.png";
				elseif (file_exists("skins/$file/preview.gif")) $preview = "preview.gif";
				else $preview = "";
				$this->skins[$file] = array(
					"selected" => $config["skin"] == $file,
					"name" => $skin->name,
					"version" => $skin->version,
					"author" => $skin->author,
					"preview" => $preview
				);
			}
			
	    }
	    closedir($handle);
	}
	ksort($this->skins);
	
	// Activate a skin in necessary.
	if (!empty($_GET["q2"]) and $this->eso->validateToken(@$_GET["token"])) $this->changeSkin($_GET["q2"]);
}

// Change the skin.
function changeSkin($skin)
{
	// Make sure the skin we're trying to change to exists!
	if (!array_key_exists($skin, $this->skins)) return false;
	
	// Write the skin configuration file...
	writeConfigFile("config/skin.php", '$config["skin"]', $skin);
	
	// ...and reload the page! All done!
	redirect("skins");
}

// Install an uploaded skin.
function installSkin()
{
	// If the uploaded file has any errors, don't proceed.
	if ($_FILES["installSkin"]["error"]) {
		$this->eso->message("invalidSkin");
		return false;
	}

	// Temorarily move the uploaded skin into the skins directory so that we can read it.
	if (!move_uploaded_file($_FILES["installSkin"]["tmp_name"], "skins/{$_FILES["installSkin"]["name"]}")) {
		$this->eso->message("notWritable", false, "skins/");
		return false;
	}

	// Unzip the skin. If we can't, show an error.
	if (!($files = unzip("skins/{$_FILES["installSkin"]["name"]}", "skins/"))) $this->eso->message("invalidSkin");
	else {
		
		// Loop through the files in the zip and make sure it's a valid skin.
		$directories = 0; $skinFound = false;
		foreach ($files as $k => $file) {

			// Strip out annoying Mac OS X files!
			if (substr($file["name"], 0, 9) == "__MACOSX/" or substr($file["name"], -9) == ".DS_Store") {
				unset($files[$k]);
				continue;
			}

			// If the zip has more than one base directory, it's not a valid skin.
			if ($file["directory"] and substr_count($file["name"], "/") < 2) $directories++;

			// Make sure there's an actual skin file in there.
			if (substr($file["name"], -8) == "skin.php") $skinFound = true;
		}

		// OK, this skin in valid!
		if ($skinFound and $directories == 1) {

			// Loop through skin files and write them to the skins directory.
			$error = false;
			foreach ($files as $k => $file) {

				// Make a directory if it doesn't exist!
				if ($file["directory"] and !is_dir("skins/{$file["name"]}")) mkdir("skins/{$file["name"]}");

				// Write a file.
				elseif (!$file["directory"]) {
					if (!writeFile("skins/{$file["name"]}", $file["content"])) {
						$this->eso->message("notWritable", false, "skins/{$file["name"]}");
						$error = true;
						break;
					}
				}
			}
			
			// Everything copied over correctly - success!
			if (!$error) $this->eso->message("skinAdded");
		}
		
		// Hmm, something went wrong. Show an error.
		else $this->eso->message("invalidSkin");
	}
	
	// Delete the temporarily uploaded skin file.
	unlink("skins/{$_FILES["installSkin"]["name"]}");
}

}

?>
