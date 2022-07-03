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

/**
 * Dashboard view: displays forum statistics and server information to
 * admins.
 */
if (!defined("IN_ESO")) exit;
?>

<?php // Use an AJAX request to check for updates so that initial page loading isn't slow. ?>
<div id='updateMessage'></div>
<script type='text/javascript'>
// <![CDATA[
Ajax.request({
	"url": eso.baseURL + "ajax.php?controller=admin&section=dashboard",
	"post": "action=checkForUpdates",
	"success": function() {
		if (this.result) getById("updateMessage").innerHTML = this.result;
		show(getById("updateMessage"), {animation: "verticalSlide"});
	}
})
// ]]>
</script>

<?php if (file_exists("install/") and $this->eso->user["memberId"] == $config["rootAdmin"]) echo $this->eso->htmlMessage("removeDirectoryWarning", "install/"); ?>

<fieldset>
<legend><?php echo $language["Forum statistics"];?></legend>
<ul class='form stats'>

<?php foreach ($this->stats as $k => $v): ?>
<li><label><?php echo $language[$k]; ?></label>
<div><?php echo number_format($v); ?></div></li>
<?php endforeach; ?>

<?php $this->callHook("forumStatistics"); ?>
</ul>
</fieldset>

<fieldset>
<legend><?php echo $language["Server information"];?></legend>
<ul class='form stats'>

<?php foreach ($this->serverInfo as $k => $v): ?>
<li><label><?php echo $language[$k]; ?></label>
<div><?php echo $v; ?></div></li>
<?php endforeach; ?>

<?php $this->callHook("serverInformation"); ?>

</ul>
</fieldset>
