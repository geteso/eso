<?php
// Aluminum/skin.php
// Aluminum skin file.

if (!defined("IN_ESO")) exit;

class Aluminum extends Skin {

var $name = "Aluminum";
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
	// Preload the default forum logo to evade flickering upon hover.
	$this->eso->addToHead("
	<link rel='preload' href='skins/{$config["skin"]}/logo.svg' as='image'/>
	<link rel='preload' href='skins/{$config["skin"]}/icons/profile.svg' as='image'/>
	<link rel='preload' href='skins/{$config["skin"]}/icons/join.svg' as='image'/>
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
