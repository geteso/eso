<?php
// forgot-password.controller.php
// Sends a user an e-mail containing a link to reset their password, and handles this link to enable the user to set a new password.

if (!defined("IN_ESO")) exit;

class forgotpassword extends Controller {

var $view = "forgotPassword.view.php";
var $title = "";
var $errors = array();
var $setPassword = false;

function init()
{
	global $language, $messages, $config;

	// If the user is logged in, kick them out.
	if ($this->eso->user) redirect("");
	
	// Set the title.
	$this->title = $language["Forgot your password"];
	
	// If a password reset token has been provided, ie. they've clicked the link in their email.
	if ($hash = @$_GET["q2"]) {
		
		// Find the user with this password reset token.  If it's an invalid token, take them back to the email form.
		$result = $this->eso->db->query("SELECT memberId FROM {$config["tablePrefix"]}members WHERE resetPassword='$hash'");
		if (!$this->eso->db->numRows($result)) redirect("forgotPassword");
		list($memberId) = $this->eso->db->fetchRow($result);
		
		$this->setPassword = true;
		
		// If the change password form has been submitted.
		if (isset($_POST["changePassword"])) {
			
			// Validate the passwords they entered.
			$password = @$_POST["password"];
			$confirm = @$_POST["confirm"];
			if ($error = validatePassword(@$_POST["password"])) $this->errors["password"] = $error;
			if ($password != $confirm) $this->errors["confirm"] = "passwordsDontMatch";
			
			// If it's all good, update the password in the database, show a success message, and redirect.
			if (!count($this->errors)) {
				$salt = $this->eso->db->query("SELECT salt FROM {$config["tablePrefix"]}members WHERE memberId=$memberId");
				$passwordHash = md5($salt . $password);
				$this->eso->db->query("UPDATE {$config["tablePrefix"]}members SET resetPassword=NULL, password='$passwordHash' WHERE memberId=$memberId");
				$this->eso->message("passwordChanged", false);
				redirect("");
			}
		}
	}
	
	// If they've submitted their email to get a password reset link, email one to them!
	if (isset($_POST["email"])) {
		
		// Find the member with this email.
		$result = $this->eso->db->query("SELECT memberId, name, email FROM {$config["tablePrefix"]}members WHERE email='{$_POST["email"]}'");
		if (!$this->eso->db->numRows($result)) {
			$this->eso->message("emailDoesntExist");
			return;
		}
		list($memberId, $name, $email) = $this->eso->db->fetchRow($result);
		
		// Update their record in the database with a special password reset hash.
		$hash = md5(rand());
		$this->eso->db->query("UPDATE {$config["tablePrefix"]}members SET resetPassword='$hash' WHERE memberId=$memberId");
		
		// Send them email containing the link, and redirect to the home page.
		if (sendEmail($email, sprintf($language["emails"]["forgotPassword"]["subject"], $name), sprintf($language["emails"]["forgotPassword"]["body"], $name, $config["forumTitle"], $config["baseURL"] . makeLink("forgot-password", $hash)))) {
			$this->eso->message("passwordEmailSent", false);
			redirect("");
		}
	}
}


}
