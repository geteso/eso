<?php
// dashboard.php
// Displays a list of plugins and their settings.

if (!defined("IN_ESO")) exit;
?>

<?php // Use an AJAX request to check for updates so that initial page loading isn't slow. ?>
<div id='updateMessage'></div>
<script type='text/javascript'>
// <![CDATA[
Ajax.request({
 	"url": eso.baseURL + "ajax.php?controller=admin&section=dashboard",
 	"post": "action=checkForUpdates",
 	"success": function() {
 		if (this.result) getById("updateMessage").innerHTML = this.result;
 		show(getById("updateMessage"), {animation: "verticalSlide"});
 	}
})
// ]]>
</script>

<?php if (file_exists("install/")) echo $this->eso->htmlMessage("removeDirectoryWarning", "install/"); ?>

<fieldset>
<legend><?php echo $language["Forum statistics"];?></legend>
<ul class='form stats'>

<?php foreach ($this->stats as $k => $v): ?>
<li><label><?php echo $language[$k]; ?></label>
<div><?php echo $v; ?></div></li>
<?php endforeach; ?>

<?php $this->callHook("forumStatistics"); ?>
</ul>
</fieldset>

<fieldset>
<legend><?php echo $language["Server information"];?></legend>
<ul class='form stats'>

<?php foreach ($this->serverInfo as $k => $v): ?>
<li><label><?php echo $language[$k]; ?></label>
<div><?php echo $v; ?></div></li>
<?php endforeach; ?>

<?php $this->callHook("serverInformation"); ?>

</ul>
</fieldset>
