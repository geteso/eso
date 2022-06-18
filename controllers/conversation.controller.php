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
if (!defined("IN_ESO")) exit;

/**
 * Conversation controller: controls anything to do with the conversation
 * view; posting, editing, pagination, deleting, etc.
 */
class ConversationController extends Controller {

var $view = "conversation.view.php";
var $editingPost = false; // Are we editing a post (specified in $_GET["editPost"])?
var $showingDeletedPost = false; // Are we showing a delete post (specified in $_GET["showDeletedPost"])?
var $conversation = array(); // An array of conversation details.
var $startFrom = 0; // Which post to start viewing from.

// Initialize the conversation and its posts. Handle non-AJAX conversation actions such as delete, edit, etc.
function init()
{
	if (defined("AJAX_REQUEST")) return;
	
	global $language, $config;

	// Get the conversation details. If no "id" is specified, an array of empty conversation details will be used.
	$id = (isset($_GET["q2"]) and $_GET["q2"] != "new") ? (int)$_GET["q2"] : false;
	$this->conversation = $this->getConversation($id);

	// Show an error if the conversation doesn't exist, or if the user is not allowed to view it.
	if (!$this->conversation) {
		$this->eso->message("cannotViewConversation", false);
		redirect("");
	}
	
	// Get the members allowed.
	$this->conversation["membersAllowed"] =& $this->getMembersAllowed();

	// Add essential variables and language definitions to be accessible through JavaScript. 
	$this->eso->addLanguageToJS("Starred", "Unstarred", "Lock", "Unlock", "Sticky", "Unsticky", "Moderator", "Moderator-plural", "Administrator", "Administrator-plural", "Member", "Member-plural", "Suspended", "Unvalidated", "confirmLeave", "confirmDiscard", "confirmDeleteConversation", "Never", "Just now", "year ago", "years ago", "month ago", "months ago", "week ago", "weeks ago", "day ago", "days ago", "hour ago", "hours ago", "minute ago", "minutes ago", "second ago", "seconds ago");
	$this->eso->addVarToJS("postsPerPage", $config["postsPerPage"]);
	$this->eso->addVarToJS("autoReloadIntervalStart", $config["autoReloadIntervalStart"]);
	$this->eso->addVarToJS("autoReloadIntervalMultiplier", $config["autoReloadIntervalMultiplier"]);	
	$this->eso->addVarToJS("autoReloadIntervalLimit", $config["autoReloadIntervalLimit"]);
	$this->eso->addVarToJS("time", time());
	
	// Work out the title of the page.
	$this->title = $this->conversation["id"] ? $this->conversation["title"] : $language["Start a conversation"];

	// If the user is attempting to start a conversation but they don't have permission, discontinue.
	// (The view will show an error.)
	if (!$this->conversation["id"] and $this->canStartConversation() !== true) return false;
	
	// Start a conversation, post a reply, or save a draft if one of the post submit buttons was clicked.
	if ((isset($_POST["saveDraft"]) or isset($_POST["postReply"])) and $this->eso->validateToken(@$_POST["token"])) {
		
		// If the conversation details are empty, we need to start a new conversation.
		if (!$this->conversation["id"]) {
			$id = $this->startConversation(array(
				"title" => @$_POST["cTitle"],
				"tags" => @$_POST["cTags"],
				"membersAllowed" => @$_SESSION["membersAllowed"],
				"starred" => @$_SESSION["starred"],
				"draft" => isset($_POST["saveDraft"]),
				"content" => @$_POST["content"]
			));
			// If there was an error creating the conversation, set the "draft" content so the user won't lose their
			// post.
			if (!$id) $this->conversation["draft"] = $_POST["content"];
			// Otherwise, redirect to the newly-created conversation!
			else redirect(conversationLink($this->conversation["id"], $this->conversation["slug"]));
		}
			
		// Save a draft in an existing conversation.
		elseif (isset($_POST["saveDraft"])) {
			$this->saveDraft($_POST["content"]);
			redirect(conversationLink($this->conversation["id"], $this->conversation["slug"]), "?start={$_GET["start"]}", "#reply");
		}
			
		// Add a reply to an existing conversation.
		else {
			$id = $this->addReply(@$_POST["content"], isset($_POST["saveDraft"]));
			
			// If there was an error posting the reply, set the "draft" content so the user won't lose their post.
			if (!$id) $this->conversation["draft"] = $_POST["content"];
			
			// Otherwise, redirect so that the new reply is visible.
			else redirect(conversationLink($this->conversation["id"], $this->conversation["slug"]), "?start=" . max(0, $this->conversation["postCount"] - $config["postsPerPage"]), "#p$id");
		}
	}

	// Discard a draft.
	if (isset($_POST["discardDraft"])) {
		// If the conversation doesn't exist, just redirect to the new conversation page.
 		if (!$this->conversation["id"]) redirect("conversation", "new");
		
		// If there are no other posts in the conversation, just delete the conversation.
		if (!$this->conversation["postCount"] and $this->deleteConversation()) {
			$this->eso->message("conversationDeleted");
			redirect("");
		}
		// Otherwise, discard the draft and redirect.
		$this->discardDraft();
		redirect(conversationLink($this->conversation["id"], $this->conversation["slug"]), "?start={$_GET["start"]}", "#reply");
	}

	// If the conversation does exist...
	if ($this->conversation["id"]) {
	
		// If we're not using pretty URLs, redirect if there is a slug in the URL.
		// If we are using pretty URLs, make sure that the slug in the URL is the same as the actual slug using conversationLink().
		if (empty($config["usePrettyURLs"]) && !empty(@$_GET["q3"])) {
			header("HTTP/1.1 301 Moved Permanently");
			redirect($this->conversation["id"], !empty($_GET["start"]) ? "?start={$_GET["start"]}" : "");
		}
		elseif (!empty($config["usePrettyURLs"]) && @$_GET["q2"] != conversationLink($this->conversation["id"], $this->conversation["slug"])) {
			header("HTTP/1.1 301 Moved Permanently");
			redirect(conversationLink($this->conversation["id"], $this->conversation["slug"]), !empty($_GET["start"]) ? "?start={$_GET["start"]}" : "");
		}
		
		// If the slug in the URL is not the same as the actual slug, redirect.
//		if (@$_GET["q3"] != $this->conversation["slug"]) {
//			header("HTTP/1.1 301 Moved Permanently");
//			redirect($this->conversation["id"], $this->conversation["slug"], !empty($_GET["start"]) ? "?start={$_GET["start"]}" : "");
//		}
		
		// If there is a slug in the URL, redirect.
//		if (!empty(@$_GET["q3"])) {
//			header("HTTP/1.1 301 Moved Permanently");
//			redirect($this->conversation["id"], !empty($_GET["start"]) ? "?start={$_GET["start"]}" : "");
//		}
		
		// Work out which post we are starting from.
		if (!empty($_GET["start"])) {
			switch ($_GET["start"]) {

				// Unread: get the user's lastRead from the database, and redirect to start from there.
				case "unread":
					$this->startFrom = max(0, min($this->conversation["lastRead"], $this->conversation["postCount"] - $config["postsPerPage"]));
					$limit = (int)$this->conversation["lastRead"];
					$postId = $this->eso->db->result($this->eso->db->query("SELECT postId FROM {$config["tablePrefix"]}posts WHERE conversationId={$this->conversation["id"]} ORDER BY time ASC LIMIT $limit, 1"), 0);
					redirect(conversationLink($this->conversation["id"], $this->conversation["slug"]), "?start=$this->startFrom", "#p$postId");
					break;

				// Last: redirect to the last post in the conversation.
				case "last":
					$this->startFrom = max(0, $this->conversation["postCount"] - $config["postsPerPage"]);
					$postId = $this->eso->db->result($this->eso->db->query("SELECT MAX(postId) FROM {$config["tablePrefix"]}posts WHERE conversationId={$this->conversation["id"]}"), 0);
					redirect(conversationLink($this->conversation["id"], $this->conversation["slug"]), "?start=$this->startFrom", "#p$postId");
					break;

				// With any luck, we can just use the start number in the URL.
				default:
					$this->startFrom = (int)$_GET["start"];
			}
		}
		
		// Make sure the startFrom number is within range.
		$this->startFrom = max(0, min($this->startFrom, $this->conversation["postCount"]));
		
		// Now perform various actions on the conversation/its posts if necessary.
		
		// Delete conversation: delete the conversation, then redirect to the index.
		if (isset($_GET["delete"]) and $this->eso->validateToken(@$_GET["token"]) and $this->deleteConversation()) {
			$this->eso->message("conversationDeleted");
			redirect("");
		}
		
		// Quote a post: get the post details (id, name, content) and then set the value of the reply textarea
		// appropriately.
		if (isset($_GET["quotePost"])) {
			$postId = (int)$_GET["quotePost"];
			$result = $this->eso->db->query("SELECT name, content FROM {$config["tablePrefix"]}posts INNER JOIN {$config["tablePrefix"]}members USING (memberId) WHERE postId=$postId AND conversationId={$this->conversation["id"]}");
			if (!$this->eso->db->numRows($result)) break;
			list($member, $content) = $this->eso->db->fetchRow($result);
			$this->conversation["draft"] = "<blockquote><cite>$member - [post:$postId]</cite>" . desanitize($this->formatForEditing($this->removeQuotes($content))) . "</blockquote>";
		}

		// Edit a post: set the $this->editingPost variable so that the post is outputted with a textarea later on.
		if (isset($_GET["editPost"])) {
			$this->editingPost = (int)$_GET["editPost"];
			// If the form was submitted, update the db and go back to the normal view.
			if ((isset($_POST["save"]) and $this->editPost($this->editingPost, $_POST["content"])) or isset($_POST["cancel"]))
				redirect(conversationLink($this->conversation["id"], $this->conversation["slug"]), "?start=$this->startFrom");
		}

		// Delete a post.
		if (isset($_GET["deletePost"]) and $this->eso->validateToken(@$_GET["token"])) {
			$this->deletePost((int)$_GET["deletePost"]);
			redirect(conversationLink($this->conversation["id"], $this->conversation["slug"]), "?start=$this->startFrom");
		}

		// Restore a post.
		if (isset($_GET["restorePost"]) and $this->eso->validateToken(@$_GET["token"])) {
			$this->restorePost((int)$_GET["restorePost"]);
			redirect(conversationLink($this->conversation["id"], $this->conversation["slug"]), "?start=$this->startFrom");
		}
		
		// Show a deleted post: set the $this->showingDeletedPost variable so that the post body is outputted later on.
		if (isset($_GET["showDeletedPost"])) $this->showingDeletedPost = (int)$_GET["showDeletedPost"];

		// Toggle sticky.
		if (isset($_GET["toggleSticky"]) and $this->eso->validateToken(@$_GET["token"])) {
			$this->toggleSticky();
			redirect(conversationLink($this->conversation["id"], $this->conversation["slug"]), "?start=$this->startFrom");
		}

		// Toggle locked.
		if (isset($_GET["toggleLock"]) and $this->eso->validateToken(@$_GET["token"])) {
			$this->toggleLock();
			redirect(conversationLink($this->conversation["id"], $this->conversation["slug"]), "?start=$this->startFrom");
		}
		
		// Update the conversation title/tags.
		if (isset($_POST["saveTitleTags"]) and $this->eso->validateToken(@$_POST["token"])) {
			$this->saveTitle($_POST["cTitle"]);
			$this->saveTags($_POST["cTags"]);
			redirect(conversationLink($this->conversation["id"], $this->conversation["slug"]), "?start=$this->startFrom");
		}
		
		// Change a member's group.
		if (isset($_POST["saveGroup"]) and $this->eso->validateToken(@$_POST["token"]))
			$this->eso->changeMemberGroup($_POST["member"], $_POST["group"]);
		
		// Save avatarAlignment to the session.
		if (!empty($_POST["avatarAlignment"]) and $this->eso->validateToken(@$_POST["token"]) and in_array($_POST["avatarAlignment"], array("alternate", "right", "left", "none"))) $_SESSION["avatarAlignment"] = $_POST["avatarAlignment"];
		
		// If the user is a guest, add an avatar alignment dropdown to the bar.
		if (!$this->eso->user) {
			// Construct the select options HTML.
			$avatarAlignmentOptions = "";
			$align = array("alternate" => $language["on alternating sides"], "right" => $language["on the right"], "left" => $language["on the left"], "none" => $language["do not display avatars"]);
			foreach ($align as $k => $v)
				$avatarAlignmentOptions .= "<option value='$k'" . (@$_SESSION["avatarAlignment"] == $k ? " selected='selected'" : "") . ">$v</option>";
			// Add it to the bar.
			$this->eso->addToBar("right", "<form action='" . curLink() . "' method='post' id='nav-dpav'><div><input type='hidden' name='token' value='{$_SESSION["token"]}'/>{$language["Display avatars"]}<select onchange='Conversation.changeAvatarAlignment(this.value)' name='avatarAlignment'>$avatarAlignmentOptions</select> <noscript><div style='display:inline'>" . $this->eso->skin->button(array("value" => $language["Save changes"])) . "</div></noscript></div></form>", 100);
		}

		// Add links to the bar.
		// Add the RSS feed button.
		$this->eso->addToBar("right", "<a href='" . makeLink("feed", "conversation", $this->conversation["id"]) . "' id='rss'><span class='button'><input type='submit' value='{$language["RSS"]}'></span></a>", 500);
		
		// Add the sticky/unsticky link if the user has permission.
		if ($this->canSticky() === true) $this->eso->addToBar("right", "<a href='" . makeLink(conversationLink($this->conversation["id"], $this->conversation["slug"]), "?toggleSticky", $this->startFrom ? "&start=$this->startFrom" : "", "&token={$_SESSION["token"]}") . "' onclick='Conversation.toggleSticky();return false'><span class='button'><input type='submit' id='stickyLink' value='" . $language[in_array("sticky", $this->conversation["labels"]) ? "Unsticky" : "Sticky"] . "'></span></a>", 400);
		
		// Add the lock/unlock link if the user has permission.
		if ($this->canLock() === true) $this->eso->addToBar("right", "<a href='" . makeLink(conversationLink($this->conversation["id"], $this->conversation["slug"]), "?toggleLock", $this->startFrom ? "&start=$this->startFrom" : "", "&token={$_SESSION["token"]}") . "' onclick='Conversation.toggleLock();return false'><span class='button'><input type='submit' id='lockLink' value='" . $language[$this->conversation["locked"] ? "Unlock" : "Lock"] . "'></span></a>", 300);
		
		// Add the delete conversation link if the user has permission.
		if ($this->canDeleteConversation() === true) $this->eso->addToBar("right", "<a href='" . makeLink(conversationLink($this->conversation["id"], $this->conversation["slug"]), "?delete", "&token={$_SESSION["token"]}") . "' onclick='return Conversation.deleteConversation()'><span class='button'><input type='submit' value='{$language["Delete conversation"]}'></span></a>", 200);
		
		// Update the user's last action.
		$this->updateLastAction();
		
		// Add the meta tags (description + keywords) to the head.
		$description = $this->eso->db->result("SELECT LEFT(content, 256) FROM {$config["tablePrefix"]}posts WHERE conversationId={$this->conversation["id"]} ORDER BY time ASC LIMIT 1", 0);
		if (strlen($description) > 255) $description = substr($description, 0, strrpos($description, " ")) . " ...";
		$description = strip_tags(str_replace(array("</p>", "</h3>", "</pre>"), " ", $description));
		$this->eso->addToHead("<meta name='keywords' content='" . str_replace(", ", ",", $this->conversation["tags"]) . "'/>");
		$this->eso->addToHead("<meta name='description' content='".sanitizeHTML($description)."'/>");
		$this->eso->addToHead("<meta property='og:description' content='".sanitizeHTML($description)."'/>");
		$this->eso->addToHead("<meta name='twitter:description' content='".sanitizeHTML($description)."'/>");
		
		// Add JavaScript variables which contain conversation information.
		$this->eso->addVarToJS("conversation", array(
			"id" => $this->conversation["id"],
			"postCount" => $this->conversation["postCount"],
			"startFrom" => $this->startFrom,
			"lastActionTime" => $this->conversation["lastActionTime"],
			"lastRead" => ($this->eso->user and $this->conversation["id"])
				? max(0, min($this->conversation["postCount"], $this->conversation["lastRead"]))
				: $this->conversation["postCount"],
			// Start the auto-reload interval at the square root of the number of seconds since the last action.
			"autoReloadInterval" => max(4, min(round(sqrt(time() - $this->conversation["lastActionTime"])), $config["autoReloadIntervalLimit"]))
		));
		
		// Update the user's last read.
		$this->updateLastRead(min($this->startFrom + $config["postsPerPage"], $this->conversation["postCount"]));

		$this->callHook("initExistingConversation");

		// Get the posts in the conversation.
		$this->conversation["posts"] = $this->getPosts(array("startFrom" => $this->startFrom, "limit" => $config["postsPerPage"]));
	
	}
	
	// If this conversation doesn't exist (it's a new conversation)...
	else {

		// Update the user's last action to say that they're "starting a conversation".
		$this->eso->updateLastAction($language["Starting a conversation"]);

		// If there's a member name in the querystring, make the conversation that we're starting private with them and
		// redirect.
		if (isset($_GET["member"]) and $this->eso->validateToken(@$_GET["token"])) {
			$_SESSION["membersAllowed"] = array();
			$this->conversation["membersAllowed"] = null;
			$this->addMember($_GET["member"]);
			redirect("conversation", "new");
		}
		
		$this->callHook("initNewConversation");
	}
	
	// The following actions can apply regardless of whether the conversation exists or not.

	// Remove a member from the membersAllowed list.
	if (isset($_GET["removeMember"]) and $this->eso->validateToken(@$_GET["token"])) {
		$this->removeMember($_GET["removeMember"]);
		redirect(conversationLink($this->conversation["id"], $this->conversation["slug"]), "?start=$this->startFrom");
	}

	// If the add member form has been submitted, attempt to add the member.
	if (isset($_POST["addMember"]) and $this->eso->validateToken(@$_POST["token"]))
		$result = $this->addMember($_POST["member"]);
	
	$this->callHook("init");
}

// Run AJAX actions.
function ajax()
{
	global $language, $config;
	
	if ($return = $this->callHook("ajax", null, true)) return $return;

	switch (@$_POST["action"]) {
		
		// Get the posts between $_POST["start"] and $_POST["end"], and/or update the user's lastRead.
		case "getPosts":
		case "updateLastRead":
			if (!$this->conversation = $this->getConversation((int)@$_POST["id"])) return;
			
			// Update the user's lastRead if specified.
			if (isset($_POST["updateLastRead"])) $this->updateLastRead($_POST["updateLastRead"]);
			
			// Work out the range of the posts that we are getting, and get them.
			if (!isset($_POST["start"]) and !isset($_POST["end"])) return;
			$start = min((int)@$_POST["start"], (int)@$_POST["end"]);
			$end = max((int)@$_POST["start"], (int)@$_POST["end"]);
			$posts = $this->getPosts(array("startFrom" => $start, "limit" => $end - $start + 1), true);
			
			// If there are posts, update the user's last action.
			if (count($posts)) $this->updateLastAction();
			return $posts;
			break;
						
		// Check for posts which have been created or edited after the specified action time.
		case "getNewPosts":
			if (!$this->conversation = $this->getConversation((int)@$_POST["id"])) return;
			if ($this->conversation["lastActionTime"] > @$_POST["lastActionTime"]) {
				$this->updateLastAction();
				$posts = $this->getPosts(array("lastActionTime" => @$_POST["lastActionTime"]), true);
				
				// If the user's browser will automatically show the new posts (as opposed to showing the "unread" part
				// of the pagination bar), work out how many new posts there are an update the user's lastRead to the
				// last visible post.
				if (isset($_POST["oldPostCount"])) {
					if ($this->conversation["postCount"] - $_POST["oldPostCount"] > $config["postsPerPage"])
						$this->updateLastRead($_POST["oldPostCount"] + $config["postsPerPage"]);
					else $this->updateLastRead($this->conversation["postCount"]);
				}
				return array(
					"postCount" => $this->conversation["postCount"],
					"newPosts" => $posts,
					"lastActionTime" => $this->conversation["lastActionTime"],
					"time" => time()
				);
			} else return array("time" => time());
			break;
		
		// Add a reply.
		case "addReply":
			if (!$this->eso->validateToken(@$_POST["token"])) return;
			if (!$this->conversation = $this->getConversation((int)@$_POST["id"])) return;
			if (!($id = $this->addReply(@$_POST["content"]))) return;
			
			// We'll need to return any posts on the last page which aren't in the JavaScript cache;
			// the most recent post that is in the JavaScript cache is specified in $_GET["haveDataUpTo"].
			if (isset($_POST["haveDataUpTo"])) {
				$startFrom = max($this->conversation["postCount"] - $config["postsPerPage"], $_POST["haveDataUpTo"]);
				$posts = $this->getPosts(array("startFrom" => $startFrom, "limit" => $this->conversation["postCount"] - $startFrom), true);
			}
			return array(
				"postCount" => $this->conversation["postCount"],
				"posts" => $posts,
				"replyId" => $id,
				"lastActionTime" => time()
			);
			break;

		// Start a conversation.
		case "startConversation":
			if (!$this->eso->validateToken(@$_POST["token"])) return;
			$this->conversation = $this->getConversation();
			if (!$conversationId = $this->startConversation(array(
				"title" => @$_POST["title"],
				"starred" => @$_SESSION["starred"],
				"tags" => @$_POST["tags"],
				"membersAllowed" => @$_SESSION["membersAllowed"],
				"draft" => @$_POST["draft"],
				"content" => @$_POST["content"]
			))) return;
			return array("redirect" => $config["baseURL"] . makeLink(conversationLink($conversationId, $this->conversation["slug"])));
			break;

		// Save a draft.
		case "saveDraft":
			if (!$this->eso->validateToken(@$_POST["token"])) return;
			if (!$this->conversation = $this->getConversation((int)@$_POST["id"])) return;
			$this->saveDraft(@$_POST["content"]);
			break;

		// Discard the draft.
		case "discardDraft":
			if (!$this->eso->validateToken(@$_POST["token"])) return;
			if (!$this->conversation = $this->getConversation((int)@$_POST["id"])) return;
			$this->discardDraft();
			break;
		
		// Delete a post.
		case "deletePost":
			if (!$this->eso->validateToken(@$_POST["token"])) return;
			$postId = (int)@$_POST["postId"];
			if (!$this->conversation = $this->getConversation("(SELECT conversationId FROM {$config["tablePrefix"]}posts WHERE postId=$postId)")) return;
			$this->deletePost($postId);
			break;
			
		// Restore a post.
		case "restorePost":
			if (!$this->eso->validateToken(@$_POST["token"])) return;
			$postId = (int)@$_POST["postId"];
			if (!$this->conversation = $this->getConversation("(SELECT conversationId FROM {$config["tablePrefix"]}posts WHERE postId=$postId)")) return;
			
			// After restoring the post, return its details from the database.
			if ($this->restorePost($postId)) return $this->getPosts(array("postIds" => $postId));
			break;

		// Edit a post.
		case "editPost":
			if (!$this->eso->validateToken(@$_POST["token"])) return;
			$postId = (int)@$_POST["postId"];
			if (!$this->conversation = $this->getConversation("(SELECT conversationId FROM {$config["tablePrefix"]}posts WHERE postId=$postId)")) return;
			if ($content = $this->editPost($postId, @$_POST["content"])) return array("content" => $this->displayPost($content));
			break;

		// Get the controls and content of for a post to be edited.
		case "getEditPost":
			$postId = (int)@$_POST["postId"];
			if (!$this->conversation = $this->getConversation("(SELECT conversationId FROM {$config["tablePrefix"]}posts WHERE postId=$postId)")) return;
			
			// Get the post details from the database so we can check if the user has permission to edit it.
			list($memberId, $account, $deleteMember, $content) = $this->eso->db->fetchRow("SELECT p.memberId, account, deleteMember, content FROM {$config["tablePrefix"]}posts p INNER JOIN {$config["tablePrefix"]}members USING (memberId) WHERE postId=$postId");
			if (($error = $this->canEditPost($postId, $memberId, $account, $deleteMember)) !== true) $this->eso->message($error);
			
			// Return an array containing the formatting controls and the editing textarea/buttons.
			else return array(
				"controls" => implode(" ", $this->getEditControls("p$postId")),
				"body" => $this->getEditArea($postId, $this->formatForEditing($content))
			);
			break;
			
		// Show a deleted post.
		case "showDeletedPost":
			$postId = (int)@$_POST["postId"];
			if (!$this->conversation = $this->getConversation("(SELECT conversationId FROM {$config["tablePrefix"]}posts WHERE postId=$postId)")) return;
			
			// Get the post details from the database so we can check if the user has permission to view it.
			list($memberId, $account, $deleteMember, $content) = $this->eso->db->fetchRow("SELECT p.memberId, account, deleteMember, content FROM {$config["tablePrefix"]}posts p INNER JOIN {$config["tablePrefix"]}members USING (memberId) WHERE postId=$postId");
			if (($message = $this->canEditPost($postId, $memberId, $account, $deleteMember)) !== true) $this->eso->message($message);
			else return $this->displayPost($content);
			break;

		// Get the unformatted content of a post for inserting into the reply textarea as a quote.
		case "getPost":
			$postId = (int)@$_POST["postId"];
			if (!$this->conversation = $this->getConversation("(SELECT conversationId FROM {$config["tablePrefix"]}posts WHERE postId=$postId)")) return;
			list($member, $content) = $this->eso->db->fetchRow($this->eso->db->query("SELECT name, content FROM {$config["tablePrefix"]}posts INNER JOIN {$config["tablePrefix"]}members USING (memberId) WHERE postId=$postId"));
			return array(
				"member" => $member . " - [post:$postId]",
				"content" => desanitize($this->formatForEditing($this->removeQuotes($content))),
			);
			break;

		// Get the formatted HTML of a string for previewing purposes.
		case "getPostFormatted":
			if (empty($_POST["content"])) return;
			return $this->displayPost($this->formatForDisplay($_POST["content"]));
			break;

		// Add a member to the membersAllowed list.
		case "addMember":
			if (!$this->eso->validateToken(@$_POST["token"])) return;
			if (!$this->conversation = $this->getConversation(isset($_POST["id"]) ? (int)$_POST["id"] : false)) return;
			$this->conversation["membersAllowed"] =& $this->getMembersAllowed();
			if ($this->addMember(@$_POST["member"])) return array("list" => $this->htmlMembersAllowedList($this->conversation["membersAllowed"]), "private" => $this->conversation["private"]);
			break;

		// Remove a member from the membersAllowed list.
		case "removeMember":
			if (!$this->eso->validateToken(@$_POST["token"])) return;
			if (!$this->conversation = $this->getConversation(isset($_POST["id"]) ? (int)$_POST["id"] : false)) return;
			$this->conversation["membersAllowed"] =& $this->getMembersAllowed();
			if ($this->removeMember(@$_POST["member"])) return array("list" => $this->htmlMembersAllowedList($this->conversation["membersAllowed"]), "private" => $this->conversation["private"]);
			break;

		// Save conversation tags.
		case "saveTags":
			if (!$this->eso->validateToken(@$_POST["token"])) return;
			if (!$this->conversation = $this->getConversation((int)@$_POST["id"])) return;
			$this->saveTags(@$_POST["tags"]);
			return desanitize($this->conversation["tags"]);
			break;

		// Save the conversation title.
		case "saveTitle":
			if (!$this->eso->validateToken(@$_POST["token"])) return;
			if (!$this->conversation = $this->getConversation((int)@$_POST["id"])) return;
			$this->saveTitle(@$_POST["title"]);
			return desanitize($this->conversation["title"]);
			break;

		// Toggle sticky/unsticky.
		case "toggleSticky":
			if (!$this->eso->validateToken(@$_POST["token"])) return;
			if (!$this->conversation = $this->getConversation((int)@$_POST["id"])) return;
			$this->toggleSticky();
			break;

		// Toggle locked/unlocked.
		case "toggleLock":
			if (!$this->eso->validateToken(@$_POST["token"])) return;
			if (!$this->conversation = $this->getConversation((int)@$_POST["id"])) return;
			$this->toggleLock();
			break;

		// Change a member's group.
		case "changeMemberGroup":
			if (!$this->eso->validateToken(@$_POST["token"])) return;
			$this->eso->changeMemberGroup(@$_POST["memberId"], @$_POST["group"]);
			break;
		
		// Save avatarAlignment to the session.
		case "saveAvatarAlignment":
			if (!$this->eso->validateToken(@$_POST["token"])) return;
			if (!empty($_POST["avatarAlignment"]) and in_array($_POST["avatarAlignment"], array("alternate", "right", "left", "none"))) $_SESSION["avatarAlignment"] = $_POST["avatarAlignment"];
	}
}

// Get the conversation details.
function getConversation($id = false)
{
	global $config, $language;
	
	// If an id is specified, pull the conversation details from the database.
	if ($id !== false) {

		// Begin constructing the query components.
		$select = array("c.conversationId AS id", "c.title AS title", "c.slug AS slug", "c.locked AS locked", "c.startMember AS startMember", "sm.name AS startMemberName", "c.posts AS postCount", "GROUP_CONCAT(t.tag ORDER BY t.tag ASC SEPARATOR ', ') AS tags", "c.private AS private", "s.lastRead AS lastRead", "s.draft AS draft", "s.starred AS starred", "IF(c.lastActionTime IS NULL,0,c.lastActionTime) AS lastActionTime");
		
		// Add labels to the 'select' part of the query, only if we're loading the page from scratch (not ajax).
		// The eso labels array contains SQL conditions that must be satisfied for that label to be 'active'.
		// Here we concatinate all these conditions into a string which can then be exploded and the active labels can
		// be determined.
		// e.g. The condition for the sticky label is: IF(sticky,1,0)
		// Therefore CONCAT(IF(sticky,1,0), ',', ...) may return '1,...' implying that the sticky label is active.
		$labels = "CONCAT(";
		// Loop through the labels to check if they apply for this conversation.
		foreach ($this->eso->labels as $k => $v) $labels .= "$v,',',";
		$labels = substr($labels, 0, -5) . ") AS labels";
		$select[] = $labels;

		$from = array(
			"{$config["tablePrefix"]}conversations c",
			"LEFT JOIN {$config["tablePrefix"]}tags t USING (conversationId)",
			"LEFT JOIN {$config["tablePrefix"]}status s ON (s.conversationId=c.conversationId AND s.memberId=" . ($this->eso->user["memberId"] ? $this->eso->user["memberId"] : 0) . ")",
			"INNER JOIN {$config["tablePrefix"]}members sm ON (sm.memberId=c.startMember)"
		);

		$where = array("c.conversationId=$id");
		if (!$this->eso->user) $where[] = "c.posts>0 AND c.private=0";
		else $where[] = "c.startMember={$this->eso->user["memberId"]} OR (c.posts>0 AND (c.private=0 OR s.allowed OR (SELECT allowed FROM {$config["tablePrefix"]}status WHERE conversationId=c.conversationId AND memberId='{$this->eso->user["account"]}')))";

		$groupBy = array("c.conversationId");
		$limit = 1;

		// Put together the components array.
		$components = array("select" => $select, "from" => $from, "where" => $where, "groupBy" => $groupBy, "limit" => $limit);
		
		$this->callHook("beforeGetConversation", array(&$components));
		
		// Compile the components into a query.
		$query = $this->eso->db->constructSelectQuery($components);

		// Run the query.
		$result = $this->eso->db->query($query);
		if (!$this->eso->db->numRows($result)) return false;

		// Get all the details from the result into an array.
		$conversation = $this->eso->db->fetchAssoc($result);
		
		// Explode the labels string and determine which labels are active. See the massive comment about a
		// screen-height up.
		$labels = explode(",", $conversation["labels"]); $i = 0;
		$conversation["labels"] = array();
		foreach ($this->eso->labels as $k => $v) {
			if ($labels[$i]) $conversation["labels"][] = $k;
			$i++;
		}
		
		$this->callHook("afterGetConversation", array(&$conversation));

		return $conversation;
	}

	// Return a new, blank conversation.
	else {
		$conversation = array(
			"id" => false,
			"labels" => array(),
			"private" => true,
			"tags" => !empty($_POST["cTags"]) ? $_POST["cTags"] : $language["exampleTags"],
			"title" => !empty($_POST["cTitle"]) ? $_POST["cTitle"] :
				((!empty($_SESSION["membersAllowed"]) and is_array($_SESSION["membersAllowed"]))
					? sprintf($language["Start a private conversation"], reset($_SESSION["membersAllowed"]))
					: $language["Enter a conversation title"]),
			"slug" => "",
			"startMember" => $this->eso->user["memberId"],
			"startMemberName" => $this->eso->user["name"],
			"draft" => "",
			"starred" => @$_SESSION["starred"],
			"locked" => false,
			"lastActionTime" => null,
			"postCount" => 0
		);
		// Add the private label if necessary.
		if (!empty($_SESSION["membersAllowed"])) $conversation["labels"][] = "private";
		
		$this->callHook("getNewConversation", array(&$conversation));
		
		return $conversation;
	}
}

// Get a list of the members allowed in the conversation.
function &getMembersAllowed()
{
	global $config;
	$membersAllowed = array();
	
	// If the conversation is not private, then everyone can view it - return an empty array.
	if (!$this->conversation["private"]) return $membersAllowed;
	
	// If the conversation doesn't exist, we'll be storing the members allowed in the session.
	if (!$this->conversation["id"]) $membersAllowed =& $_SESSION["membersAllowed"];
	
	// Otherwise, fetch the members allowed from the database.
	else {
		$result = $this->eso->db->query("SELECT s.memberId, IF(m.name IS NOT NULL,m.name,s.memberId) FROM {$config["tablePrefix"]}status s LEFT JOIN {$config["tablePrefix"]}members m USING (memberId) WHERE s.conversationId={$this->conversation["id"]} AND s.allowed=1");
		while (list($memberId, $name) = $this->eso->db->fetchRow($result)) $membersAllowed[$memberId] = $name;
	}
	
	$this->callHook("getMembersAllowed", array(&$membersAllowed));
	
	return $membersAllowed;
}

// Get the posts for this conversation into a nice array.
// $criteria is an array with one or more of the following keys:
// "startFrom": start from this offset
// "limit": only fetch this many posts
// "lastActionTime": get all posts created or modified after this time
// "postIds": get posts matching this comma-separated list of post ids.
// $display determines whether or not to run the post content through the $this->displayPost() function.
function getPosts($criteria = array(), $display = false)
{
	global $language, $config;
	
	$startFrom = (int)@$criteria["startFrom"];
	$limit = (int)@$criteria["limit"];

	// Construct the select component of the query.
	$select = array("p.postId AS id", "m.memberId AS memberId", "m.name AS name", "m.account AS account", "m.color AS color", "m.avatarFormat AS avatarFormat", "p.content AS content", "p.time AS time", "em.name AS editMember", "p.editTime AS editTime", "dm.name AS deleteMember", "m.lastSeen AS lastSeen", "IF(" . (time() - $config["userOnlineExpire"]) . " < m.lastSeen,m.lastAction,'') AS lastAction");
	
	// If we're getting posts based on the lastActionTime or specific post IDs, we'll need to find the position within
	// the conversation of each post.
	if (isset($criteria["lastActionTime"]) or isset($criteria["postIds"])) $select[] = "(SELECT COUNT(*) FROM {$config["tablePrefix"]}posts p2 WHERE p2.conversationId=p.conversationId AND p2.time<=p.time AND IF(p2.time=p.time,p2.postId<p.postId,1)) AS number";
	
	// From...
	$from = array("{$config["tablePrefix"]}posts p", "LEFT JOIN {$config["tablePrefix"]}members m USING (memberId)", "LEFT JOIN {$config["tablePrefix"]}members em ON (em.memberId=p.editMember)", "LEFT JOIN {$config["tablePrefix"]}members dm ON (dm.memberId=p.deleteMember)");
	
	// Where (determined by the contents of $criteria)...
	$where = array("p.conversationId={$this->conversation["id"]}");
	if (isset($criteria["lastActionTime"])) $where[] = "time>" . (int)$criteria["lastActionTime"] . " OR editTime>" . (int)$criteria["lastActionTime"];
	if (isset($criteria["postIds"])) $where[] = "p.postId IN ({$criteria["postIds"]})";
	
	// Compile the query components.
	$components = array("select" => $select, "from" => $from, "where" => $where, "orderBy" => array("p.time ASC"), "limit" => $limit ? "$startFrom,$limit" : "");
	
	$this->callHook("beforeGetPosts", array(&$components));
		
	// Run the query.
	$query = $this->eso->db->constructSelectQuery($components);
	$result = $this->eso->db->query($query);
	$posts = array();
	$i = $startFrom;

	// Loop through the posts and compile them into an array.
	while ($post = $this->eso->db->fetchAssoc($result)) {
		
		// Make sure the post color is in range.
		$post["color"] = min($post["color"], $this->eso->skin->numberOfColors);
		
		// $k is the position of the post within the conversation, and will depend on if we've fetched posts
		// sequentially ($i) or arbitrarily ($post["number"] if $criteria["lastActionTime"] or $criteria["postIds"] have
		// been used.)
		$k = isset($post["number"]) ? $post["number"] : $i;
				
		// Build the post array.
		$posts[$k] = array(
			"id" => $post["id"],
			"memberId" => $post["memberId"],
			"name" => $post["name"],
			"date" => date($language["dateFormat"], $post["time"]),
			"time" => $post["time"],
			"editTime" => $post["editTime"],
			"canEdit" => $this->canEditPost($post["id"], $post["memberId"], $post["account"], $post["deleteMember"]) === true,
			"info" => array(),
			"controls" => array()
		) + (!$post["deleteMember"]
		// Extra information if the post *hasn't* been deleted.
		? array(
			"color" => $post["color"],
			"account" => $post["account"],
			"accounts" => $this->eso->canChangeGroup($post["memberId"], $post["account"]),
			"body" => $display ? $this->displayPost($post["content"]) : $post["content"],
			"avatar" => $this->eso->getAvatar($post["memberId"], $post["avatarFormat"]),
			"thumb" => $this->eso->getAvatar($post["memberId"], $post["avatarFormat"], "thumb"),
			"editMember" => $post["editMember"],
			"lastAction" => strip_tags($post["lastAction"])
		// Extra information if the post *has* been deleted.
		) : array("deleteMember" => $post["deleteMember"]));
		
		// If this is a deleted post and we're showing it, include the post content in the array.
		if ($post["deleteMember"] and $this->showingDeletedPost == $post["id"]) $posts[$k]["body"] = $post["content"];
		
		$this->callHook("getPost", array(&$posts[$k], $post));
		
		$i++;
	}

	$this->callHook("afterGetPosts", array(&$posts));

	return $posts;
}

// Update the member's last read status in the db.
function updateLastRead($lastRead)
{
	if (!$this->eso->user) return;
	
	global $config;
	$lastRead = min($lastRead, $this->conversation["postCount"]);
	
	// Only update it if they've just read further than they've ever read before.
	if ($lastRead > $this->conversation["lastRead"]) {
		$this->eso->db->query("INSERT INTO {$config["tablePrefix"]}status (conversationId, memberId, lastRead)
			VALUES ({$this->conversation["id"]}, {$this->eso->user["memberId"]}, $lastRead)
			ON DUPLICATE KEY UPDATE lastRead=$lastRead");
		$this->conversation["lastRead"] = $lastRead;
	}
}

// Add a reply to this conversation.
function addReply($content, $newConversation = false)
{
	global $config;

	// Does the user have permission? Is the post content valid? Flood control?
	$hookError = $this->callHook("validateAddReply", array(&$content), true);
	if (($error = $this->canReply()) !== true or ($error = $this->validatePost($content))
		or (!$newConversation and ($error = $this->eso->db->result("SELECT 1 FROM {$config["tablePrefix"]}posts WHERE memberId={$this->eso->user["memberId"]} AND time>" . (time() - $config["timeBetweenPosts"]), 0) ? "waitToReply" : false))
		or ($error = $hookError)) {
		$this->eso->message($error);
		return false;
	}

	// Prepare the post details for the query.
	$formattedContent = $this->eso->db->escape($this->formatForDisplay($content));
	$time = time();
	$post = array(
		"conversationId" => $this->conversation["id"],
		"memberId" => $this->eso->user["memberId"],
		"time" => $time,
		"content" => "'$formattedContent'",
		"title" => "'{$this->conversation["title"]}'"
	);
	
	$this->callHook("beforeAddReply", array(&$post));

	// Construct the query and insert the post into the posts table.
	$this->eso->db->query($this->eso->db->constructInsertQuery("posts", $post));
	$id = $this->eso->db->lastInsertId();
	
	// If this is not a post being created in a brand new conversation...
	if (!$newConversation) {
	
		// Update the conversations table with the new post count, last post/action times, and last post member.
		// Also update the conversation's start time if this is the first post.
		$this->eso->db->query("UPDATE {$config["tablePrefix"]}conversations SET startTime=IF(posts IS NULL OR posts=0,$time,startTime), posts=IF(posts IS NOT NULL,posts+1,1), lastPostTime=$time, lastActionTime=$time, lastPostMember={$this->eso->user["memberId"]} WHERE conversationId={$this->conversation["id"]}");
		
		// If the user had a draft saved in this conversation before adding this reply, erase it now.
		if ($this->conversation["draft"])
			$this->eso->db->query("UPDATE {$config["tablePrefix"]}status SET draft=NULL WHERE conversationId={$this->conversation["id"]} AND memberId={$this->eso->user["memberId"]}");
	
		// Email people who have starred this conversation and want an email!
		// Conditions for the query: the member isn't themselves, they have ticked 'email me' in My settings, they've
		// starred the conversation, they have no unread posts in the conversation (apart from this one), and they're
		// not online at the moment.
		$query = "SELECT name, email, language
			FROM {$config["tablePrefix"]}members m
			LEFT JOIN {$config["tablePrefix"]}status s ON (s.conversationId={$this->conversation["id"]} AND s.memberId=m.memberId)
			WHERE m.memberId!={$this->eso->user["memberId"]} AND m.emailOnStar=1 AND s.starred=1 AND s.lastRead>={$this->conversation["postCount"]} AND (m.lastSeen IS NULL OR " . (time() - $config["userOnlineExpire"]) . ">m.lastSeen)";
		$result = $this->eso->db->query($query);
		global $versions;
		while (list($name, $email, $language) = $this->eso->db->fetchRow($result)) {
			include "languages/" . sanitizeFileName(file_exists("languages/$language.php") ? $language : $config["language"]) . ".php";
			sendEmail($email, vsprintf($language["emails"]["newReply"]["subject"], array($name, $this->conversation["title"])), vsprintf($language["emails"]["newReply"]["body"], array($name, $this->eso->user["name"], $this->conversation["title"], $config["baseURL"] . makeLink(conversationLink($this->conversation["id"], $this->conversation["slug"]), "unread"))));
			unset($langauge, $messages);
		}
	}

	// Update local conversation details.
	$this->conversation["postCount"]++;
	$this->conversation["draft"] = null;
	
	// If this is the first reply (ie. the conversation was a draft and now it isn't), email people who are in the
	// membersAllowed list.
	if ($this->conversation["postCount"] == 1 and !empty($this->conversation["membersAllowed"]))
		$this->emailPrivateAdd(array_keys($this->conversation["membersAllowed"]), true);
	
	$this->callHook("afterAddReply", array($id));
	
	return $id;
}

// Save a draft.
function saveDraft($content)
{
	global $language, $config;

	// Does the user have permission?
	if (($error = $this->canReply()) !== true or ($error = $this->validatePost($content))) {
		$this->eso->message($error);
		return false;
	}
	
	$this->callHook("saveDraft", array(&$content));
	
	// We need to use $this->eso->db->escape here because the content is raw, ie. we don't want to sanitize() it.
	$slashedContent = $this->eso->db->escape($content);
	$this->eso->db->query("INSERT INTO {$config["tablePrefix"]}status (conversationId, memberId, draft) VALUES ({$this->conversation["id"]}, {$this->eso->user["memberId"]}, '$slashedContent') ON DUPLICATE KEY UPDATE draft='$slashedContent'");
	
	// Update local conversation details.
	$this->conversation["draft"] = $content;
	if (!in_array("draft", $this->conversation["labels"])) $this->conversation["labels"][] = "draft";
	
	return true;
}

// Discard the user's draft.
function discardDraft()
{
	if (!$this->eso->user) return false;
	global $config;

	$query = "UPDATE {$config["tablePrefix"]}status SET draft=NULL WHERE conversationId={$this->conversation["id"]} AND memberId={$this->eso->user["memberId"]}";
	$this->eso->db->query($query);

	// Remove the draft label
	unset($this->conversation["labels"][array_search("draft", $this->conversation["labels"])]);
	$this->conversation["draft"] = null;
	
	$this->callHook("discardDraft");
	
	return true;
}

// Edit a post.
function editPost($postId, $content)
{
	global $config;
	$postId = (int)$postId;
	
	// Does the user have permission? Is the post content valid?
	if (($error = $this->canEditPost($postId)) !== true or ($error = $this->validatePost($content))) {
		$this->eso->message($error);
		return false;
	}
	
	// Allow the "editPost" hook to halt the process.
	if ($error = $this->callHook("editPost", array($postId, &$content, true))) {
		$this->eso->message($error);
		return false;
	}

	// Update the database with the post's new formatted content.
	$content = $this->formatForDisplay($content);
	$time = time();
	$query = "UPDATE {$config["tablePrefix"]}posts p, {$config["tablePrefix"]}conversations c SET p.content='" . $this->eso->db->escape($content) . "', p.editMember={$this->eso->user["memberId"]}, p.editTime=$time, c.lastActionTime=$time WHERE postId=$postId AND c.conversationId=p.conversationId";
	$this->eso->db->query($query);
	
	return $content;
}

// Return an array of formatting buttons for editing a post.
function getEditControls($id)
{
	global $language, $config;
	
	$controls = array(
		50 => "<span class='formattingButtons'>",
		100 => "<a href='javascript:Conversation.bold(\"$id\");void(0)' id='format-b' title='&lt;b&gt;{$language["Bold"]}&lt;/b&gt;' accesskey='b'><span>{$language["Bold"]}</span></a>",
		200 => "<a href='javascript:Conversation.italic(\"$id\");void(0)' id='format-i' title='&lt;i&gt;{$language["Italic"]}&lt;/i&gt;' accesskey='i'><span>{$language["Italic"]}</span></a>",
		300 => "<a href='javascript:Conversation.header(\"$id\");void(0)' id='format-h' title='&lt;h1&gt;{$language["Header"]}&lt;/h1&gt;' accesskey='h'><span>{$language["Header"]}</span></a>",
		400 => "<a href='javascript:Conversation.strikethrough(\"$id\");void(0)' id='format-s' title='&lt;s&gt;{$language["Strike"]}&lt;/s&gt;' accesskey='t'><span>{$language["Strike"]}</span></a>",
		500 => "<a href='javascript:Conversation.link(\"$id\");void(0)' id='format-a' class='link' title='&lt;a href=&#39;https://example.com&#39;&gt;{$language["Link"]}&lt;/a&gt;' accesskey='l'><span>{$language["Link"]}</span></a>",
		600 => "<a href='javascript:Conversation.image(\"$id\");void(0)' id='format-img' title='{$language["Image"]} &lt;img src=&#39;https://example.com/image.jpg&#39;&gt;' accesskey='m'><span>{$language["Image"]}</span></a>",
		700 => "<a href='javascript:Conversation.video(\"$id\");void(0)' id='format-video' title='{$language["Video"]} &lt;video src=&#39;https://example.com/video.mp4&#39;&gt;' accesskey='v'><span>{$language["Video"]}</span></a>",
		800 => "<a href='javascript:Conversation.quote(\"$id\");void(0)' id='format-quote' title='&lt;blockquote&gt;{$language["Quote"]}&lt;/blockquote&gt;' accesskey='q'><span>{$language["Quote"]}</span></a>",
		900 => "<a href='javascript:Conversation.fixed(\"$id\");void(0)' id='format-code' title='&lt;pre&gt;{$language["Fixed"]}&lt;/pre&gt;' accesskey='f'><span>{$language["Fixed"]}</span></a>",
		950 => "</span>",
		1000 => "<span class='formattingCheckbox'><input type='checkbox' id='$id-previewCheckbox' class='checkbox' onclick='Conversation.togglePreview(\"$id\",this.checked)' accesskey='p'/> <label for='$id-previewCheckbox'>{$language["Preview"]}</label></span>",
	);
	
	$this->callHook("getEditControls", array(&$controls));
	
	return $controls;
}

// Returns the HTML of a textarea and buttons for when a post is being edited.
function getEditArea($postId, $content)
{
	global $language;
	
	$html = "<form action='" . curLink() . "' method='post' enctype='multipart/form-data'><div class='widthFixer'>
<input type='hidden' name='token' value='{$_SESSION["token"]}'/>
<textarea cols='100' rows='10' id='p$postId-textarea' name='content'>$content</textarea>
<div id='p$postId-preview'></div>
</div>
<div class='editButtons'>
" . $this->eso->skin->button(array("name" => "cancel", "class" => "big", "value" => $language["Cancel"], "onclick" => "Conversation.cancelEdit($postId);return false", "tabindex" => "-1")) . "
" . $this->eso->skin->button(array("name" => "save", "class" => "big submit", "value" => $language["Save post"], "onclick" => "Conversation.saveEditPost($postId,document.getElementById(\"p$postId-textarea\").value);return false", "accesskey" => "s")) . "
</div>
</form>";

	$this->callHook("getEditArea", array(&$html));
	
	return $html;
}

// Convert a post from HTML back to formatting code.
function formatForEditing($content)
{
	$formatters = array();
	$this->callHook("formatForEditing", array(&$content, &$formatters));
	return $this->eso->formatter->revert($content, count($formatters) ? $formatters : false);
}

// Convert a post from formatting code into HTML.
function formatForDisplay($content)
{
	$formatters = array();
	$this->callHook("formatForDisplay", array(&$content, &$formatters));
	return $this->eso->formatter->format($content, count($formatters) ? $formatters : false);
}

// Perform render-level formatting on a post: highlight search keywords and translate "go to this post" links.
function displayPost($content)
{
	// Highlight search keywords.
	if (!empty($_SESSION["highlight"])) $content = highlight($content, $_SESSION["highlight"]);
	
	// Replace empty post links with "go to this post" links.
	global $language;
	$content = preg_replace("`(<a href='" . str_replace("?", "\?", makeLink("post", "(\d+)")) . "'[^>]*>)<\/a>`", "$1{$language["go to this post"]}</a>", $content);
	
	$this->callHook("displayPost", array(&$content));
	return $content;
}

// Validate a post - make sure it's not too long but has at least one character.
function validatePost($post)
{
	global $config;
	if (function_exists('mb_strlen')) { // Use multibytes for UTF-8 support.
		if (mb_strlen($post, "UTF-8") > $config["maxCharsPerPost"]) return "postTooLong";
		if (!mb_strlen($post, "UTF-8")) return "emptyPost";
	} else {
		if (strlen($post) > $config["maxCharsPerPost"]) return "postTooLong";
		if (!strlen($post)) return "emptyPost";
	}
	return $this->callHook("validatePost", array(&$post), true);
}

// Delete a post.
function deletePost($postId)
{
	// Don't even bother trying if they're not logged in.
	if (!$this->eso->user or $this->eso->isUnvalidated()) {
		$this->eso->message("noPermission");
		return false;
	}
	// Is the user suspended?
	if ($this->eso->isSuspended()) {
		$this->eso->message("suspended");
		return false;
	}
	
	// Delete the post (don't actually delete it, just mark it as deleted.)
	global $config;
	$postId = (int)$postId;
	$time = time();
	$query = "UPDATE {$config["tablePrefix"]}posts p, {$config["tablePrefix"]}conversations c
		SET p.deleteMember={$this->eso->user["memberId"]}, p.editTime=$time, c.lastActionTime=$time
		WHERE postId=$postId AND c.conversationId=p.conversationId" . ($this->eso->user["moderator"] ? "" : " AND p.memberId={$this->eso->user["memberId"]}");	
	$this->eso->db->query($query);
	
	// If the query didn't affect any rows, either we didn't have permission or the post didn't exist...
	if (!$this->eso->db->affectedRows()) {
		$this->eso->message("noPermission");
		return false;
	}
	
	$this->callHook("deletePost", array($postId));
	
	return true;
}

// Restore a post.
function restorePost($postId)
{
	// Don't even bother trying if they're not logged in.
	if (!$this->eso->user or $this->eso->isUnvalidated()) {
		$this->eso->message("noPermission");
		return false;
	}	
	// Is the user suspended?
	if ($this->eso->isSuspended()) {
		$this->eso->message("suspended");
		return false;
	}
	
	// Restore the post (just mark it as *not* deleted.)
	global $config;
	$postId = (int)$postId;
	$time = time();
	$this->eso->db->query("UPDATE {$config["tablePrefix"]}posts p, {$config["tablePrefix"]}conversations c
		SET p.deleteMember=NULL, p.editMember={$this->eso->user["memberId"]}, p.editTime=$time, c.lastActionTime=$time
		WHERE postId=$postId AND c.conversationId=p.conversationId" . ($this->eso->user["moderator"] ? "" : " AND p.memberId={$this->eso->user["memberId"]}"));
			
	// If the query didn't affect any rows, either we didn't have permission or the post didn't exist...
	if (!$this->eso->db->affectedRows()) {
		$this->eso->message("noPermission");
		return false;
	}
	
	$this->callHook("restorePost", array($postId));
	
	return true;
}

// Start a conversation.
function startConversation($conversation)
{
	global $config, $language;
	
	// Does the user have permission?
	// Impose some flood control measures.
	$time = time() - $config["timeBetweenPosts"];
	if (($error = $this->canStartConversation()) !== true
		or ($error = $this->eso->db->result("SELECT MAX(startTime)>$time OR MAX(time)>$time FROM {$config["tablePrefix"]}conversations, {$config["tablePrefix"]}posts WHERE startMember={$this->eso->user["memberId"]} AND memberId={$this->eso->user["memberId"]}", 0) ? "waitToReply" : false)) {
		$this->eso->message($error);
		return false;
	}
	
	// If the title is blank but the user is only saving a draft, call it "Untitled conversation."
	if ($conversation["draft"] and !$conversation["title"]) $conversation["title"] = $language["Untitled conversation"];
	
	// Check for errors; validate the title and the post content.
	$hookError = $this->callHook("validateStartConversation", array(&$conversation), true);
	if (($error = $this->validateTitle($conversation["title"])) or ($error = $this->validatePost($conversation["content"]))
		or ($error = $hookError)) {
		$this->eso->message($error);
		return false;
	}
	
	// Construct the INSERT query.
	$time = time();
	$slug = slug($conversation["title"]);
	$insert = array(
		"title" => "'" . $this->eso->db->escape($conversation["title"]) . "'",
		"slug" => "'$slug'",
		"startMember" => $this->eso->user["memberId"],
		"startTime" => $time,
		"lastPostTime" => $time,
		"lastPostMember" => $this->eso->user["memberId"],
		"lastActionTime" => $time,
		"private" => (is_array($conversation["membersAllowed"]) and count($conversation["membersAllowed"])) ? "1" : "0",
		"posts" => $conversation["draft"] ? "0" : "1"
	);
	
	$this->callHook("beforeStartConversation", array(&$insert, $conversation));
	
	// Insert the conversation into the database.	
	$query = $this->eso->db->constructInsertQuery("conversations", $insert);
	$this->eso->db->query($query);
	$conversationId = $this->eso->db->lastInsertId();
	
	// Update the local conversation variable.
	$this->conversation["id"] = $conversationId;
	$this->conversation["membersAllowed"] = $conversation["membersAllowed"];
	$this->conversation["startMember"] = $this->eso->user["memberId"];
	$this->conversation["startMemberName"] = $this->eso->user["name"];
	$this->conversation["title"] = $conversation["title"];
	$this->conversation["slug"] = $slug;

	// Add the first post or save the draft.
	if (!($conversation["draft"] ? $this->saveDraft($conversation["content"]) : $this->addReply($conversation["content"], true))) {
		$this->eso->db->query("DELETE FROM {$config["tablePrefix"]}conversations WHERE conversationId=$conversationId");
		$this->conversation["id"] = null;
		return false;
	}
	
	// Save tags.
	$this->conversation["tags"] = "";
	$this->conversation["lastActionTime"] = time();
	$this->saveTags($conversation["tags"]);

	// Star the conversation for the member.
	if ($conversation["starred"]) {
		$query = "INSERT INTO {$config["tablePrefix"]}status (conversationId, memberId, starred) VALUES ({$this->conversation["id"]}, {$this->eso->user["memberId"]}, 1) ON DUPLICATE KEY UPDATE starred=1";
		$this->eso->db->query($query);
	}

	// Save members allowed.
	if (is_array($conversation["membersAllowed"]) and count($conversation["membersAllowed"])) {
		$inserts = array();
		foreach ($conversation["membersAllowed"] as $memberId => $name) $inserts[] = "({$this->conversation["id"]}, '$memberId', 1)";
		$query = "INSERT INTO {$config["tablePrefix"]}status (conversationId, memberId, allowed) VALUES " . implode(",", $inserts) . " ON DUPLICATE KEY UPDATE allowed=1";
		$this->eso->db->query($query);
	}

	// Clear session data.
	unset($_SESSION["starred"], $_SESSION["membersAllowed"]);

	$this->callHook("afterStartConversation", array($conversationId));

	return $conversationId;
}

// Delete the conversation.
function deleteConversation()
{
	// Does the user have permission?
	if (!$this->canDeleteConversation()) return false;

	// Delete the conversation, statuses, posts, and tags from the database.
	global $config;
	$query = "DELETE c, s, p, t FROM {$config["tablePrefix"]}conversations c
		LEFT JOIN {$config["tablePrefix"]}status s ON (s.conversationId=c.conversationId)
		LEFT JOIN {$config["tablePrefix"]}posts p ON (p.conversationId=c.conversationId)
		LEFT JOIN {$config["tablePrefix"]}tags t ON (t.conversationId=c.conversationId)
		WHERE c.conversationId={$this->conversation["id"]}";
		
	$this->callHook("deleteConversation", array(&$query));
	
	$this->eso->db->query($query);
	$this->conversation["lastActionTime"] = time();
	
	return true;
}

// Add a member to the membersAllowed list.
function addMember($name)
{
	// Does the user have permission?
	if (($error = $this->canEditMembersAllowed()) !== true) {
		$this->eso->message($error);
		return false;
	}
	// Don't bother proceeding if $name is empty.
	if (!$name) return false;

	global $language, $config;
	
	// Allow a hook to set $memberId and $name.
	list($memberId, $memberName) = $this->callHook("findMemberAllowed", array($name), true);

	// If the hook didn't find anyone, then check if $name is actually a user group or a member name.
	if (!$memberId) {
		switch (strtolower($name)) {
			// Members
			case $language["Member-plural"]:
			case "members":
				$memberId = "Member";
				$memberName = $language["Member-plural"];
				break;
			// Moderators
			case $language["Moderator-plural"]:
			case "moderators":
				$memberId = "Moderator";
				$memberName = $language["Moderator-plural"];
				break;
			// Administrators
			case $language["Administrator-plural"]:
			case "administrators":
				$memberId = "Administrator";
				$memberName = $language["Administrator-plural"];
				break;
			// Otherwise, search for it in the database as an actual member name.
			default:
				$name = $this->eso->db->escape($name);
				if (!(list($memberId, $memberName) = @$this->eso->db->fetchRow("SELECT memberId, name FROM {$config["tablePrefix"]}members WHERE (name='$name' OR name LIKE '$name%') AND name NOT IN ('" . implode("','", $this->conversation["membersAllowed"]) . "') ORDER BY name='$name' DESC LIMIT 1"))) {
					$this->eso->message("memberDoesntExist");
					return false;
				}
		}
	}

	// If the conversation exists, add this member to the database as allowed.
	if ($this->conversation["id"]) {
		
		// Email the member(s) - we have to do this before we put them in the db because it will only email them if they
		// don't already have a record for this conversation in the status table.
		$this->emailPrivateAdd($memberId);

		// Set the conversation's private field to true and update the last action time.
		if (!$this->conversation["private"]) {
			$query = "UPDATE {$config["tablePrefix"]}conversations SET private=1 WHERE conversationId={$this->conversation["id"]}";
			$this->eso->db->query($query);
		}

		// Allow the member to view the conversation in the status table.
		$query = "INSERT INTO {$config["tablePrefix"]}status (conversationId, memberId, allowed) VALUES ({$this->conversation["id"]}, '$memberId', 1) ON DUPLICATE KEY UPDATE allowed=1";
		$this->eso->db->query($query);
	}

	// Update the membersAllowed array (which may in turn update the $_SESSION["membersAllowed"] array.)
	if (!is_array($this->conversation["membersAllowed"])) $this->conversation["membersAllowed"] = array();
	if (!array_key_exists($memberId, $this->conversation["membersAllowed"]))
		$this->conversation["membersAllowed"][$memberId] = $memberName;
		
	// Add the private label to the conversation.
	if (!in_array("private", $this->conversation["labels"])) $this->conversation["labels"][] = "private";
	$this->conversation["private"] = true;
	
	$this->callHook("afterAddMemberAllowed", array($memberId, $memberName));

	return true;
}

// Remove a member from the membersAllowed list.
function removeMember($memberId)
{
	// Does the user have permission?
	if (($error = $this->canEditMembersAllowed()) !== true) {
		$this->eso->message($error);
		return false;
	}
	global $config;

	// If the conversation exists, mark this member as not allowed in the conversation.
	if ($this->conversation["id"]) {
		$query = "UPDATE {$config["tablePrefix"]}status SET allowed=0 WHERE conversationId={$this->conversation["id"]} AND memberId='$memberId'";
		$this->eso->db->query($query);
	}

	// Update the membersAllowed and labels arrays (which may in turn update the $_SESSION["membersAllowed"] array.)
	if (is_array($this->conversation["membersAllowed"]) and array_key_exists($memberId, $this->conversation["membersAllowed"]))
		unset($this->conversation["membersAllowed"][$memberId]);
		
	// If there are no members left allowed in the conversation, then everyone can view the conversation.
	if (!is_array($this->conversation["membersAllowed"]) or !count($this->conversation["membersAllowed"])) {
		$this->conversation["membersAllowed"] = "Everyone";
		$this->conversation["private"] = false;
		if (($k = array_search("private", $this->conversation["labels"])) !== false) unset($this->conversation["labels"][$k]);
		
		// Turn off conversation's private field.
		if ($this->conversation["id"])  {
			$query = "UPDATE {$config["tablePrefix"]}conversations SET private=0 WHERE conversationId={$this->conversation["id"]}";
			$this->eso->db->query($query);
		}
	}
	
	$this->callHook("afterRemoveMemberAllowed", array($memberId));

	return true;
}

// Email members a notification saying that they have been added to a private conversation.
function emailPrivateAdd($memberIds, $emailAll = false)
{
	// If there are no posts in the conversation, don't email anyone!
	if ($this->conversation["postCount"] == 0) return false;
	
	global $config;
	$memberIds = (array)$memberIds;
	if (!count($memberIds)) return false;
	
	// Take the accounts mentioned in the list of members so we can use them separately in the query.
	$accounts = array_intersect(array("Member", "Moderator", "Administrator"), $memberIds);
	$memberIds = array_diff($memberIds, array("Member", "Moderator", "Administrator"));
	if (!count($memberIds)) $memberIds[] = 0;
	
	// Work out which members need to be emailed. Conditions: the member isn't themselves, the member name/account is in
	// our array, they've checked the 'email me' box in My settings, and the member musn't have a record in the status
	// table for this conversation if we're emailing ALL members in the conversation.
	$query = "SELECT DISTINCT name, email, language
		FROM {$config["tablePrefix"]}members m
		LEFT JOIN {$config["tablePrefix"]}status s ON (s.conversationId={$this->conversation["id"]} AND m.memberId=s.memberId)
		WHERE m.memberId!={$this->eso->user["memberId"]} AND (m.memberId IN (" . implode(",", $memberIds) . ") OR m.account IN ('" . implode("','", $accounts) . "')) AND m.emailOnPrivateAdd=1 " . (!$emailAll ? "AND s.memberId IS NULL" : "");
	$result = $this->eso->db->query($query);
	if (!$this->eso->db->numRows($result)) return false;
	
	// Send the email.
	global $versions;
	while (list($name, $email, $language) = $this->eso->db->fetchRow($result)) {
		include "languages/" . sanitizeFileName(file_exists("languages/$language.php") ? $language : $config["language"]) . ".php";
		$args = array($name, $this->conversation["title"], $config["baseURL"] . makeLink(conversationLink($this->conversation["id"], $this->conversation["slug"])));
		sendEmail($email, vsprintf($language["emails"]["privateAdd"]["subject"], $args), vsprintf($language["emails"]["privateAdd"]["body"], $args));
		unset($language, $messages);
	}
}

// Get the HTML for the list of members allowed.
function htmlMembersAllowedList($membersAllowed)
{
	global $language;
	
	// If there are members allowed, construct a list.
	if (is_array($membersAllowed) and count($membersAllowed)) {
		
		$count = count($membersAllowed);
		natcasesort($membersAllowed);
		
		// If the conversation starter's name is not in there, add it automatically.
		$html = !array_key_exists($this->conversation["startMember"], $membersAllowed) ? "{$this->conversation["startMemberName"]}, " : "";
		
		// Loop through each member.
		foreach ($membersAllowed as $memberId => $name) {
			if ($memberId === "Administrator" or $memberId === "Moderator" or $memberId === "Member") $name = $language[$memberId . "-plural"];
			
			// If the user can edit the list, output a link for this member.
			// However, if there is more than one member, the conversation starter can't be removed.
			if ($this->canEditMembersAllowed() and ($count == 1 or $memberId != $this->conversation["startMember"]))
				$html .= "<a href='" . makeLink(conversationLink($this->conversation["id"], $this->conversation["slug"]), "?removeMember=$memberId&token={$_SESSION["token"]}") . "' class='d' onclick='Conversation.removeMember(\"$memberId\");return false'>$name</a>, ";
				
			// Otherwise, plain text will do.
			else $html .= "$name, ";
		}
		
		$html = rtrim($html, ", ");
	
	// Otherwise, return "Everyone".
	} else $html = $language["Everyone"];
	
	$this->callHook("getMembersAllowedHTML", array(&$html, $membersAllowed));
	
	return $html;
}

// Save the conversation title.
function saveTitle($title)
{
	if (!$this->canEditTitle()) return "noPermission";
	if ($error = $this->validateTitle($title)) return $error;

	global $config;
	$slug = slug($title);
	$slashedTitle = $this->eso->db->escape($title);
	$query = "UPDATE {$config["tablePrefix"]}conversations c SET title='$slashedTitle', slug='$slug', lastActionTime=" . time() . " WHERE c.conversationId={$this->conversation["id"]}";

	$this->callHook("saveTitle", array(&$query, $title));
	
	$this->eso->db->query($query);
	$this->conversation["title"] = $this->title = $title;
	$this->conversation["slug"] = $slug;
	
	// Update the title column in the posts table as well (which is used for fulltext searching).
	$this->eso->db->query("UPDATE {$config["tablePrefix"]}posts p SET title='$slashedTitle' WHERE conversationId={$this->conversation["id"]}");
}

// Save the conversation tags.
function saveTags($tags)
{
	if (!$this->canEditTags()) return false;
	global $config;

	// What tags does this conversation already have? Which ones have been deleted? Which ones have been added?
	$newTags = explode(",", $tags);
	foreach ($newTags as $k => $v) {
		$formatted = $this->formatTag($v);
		if ($formatted) $newTags[$k] = $formatted;
		else unset($newTags[$k]);
	}
	$newTags = array_unique($newTags);
	$curTags = explode(", ", $this->conversation["tags"]);
	if (($k = array_search("", $curTags)) !== false) unset($curTags[$k]);
	$addTags = array_diff($newTags, $curTags);
	$delTags = array_diff($curTags, $newTags);

	// Up the count of added tags.
	if (count($addTags)) {
		$query = "INSERT INTO {$config["tablePrefix"]}tags VALUES ('" . implode($addTags, "', {$this->conversation["id"]}), ('") . "', {$this->conversation["id"]})";
		$this->eso->db->query($query);
	}

	// Lower the count of removed tags.
	if (count($delTags)) {
		$query = "DELETE FROM {$config["tablePrefix"]}tags WHERE tag IN ('" . implode($delTags, "', '") . "') AND conversationId={$this->conversation["id"]}";
		$this->eso->db->query($query);
	}

	// Update the conversation.
	if ($this->conversation["lastActionTime"] != time()) $this->eso->db->query("UPDATE {$config["tablePrefix"]}conversations SET lastActionTime=" . time() . " WHERE conversationId={$this->conversation["id"]}");
	$this->conversation["tags"] = implode(", ", $newTags);
	
	$this->callHook("saveTags", array($newTags, $addTags, $delTags));
}

// Convert a plain text tag string to have html links to respective tag searches.
function linkTags($tags)
{
	$tags = explode(", ", $tags);
	foreach ($tags as $k => $tag) $tags[$k] = "<a href='" . makeLink("search", "?q2=tag:$tag") . "'>$tag</a>";
	return implode(" ", $tags);
}

// Remove quotes from a post to prevent nested quotes when quoting the post.
function removeQuotes($post)
{
	while (preg_match("`(.*)<blockquote>.*?</blockquote>`", $post))
		$post = preg_replace("`(.*)<blockquote>.*?</blockquote>`", "$1", $post);
	return $post;
}

// Toggle sticky for this conversation.
function toggleSticky()
{
	if (!$this->canSticky()) return false;

	global $config;
	$query = "UPDATE {$config["tablePrefix"]}conversations SET sticky=(!sticky), lastActionTime=" . time() . " WHERE conversationId={$this->conversation["id"]}";
	
	$this->callHook("toggleSticky", array(&$query));
	
	$this->eso->db->query($query);
}

// Toggle locked for this conversation.
function toggleLock()
{
	if (!$this->canLock()) return false;

	global $config;
	$query = "UPDATE {$config["tablePrefix"]}conversations SET locked=(!locked), lastActionTime=" . time() . " WHERE conversationId={$this->conversation["id"]}";
	
	$this->callHook("toggleLock", array(&$query));
	
	$this->eso->db->query($query);
	$this->conversation["locked"] = !$this->conversation["locked"];
}

// Validate the title: make sure it has at least one character.
function validateTitle(&$title)
{
	$title = substr($title, 0, 63);
	if (!strlen($title)) return "emptyTitle";
//	if (strpos($title, '0') === 0) return "emptyTitle";
	return $this->callHook("validateTitle", array(&$title), true);
}

// Format a tag: convert to lowercase and strip unwanted characters.
function formatTag($tag)
{
	$tag = strtolower($tag);
	$tag = preg_replace(array("/[+-]/", "/ +/"), array("", " "), $tag);
	$tag = trim($tag);
	$tag = substr($tag, 0, 31);
	return $tag;
}

// Update the user's last action according to the conversation they are currently viewing.
function updateLastAction()
{
	global $language;
	$this->eso->updateLastAction("{$language["Viewing"]} " . (($this->conversation["private"] or $this->conversation["postCount"] == 0)
		? $language["a private conversation"]
		: "<a href='" . makeLink(conversationLink($this->conversation["id"], $this->conversation["slug"])) . "'>{$this->conversation["title"]}</a>"));
}

// To edit tags, user must be: conversation starter or >=moderator
function canEditTags()
{
	return $this->eso->user and ((!$this->conversation["locked"] and $this->conversation["startMember"] == $this->eso->user["memberId"]) or $this->eso->user["moderator"]);
}

// To edit members allowed, user must be: conversation starter
function canEditMembersAllowed()
{
	return $this->eso->user and $this->eso->user["memberId"] == $this->conversation["startMember"];
}

// To edit the title, user must be: conversation starter or >=moderator
function canEditTitle()
{
	return $this->eso->user and ((!$this->conversation["locked"] and $this->conversation["startMember"] == $this->eso->user["memberId"]) or $this->eso->user["moderator"]);
}

// To toggle sticky, user must be: >=moderator
function canSticky()
{
	return $this->eso->user and $this->eso->user["moderator"];
}

// To toggle lock, user must be: >=moderator
function canLock()
{
	return $this->eso->user and $this->eso->user["moderator"];
}

// To reply, user must be: logged in, not suspended, and the conversation can't be locked (unless user is >= moderator)
function canReply()
{
	if (!$this->eso->user) return "loginRequired";
	if ($this->eso->isUnvalidated()) return "noPermission";
	if ($this->eso->isSuspended()) return "suspended";
	if ($this->conversation["locked"] and !$this->eso->user["moderator"]) return "locked";
	return true;
}

// To edit a post, user must be: >=moderator or (post author and post not deleted by another member)
// Provide the post id or the post member, account, and deleteMember.
function canEditPost($postId, $memberId = false, $account = false, $deleteMember = -1)
{
	global $config;
	$postId = (int)$postId;
	if (!$memberId or !$account or $deleteMember === -1) list($memberId, $account, $deleteMember) = $this->eso->db->fetchRow("SELECT p.memberId, account, deleteMember FROM {$config["tablePrefix"]}posts p INNER JOIN {$config["tablePrefix"]}members USING (memberId) WHERE postId=$postId");
	
	if (!$this->eso->user or (!$this->eso->user["moderator"] and ($memberId != $this->eso->user["memberId"] or ($deleteMember and $deleteMember != $this->eso->user["memberId"]))) or ($account == "Administrator" and !$this->eso->user["admin"])) return "noPermission";
	if ($this->conversation["locked"] and !$this->eso->user["moderator"]) return "locked";
	if ($this->eso->isSuspended()) return "suspended";
	return true;
}

// To start a conversation, user must be: logged in and not suspended
function canStartConversation()
{
	if (!$this->eso->user) return "loginRequired";
	if ($this->eso->isUnvalidated()) return "noPermission";
	if ($this->eso->isSuspended()) return "suspended";
	return true;
}

// To delete a conversation, user must be: >=moderator
function canDeleteConversation()
{
	global $config;
	return $this->eso->user and ($this->eso->user["moderator"] or ($this->eso->user["memberId"] == $this->conversation["startMember"] and ($this->conversation["postCount"] <= 1 or !$this->eso->db->result("SELECT 1 FROM {$config["tablePrefix"]}posts WHERE conversationId={$this->conversation["id"]} AND memberId!={$this->eso->user["memberId"]}", 0))));
}

}

?>
