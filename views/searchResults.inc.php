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
 * Search results: displays a table of search results using columns
 * defined in the search controller.
 */
if(!defined("IN_ESO"))exit;
?>
<table cellspacing='0' cellpadding='2' class='c'>
<thead>
<tr><?php foreach($this->resultsTable as $column):?><th<?php if(!empty($column["class"])):?> class='<?php echo $column["class"];?>'<?php endif;?>><?php echo !empty($column["title"])?$column["title"]:"&nbsp;";?></th><?php endforeach;?></tr>
<tr id='newResults' style='display:none'><td colspan='<?php echo count($this->resultsTable);?>'><?php echo $this->eso->htmlMessage("newSearchResults");?></td></tr>
</thead>
<tbody id='conversations'>

<?php
// Returns the HTML for the contents of a cell in the star column.
function columnStar(&$search,$conversation)
{
    return $search->eso->htmlStar($conversation["id"],$conversation["starred"]);
}

// Returns the HTML for the contents of a cell in the avatar column.
function columnAvatar(&$search,$conversation)
{
    return "<a href='".makeLink(conversationLink($conversation["id"], $conversation["slug"]))."' style='margin:0' data-instant><img src='".$search->eso->getAvatar($conversation["startMemberId"],$conversation["avatarFormat"],"thumb")."' alt='' class='thumb'/></a>";
}

// Returns the HTML for the contents of a cell in the conversation column: labels, title, and tags.
function columnConversation(&$search,$conversation)
{
    global $language;
    
    // $conversation["labels"] contains comma-separated values corresponding to each label in the $eso->labels array: 0 = label does not apply, 1 = label does apply.  Read this variable and output applied labels.
    $labels=explode(",",$conversation["labels"]);$i=0;$labelsHtml="";$html="";
    foreach($search->eso->labels as $k => $v) {
        if(@$labels[$i])$labelsHtml .= "<span class='label $k'>{$language["labels"][$k]}</span> ";
        $i++;
    }
    if($labelsHtml)$html.="<span class='labels'>$labelsHtml</span>";
    
    // Output the conversation title.
    $html.="<strong";
    if($search->eso->user and !$conversation["unread"])$html.=" class='read'";
    $html.="><a href='".makeLink(conversationLink($conversation["id"], $conversation["slug"]))."' data-instant>".highlight($conversation["title"],$_SESSION["highlight"])."</a></strong><br/>";
    
    // If the conversation is unread, show a "jump to unread" link.
    // if ($search->eso->user["name"] and $conversation["unread"]) $html .= "<small id='jumplink'><a href='" . makeLink($conversation["id"], $conversation["slug"], "?start=unread") . "'>{$language["Jump to unread"]}</a></small>";
    
    // We can't forget tags.
    $html .= "<small class='tags'>{$conversation["tags"]}</small>";
	
    // Jump to last/unread link, depending on the user.
    if ($search->eso->user["name"] and $conversation["unread"]) $html .= "<small id='unreadPost'><a href='" . makeLink(conversationLink($conversation["id"], $conversation["slug"]), "?start=unread") . "'>{$language["Jump to unread"]}</a></small>";
    else $html .= "<small id='lastPost'><a href='" . makeLink(conversationLink($conversation["id"], $conversation["slug"]), "?start=last") . "'>{$language["Jump to last"]}</a></small>";
    
    $search->callHook("getConversationColumn", array(&$html, $conversation));
    
    return $html;
}

// Returns the HTML for the contents of a cell in the post count column.
function columnPosts(&$search,$conversation)
{
    return "<span class='postCount p".(($conversation["posts"]>50)?"1":(($conversation["posts"]>10)?"2":"3"))."'>{$conversation["posts"]}</span>";
}

// Returns the HTML for the contents of a cell in the "started by" column.
function columnAuthor(&$search,$conversation)
{
    return "<a href='".makeLink("profile",$conversation["startMemberId"])." '>{$conversation["startMember"]}</a><br/><small>".relativeTime($conversation["startTime"])."</small>";
}

// Returns the HTML for the contents of a cell in the "last reply" column.
function columnLastReply(&$search,$conversation)
{
    $html="<span class='lastPostMember'>";
    if($conversation["posts"]>1)$html.="<a href='".makeLink("profile",$conversation["lastPostMemberId"])."'>{$conversation["lastPostMember"]}</a>";
    $html.="</span><br/><small class='lastPostTime'>";
    if($conversation["posts"]>1)$html.=relativeTime($conversation["lastPostTime"]);
    $html.="</small>";
    return $html;
}

// If there are results, loop through the conversations and output a table row for each one.
if (count($this->results)):
foreach ($this->results as $conversation): ?>

<tr id='c<?php echo $conversation["id"]; ?>'<?php if ($conversation["starred"]): ?> class='starred'<?php endif; ?>>
<?php

// Loop through the columns defined in the search controller and echo the output of a callback function for the cell contents.
foreach ($this->resultsTable as $column): ?><td<?php if (!empty($column["class"])): ?> class='<?php echo $column["class"]; ?>'<?php endif; ?>><?php echo call_user_func_array($column["content"], array(&$this,$conversation)); ?></td>
<?php endforeach; ?>
</tr>
<?php endforeach;
endif; ?>

</tbody>
</table>

<?php
// If there are no conversations, show a message.
if (!$this->numberOfConversations): echo $this->eso->htmlMessage("noSearchResults");

// On the other hand, if there were too many results, show a "show more" message.
elseif ($this->limit==$config["results"] + 1 and $this->numberOfConversations > $config["results"]): ?>
<div id='more'>
<?php echo $this->eso->htmlMessage("viewMore", array(makeLink("search", urlencode(@$_SESSION["search"] . (@$_SESSION["search"] ? " + " : "").$language["gambits"]["limit:"].$language["gambits"]["100"])))); ?>
</div>
<?php endif; ?>
