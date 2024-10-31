<?php
if (!isset($_SESSION)) @session_start();
//
define("SFB_PATH",			"../wp-content/plugins/sfbrowser/");		// path of sfbrowser (relative to the page it is run from)

// upload folder (relative to sfbpath)
if (defined('WP_SFB_BASE'))	$_SESSION['WP_SFB_BASE'] = WP_SFB_BASE;
define("SFB_BASE",			isset($_SESSION['WP_SFB_BASE'])?$_SESSION['WP_SFB_BASE']:"../../uploads/");

// the language ISO code
if (defined('WP_SFB_LANG'))	$_SESSION['WP_SFB_LANG'] = (file_exists(SFB_PATH."lang/".WP_SFB_LANG.".po"))?WP_SFB_LANG:"en_EN";
define("SFB_LANG",			isset($_SESSION['WP_SFB_LANG'])?$_SESSION['WP_SFB_LANG']:"en_US");

define("PREVIEW_BYTES",		600);				// ASCII preview ammount
define("SFB_DENY",			"php,php3,phtml");	// forbidden file extensions

define("FILETIME",			"j-n-Y H:i");		// file time display

define("SFB_ERROR_RETURN",	"<html><head><meta http-equiv=\"Refresh\" content=\"0;URL=http:/\" /></head></html>");

define("SFB_PLUGINS",		"imageresize,filetree,createascii,wp_db");

// debug
if (defined('WP_SFB_DEBUG'))	$_SESSION['WP_SFB_DEBUG'] = WP_SFB_DEBUG;
define("SFB_DEBUG",			isset($_SESSION['WP_SFB_DEBUG'])?$_SESSION['WP_SFB_DEBUG']:false);
?>