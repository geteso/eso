<?php
/**
 * This file is part of the eso project, a derivative of esoTalk.
 * It has been modified by several contributors.  (contact@geteso.org)
 * Copyright (C) 2022 geteso.org.  <https://geteso.org>
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */
if (!defined("IN_ESO")) exit;

/**
 * Online controller: fetches a list of members currently online, ready
 * to be displayed in the view.
 */
class OnlineController extends Controller {
	
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
