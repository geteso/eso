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
 * Forum settings view: displays forum settings and an interface where an
 * admin can change them, along with the forum logo and icon.
 */
if (!defined("IN_ESO")) exit;
?>

<fieldset id='adminbasic'>
<legend><?php echo $language["Basic settings"];?></legend>

<form action='<?php echo makeLink("admin", "settings"); ?>' id='basicSettings' method='post'>
<input type='hidden' name='token' value='<?php echo $_SESSION["token"]; ?>'/>

<ul class='form settingsForm'>

<li><label><?php echo $language["Forum title"]; ?></label>
<div><input type='text' class='text' name='forumTitle' value='<?php echo $config["forumTitle"]; ?>'/></div></li>

<li><label><?php echo $language["Forum description"]; ?></label>
<div><input type='text' class='text' name='forumDescription' value='<?php echo $config["forumDescription"]; ?>'/></div></li>

<li><label><?php echo $language["Default forum language"]; ?><br/></label>
<div><select name='forumLanguage'><?php
foreach ($this->languages as $v)
echo "<option value='$v'" . ($config["language"] == $v ? " selected='selected'" : "") . ">$v</option>";	
?></select><br/><small><?php echo $language["languagePackInfo"]; ?></small></div></li>

<li><label class='checkbox'><?php echo $language["Use friendly URLs"]; ?></label>
<div><input type='checkbox' class='checkbox' name='useFriendlyURLs' value='1'<?php echo !empty($config["useFriendlyURLs"]) ? " checked='checked'" : ""; ?>/></div></li>

<li><label class='checkbox'><?php echo $language["Show forum description"]; ?></label>
<div><input type='checkbox' class='checkbox' name='showForumDescription' value='1'<?php echo !empty($config["showForumDescription"]) ? " checked='checked'" : ""; ?>/></div></li>

<li><label></label> <span class='button'><input type='submit' name='saveSettings' value='<?php echo $language["Save changes"]; ?>'/></span></li>

</ul>

</form>
</fieldset>

<fieldset>
<legend><?php echo $language["Forum logo"]; ?></legend>

<div class='msg info'><?php echo $language["logoInfo"]; ?></div>

<form action='<?php echo makeLink("admin", "settings"); ?>' id='settingsLogo' method='post' enctype='multipart/form-data'>
<input type='hidden' name='token' value='<?php echo $_SESSION["token"]; ?>'/>

<ul class='form logoForm'>

<li><label><?php echo $language["Current logo"]; ?></label>
<div><img src='<?php echo $this->eso->skin->getForumLogo();?>'/></div></li>

<li><label for='logoUpload' class='radio'>
<input type='radio' class='radio' value='upload' name='logo[type]' id='logoUpload'/>
<?php echo $language["Upload a logo from your computer"]; ?></label>
<input name='logoUpload' type='file' class='text' size='20' onchange='getById("upload").checked="true"'/></li>

<li><label for='logoUrl' class='radio'>
<input type='radio' class='radio' value='url' name='logo[type]' id='logoUrl'/>
<?php echo $language["Enter the web address of a logo"]; ?></label>
<input name='logo[url]' type='text' class='text' onkeypress='getById("url").checked="true"' value=''/></li>

<li><label for='logoNone' class='radio'>
<input type='radio' class='radio' value='none' name='logo[type]' id='logoNone'/>
<?php echo $language["Use default logo"]; ?></label></li>

<li><label></label><span class='button'>
<input type='submit' name='changeLogo' value='<?php echo $language["Change logo"]; ?>'/></span></li>

</ul>

</form>
</fieldset>

<fieldset>
<legend><?php echo $language["Forum icon"]; ?></legend>

<div class='msg info'><?php echo $language["iconInfo"]; ?></div>

<form action='<?php echo makeLink("admin", "settings"); ?>' id='settingsIcon' method='post' enctype='multipart/form-data'>
<input type='hidden' name='token' value='<?php echo $_SESSION["token"]; ?>'/>

<ul class='form iconForm'>

<li><label><?php echo $language["Current icon"]; ?></label>
<div><img src='<?php echo $this->eso->skin->getForumIcon(); ?>'/></div></li>

<li><label for='iconUpload' class='radio'>
<input type='radio' class='radio' value='upload' name='icon[type]' id='iconUpload'/>
<?php echo $language["Upload an icon from your computer"]; ?></label>
<input name='iconUpload' type='file' class='text' size='20' onchange='getById("upload").checked="true"'/></li>

<li><label for='iconUrl' class='radio'>
<input type='radio' class='radio' value='url' name='icon[type]' id='iconUrl'/>
<?php echo $language["Enter the web address of an icon"]; ?></label>
<input name='icon[url]' type='text' class='text' onkeypress='getById("url").checked="true"' value=''/></li>

<li><label for='iconNone' class='radio'>
<input type='radio' class='radio' value='none' name='icon[type]' id='iconNone'/>
<?php echo $language["Use default icon"]; ?></label></li>

<li><label></label><span class='button'>
<input type='submit' name='changeIcon' value='<?php echo $language["Change icon"]; ?>'/></span></li>

</ul>

</form>
</fieldset>