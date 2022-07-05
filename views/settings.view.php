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
 * Settings view: displays an interface where the user can change their
 * avatar, color, password/email, and other settings.
 */
if(!defined("IN_ESO"))exit;
?>
<div id='settings'>

<fieldset id='appearance'>
<legend><?php echo $language["Appearance settings"];?></legend>

<div class='p <?php echo $this->eso->user["avatarAlignment"]=="right"?"r ":"l ";?>c<?php echo $this->eso->user["color"];?>' id='preview'>
<div class='parts'><div>
<div class='hdr'><div class='pInfo'>
<div class='thumb'><a href='<?php echo makeLink("profile",$this->eso->user["memberId"]);?>'><img src='<?php echo $this->eso->getAvatar($this->eso->user["memberId"],$this->eso->user["avatarFormat"],"thumb");?>' alt=''/></a></div>
<h3><?php echo $this->eso->user["name"];?></h3></div></div>
<div class='body'>

<?php // Color palette. ?>
<div id='palette'><table cellspacing='0' cellpadding='0'><tr>
<?php for($i=1;$i<=$this->eso->skin->numberOfColors;$i++):?>
<td><a href='<?php echo makeLink("settings","?changeColor=$i","&token={$_SESSION["token"]}");?>' onclick='Settings.changeColor(<?php echo $i;?>);return false' id='color-<?php echo $i;?>' class='c<?php echo $i;?><?php if($this->eso->user["color"]==$i)echo " selected";?>'></a></td>
<?php endfor;?>
</tr></table></div>

<?php // If it's okay to upload avatars, add an avatar selection form.
if (!empty($config["changeAvatar"])): ?>
<form action='<?php echo makeLink("settings");?>' id='settingsAvatar' method='post' enctype='multipart/form-data'>
<input type='hidden' name='token' value='<?php echo $_SESSION["token"];?>'/>
<ul class='form'>

<?php // Upload an avatar. ?>
<li>
<label for='iconUpload' class='radio'>
<input type='radio' class='radio' value='upload' name='avatar[type]' id='iconUpload'<?php if(@$_POST["avatar"]["type"]=="upload")echo " checked='checked'";?>/>
<?php echo $language["Upload an avatar"];?>
</label>
<input id='upl-ava' name='avatarUpload' type='file' class='text' size='20' onchange='document.getElementById("upload").checked="true"'/>
</li>

<?php // Otherwise if avatar uploading is disabled, show a message.
else: ?>
<?php echo $this->eso->htmlMessage("avatarDisabled"); ?>
<?php endif; ?>

<?php // Get an avatar from URL.
if(ini_get("allow_url_fopen")):?>
<li>
<label for='iconUrl' class='radio'>
<input type='radio' class='radio' value='url' name='avatar[type]' id='iconUrl'<?php if(@$_POST["avatar"]["type"]=="url")echo " checked='checked'";?>/>
<?php echo $language["Enter the web address of an avatar"];?>
</label>
<input id='upl-url' name='avatar[url]' type='text' class='text' onkeypress='document.getElementById("url").checked="true"' value='<?php if(!empty($_POST["avatar"]["url"]))echo $_POST["avatar"]["url"];?>'/>
</li>
<?php endif;?>

<?php // Clear the avatar. ?>
<li>
<label for='none' class='radio'>
<input type='radio' class='radio' value='none' name='avatar[type]' id='none'<?php if(@$_POST["avatar"]["type"]=="none")echo " checked='checked'";?>/>
<?php echo $language["No avatar"];?>
</label>
</li>

<li><label id='lbl-avt'></label> <?php echo $this->eso->skin->button(array("name"=>"changeAvatar","value"=>$language["Change avatar"]));?></li>

</ul>
</form>

</div>
</div></div>
<div class='avatar'><img src='<?php
echo $this->eso->getAvatar($this->eso->user["memberId"],$this->eso->user["avatarFormat"],$this->eso->user["avatarAlignment"]=="right"?"r":"l"),"?",time();
?>' alt=''/></div>
<div class='clear'></div>
</div>

</fieldset>

<?php // Output a form with elements defined in the settings controller. ?>
<form action='<?php echo makeLink("settings");?>' method='post' enctype='multipart/form-data'>
<input type='hidden' name='token' value='<?php echo $_SESSION["token"];?>'/>

<?php
// Loop through the fieldsets in the form.
foreach($this->form as $id=>$fieldset):
    if(is_array($fieldset)):
        echo "<fieldset id='$id'>
