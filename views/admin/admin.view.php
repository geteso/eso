<?php
/**
 * This file is part of the eso project, a derivative of esoTalk.
 * It has been modified by several contributors.  (contact@geteso.org)
 * Copyright (C) 2022 geteso.org.  <https://geteso.org>
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * Displays a wrapper/menu for the administration interface and includes
 * the appropriate view.
 */
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
