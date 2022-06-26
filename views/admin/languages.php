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
 * Languages view: displays a list of languages.
 */
if (!defined("IN_ESO")) exit;
?>

<div id='admin'>
	
<ul class='menu'>
<li><a href='<?php echo makeLink("admin"); ?>'><?php echo $language["Dashboard"];?></a></li>
<li><a href='<?php echo makeLink("admin", "settings"); ?>'><?php echo $language["Forum settings"];?></a></li>
<li class='active'><a href='<?php echo makeLink("admin", "languages"); ?>'><?php echo $language["Languages"];?></a></li>
<li><a href='<?php echo makeLink("admin", "members"); ?>'><?php echo $language["Member-plural"];?></a></li>
<li><a href='<?php echo makeLink("admin", "plugins"); ?>'><?php echo $language["Plugins"];?></a></li>
<li><a href='<?php echo makeLink("admin", "skins"); ?>'><?php echo $language["Skins"];?></a></li>

<li class='separator'></li>

</ul>

<div class='inner'>

<fieldset id='addPlugin'>
<legend><?php echo $language["Add a new language pack"]; ?></legend>
<?php echo $this->eso->htmlMessage("downloadLanguagePacks", "https://geteso.org/languages"); ?>
<form action='<?php echo makeLink("languages"); ?>' method='post' enctype='multipart/form-data'>
<input type='hidden' name='token' value='<?php echo $_SESSION["token"]; ?>'/>
<ul class='form'>
<li><label><?php echo $language["Upload a language pack"]; ?></label> <input name='installLanguage' type='file' class='text' size='20'/></li>
<li><label></label> <?php echo $this->eso->skin->button(array("value" => $language["Add language pack"])); ?></li>
</ul>
</form>
</fieldset>

<?php // If there are installed language packs to display.
if (count($this->languages)): ?>

<fieldset id='adminbasic'>
<legend><?php echo $language["Installed language packs"];?></legend>

<form action='<?php echo makeLink("languages"); ?>' id='basicSettings' method='post'>
<input type='hidden' name='token' value='<?php echo $_SESSION["token"]; ?>'/>

<ul class='form settingsForm'>

<li><label><?php echo $language["Default forum language"]; ?><br/></label>
<div><select name='forumLanguage'><?php
foreach ($this->languages as $v)
echo "<option value='$v'" . ($config["language"] == $v ? " selected='selected'" : "") . ">$v</option>";	
?></select><br/><small><?php echo $language["languagePackInfo"]; ?></small></div></li>

<li><label></label> <span class='button'><input type='submit' name='saveSettings' value='<?php echo $language["Save changes"]; ?>'/></span></li>

</ul>

</form>
</fieldset>

<?php // Otherwise if there are no language packs installed, show a message.
else: ?>
<fieldset id='addPlugin'>
<legend><?php echo $language["Installed language packs"]; ?></legend>
<?php echo $this->eso->htmlMessage("noLanguagesInstalled"); ?>
</fieldset>
<?php endif; ?>

</div>

<div class='clear'></div>
</div>