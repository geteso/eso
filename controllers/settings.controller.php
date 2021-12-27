<?php
// settings.controller.php
// Handles the "My settings" page.  Changes avatar, color, and handles settings forms.

if (!defined("IN_ESO")) exit;

class settings extends Controller {
	
var $view = "settings.view.php";
var $messages = array();

// Initialize: perform any necessary saving actions, and define the form contents.
function init()
{
	// If we're not logged in, go to the join page.
	if (!$this->eso->user) redirect("join");
	
	// Set the title.
	global $config, $language;
	$this->title = $language["My settings"];	
	
	// Change the user's color.
	if (!empty($_GET["changeColor"]) and (int)$_GET["changeColor"]
		and $this->eso->validateToken(@$_GET["token"])) $this->changeColor($_GET["changeColor"]);
	
	// Change the user's avatar.
	if (isset($_POST["changeAvatar"])
	 	and $this->eso->validateToken(@$_POST["token"])
		and $this->changeAvatar()) $this->eso->message("changesSaved");
	
	// Change the user's password or email.
	if (isset($_POST["settingsPasswordEmail"]["submit"])
		and $this->eso->validateToken(@$_POST["token"])
		and $this->changePasswordEmail()) {
		$this->eso->message("changesSaved");
		redirect("settings");
	}

	// Change the user's name.
	if (isset($_POST["settingsUsername"]["submit"])
		and $this->eso->validateToken(@$_POST["token"])
		and $this->changeUsername()) {
		$this->eso->message("changesSaved");
		// Unset the user's session to keep things from... breaking!
		unset($_SESSION["user"]);
		redirect("settings");
	}
	
	// Loop through the languages directory to create a string of options to go in the language <select> tag.
	$langOptions = "";
	$this->languages = $this->eso->getLanguages();
 	foreach ($this->languages as $v) {
 		$value = ($v == $config["language"]) ? "" : $v;
 		$langOptions .= "<option value='$value'" . ($this->eso->user["language"] == $value ? " selected='selected'" : "") . ">$v</option>";
	}
	
	// Create a string of options to go in the avatar alignment <select> tag.
	$avatarAlignmentOptions = "";
	$align = array(
		"alternate" => $language["on alternating sides"],
		"right" => $language["on the right"],
		"left" => $language["on the left"],
		"none" => $language["do not display avatars"]
	);
	foreach ($align as $k => $v)
		$avatarAlignmentOptions .= "<option value='$k'" . ($this->eso->user["avatarAlignment"] == $k ? " selected='selected'" : "") . ">$v</option>";
	
	// Define the elements in the settings form.
	$this->form = array(
		
		"settingsOther" => array(
			"legend" => $language["Other settings"],
			100 => array(
				"id" => "language",
				"html" => "<label>{$language["Forum language"]}</label> <select id='language' name='language'>$langOptions</select>",
				"databaseField" => "language",
				"required" => true,
				"validate" => array("Settings", "validateLanguage")
			),
			200 => array(
				"id" => "avatarAlignment",
				"html" => "<label>{$language["Display avatars"]}</label> <select id='avatarAlignment' name='avatarAlignment'>$avatarAlignmentOptions</select>",
				"databaseField" => "avatarAlignment",
				"validate" => array("Settings", "validateAvatarAlignment"),
				"required" => true
			),
			300 => array(
				"id" => "emailOnPrivateAdd",
				"html" => "<label for='emailOnPrivateAdd' class='checkbox'>{$language["emailOnPrivateAdd"]} <span class='label private'>{$language["labels"]["private"]}</span></label> <input id='emailOnPrivateAdd' type='checkbox' class='checkbox' name='emailOnPrivateAdd' value='1' " . ($this->eso->user["emailOnPrivateAdd"] ? "checked='checked' " : "") . "/>",
				"databaseField" => "emailOnPrivateAdd",
				"checkbox" => true
			),
			400 => array(
				"id" => "emailOnStar",
				"html" => "<label for='emailOnStar' class='checkbox'>{$language["emailOnStar"]} <span class='star1 starInline'>*</span></label> <input id='emailOnStar' type='checkbox' class='checkbox' name='emailOnStar' value='1' " .  ($this->eso->user["emailOnStar"] ? "checked='checked' " : "") . "/>",
				"databaseField" => "emailOnStar",
				"checkbox" => true
			),
			500 => array(
				"id" => "disableJSEffects",
				"html" => "<label for='disableJSEffects' class='checkbox'>{$language["disableJSEffects"]}</label> <input id='disableJSEffects' type='checkbox' class='checkbox' name='disableJSEffects' value='1' " .  (!empty($this->eso->user["disableJSEffects"]) ? "checked='checked' " : "") . "/>",
				"databaseField" => "disableJSEffects",
				"checkbox" => true
			)
		)
		
	);
	
	$this->callHook("init");
	
	// Save settings if the big submit button was clicked.
	if (isset($_POST["submit"]) and $this->eso->validateToken(@$_POST["token"]) and $this->saveSettings()) {
		$this->eso->message("changesSaved");
		redirect("settings");
	}	
}

// Save settings defined by the fields in the big form array.
function saveSettings()
{
	// Get the fields which we are saving into an array (we don't need fieldsets.)
	$fields = array();
	foreach ($this->form as $k => $fieldset) {
		foreach ($fieldset as $j => $field) {
			if (!is_array($field)) continue;
			$this->form[$k][$j]["input"] = @$_POST[$field["id"]];
			$fields[] = &$this->form[$k][$j];
		}
	}
		
	// Go through the fields and validate them. If a field is required, or if data has been entered (regardless of
	// whether it's required), validate it using the field's validation callback function.
	$validationError = false;
	foreach ($fields as $k => $field) {
		if ((!empty($field["required"]) or $field["input"])	and !empty($field["validate"])
			and $msg = @call_user_func_array($field["validate"], array(&$fields[$k]["input"]))) {
			$validationError = true;
			$fields[$k]["message"] = $msg;
		}
	}
	
	$this->callHook("validateForm", array(&$validationError));
	
	// If there was a validation error, don't continue.
	if ($validationError) return false;
	
	// Construct the query to save the member's settings.
	// Loop through the form fields and use their "databaseField" and "input" attributes for the query.
	$updateData = array();
	foreach ($fields as $field) {
		if (!is_array($field)) continue;
		if (!empty($field["databaseField"])) $updateData[$field["databaseField"]] = !empty($field["checkbox"])
			? ($field["input"] ? 1 : 0)
			: "'{$field["input"]}'";
	}
	
	$this->callHook("beforeSave", array(&$updateData));
	
	// Construct and execute the query!
	$updateQuery = $this->eso->db->constructUpdateQuery("members", $updateData, array("memberId" => $this->eso->user["memberId"]));
	$this->eso->db->query($updateQuery);
	
	// Update user session data according to the field "databaseField" values.
	foreach ($fields as $field) {
		if (!empty($field["databaseField"]))
			$_SESSION["user"][$field["databaseField"]] = $this->eso->user[$field["databaseField"]] = $field["input"];
	}
	
	$this->callHook("afterSave");
	
	return true;
}

function changeUsername()
{
	global $config;
	$updateData = array();

	// Are we setting a new username?
	if (!empty($_POST["settingsUsername"]["name"])) {

		// Validate the name, then add the updating part to the query.
		$name = substr($_POST["settingsUsername"]["name"], 0, 31);
		if ($error = validateName($name)) $this->messages["username"] = $error;
		else $updateData["name"] = "'{$_POST["settingsUsername"]["name"]}'";
//		$this->messages["current"] = "reenterInformation";
	}

	// Check if the user entered their old password correctly.
	if (!$this->eso->db->result("SELECT 1 FROM {$config["tablePrefix"]}members WHERE memberId={$this->eso->user["memberId"]} AND password='" . md5($config["salt"] . $_POST["settingsUsername"]["password"]) . "'", 0)) $this->messages["current"] = "incorrectPassword";

	// Everything is valid and good to go! Run the query if necessary.
	elseif (count($updateData)) {
		$query = $this->eso->db->constructUpdateQuery("members", $updateData, array("memberId" => $this->eso->user["memberId"]));
		$this->eso->db->query($query);
		$this->messages = array();
		return true;
	}

	return false;
}

// Change the user's password and/or email.
function changePasswordEmail()
{
	global $config;
	$updateData = array();
	
	// Are we setting a new password?
	if (!empty($_POST["settingsPasswordEmail"]["new"])) {
		
		// Make a copy of the raw password; the validatePassword() function will automatically format it into a hash.
		$hash = $_POST["settingsPasswordEmail"]["new"];
		if ($error = validatePassword($hash)) $this->messages["new"] = $error;
		
		// Do both of the passwords entered match?
		elseif ($_POST["settingsPasswordEmail"]["new"] != $_POST["settingsPasswordEmail"]["confirm"]) $this->messages["confirm"] = "passwordsDontMatch";
		
		// Alright, the password stuff is all good. Add the password updating part to the query.
		else $updateData["password"] = "'$hash'";
		
		// Show a 'reenter information' message next to the current password field just in case we fail later on.
		$this->messages["current"] = "reenterInformation"; 
	}
	
	// Are we setting a new email?
	if (!empty($_POST["settingsPasswordEmail"]["email"])) {
		
		// Validate the email address. If it's ok, add the updating part to the query.
		if ($error = validateEmail($_POST["settingsPasswordEmail"]["email"])) $this->messages["email"] = $error;
		else $updateData["email"] = "'{$_POST["settingsPasswordEmail"]["email"]}'";
		$this->messages["current"] = "reenterInformation";
		
	}
	
	// Check if the user entered their old password correctly.
	if (!$this->eso->db->result("SELECT 1 FROM {$config["tablePrefix"]}members WHERE memberId={$this->eso->user["memberId"]} AND password='" . md5($config["salt"] . $_POST["settingsPasswordEmail"]["current"]) . "'", 0)) $this->messages["current"] = "incorrectPassword";
	
	// Everything is valid and good to go! Run the query if necessary.
	elseif (count($updateData)) {
		$query = $this->eso->db->constructUpdateQuery("members", $updateData, array("memberId" => $this->eso->user["memberId"]));
		$this->eso->db->query($query);
		$this->messages = array();
		return true;
	}
	
	return false;
}

// Change the user's avatar.
function changeAvatar()
{
	if (empty($_POST["avatar"]["type"])) return false;
	global $config;
	
	$allowedTypes = array("image/jpeg", "image/png", "image/gif", "image/pjpeg", "image/x-png");
	
	// This is where the user's avatar will be saved, suffixed with _thumb and an extension (eg. .jpg).
	$avatarFile = "avatars/{$this->eso->user["memberId"]}";
	
	switch ($_POST["avatar"]["type"]) {
		
		// Upload an avatar from the user's computer.
		case "upload":
			
			// Check for an error submitting the file and make sure the upload is a valid image file type.
			if ($_FILES["avatarUpload"]["error"] != 0
				or !in_array($_FILES["avatarUpload"]["type"], $allowedTypes)
				or !is_uploaded_file($_FILES["avatarUpload"]["tmp_name"])) {
				$this->eso->message("avatarError");
				return false;
			}
			
			$type = $_FILES["avatarUpload"]["type"];
			$file = $_FILES["avatarUpload"]["tmp_name"];
			break;
		
		// Upload an avatar from a remote URL.
		case "url":
			
			// Make sure we can open URLs with fopen, otherwise there's no point in continuing!
			if (!ini_get("allow_url_fopen")) return false;
			
			// Remove HTML entities and spaces from the URL.
			$url = str_replace(" ", "%20", html_entity_decode($_POST["avatar"]["url"]));
			
			// Get the image's type.
			$info = @getimagesize($url);
			$type = $info["mime"];
			$file = $avatarFile;
			
			// Check the type of the image, and open file read/write handlers.
			if (!in_array($type, $allowedTypes)
				or (($rh = fopen($url, "rb")) === false)
				or (($wh = fopen($file, "wb")) === false)) {
				$this->eso->message("avatarError");
				return false;
			}
			
			// Transfer the image from the remote location to our server.
			while (!feof($rh)) {
				if (fwrite($wh, fread($rh, 1024)) === false) {
					$this->eso->message("avatarError");
					return false;
				}
			}
			fclose($rh); fclose($wh);
			break;
		
		// Unset the user's avatar.
		case "none":
		
			// If the user doesn't have an avatar, we don't need to do anything!
			if (empty($this->eso->user["avatarFormat"])) return true;
			
			// Delete the avatar and thumbnail files.
			$file = "$avatarFile.{$this->eso->user["avatarFormat"]}";
			if (file_exists($file)) @unlink($file);
			$file = "{$avatarFile}_thumb.{$this->eso->user["avatarFormat"]}";
			if (file_exists($file)) @unlink($file);
			
			// Clear the avatarFormat field in the database and session variable.
			$this->eso->db->query("UPDATE {$config["tablePrefix"]}members SET avatarFormat=NULL WHERE memberId={$this->eso->user["memberId"]}");
			$this->eso->user["avatarFormat"] = $_SESSION["user"]["avatarFormat"] = "";
			return true;
			
		default: return false;
	}
	
	// Phew, we got through all that. Now let's turn the image into a resource...
	switch ($type) {
		case "image/jpeg": case "image/pjpeg": $image = @imagecreatefromjpeg($file); break;
		case "image/x-png": case "image/png": $image = @imagecreatefrompng($file); break;
		case "image/gif": $image = @imagecreatefromgif($file);
	}
	if (!$image) {
		$this->eso->message("avatarError");
		return false;
	}
	// ...and get its dimensions.
	list($curWidth, $curHeight) = getimagesize($file);
	
	// The dimensions we'll need are the normal avatar size and a thumbnail.
	$dimensions = array("" => array($config["avatarMaxWidth"], $config["avatarMaxHeight"]), "_thumb" => array($config["avatarThumbHeight"], $config["avatarThumbHeight"]));

	// Create new destination images according to the $dimensions.
	foreach ($dimensions as $suffix => $values) {
		
		// Set the destination.
		$destination = $avatarFile . $suffix;
		
		// Delete the user's current avatar.
		if (file_exists("$destination.{$this->eso->user["avatarFormat"]}"))
			unlink("$destination.{$this->eso->user["avatarFormat"]}");
					
		if ($this->callHook("resizeAvatar", array($image, $destination, $type, $values[0], $values[1]), true)) continue;

		// If the new max dimensions exist and are smaller than the current dimensions, we're gonna want to resize.
		$newWidth = $values[0];
		$newHeight = $values[1];
		if (($newWidth or $newHeight) and ($newWidth < $curWidth or $newHeight < $curHeight)) {
			
			// Work out the resize ratio and calculate the dimensions of the new image.
			$widthRatio = $newWidth / $curWidth;
			$heightRatio = $newHeight / $curHeight;
			$ratio = ($widthRatio and $widthRatio <= $heightRatio) ? $widthRatio : $heightRatio;
			$width = $ratio * $curWidth;
			$height = $ratio * $curHeight;
			$needsToBeResized = true;
		}
		
		// Otherwise just use the current dimensions.
		else {
			$width = $curWidth;
			$height = $curHeight;
			$needsToBeResized = false;
		}

		// If it's a gif that doesn't need to be resized (and it's not a thumbnail), we move instead of resampling 
		// so as to preserve animation.
		if (!$needsToBeResized and $type == "image/gif" and $suffix != "_thumb") {
			
			// Read the gif file's contents.
			$handle = fopen($file, "r"); 
			$contents = fread($handle, filesize($file)); 
			fclose($handle);
			
			// Filter the first 256 characters, making sure there are no HTML tags of any kind.
			// We have to do this because IE6 has a major security issue where if it finds any HTML in the first 256
			// characters, it interprets the rest of the document as HTML (even though it's clearly an image!)
			$tags = array("!-", "a hre", "bgsound", "body", "br", "div", "embed", "frame", "head", "html", "iframe", "input", "img", "link", "meta", "object", "plaintext", "script", "style", "table");
			$re = array();
			foreach ($tags as $tag) {
				$part = "(?:<";
				$length = strlen($tag);
				for ($i = 0; $i < $length; $i++) $part .= "\\x00*" . $tag[$i];
				$re[] = $part . ")";
			}
			
			// If we did find any HTML tags, we're gonna have to lose the animation by resampling the image.
			if (preg_match("/" . implode("|", $re) . "/", substr($contents, 0, 255))) $needsToBeResized = true;
			
			// But if it's all safe, write the image to the avatar file!
			else writeFile($destination . ".gif", $contents);
		}

		// If this is a gif image and it needs to be resized, if it's a thumbnail, or if it's any other type of image...
		if ($needsToBeResized or $type != "image/gif" or $suffix == "_thumb") {
			
			// -waves magic wand- Now, let's create the image!
			$newImage = imagecreatetruecolor($width, $height);
			
			// Preserve the alpha for pngs and gifs.
			if (in_array($type, array("image/png", "image/gif", "image/x-png"))) {
				imagecolortransparent($newImage, imagecolorallocate($newImage, 0, 0, 0));
				imagealphablending($newImage, false);
				imagesavealpha($newImage, true);
			}
			
			// (Oh yeah, the reason we're doin' the whole imagecopyresampled() thing even for images that don't need to 
			// be resized is because it helps prevent a possible cross-site scripting attack in which the file has 
			// malicious data after the header.)
			imagecopyresampled($newImage, $image, 0, 0, 0, 0, $width, $height, $curWidth, $curHeight);
			
			// Save the image to the correct destination and format.
			switch ($type) {
				// jpeg
				case "image/jpeg": case "image/pjpeg":
					if (!imagejpeg($newImage, "$destination.jpg", 85)) $saveError = true;
					break;
				// png
				case "image/x-png": case "image/png":
					if (!imagepng($newImage, "$destination.png")) $saveError = true;
					break;
				// gif - the only way to preserve gif transparency is to save the image as a png... but we'll still 
				// pretend that it's a gif!
				case "image/gif":
					if (!imagepng($newImage, "$destination.gif")) $saveError = true;
			}
			if (!empty($saveError))  {
				$this->eso->message("avatarError");
				return false;
			}

			// Clean up.
			imagedestroy($newImage);
		}
	}
	
	// Clean up temporary stuff.
	imagedestroy($image);
	@unlink($file);
	
	// Depending on the type of image that was uploaded, update the user's avatarFormat field.
	switch ($type) {
		case "image/jpeg": case "image/pjpeg": $avatarFormat = "jpg"; break;
		case "image/x-png": case "image/png": $avatarFormat = "png"; break;
		case "image/gif": $avatarFormat = "gif";
	}
	$this->eso->db->query("UPDATE {$config["tablePrefix"]}members SET avatarFormat='$avatarFormat' WHERE memberId={$this->eso->user["memberId"]}");
	$this->eso->user["avatarFormat"] = $_SESSION["user"]["avatarFormat"] = $avatarFormat;
	
	return true;
}

// Change the user's color.
function changeColor($color)
{
	global $config;
	
	// Make sure the color exists within the current skin!
	$color = max(1, min((int)$color, $this->eso->skin->numberOfColors));

	// Update the database and session variables with the new color.
	$this->eso->db->query("UPDATE {$config["tablePrefix"]}members SET color=$color WHERE memberId={$this->eso->user["memberId"]}");
	$this->eso->user["color"] = $_SESSION["user"]["color"] = $color;
}

// Run AJAX actions.
function ajax()
{
	if ($return = $this->callHook("ajax", null, true)) return $return;

	switch ($_POST["action"]) {
		
		// Change the user's color.
		case "changeColor":
			if (!$this->eso->validateToken(@$_POST["token"])) return;
			$this->changeColor(@$_POST["color"]);
	}
}

// Add an element to the a fieldset in the form.
function addToForm($fieldset, $field, $position = false)
{
	return addToArray($this->form[$fieldset], $field, $position);
}

// Add a fieldset to the form.
function addFieldset($fieldset, $legend, $position = false)
{
	return addToArrayString($this->form, $fieldset, array("legend" => $legend), $position);
}

// Validate the avatar alignment field: it must be "alternate", "right", "left", or "none".
function validateAvatarAlignment(&$alignment)
{
	if (!in_array($alignment, array("alternate", "right", "left", "none"))) $alignment = "alternate";
}

// Validate the language field: make sure the selected language actually exists.
function validateLanguage(&$language)
{
	if (!in_array($language, $this->languages)) $language = "";
}

}

?>
