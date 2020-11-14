<?php
// plastic/skin.php
// Plastic skin file.

if (!defined("ESO")) exit;

class Plastic extends Skin {

var $name = "Plastic";
var $version = "1.0";
var $author = "eso";
var $numberOfColors = 27;

// Add stylesheets and a favicon to the page header.
function init()
{
	global $config;
	$this->eso->addCSS("skins/{$config["skin"]}/styles.css");
	$this->eso->addCSS("skins/{$config["skin"]}/ie6.css", "ie6");
	$this->eso->addCSS("skins/{$config["skin"]}/ie7.css", "ie7");
	$this->eso->addToHead("<link rel='shortcut icon' type='image/ico' href='skins/{$config["skin"]}/favicon.ico'/>");
	// Preload the vector buttons to evade flickering upon hover.
	$this->eso->addToHead("
	<link rel='preload' type='image/ico' href='skins/{$config["skin"]}/buttons/button.svg' as='image'/>
	<link rel='preload' type='image/ico' href='skins/{$config["skin"]}/buttons/button-big.svg' as='image'/>
	<link rel='preload' type='image/ico' href='skins/{$config["skin"]}/buttons/button-hover.svg' as='image'/>
	<link rel='preload' type='image/ico' href='skins/{$config["skin"]}/buttons/button-hover-big.svg' as='image'/>
	<link rel='preload' type='image/ico' href='skins/{$config["skin"]}/buttons/button-active.svg' as='image'/>
	<link rel='preload' type='image/ico' href='skins/{$config["skin"]}/buttons/button-active-big.svg' as='image'/>
	<link rel='preload' type='image/ico' href='skins/{$config["skin"]}/buttons/button-disabled.svg' as='image'/>
	<link rel='preload' type='image/ico' href='skins/{$config["skin"]}/buttons/button-disabled-big.svg' as='image'/>
	<link rel='preload' type='image/ico' href='skins/{$config["skin"]}/icons/profile.svg' as='image'/>
	");
}

// Generate button HTML.
function button($attributes)
{
	$class = $id = $style = ""; $attr = " type='submit'";
	foreach ($attributes as $k => $v) {
		if ($k == "class") $class = " $v";
		elseif ($k == "id") $id = " id='$v'";
		elseif ($k == "style") $style = " style='$v'";
		else $attr .= " $k='$v'";
	}
	return "<span class='button$class'$id$style><input$attr/></span>";
}

}

?>
