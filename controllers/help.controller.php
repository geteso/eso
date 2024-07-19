<?php
// help.controller.php
// "Help" page with info and rules.

if (!defined("IN_ESO")) exit;

class help extends Controller {

var $view = "help/help.view.php";
var $subView = "";
var $sections = array();

function init()
{
    // Add the default sections to the menu.
    $this->defaultSections = array("about", "rules", "contact");
    $this->addSection("about", "About the forum", array($this, "aboutInit"));
    $this->addSection("rules", "Global rules", array($this, "rulesInit"));
	$this->addSection("contact", "Contact us", array($this, "contactInit"));

    $this->callHook("init");

    // Work out the current section. Use the first section (Dashboard) by default.
    $this->section = defined("AJAX_REQUEST") ? @$_GET["section"] : @$_GET["q2"];
    reset($this->sections);
    if (!array_key_exists($this->section, $this->sections)) $this->section = key($this->sections);

    // Call the current section's initilization function.
    return call_user_func_array($this->sections[$this->section]["initFunction"], array(&$this));
}

function ajax()
{
    if (empty($this->sections[$this->section]["ajaxFunction"])) return;
    return call_user_func_array($this->sections[$this->section]["ajaxFunction"], array(&$this));
}

// About the forum
function aboutInit(&$aboutController)
{
    global $config;
    $this->title = translate("About");
    $this->subView = "help/about.php";

    $this->callHook("aboutInit");
}

// Global rules
function rulesInit(&$aboutController)
{
    global $config;
    $this->title = translate("Global rules");
    $this->subView = "help/rules.php";

    $this->callHook("rulesInit");
}

// Terms of service
function termsInit(&$aboutController)
{
    global $config;
    $this->title = translate("Terms of service");
    $this->subView = "help/tos.php";

    $this->callHook("termsInit");
}

// Privacy policy
function privacyInit(&$aboutController)
{
    global $config;
    $this->title = translate("Privacy policy");
    $this->subView = "help/privacy.php";

    $this->callHook("privacyInit");
}

// Contact us
function rulesInit(&$aboutController)
{
    global $config;
    $this->title = translate("Contact us");
    $this->subView = "help/contact.php";

    $this->callHook("contactInit");
}

function addSection($id, $title, $initFunction, $ajaxFunction = false, $position = false)
{
    addToArrayString($this->sections, $id, array("title" => $title, "initFunction" => $initFunction, "ajaxFunction" => $ajaxFunction), $position);
}

}

?>
