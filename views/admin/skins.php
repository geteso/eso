<?php
// skins.php
// Displays a list of installed skins.

if (!defined("IN_ESO")) exit;
?>

<?php // If there are installed skins to display...
if (count($this->skins)): ?>

<fieldset id='skins'>
<legend><?php echo $language["Installed skins"]; ?></legend>
<ul>

<?php // Loop through each skin and output its preview/information.
foreach ($this->skins as $k => $skin): ?>
<li<?php if ($skin["selected"]): ?> class='enabled'<?php endif; ?>>
<a href='<?php echo makeLink("admin", "skins", $k, "?token={$_SESSION["token"]}"); ?>'>
<span class='preview'>
<?php if ($skin["preview"]): ?><img src='skins/<?php echo $k; ?>/<?php echo $skin["preview"]; ?>' alt='<?php echo $skin["name"]; ?>'/>
<?php else: ?><span><?php echo $language["No preview"]; ?></span>
<?php endif; ?>
</span>
<strong><?php echo $skin["name"]; ?></strong> <small><?php printf($language["version"], $skin["version"]); ?> <?php printf($language["author"], $skin["author"]); ?></small>
</a>
</li>
<?php endforeach; ?>

</ul>
</fieldset>

<?php // Otherwise if there are no plugins installed, show a message.
else: ?>
<?php echo $this->eso->htmlMessage("noSkinsInstalled"); ?>
<?php endif; ?>

<?php // Add a new skin form. ?>
<fieldset id='addSkin'>
<legend><?php echo $language["Add a new skin"]; ?></legend>
<?php echo $this->eso->htmlMessage("downloadSkins", "https://geteso.org/skins"); ?>
<form action='<?php echo makeLink("admin", "skins"); ?>' method='post' enctype='multipart/form-data'>
<input type='hidden' name='token' value='<?php echo $_SESSION["token"]; ?>'/>
<ul class='form'>
<li><label><?php echo $language["Upload a skin"]; ?></label> <input name='installSkin' type='file' class='text' size='20'/></li>
<li><label></label> <?php echo $this->eso->skin->button(array("value" => $language["Add skin"])); ?></li>
</ul>
</form>
</fieldset>
