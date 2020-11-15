<?php
// settings.php
// Displays a list of plugins and their settings.

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
?></select><br/><small>Upload languages packs to the <code>languages/</code> folder to see them here.</small></div></li>

<li><label class='checkbox'><?php echo $language["Use friendly URLs"]; ?></label>
<div><input type='checkbox' class='checkbox' name='useFriendlyURLs' value='1'<?php echo !empty($config["useFriendlyURLs"]) ? " checked='checked'" : ""; ?>/></div></li>

<li><label class='checkbox'><?php echo $language["Show forum description"]; ?></label>
<div><input type='checkbox' class='checkbox' name='showForumDescription' value='1'<?php echo !empty($config["showForumDescription"]) ? " checked='checked'" : ""; ?>/></div></li>

<li><label></label> <span class='button'><input type='submit' name='saveSettings' value='Save changes'/></span></li>

</ul>

</form>
</fieldset>

<fieldset>
<legend><?php echo $language["Forum logo"]; ?></legend>

<div class='msg info'><?php echo $language["On most skins, your forum logo appears near the title in the header of your forum. Your logo will be automatically resized to be 32 pixels high."]; ?></div>

<form action='<?php echo makeLink("admin", "settings"); ?>' id='settingsLogo' method='post' enctype='multipart/form-data'>
<input type='hidden' name='token' value='<?php echo $_SESSION["token"]; ?>'/>

<ul class='form logoForm'>
		
<li><label><?php echo $language["Current logo"]; ?></label>
<div><img src='<?php echo $this->eso->skin->getForumLogo(); ?>'/></div></li>

<li><label for='upload' class='radio'>
<input type='radio' class='radio' value='upload' name='logo[type]' id='upload'/>
<?php echo $language["Upload a logo from your computer"]; ?></label>
<input name='logoUpload' type='file' class='text' size='20' onchange='getById("upload").checked="true"'/></li>

<li><label for='url' class='radio'>
<input type='radio' class='radio' value='url' name='logo[type]' id='url'/>
<?php echo $language["Enter the web address of a logo"]; ?></label>
<input name='logo[url]' type='text' class='text' onkeypress='getById("url").checked="true"' value=''/></li>

<li><label for='none' class='radio'>
<input type='radio' class='radio' value='none' name='logo[type]' id='none'/>
<?php echo $language["Use default logo"]; ?></label></li>

<li><label></label><span class='button'>
<input type='submit' name='changeLogo' value='Change logo'/></span></li>

</ul>
	
</form>
</fieldset>
