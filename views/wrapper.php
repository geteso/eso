<?php
// wrapper.php
// Displays an HTML page with a header, bar, and footer.

if(!defined("IN_ESO"))exit;
?>
<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
<head>

<meta http-equiv='Content-Type' content='text/html; charset=<?php echo $language["charset"];?>'/>
<meta name='viewport' content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0'>

<title><?php echo ($this->controller->title?$this->controller->title." - ":"").$config["forumTitle"];?></title>
<meta name='title' content='<?php echo ($this->controller->title?$this->controller->title." - ":"").$config["forumTitle"];?>'>
<link rel='preload' href='<?php echo $config["baseURL"] . $this->skin->getForumLogo();?>' as='image'>

<!-- Apple -->
<meta name='apple-mobile-web-app-capable' content='yes'>
<meta name='apple-mobile-web-app-title' content='<?php echo $config["forumTitle"];?>'>
<meta name='apple-mobile-web-app-status-bar-style' content='default'>
<link rel='apple-touch-icon' href='<?php echo $config["baseURL"] . $this->skin->getForumIcon();?>'>

<!-- Open Graph -->
<meta property='og:site_name' content='<?php echo $config["forumTitle"];?>'>
<?php if (!empty($this->controller->title)): ?>
<meta property='og:title' content="<?php echo $this->controller->title;?>">
<?php else: ?>
<meta property='og:title' content="<?php echo $config["forumTitle"];?>">
<?php endif; ?>
<meta property='og:type' content='website'>
<meta property='og:image' content='<?php echo $config["baseURL"] . $this->skin->getForumIcon();?>'>
<meta property='og:url' content='<?php echo $config["baseURL"];?>'>
<!-- og:description added to head @ conversation.controller, search.controller -->

<!-- Twitter -->
<meta name='twitter:title' content="<?php echo ($this->controller->title ? $this->controller->title . " - " : "").$config["forumTitle"];?>">
<meta name='twitter:card' content='summary'>
<meta name='twitter:image' content='<?php echo $config["baseURL"] . $this->skin->getForumIcon();?>'>
<!-- twitter:description added to head @ conversation.controller, search.controller -->

<!-- Web app manifest -->
<link rel="manifest" href="site.webmanifest">

<?php echo $this->head();?> 
</head>

<body>
<?php $this->callHook("pageStart");?>

<div id='loading' style='display:none'><?php echo $language["Loading"];?></div>

<?php echo $this->getMessages();?>

<div id='wrapper'<?php if($this->action!="search"):?> class='small'<?php endif;?>>

<div id='hdrbarpack'>

<div id='hdr'>

<?php if (($config["showForumDescription"] == "true") && ($this->action == "search")): ?>
<h1 id="hasForumDescription">
<?php else: ?>
<h1>
<?php endif; ?>

<a href='' title='<?php echo $config["forumTitle"];?>'><img src='<?php echo $this->skin->getForumLogo();?>' data-fallback='<?php echo !empty($config["forumLogo"])?$config["forumLogo"]:"skins/{$config["skin"]}/logo.png";?>' alt=''/> 
<span id='forumTitle'><?php echo $config["forumTitle"];?>
<?php if (($config["showForumDescription"] == "true") && ($this->action == "search")): ?>
<small id='forumDescription'><?php echo $config["forumDescription"];?></small>
<?php endif; ?>
</span>
</a></h1>
<?php if($this->action=="search"):?>
<p id='stats'>
<?php foreach($this->getStatistics() as $k=>$v):?>
<span id='statistic-<?php echo $k;?>'><?php echo $v;?></span><br/>
<?php endforeach;?>
</p>
<?php else:include "views/searchForm.inc.php";endif;?>
</div>
	
<div id='bar'>
<?php if(count($this->bar["left"])):?><ul class='fl'><?php ksort($this->bar["left"]);foreach($this->bar["left"] as $v)echo "<li>$v</li>";?></ul><?php endif;?>
<?php if(count($this->bar["right"])):?><ul class='fr'><?php ksort($this->bar["right"]);foreach($this->bar["right"] as $v)echo "<li>$v</li>";?></ul><?php endif;?>
</div>

</div>

<div id='body'>
<?php $this->controller->render();?>
</div>

<?php $this->callHook("footer"); ?>
<div id='ftr'>
<?php if (count($this->footer)): ?><ul><?php
ksort($this->footer);
foreach ($this->footer as $v) echo "<li>$v</li>";
?></ul><?php endif; ?>
<p id='copyright'>
<!-- The following text constitutes a copyright notification. -->
Powered by <a href='https://geteso.org'>eso</a> <?php if ($this->user["admin"]) echo ESO_VERSION; ?>
<!-- End copyright notification. -->
</p>
</div>

</div>

<?php $this->callHook("pageEnd");?>
</body>
</html>
