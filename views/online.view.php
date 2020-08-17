<?php
// online.view.php
// Displays a list of members currently online.

if(!defined("IN_ESO"))exit;?>
<fieldset id="fieldmembers">
<legend><?php echo $language["Online members"];?></legend>

<?php
// If there are members online, list them.
if($this->numberOnline):?>

<div id='membersOnline'>
<?php while(list($memberId,$name,$avatarFormat,$color,$account,$lastSeen,$lastAction)=$this->eso->db->fetchRow($this->online)):?>
<div class='p c<?php echo $color;?>'><div class='hdr'>
<?php if($this->numberOnline<20):?><img src='<?php echo $this->eso->getAvatar($memberId,$avatarFormat,"thumb");?>' alt='' class='avatar'/><?php endif;?>
<h3><a href='<?php echo makeLink("profile",$memberId);?>'><?php echo $name;?></a></h3>
<span><?php echo $lastAction;?> (<?php echo relativeTime($lastSeen);?>)</span>
</div></div>
<?php endwhile;?>
</div>

<?php
// Otherwise, display a message.
else:echo $this->eso->htmlMessage("noMembersOnline");endif;?>

</fieldset>
