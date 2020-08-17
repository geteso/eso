<?php
// profile.view.php
// Displays a member's profile.

if(!defined("IN_ESO"))exit;
?>
<fieldset>
<legend><?php printf($language["profile"],$this->member["name"]);?></legend>

<div class='p l c<?php echo $this->member["color"];?> profile'>
<div class='parts'>

<div>
<div class='hdr'>
<div class='pInfo'>
<h3><?php echo $this->member["name"];?></h3>
<span><?php echo $language[$this->member["account"]];?></span>
</div>
</div>
<div class='body'>

<?php // Output member statistics. ?>
<ul class='form stats'>

<li><label><?php echo $language["Last active"];?></label>
<div><abbr title='<?php echo date($language["dateFormat"],$this->member["lastSeen"]);?>'><?php echo relativeTime($this->member["lastSeen"]);?></abbr> <?php echo !empty($this->member["lastAction"])?" <small>({$this->member["lastAction"]})</small>":"";?></div></li>

<li><label><?php echo $language["First posted"];?></label>
<div><abbr title='<?php echo date($language["dateFormat"],$this->member["firstPosted"]);?>'><?php echo relativeTime($this->member["firstPosted"]);?></abbr></div></li>

<li><label><?php echo $language["Post count"];?></label>
<div><?php echo number_format($this->member["postCount"]);?>
<?php if($this->member["postCount"]>0):?> <small>(<?php
$postsPerDay=round(min(max(0,$this->member["postCount"]/((time()-$this->member["firstPosted"])/60/60/24)),$this->member["postCount"]));if($postsPerDay==1)echo $language["post per day"];else printf($language["posts per day"],$postsPerDay);?>)</small><?php endif;?></div></li>

<li><label><?php echo $language["Conversations started"];?></label>
<div><?php echo number_format($this->member["conversationsStarted"]);?>
<?php if($this->member["conversationsStarted"]>0):?> <small>(<a href='<?php echo makeLink("search","?q2=author:".urlencode(desanitize($this->member["name"])));?>'><?php echo $language["show conversations started"];?></a>)</small><?php endif;?></div></li>

<li><label><?php echo $language["Conversations participated in"];?></label>
<div><?php echo $this->member["conversationsParticipated"];?>
<?php if($this->member["conversationsParticipated"]>0):?> <small>(<a href='<?php echo makeLink("search","?q2=contributor:".urlencode(desanitize($this->member["name"])));?>'><?php echo $language["show conversations participated in"];?></a>)</small><?php endif;?></div></li>
	
<?php $this->callHook("statistics");?>

<?php if($this->eso->user and $this->member["memberId"]!=$this->eso->user["memberId"]):?>
<li><label><?php echo $this->member["name"];?> &amp; <?php echo $this->eso->user["name"];?><br/><span class='label private'><?php echo $language["labels"]["private"];?></span></label> <div><a href='<?php echo makeLink("search","?q2=private+%2B+contributor:".urlencode(desanitize($this->member["name"])));?>'><?php printf($language["See the private conversations I've had"],$this->member["name"]);?></a><br/>
<a href='<?php echo makeLink("conversation","new","?member=".urlencode(desanitize($this->member["name"])),"&token={$_SESSION["token"]}");?>'><?php printf($language["Start a private conversation"],$this->member["name"]);?></a></div></li>
<?php endif;?>

</ul>
</div>
</div>

<?php // Output addition profile "sections" added by plugins.
ksort($this->sections);
foreach($this->sections as $section):?>
<div><?php echo $section;?></div>
<?php endforeach;?>

</div>
<div class='avatar'><img src='<?php echo $this->eso->getAvatar($this->member["memberId"],$this->member["avatarFormat"],"l");?>' alt=''/></div>
<div class='clear'></div>
</div>

</fieldset>
