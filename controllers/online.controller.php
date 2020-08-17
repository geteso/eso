<?php
// online.controller.php
// Fetches a list of members currently online, ready to be displayed in the view.

if (!defined("IN_ESO")) exit;

class online extends Controller {
	
var $view = "online.view.php";

function init()
{
	global $language, $config;
	
	// Set the title and make sure this page isn't indexed.
	$this->title = $language["Online members"];
	$this->eso->addToHead("<meta name='robots' content='noindex, noarchive'/>");
	
	// Fetch a list of members who have been logged in the members table as 'online' in the last $config["userOnlineExpire"] seconds.
	$this->online = $this->eso->db->query("SELECT memberId, name, avatarFormat, IF(color>{$this->eso->skin->numberOfColors},{$this->eso->skin->numberOfColors},color), account, lastSeen, lastAction FROM {$config["tablePrefix"]}members WHERE UNIX_TIMESTAMP()-{$config["userOnlineExpire"]}<lastSeen ORDER BY lastSeen DESC");
	$this->numberOnline = $this->eso->db->numRows($this->online);
}
	
}

?>
