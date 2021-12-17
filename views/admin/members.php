<?php
// settings.php
// Displays a list of plugins and their settings.

if (!defined("IN_ESO")) exit;
?>

<fieldset id='adminbasic'>
<legend><?php echo $language["Registration settings"];?></legend>

<form action='<?php echo makeLink("admin", "settings"); ?>' id='registrationSettings' method='post'>
<input type='hidden' name='token' value='<?php echo $_SESSION["token"]; ?>'/>

<ul class='form membersForm'>

<li><label><?php echo $language["Require verification"]; ?><br/></label>
<div><select name='requireVerification'><?php
foreach ($this->languages as $v)
echo "<option value='$v'" . ($config["registrationRequireVerification"] == $v ? " selected='selected'" : "") . ">$v</option>";	
?></select></div></li>

<li><label class='checkbox'><?php echo $language["Allow registration"]; ?></label>
<div><input type='checkbox' class='checkbox' name='registrationOpen' value='1'<?php echo !empty($config["registrationOpen"]) ? " checked='checked'" : ""; ?>/></div></li>

<li><label></label> <span class='button'><input type='submit' name='saveSettings' value='<?php echo $language["Save changes"]; ?>'/></span></li>

</ul>

</form>
</fieldset>
