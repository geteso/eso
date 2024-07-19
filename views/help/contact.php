<?php
// contact.php
// Tells all about the forum.

if (!defined("IN_ESO")) exit;
?>

<?php // Use an AJAX request to check for updates so that initial page loading isn't slow. ?>
<div id='updateMessage'></div>
<script type='text/javascript'>
// <![CDATA[
Ajax.request({
        "url": eso.baseURL + "ajax.php?controller=help&section=contact",
        "post": "action=checkForUpdates",
        "success": function() {
                if (this.result) getById("updateMessage").innerHTML = this.result;
                show(getById("updateMessage"), {animation: "verticalSlide"});
        }
})
// ]]>
</script>

<fieldset>
<legend>Contact us</legend>

<p>There are several effective ways in order to contact this forum's administration team. 
We are always interested in maintaining a direct line of communication with every member so as to preserve the underlying principle of this forum, which is the freedom of speech. 
</p>

<h3>Contact a moderator</h3>
<p>The best way to get in touch with a forum moderator is by creating a <i>private conversation</i> with the phrase "moderators". 
This will make your conversation viewable only to every moderator on the forum; it will be hidden from regular members, barring the original poster. 
If you plan on reporting a user and/or disclosing sensitive information, this is the best way to convey that message to everyone on the moderation team. 
Please note that private messages directed towards moderators will also be viewable to administrators.</p>
<p>Alternatively, you may ask an individual moderator for their preferred method of contact; however, contacting an individual moderator about an official request, or a request that is pertinent to the forum, should be considered bad practice.</p>

<h3>Contact the webmaster</h3>
<p>Bug reports related to the <a href="https://geteso.org/" target="_blank">esoBB forum software</a> should be forwarded to its respective <a href="https://github.com/geteso/eso/" target="_blank">GitHub repository</a>, as esoBB is an open-source project. 
Concerns relating to account security, management, or backend security should be forwarded directly to the webmaster.</p>

<p>In order to contact the webmaster, you may open a private conversation with the account named "admin" or by using e-mail. 
A private conversation should be considered the perferred method of contact, as some e-mail messages may not reach the destination due to spam filters, which are designed to limit spam mail.</p>

<p>This address may be used for general inquiries: <i>contact@esoteric.chat</i></br>
For DMCA requests and any legal matters: <i>dmca@esoteric.chat</i></p>

<p>If the subject matter of your e-mail is deemed urgent, you may prefix the message title with "URGENT" in order to promote a faster response.</p>
