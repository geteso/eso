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
 * Default configuration: don't edit this.  If you wish to change a
 * config setting, copy it into config/config.php and change it there.
 */

// The version of the code.
define("ESO_VERSION", "1.0.0d2");

$defaultConfig = array(
// This following block is filled out by the installer in config/config.php.
"mysqlHost" => "",
"mysqlUser" => "",
"mysqlPass" => "",
"mysqlDB" => "",
"tablePrefix" => "",
"forumTitle" => "",
"forumDescription" => "",
"language" => "English (casual)",
"baseURL" => "",
"rootAdmin" => 1,
"emailFrom" => "",
"cookieName" => "",

// This following block may be filled out manually.
// Be careful when editing this.  You could break your forum.
"forumLogo" => false, // Path to an image file to replace the logo.  False for skin default.
"forumIcon" => false, // Same thing as before, but for the icon.
"sendEmail" => true, // Whether or not to send emails using the PHP mail() function.
"sitemapCacheTime" => 3600, // Amount of time by which sitemaps are kept in cache.  (3600 seconds = 1 hour.)
"manifestCacheTime" => 3600, // Same thing as before, but for the web app manifest.
"manifestDisplay" => "browser", // The preferred way to display your forum in or outside of a browser.  Fullscreen, standalone, minimal-ui, or browser.
// see https://www.w3.org/TR/mediaqueries-5/#display-mode for an explanation
"verboseFatalErrors" => false, // Dumps SQL information in fatal errors.  Don't keep this enabled for production.
"gzipOutput" => true, // Whether or not to compress the page output.  Saves bandwith.
"https" => false, // Whether or not to force HTTPS.
"showForumDescription" => true, // Whether or not to display the forum description on the homepage.
"useForumDescription" => false, // Whether or not to use the forum description instead of the forumDescription language string in meta tags.

// Skins and plugins.
"skin" => "Plastic", // The default skin.  (This is overridden by config/skin.php.)
"plugins" => array("Captcha"), // A list of enabled plugins.  (This is overridden by config/plugins.php.)
"uploadPackages" => true, // Whether or not admins can upload plugin and skin packages to your forum.  Don't keep this enabled if you don't trust your admins.
"themeColor" => false, // The suggested hex color that may accent the UI of a browser viewing your forum.  (ex. 4285f4)
// see https://developer.mozilla.org/en-US/docs/Web/HTML/Element/meta/name/theme-color for an explanation

// Login and registration settings.
"changeUsername" => true, // Whether or not accounts can change their usernames.
"loginsPerMinute" => "10", // Amount of login attempts a user is limited to per minute.
"registrationOpen" => true, // Whether or not new accounts can be made on your forum.
"registrationRequireEmail" => true, // Requires email verification to register on your forum.
"registrationRequireApproval" => false, // Requires manual approval...
"reservedNames" => array("guest", "member", "members", "moderator", "moderators", "administrator", "administrators", "admin", "suspended", "eso", "name", "password", "everyone", "myself"), // Reserved user names which cannot be used.

"useFriendlyURLs" => true, // ex. example.com/index.php/conversation/1
"usePrettyURLs" => false, // ex. example.com/conversation/1-welcome-to-simon-s-test-forum
"useModRewrite" => true, // ex. example.com/conversation/1 (requires mod_rewrite and a .htaccess file!)
"minPasswordLength" => 6,
"cookieExpire" => 2592000, // Amount of time by which cookies are kept.  (2592000 seconds = 30 days.)
"cookieDomain" => "", // Sets a custom cookie domain.  Set it to .yourdomain.com to have the cookie set across all subdomains.  Keep blank to use baseURL.
"nonAsciiCharacters" => true, // Whether or not usernames may contain non-printable characters (includes things like umlauts).
"userOnlineExpire" => 4500, // Amount of time by which a user's last seen time is before the user goes offline.  (300 seconds = 5 minutes.)
"messageDisplayTime" => 20, // Amount of time by which most messages floating above the navigation bar disappear.  (20 seconds = 20 seconds.  lol.)

"results" => 20, // Number of conversations to list for a normal search.
"moreResults" => 100, // Total number of conversations to list per every request for more results.
"numberOfTagsInTagCloud" => 40, // Number of tags to show in the tag cloud.
"showAvatarThumbnails" => true, // Whether or not to show avatar thumbnails next to each conversation.
"updateCurrentResultsInterval" => 30, // Amount of time by which conversations are updated (post information) in an existing search.  (30 seconds.)
"checkForNewResultsInterval" => 60, // Amount of time by which new conversations are checked for in an existing search.  (60 seconds.)
"searchesPerMinute" => 10, // Amount of searches a user is limited to per minute.

"postsPerPage" => 20, // Maximum number of posts to display on each page of a conversation.
"timeBetweenPosts" => 10, // Minimum number of seconds between a user's post.
"maxCharsPerPost" => 50000, // Maximum number of characters per post.
"autoReloadIntervalStart" => 4, // Initial number of seconds before checking for new posts on the conversation view.
"autoReloadIntervalMultiplier" => 1.5, // Each time we check for new posts and there are none, multiply the number of seconds by this.
// ex. after 4 seconds, check for new posts.  If there are none: after 4*1.5 = 6 seconds check for new posts.  If there are none: after 6*1.5 = 9 seconds check for new posts.
"autoReloadIntervalLimit" => 512, // Maximum number of seconds between checking for new posts. 

// Avatar dimensions in pixels.
"avatarMaxWidth" => 200,
"avatarMaxHeight" => 200,
"avatarThumbWidth" => 64,
"avatarThumbHeight" => 64,
"avatarAlignment" => "alternate", // Default alignment preference for avatars.  Alternate, left, right, or none.  Individual users can override this.
"changeAvatar" => true, // Whether or not to let users change their avatar.  Useful if your forum is tight on space or can't host images.
);

?>
