<?php
// search.view.php
// Displays tag and gambit clouds.  Includes the search form and results.

if(!defined("IN_ESO"))exit;
?>
<?php $this->callHook("beforeRenderTagCloud");?>

<div id='tagArea'>
<p id='tags'><?php
// Echo the most common tags.
ksort($this->tagCloud);
foreach($this->tagCloud as $k=>$v){
    echo "<a href='".makeLink("search","?q2=".urlencode(desanitize((!empty($_SESSION["search"])?"{$_SESSION["search"]} + ":"")."{$language["gambits"]["tag:"]}$k")))."' class='$v'>".str_replace(" ","&nbsp;",$k)."</a> ";
}
?></p>

<?php $this->callHook("afterRenderTagCloud");?>

<p id='gambits'><?php
// Echo the gambits alphabetically.
ksort($this->gambitCloud);
foreach($this->gambitCloud as $k=>$v){
    echo "<a href='".makeLink("search","?q2=".urlencode(desanitize((!empty($_SESSION["search"])?"{$_SESSION["search"]} + ":"").$k)))."' class='$v'>".str_replace(" ","&nbsp;",$k)."</a> ";
}
?></p>
</div>

<?php $this->callHook("beforeRenderSearchForm");?>

<?php include $this->eso->skin->getView("searchForm.inc.php");?> 

<?php $this->callHook("afterRenderSearchForm");?>

<div id='searchResults'>
<?php include $this->eso->skin->getView("searchResults.inc.php");?>
</div>

<?php $this->callHook("afterRenderSearchResults");?>

<script type='text/javascript'>
// <![CDATA[
Search.currentSearch = '<?php if(isset($_SESSION["search"]))echo addslashes(desanitize($_SESSION["search"]));?>';
Search.init();
// ]]>
</script>
