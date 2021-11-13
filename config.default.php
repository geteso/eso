<?php
// config.default.php
// Default configuration file.
// Don't edit this.  If you wish to change a setting, copy it into config/config.php and change it there.

if (!defined("IN_ESO")) exit;

define("ESO_VERSION", "1.0.0-p.2");

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
"salt" => "",
"emailFrom" => "",
"cookieName" => "",

// This following block may be filled out manually.
// Be careful when editing this.  You could break your forum.
"forumLogo" => false, // Path to an image file to replace the logo.  False for skin default.
"forumIcon" => false, // Same thing as before, but for the icon.
"sitemapCacheTime" => 3600, // Amount of time by which sitemaps are kept in cache.  (3600 seconds = 1 hour.)
"manifestCacheTime" => 3600, // Same thing as before, but for the web app manifest.
"manifestDisplay" => "standalone", // The preferred way to display your forum as a web app.  Standalone to behave like an app.
// see https://www.w3.org/TR/appmanifest/#dfn-display-modes-values for details
"verboseFatalErrors" => false, // Dumps SQL information in fatal errors.  Don't keep this enabled for production.
"basePath" => "", // The base path to use when including or writing to any files.
"gzipOutput" => true, // Whether or not to compress the page output.  Saves bandwith.
"showForumDescription" => true, // Whether or not to display the forum description on the homepage.

// Skins and plugins.
"skin" => "Plastic", // The default skin.  (This is overridden by config/skin.php.)
"plugins" => array("Captcha"), // A list of enabled plugins.  (This is overridden by config/plugins.php.)

// Login and registration settings.
"badLoginsPerMinute" => "10", // Amount of failed login attempts a user is limited to per minute.
"registrationOpen" => "true", // Whether or not new accounts can be made on your forum.
"registrationRequireVerification" => "email", // false | "email" = require email verification | "approval" = require admin approval

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
);

?>
