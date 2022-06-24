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
 * Plugins view: displays a list of plugins and their settings.
 */
if (!defined("IN_ESO")) exit;
?>

<?php // If it's okay to upload plugin packages, add a new plugin form.
if (!empty($config["uploadPackages"])): ?>
<fieldset id='addPlugin'>
<legend><?php echo $language["Add a new plugin"]; ?></legend>
<?php echo $this->eso->htmlMessage("downloadPlugins", "https://geteso.org/plugins"); ?>
<form action='<?php echo makeLink("admin", "plugins"); ?>' method='post' enctype='multipart/form-data'>
<input type='hidden' name='token' value='<?php echo $_SESSION["token"]; ?>'/>
<ul class='form'>
<li><label><?php echo $language["Upload a plugin"]; ?></label> <input name='installPlugin' type='file' class='text' size='20'/></li>
<li><label></label> <?php echo $this->eso->skin->button(array("value" => $language["Add plugin"])); ?></li>
</ul>
</form>
</fieldset>

<?php // Otherwise if uploading packages is disabled, show a message.
else: ?>
<fieldset id='addPlugin'>
<legend><?php echo $language["Add a new plugin"]; ?></legend>
<?php echo $this->eso->htmlMessage("noUploadingPackages"); ?>
</fieldset>
<?php endif; ?>

<?php // If there are installed plugins to display.
if (count($this->plugins)): ?>

<fieldset id='plugins'>
<legend><?php echo $language["Installed plugins"]; ?></legend>

<script type='text/javascript'>
// <![CDATA[
// Toggle whether a plugin is enabled or not.
function toggleEnabled(id, enabled) {
	Ajax.request({
		"url": eso.baseURL + "ajax.php?controller=admin&q2=plugins",
		"post": "action=toggle&id=" + encodeURIComponent(id) + "&enabled=" + (enabled ? "1" : "0"),
		"success": function() {
			if (this.messages) getById("plugin-" + id + "-checkbox").checked = !enabled;
			else getById("plugin-" + id).className = "plugin" + (enabled ? " enabled" : "");
			// window.location = window.location;
		}
	});
}
// Toggle the visibility of a plugin's settings.
function toggleSettings(id) {
	for (var i in plugins) {
		if (plugins[i] != id && getById("plugin-" + plugins[i] + "-settings") && getById("plugin-" + plugins[i] + "-settings").showing)
			hide(getById("plugin-" + plugins[i] + "-settings"), {animation: "verticalSlide"});
	}
	toggle(getById("plugin-" + id + "-settings"), {animation: "verticalSlide"});
}
var plugins = [];
// ]]>
</script>

<ul>
	
<?php // Loop through each plugin and output its information.
foreach ($this->plugins as $k => $plugin): ?>
<li id='plugin-<?php echo $k; ?>' class='plugin<?php if ($plugin["loaded"]): ?> enabled<?php endif; ?>'>
<div class='controls'>
<?php if (!empty($plugin["settings"])): ?><a href='javascript:toggleSettings("<?php echo $k; ?>");void(0)'><?php echo $language["settings"]; ?></a><?php endif; ?>
</div>
<a href='<?php echo makeLink("admin", "plugins", "?toggle=$k", "&token={$_SESSION["token"]}"); ?>' class='toggle'><?php echo $plugin["loaded"] ? $language["Disable"] : $language["Enable"]; ?></a>	
<strong><?php echo $plugin["name"]; ?></strong>
<small><?php printf($language["version"], $plugin["version"]); ?> <?php printf($language["author"], $plugin["author"]); ?></small> <small><?php echo $plugin["description"]; ?></small>

<?php // Output plugin settings.
if (!empty($plugin["settings"])): ?>
<div id='plugin-<?php echo $k; ?>-settings' class='settings'>
<form action='<?php echo makeLink("admin", "plugins"); ?>' method='post'>
<input type='hidden' name='plugin' value='<?php echo $k; ?>'/>
<input type='hidden' name='token' value='<?php echo $_SESSION["token"]; ?>'/>
<?php echo $plugin["settings"]; ?>
</form>
</div>
<?php endif; ?>
<script type='text/javascript'>// <![CDATA[
plugins.push("<?php echo $k; ?>");
<?php if (!empty($plugin["settings"])):
	if (@$_POST["plugin"] != $k): ?>hide(getById("plugin-<?php echo $k; ?>-settings"));<?php
	else: ?>getById("plugin-<?php echo $k; ?>-settings").showing = true;<?php endif;
endif; ?> 
// ]]></script>
</li>
<?php endforeach; ?>

</ul>
</fieldset>

<?php // Otherwise if there are no plugins installed, show a message.
else: ?>
<fieldset id='addPlugin'>
<legend><?php echo $language["Add a new plugin"]; ?></legend>
<?php echo $this->eso->htmlMessage("noPluginsInstalled"); ?>
</fieldset>
<?php endif; ?>
