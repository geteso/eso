<?php
// about.php
// Tells all about the forum.

if (!defined("IN_ESO")) exit;
?>

<?php // Use an AJAX request to check for updates so that initial page loading isn't slow. ?>
<div id='updateMessage'></div>
<script type='text/javascript'>
// <![CDATA[
Ajax.request({
        "url": eso.baseURL + "ajax.php?controller=help&section=about",
        "post": "action=checkForUpdates",
        "success": function() {
                if (this.result) getById("updateMessage").innerHTML = this.result;
                show(getById("updateMessage"), {animation: "verticalSlide"});
        }
})
// ]]>
</script>

<fieldset>
<legend>About the forum</legend>

<p>Esoteric Chat is a discussion board where anyone can create an account and submit posts. 
The forum centers around the subject matter of topics deemed 'esoteric' or understood by a small group of people. 
Despite this, conversations are not required to adhere to any subject matter, and so this forum serves as a platform for unmoderated* content that doesn't violate the law. 
We have never had a successful demand for user information and are not under any gag orders or court orders issued by any recognized authority.</p>
<p>Certain conversations on this forum may be directed towards mature audiences only; persons less than 16 years of age should not proceed into them. 
It is assumed that the end user understands these terms as a condition of using the forum.</p>
</p><i>*While we aim towards free speech, some degree of moderation is required.  Please see the next section ("Moderation policy") for more information.</i></p>

<h3>Moderation policy</h3>
<p>Moderators are encouraged to be informal with other members, and to use their additional permissions in spirit of the purpose of this discusion board, which is to encourage the freedom of speech. 
Despite this, any content that violates United States federal or regional law is strictly prohibited.  Furthermore, this platform looks down upon, and thus condemns, the sexualized depiction of minors. 
Sexualized depictions of minors, regardless of any degree of realism, may not be embedded in conversations or uploaded as profile pictures on the basis that it depicts underage children in a sexual manner.</p>
<p>It is greatly encouraged that members who create not-safe-for-work conversations use the tag "nsfw" so as to mark it as unsafe for persons less than 18 years of age, and for those who simply wish to avoid this type of content.  
Safe-for-work conversations (those created with the intention of being safe-for-work) that are derailed may be locked, either temporarily or permanently, in order to allow for any offending content to be removed.</p>

<h3>Suspension and appeal policy</h3>
<p>If a forum member continuosly violates the <a href="help/rules/">global rules</a> or <a href="legal/tos/">terms of service</a>, moderators reserve the right to suspend that member and any other account(s) being used by the same individual.</p>
<p>Forum administrators, while capable of any actions taken by a moderator, are primarily concerned with the technical aspects of this forum. 
If you are suspended, you may directly appeal your suspension to the webmaster using the methods of communication availed to you on the <a href="help/contact/">contact page</a>. 
In the event that your appeal is deemed valid, it will be voted on by the moderation team as to whether or not your suspension should be lifted.</p>
<p>Moderators may also issue temporary suspensions, in which the moderator warns the member and informs them of when they will be unsuspended. 
The webmaster reserves the right to revoke access at any time, for any reason, without notice.
</p>

<h3>Transparency and security</h3>
<p>We pledge to publish communication with law enforcement including or regarding any successful demand for information, including any court-issued order for information regarding our members. 
If this ever occurs, the relevant information will be posted on this page ("About the forum").</p>
<p>Pursuant to Section 230 (two hundred and thirty) of the Communications Decency Act (47 U.S.C. § 230): no provider or user of an interactive computer service shall be treated as the publisher or speaker of any information provided by another information content provider. 
(<a href="https://www.eff.org/issues/cda230#:~:text=Section%20230%20says%20that%20%22No,%C2%A7%20230)." target="_blank">Source and explanation</a> courtesy of the Electronic Frontier Foundation.) 
All posts on this site are the sole responsibility of their posters.</p>
<p>User login credentials are not viewable, modifiable, nor accessible by this site's webmaster or any moderation team. 
Site-related traffic is encrypted from both ends and no analytics trackers are used on this site. 
Posts could still be intercepted by a compromised machine, the failure to privatize a user's password information, or a hole in security which has not been discovered at the time of exploitation, as nothing is completely secure. 
<b>If you are deeply concerned about the risk of your messages being intercepted, we ultimately recommend that you do not use this site on public machines or networks.</b></p>

</fieldset>
