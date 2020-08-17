<?php
// searchForm.inc.php
// Displays the search form with a "start a conversation" button.

if(!defined("IN_ESO"))exit;
?>
<form id='search' action='<?php echo makeLink("search");?>' method='post' <?php if($this->eso->action=="search"):?>class='withStartConversation'<?php endif;?>>
<div>
<input id='searchText' name='search' type='text' class='text' value='<?php echo @$_SESSION["search"];?>' spellcheck='false'/>
<div class='fr'>
<a id='reset' href='<?php echo makeLink("search","");?>'>x</a>
<?php echo $this->eso->skin->button(array("id"=>"submit","name"=>"submit","value"=>$language["Search"],"class"=>"big"));?>
<?php if($this->eso->action=="search"):?>
<?php echo $this->eso->skin->button(array("id"=>"new","name"=>"new","value"=>$language["Start a conversation"],"class"=>"big"));?>
<?php endif;?>
</div>
</div>
</form>
