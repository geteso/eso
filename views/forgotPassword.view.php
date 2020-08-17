<?php
// forgotPassword.view.php
// Displays a forum with an e-mail field to initiate a password retrieval or a form to set a new password.

if(!defined("IN_ESO"))exit;
?>
<fieldset id='forgot-pass'>
<legend><?php echo $language["Forgot your password"];?></legend>
<?php

// Display a form with an email field.
if(!$this->setPassword):
echo $this->eso->htmlMessage("forgotPassword");?>

<form id='forgot-password' action='<?php echo makeLink("forgot-password");?>' method='post'>
<ul class='form'>
	
<li><label><?php echo $language["Enter your email"];?></label>
<input type='text' autocomplete='email' value='' name='email' id='email' class='text'/></li>

<li><label id='lbl-fgps'></label> <?php echo $this->eso->skin->button(array("value"=>$language["Recover password"]));?></li>

</ul>
</form>

<?php

// Display a form to set a new password.
else:
echo $this->eso->htmlMessage("setNewPassword");?>

<form id='forgot-pass-reset' action='<?php echo makeLink("forgot-password",@$_GET["q2"]);?>' method='post'>
<ul class='form'>

<li><label><?php echo $language["New password"];?></label>
<input type='password' autocomplete='new-password' value='' name='password' id='password' class='text'/>
<?php if(isset($this->errors["password"])):echo $this->eso->htmlMessage($this->errors["password"]);endif;?></li>

<li><label><?php echo $language["Confirm password"];?></label>
<input type='password' autocomplete='new-password' value='' name='confirm' id='confirm' class='text'/>
<?php if(isset($this->errors["confirm"])):echo $this->eso->htmlMessage($this->errors["confirm"]);endif;?></li>

<li><label id='lbl-fgcf'></label>
<?php echo $this->eso->skin->button(array("name"=>"changePassword","value"=>$language["Change password"]));?></li>

</ul>
</form>

<?php endif;?>
</fieldset>
