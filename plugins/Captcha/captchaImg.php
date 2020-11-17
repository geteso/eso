<?php
// Captcha/captchaImg.php
// Generates a captcha image.

// Set headers to prevent caching.
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
header("Content-type: image/jpeg");

// Include the captcha configuration.
require "../../config/config.php";
if (file_exists("../../config/Captcha.php")) include "../../config/Captcha.php";
$numberOfCharacters = !empty($config["Captcha"]["numberOfCharacters"]) ? $config["Captcha"]["numberOfCharacters"] : 3;

// Start a session if one does not already exist.
if (!session_id()) {
	session_name("{$config["cookieName"]}_Session");
	session_start();
}

// Set up the captcha image.
$font = realpath("exagger8.otf");
$imgX = 30 + 30 * $numberOfCharacters;
$imgY = 40;
$img = @imagecreatetruecolor($imgX, $imgY) or die("Cannot Initialize new GD image stream");

// Generate the captcha string that the user will have to enter.
$chars = "abcdefghikmnprstuvwxy3456789";
$string = "";
for ($i = 0; $i < $numberOfCharacters; $i++) $string .= $chars[rand(0, strlen($chars) - 1)];
$_SESSION["captcha"] = md5($string);

// Draw the background of the image.
$background = imagecolorallocate($img, rand(110, 150), rand(110, 150), rand(110, 150));
imagefill($img, 0, 0, $background);

// Draw a few circles.
for ($i = 0; $i < 3; $i++) {
	$color = imagecolorallocate($img, rand(110, 150), rand(110, 150), rand(110, 150));
	imagefilledellipse($img, rand(0, $imgX), rand(0, $imgY), rand(25, $imgX), rand(25, $imgY), $color);
}

// Draw the characters.
$length = strlen($string);
for ($i = 0; $i < $length; $i++) {
	$shadow = imagecolorallocate($img, rand(100, 125), rand(100, 125), rand(100, 125));
	$color = imagecolorallocate($img, rand(50, 100), rand(50, 100), rand(50, 100));
	$angle = rand(-30, 30);
	imagettftext($img, 30, $angle, $i * 30 + 20 + 3, 30 + 3, $shadow, $font, $string[$i]);
	imagettftext($img, 30, $angle, $i * 30 + 20, 30, $color, $font, $string[$i]);
}

// Output the image!
imagejpeg($img, null, 40);
imagedestroy($img);

?>
