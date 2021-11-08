<?php
// feed.view.php
// Outputs items specified by the feed controller in RSS format.

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
