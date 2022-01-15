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
?>

<fieldset id='adminbasic'>
<legend><?php echo $language["Registration settings"];?></legend>

<form action='<?php echo makeLink("admin", "members"); ?>' id='registrationSettings' method='post'>
<input type='hidden' name='token' value='<?php echo $_SESSION["token"]; ?>'/>

<ul class='form membersForm'>

<li><label><?php echo $language["Require verification"]; ?><br/></label>
<div><select name='requireVerification'><?php
foreach ($this->registrationSettings as $v)
echo "<option value='$v'" . ($config["registrationRequireVerification"] == $v ? " selected='selected'" : "") . ">$v</option>";	
?></select></div></li>

<li><label class='checkbox'><?php echo $language["Allow registration"]; ?></label>
<div><input type='checkbox' class='checkbox' name='registrationOpen' value='1'<?php echo !empty($config["registrationOpen"]) ? " checked='checked'" : ""; ?>/></div></li>

<li><label></label> <span class='button'><input type='submit' name='saveMembersSettings' value='<?php echo $language["Save changes"]; ?>'/></span></li>

</ul>

</form>
</fieldset>
