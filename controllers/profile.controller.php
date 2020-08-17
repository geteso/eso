<?php
// profile.controller.php
// Fetches member information to be displayed on the profile view.

if (!defined("IN_ESO")) exit;

class profile extends Controller {

var $view = "profile.view.php";
var $member = array();
var $sections = array();

// Set up the page and fetch the member data.
function init()
{
	if (defined("AJAX_REQUEST")) return;
	
	// If there's a member ID specified in the URL, use that.  Otherwise, use the currently logged in user.
	if (!empty($_GET["q2"])) $memberId = (int)$_GET["q2"];
	elseif ($this->eso->user) $memberId = $this->eso->user["memberId"];
	else $memberId = false;
	
	// Get the member data: if the member doesn't exist, redirect to the index.
	if (!$memberId or !($this->member = $this->getMember($memberId))) {
		$this->eso->message("memberDoesntExist", false);
		redirect("");
	}
	
	// Set the title.
	global $language;
	$this->title = sprintf($language["profile"], $this->member["name"]);
	
	$this->callHook("init");
}

// Run AJAX actions.
function ajax()
{
	if ($return = $this->callHook("ajax", null, true)) return $return;
}

// Add a section to the profile page.
function addSection($sectionHTML, $position = false)
{
	addToArray($this->sections, $sectionHTML, $position);
}

// Fetch a member's details from the database.
function getMember($memberId)
{
	global $config;
	if (empty($memberId)) return false;

	// Construct the query components.
	$select = array("m.memberId AS memberId", "m.name AS name", "IF(m.color>{$this->eso->skin->numberOfColors},{$this->eso->skin->numberOfColors},m.color) AS color", "m.account AS account", "m.lastSeen AS lastSeen", "IF(" . (time() - $config["userOnlineExpire"]) . "<m.lastSeen,m.lastAction,'') AS lastAction", "m.avatarFormat AS avatarFormat",
		"(SELECT MIN(time) FROM {$config["tablePrefix"]}posts p WHERE p.memberId=m.memberId) AS firstPosted",
		"(SELECT COUNT(*) FROM {$config["tablePrefix"]}conversations c WHERE c.startMember=m.memberId) AS conversationsStarted",
		"(SELECT COUNT(DISTINCT conversationId) FROM {$config["tablePrefix"]}posts p WHERE p.memberId=m.memberId) AS conversationsParticipated",
		"(SELECT COUNT(*) FROM {$config["tablePrefix"]}posts p WHERE p.memberId=m.memberId) AS postCount"
	);
	$from = array("{$config["tablePrefix"]}members m");
	$where = array("m.memberId=$memberId");

	// Put together the query components.
	$components = array("select" => $select, "from" => $from, "where" => $where);	
	
	$this->callHook("beforeGetMember", array(&$components));
	
	// Construct and run the query!
	$query = $this->eso->db->constructSelectQuery($components);
	$result = $this->eso->db->query($query);
	if (!$this->eso->db->numRows($result)) return false;

	// Get all the details from the query into an array.
	$member = $this->eso->db->fetchAssoc($result);
	
	$this->callHook("afterGetMember", array(&$member));
	
	return $member;
}

}

?>
