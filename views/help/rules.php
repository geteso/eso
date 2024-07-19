<?php
// rules.php
// Tells all about the forum.

if (!defined("IN_ESO")) exit;
?>

<?php // Use an AJAX request to check for updates so that initial page loading isn't slow. ?>
<div id='updateMessage'></div>
<script type='text/javascript'>
// <![CDATA[
Ajax.request({
    "url": eso.baseURL + "ajax.php?controller=help&section=rules",
    "post": "action=checkForUpdates",
    "success": function() {
        if (this.result) getById("updateMessage").innerHTML = this.result;
        show(getById("updateMessage"), {animation: "verticalSlide"});
    }
})
// ]]>
</script>

<fieldset>
<legend>Global rules</legend>

<p>By using this website, you agree that you'll follow these rules, and understand that if we reasonably think you haven't followed these rules, we may suspend your account at our own discretion.</p>
<pre>1. You will not use this website ("Esoteric Chat") to do anything that violates local or federal United States law.

2. You will cease to use the site and not continue to operate your forum account if you are under the age of 13.
	a. You will cease to participate in conversations tagged "nsfw" if you are under the age of 16.

3. Sexualized depictions of minors, including "lolicon," may not be embedded in conversations or uploaded as profile pictures.

4. You will not post or request personal information ("dox") or calls to invasion ("raids").

5. You will not embed or post links to advertising, referral services, or potentially harmful websites.

6. You will not threaten members with their personal information, post any personal information, or request said information either in public or in private ("phishing" and/or "dox").

7. Submitting false information to the complaint address listed on the contact page ("Contact us") is considered an abuse of the report process offered by this forum and may result in suspension.</pre>
<p>Reminder: the use of this forum is a privilege and not a right. 
The webmaster, and by extension, the moderation team, reserve the right to revoke access at any time for any reason without notice. 
If you believe that another member has violated one or more of these rules, or the <a href="legal/tos/">terms of service</a>, you may report them using the methods of communication listed on the <a href="help/contact/">contact page</a>.</p>

</fieldset>
