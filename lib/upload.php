<?php
// upload.php
// Provides a way to easily validate and manage uploaded files.
// Typically, an upload should be validated using the getUploadedFile() function, which will return the temporary filename of the uploaded file.
// This can then be saved with saveAs() or saveAsImage().

class Upload {

function iniToBytes($value)
{
	$l = substr($value, -1);
	$ret = substr($value, 0, -1);
	switch(strtoupper($l)){
		case "P":
			$ret *= 1024;
		case "T":
			$ret *= 1024;
		case "G":
			$ret *= 1024;
		case "M":
			$ret *= 1024;
		case "K":
			$ret *= 1024;
		break;
	}
	return $ret;
}

// Get the maximum file upload size in bytes.
function maxUploadSize()
{
	return min($this->iniToBytes(ini_get("post_max_size")), $this->iniToBytes(ini_get("upload_max_filesize")));
}

// Validate an uploaded file and return its temporary file name.
function getUploadedFile($key, $allowedTypes = array())
{
	$error = false;

	// If the uploaded file doesn't exist, then we have to fail.
	if (!isset($_FILES[$key]) or !is_uploaded_file($_FILES[$key]["tmp_name"]))
		$error = "fileUploadFailed";

	// Otherwise, check for an error.
	else {
		$file = $_FILES[$key];
		switch ($file["error"]) {
			case 1:
			case 2:
				$error = sprintf("fileUploadTooBig", ini_get("upload_max_filesize"));
				break;
			case 3:
			case 4:
			case 6:
			case 7:
			case 8:
				$error = "fileUploadFailed";
		}
	}

	// If there was no error, return the path to the uploaded file.
	if (!$error) return $file["tmp_name"];
}

// Save an uploaded file to the specified destination.
function saveAs($source, $destination)
{
	// Attempt to move the uploaded file to the destination. If we can't, throw an exception.
	if (!move_uploaded_file($source, $destination))
//		throw new Exception(sprintf("message.fileUploadFailedMove", $destination));
		return false;

	return $destination;
}

// todo: saveasImage(), convert an uploaded file to a safe image with size restraints

}