<legend><a href='#' onclick='Settings.toggleFieldset(\"$id\");return false'>{$fieldset["legend"]}</a></legend>
<ul class='form' id='{$id}Form'>";
       ksort($fieldset);
    
       foreach($fieldset as $k=>$field):
            if($k=="legend" or $k=="hidden")continue;
            if(is_array($field)):
                echo "<li>{$field["html"]}";
                if(!empty($field["message"]))echo $this->eso->htmlMessage($field["message"]);
                echo "</li>";
            else:echo $field;endif;
        endforeach;
    
        echo "</ul></fieldset>";
        if(!empty($fieldset["hidden"])):
            echo "<script type='text/javascript'>Settings.hideFieldset(\"$id\")</script>";endif;
    else:echo $fieldset;endif;
endforeach;
?>
<?php echo $this->eso->skin->button(array("value"=>$language["Save changes"],"name"=>"submit","class"=>"big submit"));?>

</form>

<?php // Output the change my password or email form. ?>
<form action='<?php echo makeLink("settings");?>' method='post'>
<input type='hidden' name='token' value='<?php echo $_SESSION["token"];?>'/>
<fieldset id='settingsPassword'>
<legend><a href='#' onclick='Settings.toggleFieldset("settingsPassword");return false'><?php echo $language["Change my password or email"];?></a></legend>
<ul class='form' id='settingsPasswordForm'>
	
<li>
<label><?php echo $language["New password"];?> <small><?php echo $language["optional"];?></small></label> <input type='password' name='settingsPasswordEmail[new]' class='text' autocomplete='new-password' value='<?php echo @$_POST["settingsPasswordEmail"]["new"];?>'/>
<?php if(!empty($this->messages["new"]))echo $this->eso->htmlMessage($this->messages["new"]);?>
</li>

<li>
<label><small><?php echo $language["Confirm password"];?></small></label> <input type='password' name='settingsPasswordEmail[confirm]' class='text' autocomplete='new-password' value=''/>
<?php if(!empty($this->messages["confirm"]))echo $this->eso->htmlMessage($this->messages["confirm"]);?>
</li>

<li>
<label><?php echo $language["New email"];?> <small><?php echo $language["optional"];?></small></label> <input type='text' name='settingsPasswordEmail[email]' class='text' autocomplete='email' value='<?php echo @$_POST["settingsPasswordEmail"]["email"];?>'/>
<?php if(!empty($this->messages["email"]))echo $this->eso->htmlMessage($this->messages["email"]);?>
</li>

<li>
<label><?php echo $language["My current password"];?></label> <input type='password' name='settingsPasswordEmail[current]' class='text' autocomplete='current-password'/>
<?php if(!empty($this->messages["current"]))echo $this->eso->htmlMessage($this->messages["current"]);?>
</li>

<li><label id='lbl-pass'></label> <?php echo $this->eso->skin->button(array("value"=>$language["Save changes"],"name"=>"settingsPasswordEmail[submit]"));?></li>

</ul></fieldset>
<?php if(!count($this->messages)):?><script type='text/javascript'>Settings.hideFieldset("settingsPassword")</script><?php endif;?>
</form>

<?php // If it's okay to change names, output the change my username form.
if (!empty($config["changeUsername"])): ?>
<form action='<?php echo makeLink("settings");?>' method='post'>
<input type='hidden' name='token' value='<?php echo $_SESSION["token"];?>'/>
<fieldset id='settingsUser'>
<legend><a href='#' onclick='Settings.toggleFieldset("settingsUser");return false'><?php echo $language["Change my username"];?></a></legend>
<ul class='form' id='settingsUserForm'>

<li>
<label><?php echo $language["New username"];?> <small><?php echo $language["optional"];?></small><small style='display:block'><?php echo $language["changeYourName"];?></small></label> <input type='text' name='settingsUsername[name]' class='text' autocomplete='username' value='<?php echo @$_POST["settingsUsername"]["name"];?>'/>
<?php if(!empty($this->messages["username"]))echo $this->eso->htmlMessage($this->messages["username"]);?>
</li>

<li>
<label><?php echo $language["My current password"];?></label> <input type='password' name='settingsUsername[password]' class='text' autocomplete='current-password'/>
<?php if(!empty($this->messages["password"]))echo $this->eso->htmlMessage($this->messages["password"]);?>
</li>

<li><label id='lbl-pass'></label> <?php echo $this->eso->skin->button(array("value"=>$language["Save changes"],"name"=>"settingsUsername[submit]"));?></li>

</ul></fieldset>

<?php $this->callHook("settingsPageEnd");?>

<?php if(!count($this->messages)):?><script type='text/javascript'>Settings.hideFieldset("settingsUser")</script><?php endif;?>
</form>
<?php endif;?>

</div>
