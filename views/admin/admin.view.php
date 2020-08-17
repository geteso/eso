<?php
// admin.view.php
// Displays a wrapper/menu for the administration interface and includes the appropriate view.

if (!defined("IN_ESO")) exit;
?>
<div id='admin'>
	
<ul class='menu'>
<?php // Output a links to the default admin sections.
foreach ($this->defaultSections as $v): ?>
<li<?php if ($this->section == $v): ?> class='active'<?php endif; ?>><a href='<?php echo makeLink("admin", $v); ?>'><?php echo $this->sections[$v]["title"]; ?></a></li>
<?php endforeach; ?>
			
<?php // If there are any additional sections which have been added by plugins, output them below a separator.
if ($sections = array_diff(array_keys($this->sections), $this->defaultSections) and count($sections)): ?>
<li class='separator'></li>

<?php foreach ($sections as $v): ?>
<li<?php if ($this->section == $v): ?> class=''<?php endif; ?>><a href='<?php echo makeLink("admin", $v); ?>'><?php echo $this->sections[$v]["title"]; ?></a></li>
<?php endforeach; ?>
<?php endif; ?>
</ul>

<div class='inner'>
<?php include $this->eso->skin->getView($this->subView); ?>
</div>

<div class='clear'></div>
</div>
