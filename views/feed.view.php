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
 * Feed view: outputs items specified by the feed controller in RSS
 * format.
 */
if(!defined("IN_ESO"))exit;
?><?php echo "<?xml version='1.0' encoding='{$language["charset"]}'?>\n";?>
<rss version='2.0'>
	<channel>
		<title><?php echo $this->controller->title;?></title>
		<link><?php echo sanitizeHTML($this->controller->link);?></link>
		<description><?php echo $this->controller->description;?></description>
		<pubDate><?php echo $this->controller->pubDate;?></pubDate>
		<generator>eso</generator>
<?php foreach($this->controller->items as $item):?>
		<item>
			<title><?php echo $item["title"];?></title>
			<link><?php echo sanitizeHTML($item["link"]);?></link>
			<description><?php echo $item["description"];?></description>
			<pubDate><?php echo $item["date"];?></pubDate>
			<guid><?php echo sanitizeHTML($item["link"]);?></guid>
		</item>
<?php endforeach;?>
	</channel>
</rss>
