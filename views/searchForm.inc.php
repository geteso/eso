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
 * Search form: displays the search form (with a 'Start a conversation'
 * button if on the search view.)
 */
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
